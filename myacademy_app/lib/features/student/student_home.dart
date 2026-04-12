import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';

class StudentHome extends StatefulWidget {
  const StudentHome({super.key});

  @override
  State<StudentHome> createState() => _StudentHomeState();
}

class _StudentHomeState extends State<StudentHome> with SingleTickerProviderStateMixin {
  final _db = DatabaseHelper();
  late TabController _tabs;

  Map<String, dynamic>? _reportCard;
  List<Map<String, dynamic>> _homework      = [];
  List<Map<String, dynamic>> _timetable     = [];
  List<Map<String, dynamic>> _announcements = [];
  bool _loading = true;
  String? _error;
  int? _studentId;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 4, vsync: this);
    _load();
  }

  @override
  void dispose() { _tabs.dispose(); super.dispose(); }

  Future<void> _load() async {
    final auth = context.read<AuthProvider>();
    setState(() { _loading = true; _error = null; });
    try {
      final termData = await auth.apiService.getWithCache('/term');
      final term     = termData['term'] ?? 1;
      final session  = termData['session'] ?? '';
      final studData = await auth.apiService.getWithCache('/students');
      final students = (studData['data'] as List?) ?? [];
      if (students.isEmpty) throw Exception('No student record linked to this account.');
      _studentId  = students.first['id'] as int;
      final rcData = await auth.apiService.getWithCache('/students/$_studentId/report-card?term=$term&session=$session');
      _reportCard  = rcData['data'];
    } catch (e) {
      _error = e.toString();
    }
    _homework      = await _db.getAllHomework();
    _timetable     = await _db.getTimetable();
    _announcements = await _db.getAnnouncements();
    setState(() => _loading = false);
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    return MobileLayout(
      title: 'Student Portal',
      child: Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [Color(0xFF3B82F6), Color(0xFF1D4ED8)]),
            ),
            child: Text('Welcome, ${user?.name ?? 'Student'}!',
                style: const TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold)),
          ),
          Container(
            color: Colors.white,
            child: TabBar(
              controller: _tabs,
              labelColor: const Color(0xFF3B82F6),
              unselectedLabelColor: const Color(0xFF64748B),
              indicatorColor: const Color(0xFF3B82F6),
              labelStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
              tabs: const [
                Tab(icon: Icon(Icons.bar_chart, size: 18), text: 'Results'),
                Tab(icon: Icon(Icons.assignment, size: 18), text: 'Homework'),
                Tab(icon: Icon(Icons.schedule, size: 18), text: 'Timetable'),
                Tab(icon: Icon(Icons.campaign, size: 18), text: 'News'),
              ],
            ),
          ),
          if (_loading) const LinearProgressIndicator(color: Color(0xFF3B82F6)),
          Expanded(
            child: TabBarView(
              controller: _tabs,
              children: [_buildResults(), _buildHomework(), _buildTimetable(), _buildAnnouncements()],
            ),
          ),
        ],
      ),
    );
  }

  // ── Results ─────────────────────────────────────────────────────────────────

  Widget _buildResults() {
    if (_error != null && _reportCard == null) return _buildError(_error!);
    if (_reportCard == null) return const Center(child: Text('No results available.', style: TextStyle(color: Color(0xFF64748B))));
    final subjects = (_reportCard!['subjects'] as List?) ?? [];
    final session  = _reportCard!['session'] ?? '';
    final term     = _reportCard!['term'] ?? '';
    return Column(children: [
      Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        color: const Color(0xFFF8FAFC),
        child: Row(children: [
          const Icon(Icons.info_outline, size: 13, color: Color(0xFF64748B)),
          const SizedBox(width: 6),
          Text('$session — Term $term', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
        ]),
      ),
      Expanded(
        child: subjects.isEmpty
            ? const Center(child: Text('No scores recorded yet.', style: TextStyle(color: Color(0xFF64748B))))
            : ListView.separated(
                padding: const EdgeInsets.all(16),
                itemCount: subjects.length,
                separatorBuilder: (_, __) => const SizedBox(height: 8),
                itemBuilder: (_, i) => _subjectCard(subjects[i]),
              ),
      ),
    ]);
  }

  Widget _subjectCard(dynamic s) {
    final grade = s['grade'] as String? ?? 'F';
    final color = grade == 'A'
        ? const Color(0xFF10B981)
        : grade == 'B'
            ? const Color(0xFF3B82F6)
            : grade == 'F'
                ? const Color(0xFFEF4444)
                : const Color(0xFFF59E0B);
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFF1F5F9))),
      child: Row(children: [
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(s['subject'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
          const SizedBox(height: 3),
          Text('CA1: ${s['ca1']}  CA2: ${s['ca2']}  Exam: ${s['exam']}',
              style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
        ])),
        Column(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(color: color.withOpacity(0.1), shape: BoxShape.circle),
            alignment: Alignment.center,
            child: Text(grade, style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 16)),
          ),
          Text('${s['total']}', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
        ]),
      ]),
    );
  }

  // ── Homework ────────────────────────────────────────────────────────────────

  Widget _buildHomework() {
    if (_homework.isEmpty) return const Center(child: Text('No homework assigned.', style: TextStyle(color: Color(0xFF64748B))));
    final now = DateTime.now().toIso8601String().substring(0, 10);
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _homework.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, i) {
        final h       = _homework[i];
        final due     = h['due_date'] as String? ?? '';
        final overdue = due.isNotEmpty && due.compareTo(now) < 0;
        return GestureDetector(
          onTap: () => _showSubmitSheet(h),
          child: Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: overdue ? const Color(0xFFFECACA) : const Color(0xFFF1F5F9)),
            ),
            child: Row(children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(color: const Color(0xFF8B5CF6).withOpacity(0.1), borderRadius: BorderRadius.circular(10)),
                child: const Icon(Icons.assignment, color: Color(0xFF8B5CF6), size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(h['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                Text(h['subject_name'] ?? '', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                Text('Due: $due',
                    style: TextStyle(fontSize: 11, color: overdue ? const Color(0xFFEF4444) : const Color(0xFF9CA3AF))),
              ])),
              const Icon(Icons.chevron_right, color: Color(0xFF9CA3AF), size: 18),
            ]),
          ),
        );
      },
    );
  }

  void _showSubmitSheet(Map<String, dynamic> hw) {
    if (hw['id'] == null || _studentId == null) return;
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => _SubmitSheet(homework: hw, studentId: _studentId!, onSubmitted: () { Navigator.pop(context); _load(); }),
    );
  }

  // ── Timetable ───────────────────────────────────────────────────────────────

  Widget _buildTimetable() {
    if (_timetable.isEmpty) return const Center(child: Text('No timetable available.', style: TextStyle(color: Color(0xFF64748B))));
    const days = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    final grouped = <int, List<Map<String, dynamic>>>{};
    for (final e in _timetable) {
      grouped.putIfAbsent(e['day_of_week'] as int? ?? 1, () => []).add(e);
    }
    return ListView(
      padding: const EdgeInsets.all(16),
      children: grouped.entries.map((entry) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Text(days[entry.key], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A))),
          ),
          ...entry.value.map((e) => Container(
            margin: const EdgeInsets.only(bottom: 8),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFF1F5F9))),
            child: Row(children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(color: const Color(0xFF3B82F6).withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
                child: Text('${e['starts_at']}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF3B82F6))),
              ),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(e['subject_name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                Text(e['teacher_name'] ?? '', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              ])),
              if ((e['room'] as String?)?.isNotEmpty == true)
                Text(e['room'] as String, style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
            ]),
          )),
        ],
      )).toList(),
    );
  }

  // ── Announcements ───────────────────────────────────────────────────────────

  Widget _buildAnnouncements() {
    if (_announcements.isEmpty) return const Center(child: Text('No announcements.', style: TextStyle(color: Color(0xFF64748B))));
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _announcements.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final a    = _announcements[i];
        final date = (a['published_at'] as String?)?.substring(0, 10) ?? '';
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFF1F5F9))),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              Expanded(child: Text(a['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14))),
              Text(date, style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
            ]),
            const SizedBox(height: 6),
            Text(a['body'] ?? '', style: const TextStyle(fontSize: 13, color: Color(0xFF374151))),
            if ((a['author_name'] as String?)?.isNotEmpty == true) ...[
              const SizedBox(height: 4),
              Text('— ${a['author_name']}', style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
            ],
          ]),
        );
      },
    );
  }

  Widget _buildError(String msg) => Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            const Icon(Icons.error_outline, size: 48, color: Color(0xFFEF4444)),
            const SizedBox(height: 12),
            Text(msg, textAlign: TextAlign.center, style: const TextStyle(color: Color(0xFF64748B))),
            const SizedBox(height: 16),
            ElevatedButton(onPressed: _load, child: const Text('Retry')),
          ]),
        ),
      );
}

// ─── Submit Homework Sheet ─────────────────────────────────────────────────────

class _SubmitSheet extends StatefulWidget {
  final Map<String, dynamic> homework;
  final int studentId;
  final VoidCallback onSubmitted;
  const _SubmitSheet({required this.homework, required this.studentId, required this.onSubmitted});

  @override
  State<_SubmitSheet> createState() => _SubmitSheetState();
}

class _SubmitSheetState extends State<_SubmitSheet> {
  final _db   = DatabaseHelper();
  final _ctrl = TextEditingController();
  bool _saving = false;
  Map<String, dynamic>? _existing;

  @override
  void initState() {
    super.initState();
    _loadExisting();
  }

  @override
  void dispose() { _ctrl.dispose(); super.dispose(); }

  Future<void> _loadExisting() async {
    final sub = await _db.getSubmission(widget.homework['id'] as int, widget.studentId);
    if (sub != null && mounted) setState(() { _existing = sub; _ctrl.text = sub['submission'] ?? ''; });
  }

  Future<void> _submit() async {
    if (_ctrl.text.trim().isEmpty) return;
    setState(() => _saving = true);
    final auth = context.read<AuthProvider>();
    await _db.saveSubmissionLocally(widget.homework['id'] as int, widget.studentId, _ctrl.text.trim());
    await auth.syncService.notifyDirty();
    await auth.syncService.syncNow();
    setState(() => _saving = false);
    widget.onSubmitted();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom, left: 20, right: 20, top: 20),
      child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(widget.homework['title'] ?? '', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
        const SizedBox(height: 4),
        Text(widget.homework['content'] ?? '', style: const TextStyle(fontSize: 13, color: Color(0xFF64748B))),
        const SizedBox(height: 16),
        if (_existing?['grade'] != null)
          Container(
            padding: const EdgeInsets.all(10),
            margin: const EdgeInsets.only(bottom: 12),
            decoration: BoxDecoration(color: const Color(0xFFD1FAE5), borderRadius: BorderRadius.circular(10)),
            child: Text('Grade: ${_existing!['grade']}  •  ${_existing!['feedback'] ?? ''}',
                style: const TextStyle(color: Color(0xFF065F46), fontWeight: FontWeight.w600)),
          ),
        TextField(
          controller: _ctrl,
          maxLines: 5,
          decoration: InputDecoration(
            labelText: 'Your answer',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
          ),
        ),
        const SizedBox(height: 16),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: _saving ? null : _submit,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF8B5CF6),
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: _saving
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : Text(_existing != null ? 'Update Submission' : 'Submit',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ),
        const SizedBox(height: 20),
      ]),
    );
  }
}
