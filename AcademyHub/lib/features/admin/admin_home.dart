import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';

class AdminHome extends StatefulWidget {
  const AdminHome({super.key});

  @override
  State<AdminHome> createState() => _AdminHomeState();
}

class _AdminHomeState extends State<AdminHome> with SingleTickerProviderStateMixin {
  final _db = DatabaseHelper();
  late TabController _tabs;

  List<Map<String, dynamic>> _students      = [];
  List<Map<String, dynamic>> _homework      = [];
  List<Map<String, dynamic>> _announcements = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 3, vsync: this);
    _load();
  }

  @override
  void dispose() { _tabs.dispose(); super.dispose(); }

  Future<void> _load() async {
    final auth = context.read<AuthProvider>();
    setState(() => _loading = true);
    try {
      final r = await auth.apiService.getWithCache('/students');
      _students = ((r['data'] as List?) ?? []).cast<Map<String, dynamic>>();
    } catch (_) {
      _students = await _db.getAllStudents();
    }
    _homework      = await _db.getAllHomework();
    _announcements = await _db.getAnnouncements();
    setState(() => _loading = false);
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    return MobileLayout(
      title: 'MyAcademy',
      child: Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [Color(0xFF6366F1), Color(0xFF4F46E5)]),
            ),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Welcome, ${user?.name ?? 'Admin'}!',
                  style: const TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold)),
              const SizedBox(height: 2),
              Text('${_students.length} students enrolled',
                  style: const TextStyle(color: Colors.white70, fontSize: 13)),
            ]),
          ),
          Container(
            color: Colors.white,
            child: TabBar(
              controller: _tabs,
              labelColor: const Color(0xFF6366F1),
              unselectedLabelColor: const Color(0xFF64748B),
              indicatorColor: const Color(0xFF6366F1),
              labelStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
              tabs: const [
                Tab(icon: Icon(Icons.dashboard, size: 18), text: 'Overview'),
                Tab(icon: Icon(Icons.assignment, size: 18), text: 'Homework'),
                Tab(icon: Icon(Icons.campaign, size: 18), text: 'News'),
              ],
            ),
          ),
          if (_loading) const LinearProgressIndicator(color: Color(0xFF6366F1)),
          Expanded(
            child: TabBarView(
              controller: _tabs,
              children: [_buildOverview(context), _buildHomework(), _buildAnnouncements()],
            ),
          ),
        ],
      ),
    );
  }

  // ── Overview ─────────────────────────────────────────────────────────────────

  Widget _buildOverview(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: 1.5,
          children: [
            _statCard('Students', '${_students.length}', Icons.people, const Color(0xFF3B82F6)),
            _statCard('Homework', '${_homework.length}', Icons.assignment, const Color(0xFF8B5CF6)),
            _statCard('Announcements', '${_announcements.length}', Icons.campaign, const Color(0xFF10B981)),
            _statCard('Pending Sync', '', Icons.cloud_upload, const Color(0xFFF59E0B),
                pendingStream: true),
          ],
        ),
        const SizedBox(height: 20),
        const Text('Quick Actions',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
        const SizedBox(height: 12),
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: 1.2,
          children: [
            _actionCard('Attendance', Icons.how_to_reg, const Color(0xFF10B981), () => context.go('/attendance')),
            _actionCard('Scores', Icons.edit_note, const Color(0xFF3B82F6), () => context.go('/scores')),
            _actionCard('Homework', Icons.assignment, const Color(0xFF8B5CF6), () => context.go('/homework')),
            _actionCard('CBT Exams', Icons.computer, const Color(0xFFF59E0B), () => context.go('/cbt')),
          ],
        ),
        const SizedBox(height: 20),
        const Text('Recent Students',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
        const SizedBox(height: 12),
        ..._students.take(5).map((s) {
          final name = '${s['first_name'] ?? ''} ${s['last_name'] ?? ''}';
          final cls  = s['school_class']?['name'] ?? '';
          return Container(
            margin: const EdgeInsets.only(bottom: 8),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFF1F5F9)),
            ),
            child: Row(children: [
              CircleAvatar(
                radius: 18,
                backgroundColor: const Color(0xFF6366F1).withValues(alpha: 0.1),
                child: Text(name.isNotEmpty ? name[0] : '?',
                    style: const TextStyle(color: Color(0xFF6366F1), fontWeight: FontWeight.bold)),
              ),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                Text(cls, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              ])),
              Text(s['admission_number'] ?? '', style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
            ]),
          );
        }),
      ]),
    );
  }

  Widget _statCard(String label, String value, IconData icon, Color color, {bool pendingStream = false}) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: const [BoxShadow(color: Color(0x0A000000), blurRadius: 4, offset: Offset(0, 2))],
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Container(
          padding: const EdgeInsets.all(7),
          decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
          child: Icon(icon, color: color, size: 18),
        ),
        const Spacer(),
        if (pendingStream)
          StreamBuilder<int>(
            stream: context.read<AuthProvider>().syncService.pendingCountStream,
            builder: (_, snap) => Text('${snap.data ?? 0}',
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
          )
        else
          Text(value, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
        Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
      ]),
    );
  }

  Widget _actionCard(String title, IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: const Color(0xFFF1F5F9)),
          boxShadow: const [BoxShadow(color: Color(0x0A000000), blurRadius: 4, offset: Offset(0, 2))],
        ),
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(height: 10),
          Text(title,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF374151)),
              textAlign: TextAlign.center),
        ]),
      ),
    );
  }

  // ── Homework ─────────────────────────────────────────────────────────────────

  Widget _buildHomework() {
    if (_homework.isEmpty) return const Center(child: Text('No homework yet.', style: TextStyle(color: Color(0xFF64748B))));
    final now = DateTime.now().toIso8601String().substring(0, 10);
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _homework.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final h       = _homework[i];
        final due     = h['due_date'] as String? ?? '';
        final overdue = due.isNotEmpty && due.compareTo(now) < 0;
        final isDirty = (h['is_dirty'] as int? ?? 0) == 1;
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: isDirty ? const Color(0xFFFBBF24) : overdue ? const Color(0xFFFECACA) : const Color(0xFFF1F5F9)),
          ),
          child: Row(children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: const Color(0xFF8B5CF6).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
              child: const Icon(Icons.assignment, color: Color(0xFF8B5CF6), size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [
                Expanded(child: Text(h['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14))),
                if (isDirty) const Icon(Icons.cloud_off, size: 14, color: Color(0xFFF59E0B)),
              ]),
              Text(h['subject_name'] ?? '', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              Text('Due: $due',
                  style: TextStyle(fontSize: 11, color: overdue ? const Color(0xFFEF4444) : const Color(0xFF9CA3AF))),
            ])),
          ]),
        );
      },
    );
  }

  // ── Announcements ─────────────────────────────────────────────────────────────

  Widget _buildAnnouncements() {
    if (_announcements.isEmpty) return const Center(child: Text('No announcements.', style: TextStyle(color: Color(0xFF64748B))));
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _announcements.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final a    = _announcements[i];
        final date = (a['published_at'] as String?)?.substring(0, 10) ?? '';
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFF1F5F9)),
          ),
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
}
