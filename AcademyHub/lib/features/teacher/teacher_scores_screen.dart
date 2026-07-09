import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

class TeacherScoresScreen extends StatefulWidget {
  const TeacherScoresScreen({super.key});

  @override
  State<TeacherScoresScreen> createState() => _TeacherScoresScreenState();
}

class _TeacherScoresScreenState extends State<TeacherScoresScreen> {
  final _db = DatabaseHelper();

  List<Map<String, dynamic>> _classes = [];
  List<Map<String, dynamic>> _subjects = [];
  List<Map<String, dynamic>> _students = [];

  // studentId -> {ca1, ca2, exam} controllers
  final Map<int, Map<String, TextEditingController>> _controllers = {};

  int? _selectedClassId;
  int? _selectedSubjectId;
  int _term = 1;
  String _session = '';
  bool _loading = false;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _loadTermThenClasses();
  }

  Future<void> _loadTermThenClasses() async {
    final auth = context.read<AuthProvider>();
    try {
      final termData = await auth.apiService.getWithCache('/term');
      _term    = termData['term'] ?? 1;
      _session = termData['session'] ?? '';
    } catch (_) {}
    _loadClasses();
  }

  @override
  void dispose() {
    for (final map in _controllers.values) {
      for (var c in map.values) {
        c.dispose();
      }
    }
    super.dispose();
  }

  Future<void> _loadClasses() async {
    final auth = context.read<AuthProvider>();
    if (!mounted) return;
    setState(() => _loading = true);
    try {
      final data = await auth.apiService.getWithCache('/teacher/classes');
      if (mounted) {
        setState(() => _classes = (data['data'] as List).cast<Map<String, dynamic>>());
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _onClassChanged(int classId) async {
    final auth = context.read<AuthProvider>();
    setState(() {
      _selectedClassId = classId;
      _selectedSubjectId = null;
      _students = [];
      _subjects = [];
      _controllers.clear();
    });

    // Load subjects
    try {
      final data = await auth.apiService.getWithCache('/teacher/classes/$classId/subjects');
      final list = (data['data'] as List).cast<Map<String, dynamic>>();
      await _db.upsertSubjects(classId, list);
      if (mounted) setState(() => _subjects = list);
    } catch (_) {
      final list = await _db.getSubjectsByClass(classId);
      if (mounted) setState(() => _subjects = list);
    }

    // Load students
    try {
      final data = await auth.apiService.getWithCache('/teacher/classes/$classId/students');
      final list = (data['data'] as List).cast<Map<String, dynamic>>();
      await _db.upsertStudents(list);
      if (mounted) setState(() => _students = list);
    } catch (_) {
      final list = await _db.getStudentsByClass(classId);
      if (mounted) setState(() => _students = list);
    }
  }

  Future<void> _onSubjectChanged(int subjectId) async {
    setState(() => _selectedSubjectId = subjectId);
    _controllers.clear();

    // Load existing scores
    final scores = await _db.getScores(_selectedClassId!, _term, _session);
    final scoreMap = <int, Map<String, dynamic>>{};
    for (final s in scores) {
      if (s['subject_id'] == subjectId) {
        scoreMap[s['student_id'] as int] = s;
      }
    }

    for (final student in _students) {
      final id = student['id'] as int;
      final existing = scoreMap[id];
      _controllers[id] = {
        'ca1': TextEditingController(text: existing != null ? '${existing['ca1'] ?? ''}' : ''),
        'ca2': TextEditingController(text: existing != null ? '${existing['ca2'] ?? ''}' : ''),
        'exam': TextEditingController(text: existing != null ? '${existing['exam'] ?? ''}' : ''),
      };
    }
    setState(() {});
  }

  Future<void> _save() async {
    if (_selectedClassId == null || _selectedSubjectId == null) return;
    setState(() => _saving = true);

    final auth = context.read<AuthProvider>();

    for (final student in _students) {
      final id = student['id'] as int;
      final c = _controllers[id];
      if (c == null) continue;
      final ca1 = int.tryParse(c['ca1']!.text) ?? 0;
      final ca2 = int.tryParse(c['ca2']!.text) ?? 0;
      final exam = int.tryParse(c['exam']!.text) ?? 0;

      await _db.saveScoreLocally(id, _selectedSubjectId!, _selectedClassId!, _term, _session, ca1, ca2, exam);
    }

    await auth.syncService.notifyDirty();
    await auth.syncService.syncNow();

    if (mounted) {
      setState(() => _saving = false);
      CustomToast.show(
        context: context,
        message: 'Student scores saved successfully',
        type: 'success',
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final accent = auth.tenantPrimaryColor;

    return MobileLayout(
      title: 'Score Entry',
      child: Container(
        color: AppColors.background,
        child: Column(
          children: [
            _buildFilters(accent),
            if (_loading) LinearProgressIndicator(color: accent, minHeight: 2),
            Expanded(child: _buildContent(accent)),
            if (_students.isNotEmpty && _selectedSubjectId != null) _buildSaveButton(),
          ],
        ),
      ),
    );
  }

  Widget _buildFilters(Color accent) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border(bottom: BorderSide(color: AppColors.borderLight)),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Row(
            children: [
              Expanded(
                child: DropdownButtonFormField<int>(
                  decoration: _inputDecoration('Class'),
                  dropdownColor: AppColors.surface2,
                  initialValue: _selectedClassId,
                  style: GoogleFonts.inter(color: AppColors.textPrimary, fontSize: 14),
                  items: _classes.map((c) => DropdownMenuItem<int>(
                    value: c['id'] as int,
                    child: Text(c['name'] as String, style: GoogleFonts.inter(color: AppColors.textPrimary)),
                  )).toList(),
                  onChanged: (v) { if (v != null) _onClassChanged(v); },
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: DropdownButtonFormField<int>(
                  decoration: _inputDecoration('Term'),
                  dropdownColor: AppColors.surface2,
                  initialValue: _term,
                  style: GoogleFonts.inter(color: AppColors.textPrimary, fontSize: 14),
                  items: [
                    DropdownMenuItem(value: 1, child: Text('Term 1', style: TextStyle(color: AppColors.textPrimary))),
                    DropdownMenuItem(value: 2, child: Text('Term 2', style: TextStyle(color: AppColors.textPrimary))),
                    DropdownMenuItem(value: 3, child: Text('Term 3', style: TextStyle(color: AppColors.textPrimary))),
                  ],
                  onChanged: (v) => setState(() => _term = v ?? 1),
                ),
              ),
            ],
          ),
          if (_subjects.isNotEmpty) ...[
            const SizedBox(height: 12),
            DropdownButtonFormField<int>(
              decoration: _inputDecoration('Subject'),
              dropdownColor: AppColors.surface2,
              initialValue: _selectedSubjectId,
              style: GoogleFonts.inter(color: AppColors.textPrimary, fontSize: 14),
              items: _subjects.map((s) => DropdownMenuItem<int>(
                value: s['id'] as int,
                child: Text(s['name'] as String, style: GoogleFonts.inter(color: AppColors.textPrimary)),
              )).toList(),
              onChanged: (v) { if (v != null) _onSubjectChanged(v); },
            ),
          ],
          if (_selectedClassId != null && _selectedSubjectId != null) ...[
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              height: 38,
              child: OutlinedButton.icon(
                onPressed: () {
                  final clsName = _classes.firstWhere((c) => c['id'] == _selectedClassId)['name'] ?? 'Class';
                  final subName = _subjects.firstWhere((s) => s['id'] == _selectedSubjectId)['name'] ?? 'Subject';
                  context.push('/csv-import', extra: {
                    'classId': _selectedClassId,
                    'subjectId': _selectedSubjectId,
                    'className': clsName,
                    'subjectName': subName,
                  });
                },
                icon: Icon(Icons.upload_file, size: 16, color: accent),
                label: Text('Import Scores from CSV', style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold, color: accent)),
                style: OutlinedButton.styleFrom(
                  side: BorderSide(color: accent),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildContent(Color accent) {
    if (_selectedSubjectId == null || _students.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.edit_note_rounded, size: 48, color: AppColors.textMuted),
            const SizedBox(height: 12),
            Text(
              'Select a class and subject to enter grades',
              style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 14),
            ),
          ],
        ),
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _students.length,
      separatorBuilder: (_, _) => const SizedBox(height: 10),
      itemBuilder: (context, i) {
        final s = _students[i];
        final id = s['id'] as int;
        final c = _controllers[id];
        if (c == null) return const SizedBox.shrink();

        return Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('${s['first_name'] ?? ''} ${s['last_name'] ?? ''}', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary)),
              const SizedBox(height: 2),
              Text(s['admission_number'] ?? '', style: GoogleFonts.inter(fontSize: 11, color: AppColors.textSecondary)),
              const SizedBox(height: 14),
              Row(
                children: [
                  _scoreField(c['ca1']!, 'CA1'),
                  const SizedBox(width: 8),
                  _scoreField(c['ca2']!, 'CA2'),
                  const SizedBox(width: 8),
                  _scoreField(c['exam']!, 'Exam'),
                  const SizedBox(width: 12),
                  // Live total
                  ValueListenableBuilder(
                    valueListenable: c['ca1']!,
                    builder: (_, _, _) => ValueListenableBuilder(
                      valueListenable: c['ca2']!,
                      builder: (_, _, _) => ValueListenableBuilder(
                        valueListenable: c['exam']!,
                        builder: (_, _, _) {
                          final total = (int.tryParse(c['ca1']!.text) ?? 0) +
                              (int.tryParse(c['ca2']!.text) ?? 0) +
                              (int.tryParse(c['exam']!.text) ?? 0);
                          return Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppColors.info.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: AppColors.info.withValues(alpha: 0.25)),
                            ),
                            child: Column(
                              children: [
                                Text('$total', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.info)),
                                Text('Total', style: GoogleFonts.inter(fontSize: 9, color: AppColors.textSecondary)),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _scoreField(TextEditingController controller, String label) {
    return Expanded(
      child: TextField(
        controller: controller,
        keyboardType: TextInputType.number,
        textAlign: TextAlign.center,
        style: GoogleFonts.inter(color: AppColors.textPrimary, fontSize: 14, fontWeight: FontWeight.bold),
        decoration: InputDecoration(
          labelText: label,
          labelStyle: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 11),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: AppColors.borderLight)),
          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: AppColors.borderLight)),
          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
          contentPadding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
          isDense: true,
          fillColor: AppColors.surface2,
          filled: true,
        ),
      ),
    );
  }

  Widget _buildSaveButton() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: SizedBox(
        width: double.infinity,
        height: 52,
        child: ElevatedButton(
          onPressed: _saving ? null : _save,
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.teacherAccent,
            foregroundColor: Colors.black,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
          child: _saving
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    color: Colors.black,
                    strokeWidth: 2,
                  ),
                )
              : Text(
                  'Save Scores',
                  style: GoogleFonts.inter(color: Colors.black, fontWeight: FontWeight.bold, fontSize: 15),
                ),
        ),
      ),
    );
  }

  InputDecoration _inputDecoration(String label) => InputDecoration(
        labelText: label,
        labelStyle: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 12),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: AppColors.borderLight),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: AppColors.borderLight),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: AppColors.borderLight),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        isDense: true,
        fillColor: AppColors.surface2,
        filled: true,
      );
}
