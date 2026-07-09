import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

class AdminSessionsScreen extends StatefulWidget {
  const AdminSessionsScreen({super.key});

  @override
  State<AdminSessionsScreen> createState() => _AdminSessionsScreenState();
}

class _AdminSessionsScreenState extends State<AdminSessionsScreen> {
  List<dynamic> _sessions = [];
  List<dynamic> _terms = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final auth = context.read<AuthProvider>();
    setState(() => _loading = true);

    try {
      final sessionsData = await auth.apiService.getWithCache('/admin/sessions');
      final termsData = await auth.apiService.getWithCache('/admin/terms');

      if (mounted) {
        setState(() {
          _sessions = sessionsData as List;
          _terms = termsData as List;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        CustomToast.show(context: context, message: 'Error loading configurations: $e', type: 'error');
      }
    }
  }

  Future<void> _toggleSessionActive(int id, bool currentStatus) async {
    final auth = context.read<AuthProvider>();
    try {
      await auth.apiService.dio.put('/admin/sessions/$id', data: {
        'is_active': !currentStatus,
        'name': _sessions.firstWhere((s) => s['id'] == id)['name'],
      });
      _loadData();
      if (mounted) {
        CustomToast.show(context: context, message: 'Session status updated.', type: 'success');
      }
    } catch (e) {
      if (mounted) {
        CustomToast.show(context: context, message: 'Failed to toggle status: $e', type: 'error');
      }
    }
  }

  Future<void> _showSessionForm({Map<String, dynamic>? session}) async {
    final nameController = TextEditingController(text: session?['name'] ?? '');
    bool isActive = session?['is_active'] == true;
    final authProvider = context.read<AuthProvider>();

    final result = await showDialog<bool>(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          backgroundColor: AppColors.surface,
          title: Text(session == null ? 'Create Session' : 'Edit Session',
              style: GoogleFonts.inter(fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nameController,
                style: GoogleFonts.inter(color: AppColors.textPrimary),
                decoration: InputDecoration(
                  labelText: 'Session Name (e.g. 2026/2027)',
                  labelStyle: GoogleFonts.inter(color: AppColors.textSecondary),
                  enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: AppColors.borderLight)),
                ),
              ),
              const SizedBox(height: 12),
              CheckboxListTile(
                title: Text('Active Session?', style: GoogleFonts.inter(color: AppColors.textPrimary)),
                value: isActive,
                onChanged: (val) {
                  if (val != null) {
                    setDialogState(() => isActive = val);
                  }
                },
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: Text('Cancel', style: GoogleFonts.inter(color: AppColors.textSecondary)),
            ),
            ElevatedButton(
              onPressed: () => Navigator.pop(context, true),
              style: ElevatedButton.styleFrom(backgroundColor: context.read<AuthProvider>().tenantPrimaryColor),
              child: Text('Save', style: GoogleFonts.inter(color: Colors.black)),
            ),
          ],
        ),
      ),
    );

    if (result == true) {
      final auth = authProvider;
      try {
        if (session == null) {
          await auth.apiService.dio.post('/admin/sessions', data: {
            'name': nameController.text.trim(),
            'is_active': isActive,
          });
        } else {
          await auth.apiService.dio.put('/admin/sessions/${session['id']}', data: {
            'name': nameController.text.trim(),
            'is_active': isActive,
          });
        }
        _loadData();
      } catch (e) {
        if (mounted) {
          CustomToast.show(context: context, message: 'Failed to save session: $e', type: 'error');
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;

    return DefaultTabController(
      length: 2,
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          title: Text('Academic Settings',
              style: GoogleFonts.inter(
                  fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
          backgroundColor: AppColors.surface,
          elevation: 0,
          iconTheme: IconThemeData(color: AppColors.textPrimary),
          bottom: TabBar(
            labelColor: primary,
            unselectedLabelColor: AppColors.textSecondary,
            indicatorColor: primary,
            dividerColor: AppColors.borderLight,
            tabs: const [
              Tab(text: 'Sessions'),
              Tab(text: 'Terms'),
            ],
          ),
        ),
        body: _loading
            ? const Center(child: CircularProgressIndicator())
            : TabBarView(
                children: [
                  _buildSessionsTab(primary),
                  _buildTermsTab(primary),
                ],
              ),
      ),
    );
  }

  Widget _buildSessionsTab(Color primary) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Academic Sessions',
                style: GoogleFonts.inter(
                    fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
            ElevatedButton.icon(
              onPressed: () => _showSessionForm(),
              style: ElevatedButton.styleFrom(backgroundColor: primary, foregroundColor: Colors.black),
              icon: const Icon(Icons.add, size: 16),
              label: Text('New Session', style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
        const SizedBox(height: 12),
        ..._sessions.map((s) {
          final active = s['is_active'] == true;
          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.borderLight),
            ),
            child: Row(
              children: [
                Icon(Icons.calendar_today, color: active ? primary : AppColors.textMuted),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(s['name'] ?? '',
                          style: GoogleFonts.inter(
                              fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary)),
                      Text(active ? 'Active' : 'Inactive',
                          style: GoogleFonts.inter(fontSize: 11, color: active ? primary : AppColors.textMuted)),
                    ],
                  ),
                ),
                Switch(
                  value: active,
                  activeThumbColor: primary,
                  onChanged: (val) => _toggleSessionActive(s['id'] as int, active),
                ),
                IconButton(
                  icon: const Icon(Icons.edit, size: 18),
                  onPressed: () => _showSessionForm(session: s),
                )
              ],
            ),
          );
        }),
      ],
    );
  }

  Widget _buildTermsTab(Color primary) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Academic Terms',
                style: GoogleFonts.inter(
                    fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
          ],
        ),
        const SizedBox(height: 12),
        ..._terms.map((t) {
          final active = t['is_active'] == true;
          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.borderLight),
            ),
            child: Row(
              children: [
                Icon(Icons.school_outlined, color: active ? primary : AppColors.textMuted),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(t['name'] ?? '',
                          style: GoogleFonts.inter(
                              fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary)),
                      Text('Session: ${t['academic_session']?['name'] ?? ''}',
                          style: GoogleFonts.inter(fontSize: 11, color: AppColors.textSecondary)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: active ? primary.withValues(alpha: 0.15) : AppColors.surface2,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(active ? 'Active' : 'Inactive',
                      style: GoogleFonts.inter(
                          fontSize: 10, fontWeight: FontWeight.bold, color: active ? primary : AppColors.textMuted)),
                ),
              ],
            ),
          );
        }),
      ],
    );
  }
}
