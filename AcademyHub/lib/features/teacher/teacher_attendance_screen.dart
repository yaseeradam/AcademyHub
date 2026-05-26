import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';

class TeacherAttendanceScreen extends StatefulWidget {
  const TeacherAttendanceScreen({super.key});

  @override
  State<TeacherAttendanceScreen> createState() => _TeacherAttendanceScreenState();
}

class _TeacherAttendanceScreenState extends State<TeacherAttendanceScreen> {
  final _db = DatabaseHelper();

  List<Map<String, dynamic>> _classes = [];
  List<Map<String, dynamic>> _students = [];
  Map<int, String> _marks = {}; // studentId -> status

  int? _selectedClassId;
  String _selectedDate = DateTime.now().toIso8601String().substring(0, 10);
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

  Future<void> _loadClasses() async {
    final auth = context.read<AuthProvider>();
    setState(() => _loading = true);
    try {
      final data = await auth.apiService.getWithCache('/teacher/classes');
      final list = (data['data'] as List).cast<Map<String, dynamic>>();
      await _db.upsertStudents([]); // ensure db ready
      setState(() => _classes = list);
    } catch (_) {
      // offline — classes list stays empty, user picks from cache
    } finally {
      setState(() => _loading = false);
    }
  }

  Future<void> _loadStudents(int classId) async {
    final auth = context.read<AuthProvider>();
    setState(() { _loading = true; _students = []; _marks = {}; });

    // Try network first, fallback to local
    try {
      final data = await auth.apiService.getWithCache('/teacher/classes/$classId/students');
      final list = (data['data'] as List).cast<Map<String, dynamic>>();
      await _db.upsertStudents(list);
      setState(() => _students = list);
    } catch (_) {
      final local = await _db.getStudentsByClass(classId);
      setState(() => _students = local);
    }

    // Load existing attendance for today
    final existing = await _db.getAttendance(classId, _selectedDate, _term, _session);
    final marksMap = <int, String>{};
    for (final row in existing) {
      marksMap[row['student_id'] as int] = row['status'] as String;
    }
    // Default all to present
    for (final s in _students) {
      marksMap.putIfAbsent(s['id'] as int, () => 'present');
    }
    setState(() { _marks = marksMap; _loading = false; });
  }

  Future<void> _save() async {
    if (_selectedClassId == null || _students.isEmpty) return;
    setState(() => _saving = true);

    final auth = context.read<AuthProvider>();
    final marksList = _marks.entries.map((e) => {'student_id': e.key, 'status': e.value}).toList();

    // Always save locally first
    for (final m in marksList) {
      await _db.saveAttendanceLocally(m['student_id'] as int, _selectedClassId!, _selectedDate, _term, _session, m['status'] as String);
    }

    // Notify sync service there's dirty data
    await auth.syncService.notifyDirty();

    // Try immediate sync
    await auth.syncService.syncNow();

    setState(() => _saving = false);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Attendance saved'), backgroundColor: Color(0xFF10B981)),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return MobileLayout(
      title: 'Attendance',
      child: Column(
        children: [
          _buildFilters(),
          if (_loading) const LinearProgressIndicator(color: Color(0xFF3B82F6)),
          Expanded(child: _students.isEmpty ? _buildEmpty() : _buildList()),
          if (_students.isNotEmpty) _buildSaveButton(),
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
                  initialValue: _selectedClassId,
                  items: _classes.map((c) => DropdownMenuItem<int>(value: c['id'] as int, child: Text(c['name'] as String))).toList(),
                  onChanged: (v) {
                    setState(() => _selectedClassId = v);
                    if (v != null) _loadStudents(v);
                  },
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: InkWell(
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: context,
                      initialDate: DateTime.now(),
                      firstDate: DateTime(2020),
                      lastDate: DateTime.now(),
                    );
                    if (picked != null) {
                      setState(() => _selectedDate = picked.toIso8601String().substring(0, 10));
                      if (_selectedClassId != null) _loadStudents(_selectedClassId!);
                    }
                  },
                  child: InputDecorator(
                    decoration: _inputDecoration('Date'),
                    child: Text(_selectedDate, style: const TextStyle(fontSize: 14)),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildList() {
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _students.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (context, i) {
        final s = _students[i];
        final id = s['id'] as int;
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFF1F5F9)),
          ),
          child: Row(
            children: [
              CircleAvatar(
                radius: 18,
                backgroundColor: const Color(0xFF3B82F6).withValues(alpha: 0.1),
                child: Text('${s['first_name']?[0] ?? '?'}', style: const TextStyle(color: Color(0xFF3B82F6), fontWeight: FontWeight.bold)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('${s['first_name']} ${s['last_name']}', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                    Text(s['admission_number'] ?? '', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                  ],
                ),
              ),
              _statusChip('P', 'present', id, const Color(0xFF10B981)),
              const SizedBox(width: 6),
              _statusChip('A', 'absent', id, const Color(0xFFEF4444)),
              const SizedBox(width: 6),
              _statusChip('L', 'late', id, const Color(0xFFF59E0B)),
            ],
          ),
        );
      },
    );
  }

  Widget _statusChip(String label, String value, int studentId, Color color) {
    final selected = _marks[studentId] == value;
    return GestureDetector(
      onTap: () => setState(() => _marks[studentId] = value),
      child: Container(
        width: 32,
        height: 32,
        decoration: BoxDecoration(
          color: selected ? color : color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(8),
        ),
        alignment: Alignment.center,
        child: Text(label, style: TextStyle(color: selected ? Colors.white : color, fontWeight: FontWeight.bold, fontSize: 13)),
      ),
    );
  }

  Widget _buildEmpty() => const Center(child: Text('Select a class to take attendance', style: TextStyle(color: Color(0xFF64748B))));

  Widget _buildSaveButton() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: SizedBox(
        width: double.infinity,
        child: ElevatedButton(
          onPressed: _saving ? null : _save,
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF3B82F6),
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
          child: _saving
              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : const Text('Save Attendance', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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
