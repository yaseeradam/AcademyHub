import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/constants.dart';

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
    if (mounted) setState(() => _loading = true);
    try {
      final auth = context.read<AuthProvider>();

      // 1. Fetch CBT Exams
      try {
        await auth.apiService.getWithCache('/homework'); // CBT relies on standard caching
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

      if (mounted) setState(() => _loading = false);
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('CBT Exam Management', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary)),
        backgroundColor: AppColors.surface,
        foregroundColor: AppColors.textPrimary,
        elevation: 0,
        shape: Border(bottom: BorderSide(color: AppColors.borderLight)),
        bottom: TabBar(
          controller: _tabController,
          labelColor: primary,
          unselectedLabelColor: AppColors.textSecondary,
          indicatorColor: primary,
          dividerColor: AppColors.borderLight,
          tabs: const [
            Tab(icon: Icon(Icons.computer_rounded), text: 'Active Exams'),
            Tab(icon: Icon(Icons.history_edu_rounded), text: 'Student Attempts'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
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
      return Center(child: Text('No active computer-based exams found.', style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 14)));
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
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                exam['title'] ?? 'Exam',
                style: GoogleFonts.spaceGrotesk(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Icon(Icons.timer_outlined, size: 14, color: primary),
                  const SizedBox(width: 4),
                  Text('$duration mins', style: GoogleFonts.spaceGrotesk(fontSize: 12, color: AppColors.textSecondary)),
                  const SizedBox(width: 16),
                  Icon(Icons.help_outline_rounded, size: 14, color: primary),
                  const SizedBox(width: 4),
                  Text('$totalQuestions questions', style: GoogleFonts.spaceGrotesk(fontSize: 12, color: AppColors.textSecondary)),
                  const SizedBox(width: 16),
                  Icon(Icons.emoji_events_outlined, size: 14, color: primary),
                  const SizedBox(width: 4),
                  Text('Pass: $passPercentage%', style: GoogleFonts.spaceGrotesk(fontSize: 12, color: AppColors.textSecondary)),
                ],
              ),
              if ((exam['instructions'] as String?)?.isNotEmpty == true) ...[
                const SizedBox(height: 10),
                Text(
                  exam['instructions'],
                  style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary),
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
      return Center(child: Text('No student exam attempts found.', style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 14)));
    }
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: _attempts.length,
      separatorBuilder: (_, index) => const SizedBox(height: 10),
      itemBuilder: (context, i) {
        final attempt = _attempts[i];
        final rawScore = attempt['score'];
        double score = 0.0;
        if (rawScore != null) {
          if (rawScore is num) {
            score = rawScore.toDouble();
          } else if (rawScore is String) {
            score = double.tryParse(rawScore) ?? 0.0;
          }
        }
        final date = (attempt['started_at'] as String?)?.substring(0, 16).replaceFirst('T', ' ') ?? '';
        final isDirty = (attempt['is_dirty'] as int? ?? 0) == 1;

        final passed = score >= 50.0;

        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Attempt ID: #${attempt['id']}', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary)),
                    const SizedBox(height: 4),
                    Text('Started: $date', style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary)),
                    if (isDirty)
                      Row(
                        children: [
                          const Icon(Icons.cloud_off_rounded, size: 12, color: AppColors.warning),
                          const SizedBox(width: 4),
                          Text('Pending sync', style: GoogleFonts.spaceGrotesk(fontSize: 10, color: AppColors.warning, fontWeight: FontWeight.bold)),
                        ],
                      ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: passed 
                      ? AppColors.success.withValues(alpha: 0.12) 
                      : AppColors.error.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: passed 
                        ? AppColors.success.withValues(alpha: 0.25) 
                        : AppColors.error.withValues(alpha: 0.25),
                  ),
                ),
                child: Text(
                  '${score.toStringAsFixed(1)}%',
                  style: GoogleFonts.spaceGrotesk(
                    fontWeight: FontWeight.bold,
                    color: passed ? AppColors.success : AppColors.error,
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
