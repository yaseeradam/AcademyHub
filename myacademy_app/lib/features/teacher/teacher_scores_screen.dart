import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';

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
      map.values.forEach((c) => c.dispose());
    }
    super.dispose();
  }

  Future<void> _loadClasses() async {
    final auth = context.read<AuthProvider>();
    setState(() => _loading = true);
    try {
      final data = await auth.apiService.getWithCache('/teacher/classes');
      setState(() => _classes = (data['data'] as List).cast<Map<String, dynamic>>());
    } catch (_) {}
    setState(() => _loading = false);
  }

  Future<void> _onClassChanged(int classId) async {
    final auth = context.read<AuthProvider>();
    setState(() { _selectedClassId = classId; _selectedSubjectId = null; _students = []; _subjects = []; });

    // Load subjects
    try {
      final data = await auth.apiService.getWithCache('/teacher/classes/$classId/subjects');
      final list = (data['data'] as List).cast<Map<String, dynamic>>();
      await _db.upsertSubjects(classId, list);
      setState(() => _subjects = list);
    } catch (_) {
      setState(() => _subjects = await _db.getSubjectsByClass(classId));
    }

    // Load students
    try {
      final data = await auth.apiService.getWithCache('/teacher/classes/$classId/students');
      final list = (data['data'] as List).cast<Map<String, dynamic>>();
      await _db.upsertStudents(list);
      setState(() => _students = list);
    } catch (_) {
      setState(() => _students = await _db.getStudentsByClass(classId));
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

    setState(() => _saving = false);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Scores saved'), backgroundColor: Color(0xFF10B981)),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return MobileLayout(
      title: 'Score Entry',
      child: Column(
        children: [
          _buildFilters(),
          if (_loading) const LinearProgressIndicator(color: Color(0xFF3B82F6)),
          Expanded(child: _buildContent()),
          if (_students.isNotEmpty && _selectedSubjectId != null) _buildSaveButton(),
        ],
      ),
    );
  }

  Widget _buildFilters() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Row(
            children: [
              Expanded(
                child: DropdownButtonFormField<int>(
                  decoration: _inputDecoration('Class'),
                  value: _selectedClassId,
                  items: _classes.map((c) => DropdownMenuItem<int>(value: c['id'] as int, child: Text(c['name'] as String))).toList(),
                  onChanged: (v) { if (v != null) _onClassChanged(v); },
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: DropdownButtonFormField<int>(
                  decoration: _inputDecoration('Term'),
                  value: _term,
                  items: const [
                    DropdownMenuItem(value: 1, child: Text('Term 1')),
                    DropdownMenuItem(value: 2, child: Text('Term 2')),
                    DropdownMenuItem(value: 3, child: Text('Term 3')),
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
              value: _selectedSubjectId,
              items: _subjects.map((s) => DropdownMenuItem<int>(value: s['id'] as int, child: Text(s['name'] as String))).toList(),
              onChanged: (v) { if (v != null) _onSubjectChanged(v); },
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildContent() {
    if (_selectedSubjectId == null || _students.isEmpty) {
      return const Center(child: Text('Select a class and subject', style: TextStyle(color: Color(0xFF64748B))));
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _students.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, i) {
        final s = _students[i];
        final id = s['id'] as int;
        final c = _controllers[id];
        if (c == null) return const SizedBox.shrink();

        return Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFF1F5F9)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('${s['first_name']} ${s['last_name']}', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
              Text(s['admission_number'] ?? '', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              const SizedBox(height: 10),
              Row(
                children: [
                  _scoreField(c['ca1']!, 'CA1'),
                  const SizedBox(width: 8),
                  _scoreField(c['ca2']!, 'CA2'),
                  const SizedBox(width: 8),
                  _scoreField(c['exam']!, 'Exam'),
                  const SizedBox(width: 8),
                  // Live total
                  ValueListenableBuilder(
                    valueListenable: c['ca1']!,
                    builder: (_, __, ___) => ValueListenableBuilder(
                      valueListenable: c['ca2']!,
                      builder: (_, __, ___) => ValueListenableBuilder(
                        valueListenable: c['exam']!,
                        builder: (_, __, ___) {
                          final total = (int.tryParse(c['ca1']!.text) ?? 0) +
                              (int.tryParse(c['ca2']!.text) ?? 0) +
                              (int.tryParse(c['exam']!.text) ?? 0);
                          return Column(
                            children: [
                              Text('$total', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Color(0xFF3B82F6))),
                              const Text('Total', style: TextStyle(fontSize: 10, color: Color(0xFF64748B))),
                            ],
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
        decoration: InputDecoration(
          labelText: label,
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
          contentPadding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
          isDense: true,
        ),
      ),
    );
  }

  Widget _buildSaveButton() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: SizedBox(
        width: double.infinity,
        child: ElevatedButton(
          onPressed: _saving ? null : _save,
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF10B981),
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
          child: _saving
              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : const Text('Save Scores', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        ),
      ),
    );
  }

  InputDecoration _inputDecoration(String label) => InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        isDense: true,
      );
}
