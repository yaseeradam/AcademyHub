import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:sqflite/sqflite.dart';
import 'dart:convert';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/constants.dart';

class AnalyticsDashboardScreen extends StatefulWidget {
  const AnalyticsDashboardScreen({super.key});

  @override
  State<AnalyticsDashboardScreen> createState() => _AnalyticsDashboardScreenState();
}

class _AnalyticsDashboardScreenState extends State<AnalyticsDashboardScreen> with SingleTickerProviderStateMixin {
  final _db = DatabaseHelper();
  late TabController _tabController;
  bool _loading = true;

  // Selected filters
  int _selectedTerm = 1;
  String _selectedSession = '';
  List<String> _sessionsList = [];

  // Core metrics
  int _totalStudentsCount = 0;
  double _academicAverage = 0.0;
  double _highestScore = 0.0;
  double _lowestScore = 0.0;
  int _totalAssessments = 0;

  double _attendanceRate = 100.0;
  int _attPresent = 0;
  int _attAbsent = 0;
  int _attLate = 0;

  int _totalCbtAttempts = 0;
  int _completedCbtAttempts = 0;
  double _cbtAverageScore = 0.0;

  // Grade distributions
  Map<String, int> _gradeDistribution = {};
  List<Map<String, dynamic>> _subjectPerformances = [];
  List<Map<String, dynamic>> _classComparisonList = [];
  Map<String, int> _cbtDistribution = {};

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _initFilters().then((_) => _calculateMetrics());
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _initFilters() async {
    final prefs = await SharedPreferences.getInstance();
    _selectedTerm = prefs.getInt('active_term') ?? 1;
    _selectedSession = prefs.getString('active_session') ?? '2026/2027';

    // Fetch sessions list dynamically from existing database entries to populate filter
    final db = await _db.database;
    final scoresSessions = await db.query('local_scores', columns: ['session'], distinct: true);
    final attendanceSessions = await db.query('local_attendance', columns: ['session'], distinct: true);

    final sessions = <String>{};
    if (_selectedSession.isNotEmpty) sessions.add(_selectedSession);

    for (final row in scoresSessions) {
      final s = row['session'] as String?;
      if (s != null && s.isNotEmpty) sessions.add(s);
    }
    for (final row in attendanceSessions) {
      final s = row['session'] as String?;
      if (s != null && s.isNotEmpty) sessions.add(s);
    }

    if (sessions.isEmpty) {
      sessions.add('2026/2027');
    }

    setState(() {
      _sessionsList = sessions.toList()..sort((a, b) => b.compareTo(a));
      if (!_sessionsList.contains(_selectedSession)) {
        _selectedSession = _sessionsList.first;
      }
    });
  }

  Future<void> _calculateMetrics() async {
    if (mounted) setState(() => _loading = true);

    try {
      final db = await _db.database;

      // 1. Total Student Count
      final studentCount = Sqflite.firstIntValue(
            await db.rawQuery('SELECT COUNT(*) FROM local_students'),
          ) ?? 0;

      // 2. Academic Averages & Highs/Lows
      final scoreRes = await db.rawQuery(
        'SELECT AVG(total) as avg_score, MIN(total) as min_score, MAX(total) as max_score, COUNT(*) as count '
        'FROM local_scores WHERE term = ? AND session = ?',
        [_selectedTerm, _selectedSession],
      );
      final firstScoreRow = scoreRes.first;
      final avgScore = (firstScoreRow['avg_score'] as num?)?.toDouble() ?? 0.0;
      final minScore = (firstScoreRow['min_score'] as num?)?.toDouble() ?? 0.0;
      final maxScore = (firstScoreRow['max_score'] as num?)?.toDouble() ?? 0.0;
      final assessmentsCount = (firstScoreRow['count'] as num?)?.toInt() ?? 0;

      // Grade distribution calculation
      final gradesRes = await db.rawQuery(
        'SELECT grade, COUNT(*) as count FROM local_scores WHERE term = ? AND session = ? GROUP BY grade',
        [_selectedTerm, _selectedSession],
      );
      final gradeMap = <String, int>{};
      for (final row in gradesRes) {
        final g = (row['grade'] as String? ?? 'N/A').toUpperCase().trim();
        if (g.isNotEmpty) {
          gradeMap[g] = (row['count'] as num?)?.toInt() ?? 0;
        }
      }

      // Subject Rankings
      final subjectRes = await db.rawQuery(
        'SELECT subject_name, AVG(total) as avg_score, COUNT(*) as count '
        'FROM local_scores WHERE term = ? AND session = ? '
        'GROUP BY subject_name ORDER BY avg_score DESC',
        [_selectedTerm, _selectedSession],
      );
      final subjects = subjectRes.map((row) {
        return {
          'name': row['subject_name'] ?? 'Unknown',
          'avg': (row['avg_score'] as num?)?.toDouble() ?? 0.0,
          'count': (row['count'] as num?)?.toInt() ?? 0,
        };
      }).toList();

      // 3. Attendance rate
      final attRes = await db.rawQuery(
        'SELECT status, COUNT(*) as count FROM local_attendance WHERE term = ? AND session = ? GROUP BY status',
        [_selectedTerm, _selectedSession],
      );
      int present = 0;
      int absent = 0;
      int late = 0;
      for (final row in attRes) {
        final status = (row['status'] as String? ?? 'absent').toLowerCase();
        final count = (row['count'] as num?)?.toInt() ?? 0;
        if (status == 'present') {
          present = count;
        } else if (status == 'absent') {
          absent = count;
        } else if (status == 'late') {
          late = count;
        }
      }
      final totalAtt = present + absent + late;
      final attRate = totalAtt > 0 ? ((present + late) / totalAtt) * 100 : 100.0;

      // 4. CBT Stats
      final cbtRes = await db.rawQuery(
        'SELECT COUNT(*) as total, '
        'SUM(CASE WHEN submitted_at IS NOT NULL THEN 1 ELSE 0 END) as completed, '
        'AVG(score) as avg_score FROM local_cbt_attempts',
      );
      final firstCbtRow = cbtRes.first;
      final totalCbt = (firstCbtRow['total'] as num?)?.toInt() ?? 0;
      final completedCbt = (firstCbtRow['completed'] as num?)?.toInt() ?? 0;
      final avgCbt = (firstCbtRow['avg_score'] as num?)?.toDouble() ?? 0.0;

      // CBT distribution
      final cbtAttemptsRes = await db.rawQuery('SELECT score FROM local_cbt_attempts WHERE submitted_at IS NOT NULL');
      final cbtDist = {'90-100%': 0, '80-89%': 0, '70-79%': 0, '60-69%': 0, 'Below 60%': 0};
      for (final row in cbtAttemptsRes) {
        final score = (row['score'] as num?)?.toDouble() ?? 0.0;
        if (score >= 90) {
          cbtDist['90-100%'] = cbtDist['90-100%']! + 1;
        } else if (score >= 80) {
          cbtDist['80-89%'] = cbtDist['80-89%']! + 1;
        } else if (score >= 70) {
          cbtDist['70-79%'] = cbtDist['70-79%']! + 1;
        } else if (score >= 60) {
          cbtDist['60-69%'] = cbtDist['60-69%']! + 1;
        } else {
          cbtDist['Below 60%'] = cbtDist['Below 60%']! + 1;
        }
      }

      // 5. Class Comparison rankings
      final classNamesMap = await _loadClassNamesMap();
      final classScoresRes = await db.rawQuery(
        'SELECT class_id, AVG(total) as avg_score, COUNT(*) as assessments '
        'FROM local_scores WHERE term = ? AND session = ? GROUP BY class_id',
        [_selectedTerm, _selectedSession],
      );

      final classAttRes = await db.rawQuery(
        'SELECT class_id, COUNT(*) as total, '
        'SUM(CASE WHEN status = "present" OR status = "late" THEN 1 ELSE 0 END) as present '
        'FROM local_attendance WHERE term = ? AND session = ? GROUP BY class_id',
        [_selectedTerm, _selectedSession],
      );

      final classStudentsRes = await db.rawQuery(
        'SELECT class_id, COUNT(*) as count FROM local_students GROUP BY class_id',
      );

      final studentCounts = <int, int>{};
      for (final row in classStudentsRes) {
        studentCounts[row['class_id'] as int] = (row['count'] as num?)?.toInt() ?? 0;
      }

      final attendanceRates = <int, double>{};
      for (final row in classAttRes) {
        final cid = row['class_id'] as int;
        final total = (row['total'] as num?)?.toInt() ?? 0;
        final pres = (row['present'] as num?)?.toInt() ?? 0;
        attendanceRates[cid] = total > 0 ? (pres / total) * 100 : 100.0;
      }

      final comparisonList = <Map<String, dynamic>>[];
      for (final row in classScoresRes) {
        final cid = row['class_id'] as int;
        final avg = (row['avg_score'] as num?)?.toDouble() ?? 0.0;
        final assessments = (row['assessments'] as num?)?.toInt() ?? 0;
        comparisonList.add({
          'class_id': cid,
          'name': classNamesMap[cid] ?? 'Class $cid',
          'avg': avg,
          'students': studentCounts[cid] ?? 0,
          'attendance': attendanceRates[cid] ?? 100.0,
          'assessments': assessments,
        });
      }
      comparisonList.sort((a, b) => (b['avg'] as double).compareTo(a['avg'] as double));

      if (mounted) {
        setState(() {
          _totalStudentsCount = studentCount;
          _academicAverage = avgScore;
          _highestScore = maxScore;
          _lowestScore = minScore;
          _totalAssessments = assessmentsCount;
          _gradeDistribution = gradeMap;
          _subjectPerformances = subjects;

          _attendanceRate = attRate;
          _attPresent = present;
          _attAbsent = absent;
          _attLate = late;

          _totalCbtAttempts = totalCbt;
          _completedCbtAttempts = completedCbt;
          _cbtAverageScore = avgCbt;
          _cbtDistribution = cbtDist;

          _classComparisonList = comparisonList;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<Map<int, String>> _loadClassNamesMap() async {
    final map = <int, String>{};
    try {
      final cacheStr = await _db.getCache('/teacher/classes');
      if (cacheStr != null) {
        final data = jsonDecode(cacheStr);
        final list = (data['data'] as List? ?? data as List? ?? []);
        for (final item in list) {
          final id = item['id'] as int?;
          final name = item['name'] as String?;
          if (id != null && name != null) {
            map[id] = name;
          }
        }
      }
    } catch (_) {}

    try {
      final cacheStr = await _db.getCache('/students');
      if (cacheStr != null) {
        final data = jsonDecode(cacheStr);
        final list = (data['data'] as List? ?? data as List? ?? []);
        for (final item in list) {
          final classObj = item['school_class'];
          if (classObj != null) {
            final id = classObj['id'] as int?;
            final name = classObj['name'] as String?;
            if (id != null && name != null) {
              map[id] = name;
            }
          }
        }
      }
    } catch (_) {}
    return map;
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(
          'Analytics Dashboard',
          style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary),
        ),
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
          isScrollable: true,
          tabAlignment: TabAlignment.start,
          tabs: const [
            Tab(icon: Icon(Icons.dashboard_outlined), text: 'Overview'),
            Tab(icon: Icon(Icons.school_outlined), text: 'Academics'),
            Tab(icon: Icon(Icons.bar_chart_outlined), text: 'Classes'),
            Tab(icon: Icon(Icons.computer_outlined), text: 'CBT System'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _calculateMetrics,
          ),
        ],
      ),
      body: Column(
        children: [
          // Filter Bar
          Container(
            color: AppColors.surface,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            child: Row(
              children: [
                Expanded(
                  child: DropdownButtonFormField<String>(
                    initialValue: _selectedSession,
                    dropdownColor: AppColors.surface2,
                    style: GoogleFonts.inter(color: AppColors.textPrimary, fontSize: 12),
                    decoration: _filterDecoration('Session'),
                    items: _sessionsList
                        .map((s) => DropdownMenuItem(value: s, child: Text(s, style: GoogleFonts.inter(color: AppColors.textPrimary))))
                        .toList(),
                    onChanged: (val) {
                      if (val != null) {
                        setState(() => _selectedSession = val);
                        _calculateMetrics();
                      }
                    },
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: DropdownButtonFormField<int>(
                    initialValue: _selectedTerm,
                    dropdownColor: AppColors.surface2,
                    style: GoogleFonts.inter(color: AppColors.textPrimary, fontSize: 12),
                    decoration: _filterDecoration('Term'),
                    items: const [
                      DropdownMenuItem(value: 1, child: Text('1st Term')),
                      DropdownMenuItem(value: 2, child: Text('2nd Term')),
                      DropdownMenuItem(value: 3, child: Text('3rd Term')),
                    ],
                    onChanged: (val) {
                      if (val != null) {
                        setState(() => _selectedTerm = val);
                        _calculateMetrics();
                      }
                    },
                  ),
                ),
              ],
            ),
          ),
          if (_loading) LinearProgressIndicator(color: primary, minHeight: 2),
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildOverviewTab(primary),
                _buildAcademicsTab(primary),
                _buildClassesTab(primary),
                _buildCbtTab(primary),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ─── Filter decoration helper ──────────────────────────────────────────────
  InputDecoration _filterDecoration(String label) => InputDecoration(
        labelText: label,
        labelStyle: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 10),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: AppColors.borderLight)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: AppColors.borderLight)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: AppColors.borderLight)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        isDense: true,
        fillColor: AppColors.surface2,
        filled: true,
      );

  // ─── Overview Tab ──────────────────────────────────────────────────────────
  Widget _buildOverviewTab(Color primary) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Welcome Card
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [primary.withValues(alpha: 0.8), Colors.deepPurple.shade900],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: primary.withValues(alpha: 0.2),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'KPI Overview Console',
                style: GoogleFonts.inter(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
              ),
              const SizedBox(height: 4),
              Text(
                'Calculated offline from synced school registers for $_selectedSession - Term $_selectedTerm.',
                style: GoogleFonts.inter(fontSize: 11, color: Colors.white70),
              ),
            ],
          ),
        ).animate().fadeIn(duration: 400.ms).slideY(begin: 0.1, end: 0),
        const SizedBox(height: 16),

        // Grid View of Main Metrics
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: 1.35,
          children: [
            _buildQuickStatCard(
              label: 'Academic GPA',
              value: '${_academicAverage.toStringAsFixed(1)}%',
              icon: Icons.school_rounded,
              color: primary,
              trendMsg: '$_totalAssessments assessments',
            ),
            _buildQuickStatCard(
              label: 'Attendance Rate',
              value: '${_attendanceRate.toStringAsFixed(1)}%',
              icon: Icons.calendar_today_rounded,
              color: AppColors.success,
              trendMsg: '$_attPresent present • $_attAbsent absent • $_attLate late',
            ),
            _buildQuickStatCard(
              label: 'Enrolled Students',
              value: '$_totalStudentsCount',
              icon: Icons.group_rounded,
              color: AppColors.info,
              trendMsg: 'Active registers cached',
            ),
            _buildQuickStatCard(
              label: 'CBT Exam Avg',
              value: _totalCbtAttempts > 0 ? '${_cbtAverageScore.toStringAsFixed(1)}%' : 'N/A',
              icon: Icons.computer_rounded,
              color: AppColors.parentAccent,
              trendMsg: '$_completedCbtAttempts/$_totalCbtAttempts completed',
            ),
          ],
        ).animate().fadeIn(delay: 100.ms, duration: 400.ms),
        const SizedBox(height: 16),

        // Attendance Correlation Insight Card
        _buildAttendanceInsightCard(primary),
      ],
    );
  }

  Widget _buildQuickStatCard({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
    required String trendMsg,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderLight),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label,
                style: GoogleFonts.inter(fontSize: 10, color: AppColors.textSecondary, fontWeight: FontWeight.w600),
              ),
              Icon(icon, color: color, size: 16),
            ],
          ),
          const Spacer(),
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
              fontFeatures: const [FontFeature.tabularFigures()],
            ),
          ),
          const SizedBox(height: 4),
          Text(
            trendMsg,
            style: GoogleFonts.inter(fontSize: 9, color: AppColors.textMuted),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildAttendanceInsightCard(Color primary) {
    final bool hasRisk = _attendanceRate < 85.0;
    final bool hasExcellent = _attendanceRate >= 92.0 && _academicAverage >= 70.0;

    Color cardColor = AppColors.info.withValues(alpha: 0.08);
    Color borderColor = AppColors.info.withValues(alpha: 0.25);
    Color textColor = AppColors.info;
    String title = 'Statistical Insight';
    String message = 'Class performance remains stable. Maintain registers syncing for updated insights.';
    IconData icon = Icons.info_outline;

    if (hasRisk) {
      cardColor = AppColors.error.withValues(alpha: 0.08);
      borderColor = AppColors.error.withValues(alpha: 0.25);
      textColor = AppColors.error;
      title = 'Attendance Warning Flag';
      message = 'Overall school attendance is below 85%. Poor presence logs show high correlation to lower GPA grades.';
      icon = Icons.warning_amber_rounded;
    } else if (hasExcellent) {
      cardColor = AppColors.success.withValues(alpha: 0.08);
      borderColor = AppColors.success.withValues(alpha: 0.25);
      textColor = AppColors.success;
      title = 'Honor Roll Correlation';
      message = 'Outstanding! Strong class presence logs correlate directly to the positive average grade achievements.';
      icon = Icons.emoji_events_outlined;
    }

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: cardColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: borderColor),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: textColor, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: GoogleFonts.inter(fontWeight: FontWeight.bold, color: textColor, fontSize: 13),
                ),
                const SizedBox(height: 4),
                Text(
                  message,
                  style: GoogleFonts.inter(fontSize: 11, color: AppColors.textPrimary, height: 1.4),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ─── Academics Tab ─────────────────────────────────────────────────────────
  Widget _buildAcademicsTab(Color primary) {
    if (_subjectPerformances.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Text(
            'No subject scores loaded for $_selectedSession.',
            style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 13),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // GPA Ranges card
        Row(
          children: [
            Expanded(
              child: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.borderLight),
                ),
                child: Column(
                  children: [
                    Text('Highest Task Score', style: GoogleFonts.inter(fontSize: 10, color: AppColors.textSecondary, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    Text('${_highestScore.toStringAsFixed(1)}%', style: GoogleFonts.inter(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.success)),
                  ],
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.borderLight),
                ),
                child: Column(
                  children: [
                    Text('Lowest Task Score', style: GoogleFonts.inter(fontSize: 10, color: AppColors.textSecondary, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    Text('${_lowestScore.toStringAsFixed(1)}%', style: GoogleFonts.inter(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.error)),
                  ],
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        // Subject Rankings Header
        Text(
          'Top Performing Subjects',
          style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        ),
        const SizedBox(height: 10),

        // List of subject performance with progress bars
        Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _subjectPerformances.length,
            separatorBuilder: (context, index) => Divider(height: 1, color: AppColors.borderLight),
            itemBuilder: (context, i) {
              final sub = _subjectPerformances[i];
              final double score = sub['avg'];
              final String name = sub['name'];
              final int count = sub['count'];
              final double normalized = (score / 100.0).clamp(0.0, 1.0);

              Color progressColor = primary;
              if (score >= 70) {
                progressColor = AppColors.success;
              } else if (score < 50) {
                progressColor = AppColors.error;
              }

              return Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            name,
                            style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        Text(
                          '${score.toStringAsFixed(1)}%',
                          style: GoogleFonts.inter(
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                            color: progressColor,
                            fontFeatures: const [FontFeature.tabularFigures()],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Expanded(
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(4),
                            child: LinearProgressIndicator(
                              value: normalized,
                              backgroundColor: AppColors.surface2,
                              color: progressColor,
                              minHeight: 6,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Text(
                          '$count entries',
                          style: GoogleFonts.inter(fontSize: 10, color: AppColors.textMuted),
                        ),
                      ],
                    ),
                  ],
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 24),

        // Grade Distribution Header
        Text(
          'Score Grade Distribution',
          style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        ),
        const SizedBox(height: 10),

        // Custom Grade count distribution indicators
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Column(
            children: ['A', 'B', 'C', 'D', 'F'].map((g) {
              final count = _gradeDistribution[g] ?? 0;
              final double maxCount = _gradeDistribution.values.isEmpty
                  ? 1.0
                  : _gradeDistribution.values.reduce((a, b) => a > b ? a : b).toDouble();
              final double ratio = maxCount > 0 ? count / maxCount : 0.0;

              Color gradeColor = AppColors.info;
              if (g == 'A' || g == 'B') gradeColor = AppColors.success;
              if (g == 'F') gradeColor = AppColors.error;

              return Padding(
                padding: const EdgeInsets.only(bottom: 8.0),
                child: Row(
                  children: [
                    SizedBox(
                      width: 24,
                      child: Text(
                        g,
                        style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary),
                      ),
                    ),
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: LayoutBuilder(
                          builder: (ctx, constraints) {
                            return Stack(
                              children: [
                                Container(height: 18, color: AppColors.surface2),
                                FractionallySizedBox(
                                  widthFactor: ratio.clamp(0.02, 1.0),
                                  child: Container(
                                    height: 18,
                                    decoration: BoxDecoration(
                                      color: gradeColor.withValues(alpha: 0.8),
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                  ),
                                ),
                                Positioned.fill(
                                  left: 8,
                                  child: Align(
                                    alignment: Alignment.centerLeft,
                                    child: Text(
                                      '$count grades',
                                      style: GoogleFonts.inter(
                                        fontSize: 9,
                                        fontWeight: FontWeight.bold,
                                        color: ratio > 0.4 ? Colors.white : AppColors.textSecondary,
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            );
                          },
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }

  // ─── Classes Tab ───────────────────────────────────────────────────────────
  Widget _buildClassesTab(Color primary) {
    if (_classComparisonList.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Text(
            'No class rankings computed yet. Ensure scores are assigned to classes.',
            style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 13),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Text(
          'Class Rankings by Performance',
          style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        ),
        const SizedBox(height: 10),

        // Custom list representing the comparison table
        ...List.generate(_classComparisonList.length, (i) {
          final c = _classComparisonList[i];
          final double avg = c['avg'];
          final double attendance = c['attendance'];
          final int students = c['students'];
          final int assessments = c['assessments'];

          Color badgeColor = AppColors.info;
          if (avg >= 70) {
            badgeColor = AppColors.success;
          } else if (avg < 50) {
            badgeColor = AppColors.error;
          }

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.borderLight),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.02),
                  blurRadius: 4,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                // Rank Number Circle
                CircleAvatar(
                  radius: 14,
                  backgroundColor: primary.withValues(alpha: 0.12),
                  child: Text(
                    '${i + 1}',
                    style: TextStyle(color: primary, fontWeight: FontWeight.bold, fontSize: 11),
                  ),
                ),
                const SizedBox(width: 12),

                // Name and students stats
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        c['name'],
                        style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '$students students • $assessments tasks',
                        style: GoogleFonts.inter(fontSize: 10, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),

                // Attendance + Average Score metrics
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: badgeColor.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Text(
                        '${avg.toStringAsFixed(1)}% GPA',
                        style: GoogleFonts.inter(
                          color: badgeColor,
                          fontWeight: FontWeight.bold,
                          fontSize: 11,
                        ),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.calendar_today_rounded, size: 9, color: AppColors.textMuted),
                        const SizedBox(width: 4),
                        Text(
                          '${attendance.toStringAsFixed(0)}% Att.',
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            color: attendance < 85 ? AppColors.error : AppColors.textSecondary,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ).animate().fadeIn(delay: (i * 50).ms, duration: 300.ms);
        }),
      ],
    );
  }

  // ─── CBT Tab ───────────────────────────────────────────────────────────────
  Widget _buildCbtTab(Color primary) {
    if (_totalCbtAttempts == 0) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Text(
            'No computer-based exam data available.',
            style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 13),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // KPI Summary metrics
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildCbtSummaryItem(
                value: '${((_completedCbtAttempts / _totalCbtAttempts) * 100).toStringAsFixed(0)}%',
                label: 'Completion',
                color: primary,
              ),
              _buildCbtSummaryItem(
                value: '${_cbtAverageScore.toStringAsFixed(1)}%',
                label: 'Average Score',
                color: AppColors.success,
              ),
              _buildCbtSummaryItem(
                value: '$_totalCbtAttempts',
                label: 'Attempts',
                color: AppColors.info,
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Distribution list
        Text(
          'Attempts Score Range Distribution',
          style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        ),
        const SizedBox(height: 10),

        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Column(
            children: _cbtDistribution.entries.map((entry) {
              final double ratio = _totalCbtAttempts > 0 ? entry.value / _totalCbtAttempts : 0.0;
              Color rangeColor = primary;
              if (entry.key.contains('Below') || entry.key.contains('60')) {
                rangeColor = AppColors.error;
              } else if (entry.key.contains('90') || entry.key.contains('80')) {
                rangeColor = AppColors.success;
              }

              return Padding(
                padding: const EdgeInsets.only(bottom: 8.0),
                child: Row(
                  children: [
                    SizedBox(
                      width: 80,
                      child: Text(
                        entry.key,
                        style: GoogleFonts.inter(fontSize: 11, color: AppColors.textSecondary, fontWeight: FontWeight.bold),
                      ),
                    ),
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: Stack(
                          children: [
                            Container(height: 14, color: AppColors.surface2),
                            FractionallySizedBox(
                              widthFactor: ratio.clamp(0.01, 1.0),
                              child: Container(
                                height: 14,
                                decoration: BoxDecoration(
                                  color: rangeColor.withValues(alpha: 0.8),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      '${entry.value}',
                      style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 11, color: AppColors.textPrimary),
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }

  Widget _buildCbtSummaryItem({
    required String value,
    required String label,
    required Color color,
  }) {
    return Column(
      children: [
        Text(
          value,
          style: GoogleFonts.inter(
            fontSize: 22,
            fontWeight: FontWeight.bold,
            color: color,
            fontFeatures: const [FontFeature.tabularFigures()],
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: GoogleFonts.inter(fontSize: 10, color: AppColors.textSecondary, fontWeight: FontWeight.w600),
        ),
      ],
    );
  }
}
