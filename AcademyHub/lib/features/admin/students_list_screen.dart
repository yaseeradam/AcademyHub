import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';

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
    setState(() => _loading = true);
    try {
      final auth = context.read<AuthProvider>();
      List<Map<String, dynamic>> list = [];
      try {
        final r = await auth.apiService.getWithCache('/students');
        list = ((r['data'] as List?) ?? []).cast<Map<String, dynamic>>();
        await _db.upsertStudents(list);
      } catch (_) {
        list = await _db.getAllStudents();
      }
      setState(() {
        _students = list;
        _loading = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
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
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Students Directory', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadStudents,
          ),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            color: Colors.white,
            padding: const EdgeInsets.all(16),
            child: TextField(
              decoration: InputDecoration(
                hintText: 'Search by student name or admission number...',
                prefixIcon: const Icon(Icons.search),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              ),
              onChanged: (val) => setState(() => _searchQuery = val),
            ),
          ),
          if (_loading) LinearProgressIndicator(color: primary),
          Expanded(
            child: filteredStudents.isEmpty
                ? const Center(child: Text('No students found.', style: TextStyle(color: Color(0xFF64748B))))
                : ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: filteredStudents.length,
                    separatorBuilder: (_, index) => const SizedBox(height: 10),
                    itemBuilder: (context, i) {
                      final s = filteredStudents[i];
                      final name = '${s['first_name'] ?? ''} ${s['last_name'] ?? ''}';
                      final admNo = s['admission_number'] ?? '';
                      final cls = s['school_class']?['name'] ?? s['section']?['name'] ?? 'Class info cached';

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
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: const Color(0xFFF1F5F9)),
                            boxShadow: const [BoxShadow(color: Color(0x05000000), blurRadius: 4, offset: Offset(0, 2))],
                          ),
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 22,
                                backgroundColor: primary.withValues(alpha: 0.1),
                                child: Text(
                                  name.isNotEmpty ? name[0].toUpperCase() : '?',
                                  style: TextStyle(color: primary, fontWeight: FontWeight.bold, fontSize: 18),
                                ),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
                                    const SizedBox(height: 4),
                                    Text(cls, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                                    Text(admNo, style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
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
