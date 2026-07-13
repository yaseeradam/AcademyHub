import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/constants.dart';

class StudentsListScreen extends StatefulWidget {
  const StudentsListScreen({super.key});

  @override
  State<StudentsListScreen> createState() => _StudentsListScreenState();
}

class _StudentsListScreenState extends State<StudentsListScreen> {
  final _db = DatabaseHelper();
  List<Map<String, dynamic>> _students = [];
  bool _loading = true;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _loadStudents();
  }

  Future<void> _loadStudents() async {
    // Load from local DB first — instant, shows UI immediately
    try {
      final localList = await _db.getAllStudents();
      if (mounted) {
        setState(() {
          _students = localList;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }

    // Silently refresh from network
    try {
      final auth = context.read<AuthProvider>();
      final r = await auth.apiService.getWithCache('/students');
      final list = ((r['data'] as List?) ?? []).cast<Map<String, dynamic>>();
      await _db.upsertStudents(list);
      if (mounted) setState(() => _students = list);
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;

    final filteredStudents = _students.where((s) {
      final firstName = (s['first_name'] as String? ?? '').toLowerCase();
      final lastName = (s['last_name'] as String? ?? '').toLowerCase();
      final admNo = (s['admission_number'] as String? ?? '').toLowerCase();
      final query = _searchQuery.toLowerCase();
      return firstName.contains(query) || lastName.contains(query) || admNo.contains(query);
    }).toList();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Students Directory', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary)),
        backgroundColor: AppColors.surface,
        foregroundColor: AppColors.textPrimary,
        elevation: 0,
        shape: Border(bottom: BorderSide(color: AppColors.borderLight)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _loadStudents,
          ),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            color: AppColors.surface,
            padding: const EdgeInsets.all(16),
            child: TextField(
              style: GoogleFonts.inter(color: AppColors.textPrimary, fontSize: 14),
              decoration: InputDecoration(
                hintText: 'Search by student name or admission number...',
                hintStyle: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 14),
                prefixIcon: Icon(Icons.search_rounded, color: AppColors.textSecondary),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: AppColors.borderLight)),
                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: AppColors.borderLight)),
                focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: primary, width: 1.5)),
                fillColor: AppColors.surface2,
                filled: true,
              ),
              onChanged: (val) => setState(() => _searchQuery = val),
            ),
          ),
          if (_loading) LinearProgressIndicator(color: primary, minHeight: 2),
          Expanded(
            child: filteredStudents.isEmpty
                ? Center(child: Text('No students found.', style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 14)))
                : ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: filteredStudents.length,
                    separatorBuilder: (_, index) => const SizedBox(height: 10),
                    itemBuilder: (context, i) {
                      final s = filteredStudents[i];
                      final name = '${s['first_name'] ?? ''} ${s['last_name'] ?? ''}'.trim();
                      final admNo = s['admission_number'] ?? '';
                      final cls = s['school_class']?['name'] ?? s['section']?['name'] ?? 'Class info cached';
                      final initial = name.isNotEmpty ? name[0].toUpperCase() : '?';

                      return GestureDetector(
                        onTap: () {
                          context.push('/performance', extra: {
                            'studentId': s['id'],
                            'studentName': name,
                            'admissionNumber': admNo,
                          });
                        },
                        child: Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: AppColors.borderLight),
                          ),
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 22,
                                backgroundColor: primary.withValues(alpha: 0.12),
                                child: Text(
                                  initial,
                                  style: GoogleFonts.inter(color: primary, fontWeight: FontWeight.bold, fontSize: 18),
                                ),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(name, style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary)),
                                    const SizedBox(height: 4),
                                    Text(cls, style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary)),
                                    Text(admNo, style: GoogleFonts.inter(fontSize: 11, color: AppColors.textSecondary)),
                                  ],
                                ),
                              ),
                              Icon(Icons.bar_chart_rounded, color: primary, size: 20),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
