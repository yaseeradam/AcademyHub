import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';

class CbtManagementScreen extends StatefulWidget {
  const CbtManagementScreen({super.key});

  @override
  State<CbtManagementScreen> createState() => _CbtManagementScreenState();
}

class _CbtManagementScreenState extends State<CbtManagementScreen> with SingleTickerProviderStateMixin {
  final _db = DatabaseHelper();
  late TabController _tabController;

  List<Map<String, dynamic>> _exams = [];
  List<Map<String, dynamic>> _attempts = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadCbtData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadCbtData() async {
    setState(() => _loading = true);
    try {
      final auth = context.read<AuthProvider>();

      // 1. Fetch CBT Exams
      try {
        await auth.apiService.getWithCache('/homework'); // CBT relies on standard caching
        // In real backend, CBT endpoints are queried. Let's load from sqlite database
        _exams = await _db.getCbtExams();
      } catch (_) {
        _exams = await _db.getCbtExams();
      }

      // 2. Fetch CBT attempts
      try {
        _attempts = await _db.database.then(
          (db) => db.query('local_cbt_attempts', orderBy: 'started_at DESC')
        );
      } catch (_) {}

      setState(() => _loading = false);
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('CBT Exam Management', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          labelColor: primary,
          unselectedLabelColor: const Color(0xFF64748B),
          indicatorColor: primary,
          tabs: const [
            Tab(icon: Icon(Icons.computer), text: 'Active Exams'),
            Tab(icon: Icon(Icons.history_edu), text: 'Student Attempts'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadCbtData,
          ),
        ],
      ),
      body: _loading
          ? Center(child: CircularProgressIndicator(color: primary))
          : TabBarView(
              controller: _tabController,
              children: [
                _buildActiveExamsTab(primary),
                _buildAttemptsTab(primary),
              ],
            ),
    );
  }

  Widget _buildActiveExamsTab(Color primary) {
    if (_exams.isEmpty) {
      return const Center(child: Text('No active computer-based exams found.', style: TextStyle(color: Color(0xFF64748B))));
    }
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _exams.length,
      separatorBuilder: (_, index) => const SizedBox(height: 12),
      itemBuilder: (context, i) {
        final exam = _exams[i];
        final duration = exam['duration_minutes'] ?? 0;
        final passPercentage = exam['pass_percentage'] ?? 50.0;
        final totalQuestions = exam['total_questions'] ?? 0;

        return Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFF1F5F9)),
            boxShadow: const [BoxShadow(color: Color(0x05000000), blurRadius: 4, offset: Offset(0, 2))],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                exam['title'] ?? 'Exam',
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Icon(Icons.timer_outlined, size: 14, color: primary),
                  const SizedBox(width: 4),
                  Text('$duration mins', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                  const SizedBox(width: 16),
                  Icon(Icons.help_outline, size: 14, color: primary),
                  const SizedBox(width: 4),
                  Text('$totalQuestions questions', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                  const SizedBox(width: 16),
                  Icon(Icons.emoji_events_outlined, size: 14, color: primary),
                  const SizedBox(width: 4),
                  Text('Pass: $passPercentage%', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                ],
              ),
              if ((exam['instructions'] as String?)?.isNotEmpty == true) ...[
                const SizedBox(height: 10),
                Text(
                  exam['instructions'],
                  style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  Widget _buildAttemptsTab(Color primary) {
    if (_attempts.isEmpty) {
      return const Center(child: Text('No student exam attempts found.', style: TextStyle(color: Color(0xFF64748B))));
    }
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _attempts.length,
      separatorBuilder: (_, index) => const SizedBox(height: 10),
      itemBuilder: (context, i) {
        final attempt = _attempts[i];
        final score = (attempt['score'] as num?)?.toDouble() ?? 0.0;
        final date = (attempt['started_at'] as String?)?.substring(0, 16).replaceFirst('T', ' ') ?? '';
        final isDirty = (attempt['is_dirty'] as int? ?? 0) == 1;

        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFF1F5F9)),
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Attempt ID: #${attempt['id']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    const SizedBox(height: 4),
                    Text('Started: $date', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                    if (isDirty)
                      const Row(
                        children: [
                          Icon(Icons.cloud_off, size: 12, color: Color(0xFFF59E0B)),
                          SizedBox(width: 4),
                          Text('Pending sync', style: TextStyle(fontSize: 10, color: Color(0xFFF59E0B), fontWeight: FontWeight.bold)),
                        ],
                      ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: score >= 50 ? const Color(0xFFD1FAE5) : const Color(0xFFFEE2E2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${score.toStringAsFixed(1)}%',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    color: score >= 50 ? const Color(0xFF065F46) : const Color(0xFF991B1B),
                    fontSize: 13,
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
