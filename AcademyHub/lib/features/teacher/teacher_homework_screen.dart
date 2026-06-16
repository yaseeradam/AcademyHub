import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

class TeacherHomeworkScreen extends StatefulWidget {
  const TeacherHomeworkScreen({super.key});

  @override
  State<TeacherHomeworkScreen> createState() => _TeacherHomeworkScreenState();
}

class _TeacherHomeworkScreenState extends State<TeacherHomeworkScreen> {
  final _db = DatabaseHelper();
  List<Map<String, dynamic>> _classes  = [];
  List<Map<String, dynamic>> _subjects = [];
  List<Map<String, dynamic>> _homework = [];
  int? _selectedClassId;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final auth = context.read<AuthProvider>();
    if (!mounted) return;
    setState(() => _loading = true);
    try {
      final r = await auth.apiService.getWithCache('/teacher/classes');
      if (mounted) {
        _classes = (r['data'] as List).cast<Map<String, dynamic>>();
      }
    } catch (_) {}
    final hw = await _db.getAllHomework();
    if (mounted) {
      setState(() { _homework = hw; _loading = false; });
    }
  }

  Future<void> _loadForClass(int classId) async {
    final auth = context.read<AuthProvider>();
    setState(() { _selectedClassId = classId; _loading = true; });
    try {
      final r = await auth.apiService.getWithCache('/teacher/classes/$classId/subjects');
      _subjects = (r['data'] as List).cast<Map<String, dynamic>>();
    } catch (_) {
      _subjects = await _db.getSubjectsByClass(classId);
    }
    final hw = await _db.getHomeworkByClass(classId);
    if (mounted) {
      setState(() { _homework = hw; _loading = false; });
    }
  }

  void _showCreateSheet(Color accent) {
    if (_selectedClassId == null) {
      CustomToast.show(
        context: context,
        message: 'Select a class first before creating homework',
        type: 'warning',
      );
      return;
    }
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => _CreateHomeworkSheet(
        classId: _selectedClassId!,
        subjects: _subjects,
        accentColor: accent,
        onCreated: () { Navigator.pop(context); _loadForClass(_selectedClassId!); },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final accent = auth.tenantPrimaryColor;

    return MobileLayout(
      title: 'Homework Assignments',
      child: Container(
        color: AppColors.background,
        child: Column(
          children: [
            _buildClassFilter(),
            if (_loading) LinearProgressIndicator(color: accent, minHeight: 2),
            Expanded(child: _homework.isEmpty ? _buildEmpty() : _buildList(accent)),
            _buildFab(accent),
          ],
        ),
      ),
    );
  }

  Widget _buildClassFilter() => Container(
        decoration: BoxDecoration(
          color: AppColors.surface,
          border: Border(bottom: BorderSide(color: AppColors.borderLight)),
        ),
        padding: const EdgeInsets.all(16),
        child: DropdownButtonFormField<int>(
          decoration: InputDecoration(
            labelText: 'Class',
            labelStyle: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: AppColors.borderLight)),
            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: AppColors.borderLight)),
            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: AppColors.borderLight)),
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            isDense: true,
            fillColor: AppColors.surface2,
            filled: true,
          ),
          dropdownColor: AppColors.surface2,
          initialValue: _selectedClassId,
          style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontSize: 14),
          items: _classes.map((c) => DropdownMenuItem<int>(
            value: c['id'] as int,
            child: Text(c['name'] as String, style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary)),
          )).toList(),
          onChanged: (v) { if (v != null) _loadForClass(v); },
        ),
      );

  Widget _buildList(Color accent) => ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: _homework.length,
        separatorBuilder: (_, _) => const SizedBox(height: 8),
        itemBuilder: (context, i) {
          final h       = _homework[i];
          final isDirty = (h['is_dirty'] as int? ?? 0) == 1;
          final due     = h['due_date'] as String? ?? '';
          return Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: isDirty 
                    ? AppColors.warning.withValues(alpha: 0.4) 
                    : AppColors.borderLight,
              ),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.parentAccent.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.assignment_rounded, color: AppColors.parentAccent, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(children: [
                        Expanded(child: Text(h['title'] ?? '', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.w600, fontSize: 14, color: AppColors.textPrimary))),
                        if (isDirty) const Icon(Icons.cloud_off, size: 14, color: AppColors.warning),
                      ]),
                      Text(h['subject_name'] ?? '', style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary)),
                      Text('Due: $due', style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary)),
                    ],
                  ),
                ),
                if (h['id'] != null)
                  IconButton(
                    icon: Icon(Icons.people_outline_rounded, color: accent, size: 20),
                    onPressed: () => _viewSubmissions(h),
                  ),
              ],
            ),
          );
        },
      );

  void _viewSubmissions(Map<String, dynamic> hw) {
    Navigator.push(context, MaterialPageRoute(
      builder: (_) => _SubmissionsScreen(homeworkId: hw['id'] as int, title: hw['title'] as String),
    ));
  }

  Widget _buildEmpty() => Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.assignment_outlined, size: 48, color: AppColors.textMuted),
            const SizedBox(height: 12),
            Text('No homework assignments yet', style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 14)),
            const SizedBox(height: 8),
            if (_selectedClassId != null)
              TextButton(
                onPressed: () => _showCreateSheet(context.read<AuthProvider>().tenantPrimaryColor),
                child: Text('Create first homework', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, color: AppColors.parentAccent)),
              ),
          ],
        ),
      );

  Widget _buildFab(Color accent) => Padding(
        padding: const EdgeInsets.all(16),
        child: SizedBox(
          width: double.infinity,
          height: 52,
          child: ElevatedButton.icon(
            onPressed: () => _showCreateSheet(accent),
            icon: const Icon(Icons.add, color: Colors.black, size: 18),
            label: Text('New Homework', style: GoogleFonts.spaceGrotesk(color: Colors.black, fontWeight: FontWeight.bold, fontSize: 15)),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.parentAccent,
              foregroundColor: Colors.black,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
        ),
      );
}

// ─── Create Homework Bottom Sheet ─────────────────────────────────────────────

class _CreateHomeworkSheet extends StatefulWidget {
  final int classId;
  final List<Map<String, dynamic>> subjects;
  final Color accentColor;
  final VoidCallback onCreated;

  const _CreateHomeworkSheet({
    required this.classId, 
    required this.subjects, 
    required this.accentColor,
    required this.onCreated,
  });

  @override
  State<_CreateHomeworkSheet> createState() => _CreateHomeworkSheetState();
}

class _CreateHomeworkSheetState extends State<_CreateHomeworkSheet> {
  final _db          = DatabaseHelper();
  final _titleCtrl   = TextEditingController();
  final _contentCtrl = TextEditingController();
  int?   _subjectId;
  String _dueDate    = DateTime.now().add(const Duration(days: 7)).toIso8601String().substring(0, 10);
  bool   _saving     = false;

  @override
  void dispose() {
    _titleCtrl.dispose();
    _contentCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (_titleCtrl.text.isEmpty || _contentCtrl.text.isEmpty || _subjectId == null) {
      CustomToast.show(
        context: context,
        message: 'Please fill in all homework details',
        type: 'warning',
      );
      return;
    }
    setState(() => _saving = true);
    final auth        = context.read<AuthProvider>();
    final subjectName = widget.subjects.firstWhere((s) => s['id'] == _subjectId, orElse: () => {})['name'] ?? '';

    // Save locally first
    await _db.saveHomeworkLocally(
      classId: widget.classId, subjectId: _subjectId!,
      teacherId: auth.user!.id, title: _titleCtrl.text.trim(),
      content: _contentCtrl.text.trim(), dueDate: _dueDate,
      subjectName: subjectName,
    );

    // Notify + try immediate sync
    await auth.syncService.notifyDirty();
    await auth.syncService.syncNow();

    if (mounted) {
      setState(() => _saving = false);
      CustomToast.show(
        context: context,
        message: 'Homework published successfully!',
        type: 'success',
      );
      widget.onCreated();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.surface,
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom, left: 20, right: 20, top: 20),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Center(
              child: Container(
                width: 36,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(color: AppColors.borderLight, borderRadius: BorderRadius.circular(2)),
              ),
            ),
            Text('New Homework Assignment', style: GoogleFonts.spaceGrotesk(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
            const SizedBox(height: 16),
            DropdownButtonFormField<int>(
              decoration: _dec('Subject'),
              dropdownColor: AppColors.surface2,
              initialValue: _subjectId,
              style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontSize: 14),
              items: widget.subjects.map((s) => DropdownMenuItem<int>(
                value: s['id'] as int,
                child: Text(s['name'] as String, style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary)),
              )).toList(),
              onChanged: (v) => setState(() => _subjectId = v),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _titleCtrl, 
              style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontSize: 14),
              decoration: _dec('Title'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _contentCtrl, 
              style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontSize: 14),
              decoration: _dec('Instructions / Requirements'), 
              maxLines: 4,
            ),
            const SizedBox(height: 12),
            InkWell(
              onTap: () async {
                final picked = await showDatePicker(
                  context: context,
                  initialDate: DateTime.now().add(const Duration(days: 7)),
                  firstDate: DateTime.now(),
                  lastDate: DateTime.now().add(const Duration(days: 365)),
                  builder: (context, child) {
                    return Theme(
                      data: Theme.of(context).copyWith(
                        colorScheme: ColorScheme.dark(
                          primary: widget.accentColor,
                          onPrimary: Colors.black,
                          surface: AppColors.surface,
                          onSurface: AppColors.textPrimary,
                        ),
                      ),
                      child: child!,
                    );
                  },
                );
                if (picked != null) setState(() => _dueDate = picked.toIso8601String().substring(0, 10));
              },
              child: InputDecorator(
                decoration: _dec('Due Date'),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(_dueDate, style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontSize: 14)),
                    Icon(Icons.calendar_today_rounded, size: 16, color: widget.accentColor),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                onPressed: _saving ? null : _save,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.parentAccent,
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: _saving
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(color: Colors.black, strokeWidth: 2),
                      )
                    : Text('Save Homework', style: GoogleFonts.spaceGrotesk(color: Colors.black, fontWeight: FontWeight.bold, fontSize: 15)),
              ),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  InputDecoration _dec(String label) => InputDecoration(
        labelText: label,
        labelStyle: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 12),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: AppColors.borderLight)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: AppColors.borderLight)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.parentAccent, width: 1.5)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        isDense: true,
        fillColor: AppColors.surface2,
        filled: true,
      );
}

// ─── Submissions Screen ────────────────────────────────────────────────────────

class _SubmissionsScreen extends StatefulWidget {
  final int homeworkId;
  final String title;
  const _SubmissionsScreen({required this.homeworkId, required this.title});

  @override
  State<_SubmissionsScreen> createState() => _SubmissionsScreenState();
}

class _SubmissionsScreenState extends State<_SubmissionsScreen> {
  List<dynamic> _submissions = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final auth = context.read<AuthProvider>();
    try {
      final r = await auth.apiService.getWithCache('/homework/${widget.homeworkId}/submissions');
      if (mounted) {
        setState(() { _submissions = r['data'] ?? []; _loading = false; });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final accent = auth.tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(widget.title, style: GoogleFonts.spaceGrotesk(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
        backgroundColor: AppColors.surface,
        foregroundColor: AppColors.textPrimary,
        elevation: 0,
        shape: Border(bottom: BorderSide(color: AppColors.borderLight)),
      ),
      body: _loading
          ? Center(child: CircularProgressIndicator(color: accent))
          : _submissions.isEmpty
              ? Center(child: Text('No submissions yet', style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 14)))
              : ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: _submissions.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 8),
                  itemBuilder: (context, i) {
                    final s    = _submissions[i];
                    final name = '${s['student']?['first_name'] ?? ''} ${s['student']?['last_name'] ?? ''}'.trim();
                    return Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.borderLight),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(children: [
                            Expanded(child: Text(name, style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary))),
                            if (s['grade'] != null)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(color: AppColors.success.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(6)),
                                child: Text(s['grade'], style: GoogleFonts.spaceGrotesk(color: AppColors.success, fontWeight: FontWeight.bold, fontSize: 11)),
                              ),
                          ]),
                          const SizedBox(height: 6),
                          Text(s['submission'] ?? '', style: GoogleFonts.spaceGrotesk(fontSize: 13, color: AppColors.textSecondary)),
                          if (s['feedback'] != null) ...[
                            const SizedBox(height: 6),
                            Text('Feedback: ${s['feedback']}', style: GoogleFonts.spaceGrotesk(fontSize: 12, color: AppColors.textSecondary, fontStyle: FontStyle.italic)),
                          ],
                        ],
                      ),
                    );
                  },
                ),
    );
  }
}
