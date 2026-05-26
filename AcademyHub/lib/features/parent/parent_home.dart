import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';

class ParentHome extends StatefulWidget {
  const ParentHome({super.key});

  @override
  State<ParentHome> createState() => _ParentHomeState();
}

class _ParentHomeState extends State<ParentHome> with SingleTickerProviderStateMixin {
  final _db = DatabaseHelper();
  late TabController _tabs;

  List<Map<String, dynamic>> _children      = [];
  List<Map<String, dynamic>> _homework      = [];
  List<Map<String, dynamic>> _announcements = [];
  List<dynamic>              _billing       = [];
  bool _loading = true;

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
    setState(() { _loading = true; });

    // Try network first, fall back to cache/local
    try {
      final r = await auth.apiService.getWithCache('/students');
      final list = ((r['data'] as List?) ?? []).cast<Map<String, dynamic>>();
      await _db.upsertStudents(list);
      _children = list;
    } catch (_) {
      _children = await _db.getAllStudents();
    }

    try {
      final r = await auth.apiService.getWithCache('/billing');
      _billing = (r['data'] as List?) ?? [];
    } catch (_) {}

    _homework      = await _db.getAllHomework();
    _announcements = await _db.getAnnouncements();

    setState(() => _loading = false);
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    return MobileLayout(
      title: 'Parent Portal',
      child: Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [Color(0xFF10B981), Color(0xFF059669)]),
            ),
            child: Text('Welcome, ${user?.name ?? 'Parent'}!',
                style: const TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold)),
          ),
          Container(
            color: Colors.white,
            child: TabBar(
              controller: _tabs,
              labelColor: const Color(0xFF10B981),
              unselectedLabelColor: const Color(0xFF64748B),
              indicatorColor: const Color(0xFF10B981),
              labelStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
              tabs: const [
                Tab(icon: Icon(Icons.people, size: 18), text: 'Children'),
                Tab(icon: Icon(Icons.assignment, size: 18), text: 'Homework'),
                Tab(icon: Icon(Icons.receipt_long, size: 18), text: 'Fees'),
                Tab(icon: Icon(Icons.campaign, size: 18), text: 'News'),
              ],
            ),
          ),
          if (_loading) const LinearProgressIndicator(color: Color(0xFF10B981)),
          Expanded(
            child: TabBarView(
              controller: _tabs,
              children: [_buildChildren(), _buildHomework(), _buildBilling(), _buildAnnouncements()],
            ),
          ),
        ],
      ),
    );
  }

  // ── Children ─────────────────────────────────────────────────────────────────

  Widget _buildChildren() {
    if (_children.isEmpty) return _empty('No children linked to this account.');
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _children.length,
      separatorBuilder: (_, _) => const SizedBox(height: 10),
      itemBuilder: (_, i) {
        final c    = _children[i];
        final name = '${c['first_name'] ?? ''} ${c['last_name'] ?? ''}';
        final cls  = c['school_class']?['name'] ?? c['section']?['name'] ?? '';
        return Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: const Color(0xFFF1F5F9)),
            boxShadow: const [BoxShadow(color: Color(0x0A000000), blurRadius: 4, offset: Offset(0, 2))],
          ),
          child: Row(children: [
            CircleAvatar(
              radius: 22,
              backgroundColor: const Color(0xFF10B981).withValues(alpha: 0.1),
              child: Text(name.isNotEmpty ? name[0] : '?',
                  style: const TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.bold, fontSize: 18)),
            ),
            const SizedBox(width: 14),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
              Text(cls, style: const TextStyle(fontSize: 13, color: Color(0xFF64748B))),
              Text(c['admission_number'] ?? '', style: const TextStyle(fontSize: 12, color: Color(0xFF9CA3AF))),
            ])),
            const Icon(Icons.chevron_right, color: Color(0xFF9CA3AF)),
          ]),
        );
      },
    );
  }

  // ── Homework ─────────────────────────────────────────────────────────────────

  Widget _buildHomework() {
    if (_homework.isEmpty) return _empty('No homework assigned yet.');
    final now = DateTime.now().toIso8601String().substring(0, 10);
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _homework.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final h       = _homework[i];
        final due     = h['due_date'] as String? ?? '';
        final overdue = due.isNotEmpty && due.compareTo(now) < 0;
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: overdue ? const Color(0xFFFECACA) : const Color(0xFFF1F5F9)),
          ),
          child: Row(children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFF8B5CF6).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.assignment, color: Color(0xFF8B5CF6), size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(h['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
              Text(h['subject_name'] ?? '', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              Text('Due: $due',
                  style: TextStyle(fontSize: 11, color: overdue ? const Color(0xFFEF4444) : const Color(0xFF9CA3AF))),
            ])),
          ]),
        );
      },
    );
  }

  // ── Fees ─────────────────────────────────────────────────────────────────────

  Widget _buildBilling() {
    if (_billing.isEmpty) return _empty('No fee transactions found.');
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _billing.length > 20 ? 20 : _billing.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final tx      = _billing[i];
        final isIn    = tx['type'] == 'Income';
        final color   = isIn ? const Color(0xFF10B981) : const Color(0xFFEF4444);
        final date    = (tx['date'] as String?)?.split('T').first ?? '';
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFF1F5F9)),
          ),
          child: Row(children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
              child: Icon(isIn ? Icons.arrow_downward : Icons.arrow_upward, color: color, size: 18),
            ),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(tx['category'] ?? 'Fee Payment',
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
              Text(date, style: const TextStyle(fontSize: 12, color: Color(0xFF9CA3AF))),
            ])),
            Text('₦${tx['amount_paid']}',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: color)),
          ]),
        );
      },
    );
  }

  // ── Announcements ─────────────────────────────────────────────────────────────

  Widget _buildAnnouncements() {
    if (_announcements.isEmpty) return _empty('No announcements.');
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

  Widget _empty(String msg) => Center(
        child: Text(msg, style: const TextStyle(color: Color(0xFF64748B))),
      );
}
