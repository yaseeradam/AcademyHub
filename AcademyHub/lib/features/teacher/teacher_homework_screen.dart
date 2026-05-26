import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';

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
    setState(() => _loading = true);
    try {
      final r = await auth.apiService.getWithCache('/teacher/classes');
      _classes = (r['data'] as List).cast<Map<String, dynamic>>();
    } catch (_) {}
    final hw = await _db.getAllHomework();
    setState(() { _homework = hw; _loading = false; });
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
    setState(() { _homework = hw; _loading = false; });
  }

  void _showCreateSheet() {
    if (_selectedClassId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Select a class first')),
      );
      return;
    }
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => _CreateHomeworkSheet(
        classId: _selectedClassId!,
        subjects: _subjects,
        onCreated: () { Navigator.pop(context); _loadForClass(_selectedClassId!); },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return MobileLayout(
      title: 'Homework',
      child: Column(
        children: [
          _buildClassFilter(),
          if (_loading) const LinearProgressIndicator(color: Color(0xFF8B5CF6)),
          Expanded(child: _homework.isEmpty ? _buildEmpty() : _buildList()),
          _buildFab(),
        ],
      ),
    );
  }

  Widget _buildClassFilter() => Container(
        color: Colors.white,
        padding: const EdgeInsets.all(16),
        child: DropdownButtonFormField<int>(
          decoration: InputDecoration(
            labelText: 'Class',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            isDense: true,
          ),
          initialValue: _selectedClassId,
          items: _classes.map((c) => DropdownMenuItem<int>(value: c['id'] as int, child: Text(c['name'] as String))).toList(),
          onChanged: (v) { if (v != null) _loadForClass(v); },
        ),
      );

  Widget _buildList() => ListView.separated(
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
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: isDirty ? const Color(0xFFFBBF24) : const Color(0xFFF1F5F9)),
              boxShadow: const [BoxShadow(color: Color(0x0A000000), blurRadius: 4, offset: Offset(0, 2))],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF8B5CF6).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.assignment, color: Color(0xFF8B5CF6), size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(children: [
                        Expanded(child: Text(h['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15))),
                        if (isDirty) const Icon(Icons.cloud_off, size: 14, color: Color(0xFFF59E0B)),
                      ]),
                      Text(h['subject_name'] ?? '', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                      Text('Due: $due', style: const TextStyle(fontSize: 12, color: Color(0xFF9CA3AF))),
                    ],
                  ),
                ),
                if (h['id'] != null)
                  IconButton(
                    icon: const Icon(Icons.people_outline, color: Color(0xFF3B82F6), size: 20),
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
            const Icon(Icons.assignment_outlined, size: 56, color: Color(0xFFCBD5E1)),
            const SizedBox(height: 12),
            const Text('No homework yet', style: TextStyle(color: Color(0xFF64748B), fontSize: 15)),
            const SizedBox(height: 8),
            if (_selectedClassId != null)
              TextButton(onPressed: _showCreateSheet, child: const Text('Create first homework')),
          ],
        ),
      );

  Widget _buildFab() => Padding(
        padding: const EdgeInsets.all(16),
        child: SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: _showCreateSheet,
            icon: const Icon(Icons.add, color: Colors.white),
            label: const Text('New Homework', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF8B5CF6),
              padding: const EdgeInsets.symmetric(vertical: 14),
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
  final VoidCallback onCreated;

  const _CreateHomeworkSheet({required this.classId, required this.subjects, required this.onCreated});

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
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Fill all fields')));
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

    setState(() => _saving = false);
    widget.onCreated();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom, left: 20, right: 20, top: 20),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('New Homework', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            DropdownButtonFormField<int>(
              decoration: _dec('Subject'),
              initialValue: _subjectId,
              items: widget.subjects.map((s) => DropdownMenuItem<int>(value: s['id'] as int, child: Text(s['name'] as String))).toList(),
              onChanged: (v) => setState(() => _subjectId = v),
            ),
            const SizedBox(height: 12),
            TextField(controller: _titleCtrl, decoration: _dec('Title')),
            const SizedBox(height: 12),
            TextField(controller: _contentCtrl, decoration: _dec('Instructions'), maxLines: 4),
            const SizedBox(height: 12),
            InkWell(
              onTap: () async {
                final picked = await showDatePicker(
                  context: context,
                  initialDate: DateTime.now().add(const Duration(days: 7)),
                  firstDate: DateTime.now(),
                  lastDate: DateTime.now().add(const Duration(days: 365)),
                );
                if (picked != null) setState(() => _dueDate = picked.toIso8601String().substring(0, 10));
              },
              child: InputDecorator(
                decoration: _dec('Due Date'),
                child: Text(_dueDate),
              ),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _saving ? null : _save,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF8B5CF6),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: _saving
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Save Homework', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  InputDecoration _dec(String label) => InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        isDense: true,
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
      setState(() { _submissions = r['data'] ?? []; _loading = false; });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(widget.title, style: const TextStyle(fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _submissions.isEmpty
              ? const Center(child: Text('No submissions yet', style: TextStyle(color: Color(0xFF64748B))))
              : ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: _submissions.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 8),
                  itemBuilder: (context, i) {
                    final s    = _submissions[i];
                    final name = '${s['student']?['first_name'] ?? ''} ${s['student']?['last_name'] ?? ''}';
                    return Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFF1F5F9)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(children: [
                            Expanded(child: Text(name, style: const TextStyle(fontWeight: FontWeight.w600))),
                            if (s['grade'] != null)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(color: const Color(0xFF10B981).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6)),
                                child: Text(s['grade'], style: const TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.bold, fontSize: 12)),
                              ),
                          ]),
                          const SizedBox(height: 6),
                          Text(s['submission'] ?? '', style: const TextStyle(fontSize: 13, color: Color(0xFF374151))),
                          if (s['feedback'] != null) ...[
                            const SizedBox(height: 6),
                            Text('Feedback: ${s['feedback']}', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                          ],
                        ],
                      ),
                    );
                  },
                ),
    );
  }
}
