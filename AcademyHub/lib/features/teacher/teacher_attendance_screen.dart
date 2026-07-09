import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

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
    if (!mounted) return;
    setState(() => _loading = true);
    try {
      final data = await auth.apiService.getWithCache('/teacher/classes');
      final list = (data['data'] as List).cast<Map<String, dynamic>>();
      await _db.upsertStudents([]); // ensure db ready
      if (mounted) {
        setState(() => _classes = list);
      }
    } catch (_) {
      // offline — classes list stays empty, user picks from cache
    } finally {
      if (mounted) setState(() => _loading = false);
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
      if (mounted) setState(() => _students = list);
    } catch (_) {
      final local = await _db.getStudentsByClass(classId);
      if (mounted) setState(() => _students = local);
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
    if (mounted) {
      setState(() { _marks = marksMap; _loading = false; });
    }
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

    if (mounted) {
      setState(() => _saving = false);
      CustomToast.show(
        context: context,
        message: 'Attendance saved successfully',
        type: 'success',
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final accent = auth.tenantPrimaryColor;

    return MobileLayout(
      title: 'Class Attendance',
      child: Container(
        color: AppColors.background,
        child: Column(
          children: [
            _buildFilters(accent),
            if (_loading) LinearProgressIndicator(color: accent, minHeight: 2),
            Expanded(child: _students.isEmpty ? _buildEmpty() : _buildList(accent)),
            if (_students.isNotEmpty) _buildSaveButton(accent),
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
                      builder: (context, child) {
                        return Theme(
                          data: Theme.of(context).copyWith(
                            colorScheme: ColorScheme.dark(
                              primary: accent,
                              onPrimary: Colors.black,
                              surface: AppColors.surface,
                              onSurface: AppColors.textPrimary,
                            ),
                          ),
                          child: child!,
                        );
                      },
                    );
                    if (picked != null) {
                      setState(() => _selectedDate = picked.toIso8601String().substring(0, 10));
                      if (_selectedClassId != null) _loadStudents(_selectedClassId!);
                    }
                  },
                  child: InputDecorator(
                    decoration: _inputDecoration('Date'),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(_selectedDate, style: GoogleFonts.inter(color: AppColors.textPrimary, fontSize: 14)),
                        Icon(Icons.calendar_today_rounded, size: 16, color: accent),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildList(Color accent) {
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _students.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (context, i) {
        final s = _students[i];
        final id = s['id'] as int;
        final name = '${s['first_name'] ?? ''} ${s['last_name'] ?? ''}'.trim();
        final initial = name.isNotEmpty ? name[0].toUpperCase() : '?';

        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Row(
            children: [
              CircleAvatar(
                radius: 18,
                backgroundColor: AppColors.teacherAccent.withValues(alpha: 0.12),
                child: Text(initial, style: GoogleFonts.inter(color: AppColors.teacherAccent, fontWeight: FontWeight.bold)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(name, style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 14, color: AppColors.textPrimary)),
                    Text(s['admission_number'] ?? '', style: GoogleFonts.inter(fontSize: 11, color: AppColors.textSecondary)),
                  ],
                ),
              ),
              _statusChip('P', 'present', id, AppColors.success),
              const SizedBox(width: 8),
              _statusChip('A', 'absent', id, AppColors.error),
              const SizedBox(width: 8),
              _statusChip('L', 'late', id, AppColors.warning),
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
          color: selected ? color : color.withValues(alpha: 0.12),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: selected ? color : color.withValues(alpha: 0.25), width: 1),
        ),
        alignment: Alignment.center,
        child: Text(
          label,
          style: GoogleFonts.inter(
            color: selected ? Colors.black : color,
            fontWeight: FontWeight.bold,
            fontSize: 13,
          ),
        ),
      ),
    );
  }

  Widget _buildEmpty() => Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.how_to_reg_rounded, size: 48, color: AppColors.textMuted),
            const SizedBox(height: 12),
            Text(
              'Select a class to take attendance',
              style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 14),
            ),
          ],
        ),
      );

  Widget _buildSaveButton(Color accent) {
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
                  'Save Attendance',
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
