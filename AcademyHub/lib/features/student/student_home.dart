import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';
import '../../core/constants.dart';
import 'cbt_exam_screen.dart';
import 'notes_download_tile.dart';
import 'homework_submit_sheet.dart';

class StudentHome extends StatefulWidget {
  const StudentHome({super.key});

  @override
  State<StudentHome> createState() => _StudentHomeState();
}

class _StudentHomeState extends State<StudentHome>
    with TickerProviderStateMixin {
  final _db = DatabaseHelper();

  Map<String, dynamic>? _studentStats;
  List<Map<String, dynamic>> _reportSubjects = [];
  List<Map<String, dynamic>> _homework       = [];
  List<Map<String, dynamic>> _timetable      = [];
  List<Map<String, dynamic>> _announcements  = [];
  List<Map<String, dynamic>> _cbtExams       = [];
  List<Map<String, dynamic>> _elearningNotes = [];

  bool _loading = true;
  String _noteSearchQuery = '';
  String? _selectedSubjectFilter;
  int _selectedTab = 0;
  bool _isLoadingData = false;

  void _selectTabByLabel(List<AHNavItem> activeTabs, String label) {
    final idx = activeTabs.indexWhere((t) => t.label == label);
    if (idx != -1) {
      setState(() => _selectedTab = idx);
    }
  }

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    if (_isLoadingData) return;
    _isLoadingData = true;
    final auth = context.read<AuthProvider>();
    final admissionNo = auth.user?.email ?? '';
    if (mounted) setState(() => _loading = true);
    try {
      _studentStats   = await _db.getStudentStats(admissionNo);
      _cbtExams       = await _db.getCbtExams();
      _elearningNotes = await _db.getELearningNotes();
      _homework       = await _db.getAllHomework();
      _timetable      = await _db.getTimetable();
      _announcements  = await _db.getAnnouncements();
      _reportSubjects = await _db.getScores(0, 1, '');
    } catch (_) {}
    finally {
      _isLoadingData = false;
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _triggerRefresh() async {
    if (_isLoadingData) return;
    if (mounted) setState(() => _loading = true);
    final auth = context.read<AuthProvider>();
    try {
      await Future.wait([
        auth.syncService.backgroundRefresh('student'),
        auth.refreshPlugins(),
      ]);
      await _loadData();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Sync failed: $e')),
        );
      }
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth    = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;
    const accent  = AppColors.studentAccent;

    final activeTabs = <AHNavItem>[
      const AHNavItem(
        icon: Icons.dashboard_outlined,
        activeIcon: Icons.dashboard_rounded,
        label: 'Feed',
        iconBg: Color(0xFFE0E7FF),
        iconColor: Color(0xFF4F46E5),
      ),
      const AHNavItem(
        icon: Icons.bar_chart_outlined,
        activeIcon: Icons.bar_chart_rounded,
        label: 'Results',
        iconBg: Color(0xFFE0F2FE),
        iconColor: Color(0xFF0284C7),
      ),
    ];

    final activePages = <Widget>[
      _buildFeed(primary, accent, activeTabs),
      _buildResults(primary),
    ];

    if (auth.isPluginActive('e-learning')) {
      activeTabs.add(const AHNavItem(
        icon: Icons.menu_book_outlined,
        activeIcon: Icons.menu_book_rounded,
        label: 'Learn',
        iconBg: Color(0xFFF3E8FF),
        iconColor: Color(0xFF7C3AED),
      ));
      activePages.add(_buildELearning(primary));
    }

    if (auth.isPluginActive('cbt')) {
      activeTabs.add(const AHNavItem(
        icon: Icons.computer_outlined,
        activeIcon: Icons.computer_rounded,
        label: 'Exams',
        iconBg: Color(0xFFFFE4E6),
        iconColor: Color(0xFFE11D48),
      ));
      activePages.add(_buildCbtExams(primary));
    }

    activeTabs.add(const AHNavItem(
      icon: Icons.event_note_outlined,
      activeIcon: Icons.event_note_rounded,
      label: 'Schedule',
      iconBg: Color(0xFFFEF9C3),
      iconColor: Color(0xFFCA8A04),
    ));
    activePages.add(_buildSchedule(primary));

    int currentTab = _selectedTab;
    if (currentTab >= activeTabs.length) {
      currentTab = 0;
    }

    return RoleShell(
      title: 'Student Portal',
      navItems: activeTabs,
      selectedIndex: currentTab,
      onTabSelected: (i) => setState(() => _selectedTab = i),
      accentColor: accent,
      loading: _loading,
      onRefresh: _triggerRefresh,
      body: activePages[currentTab],
    );
  }

  // ─── Feed Tab ──────────────────────────────────────────────────────────────
  Widget _buildFeed(Color primary, Color accent, List<AHNavItem> activeTabs) {
    final stats         = _studentStats;
    final rawAtt = stats?['attendance_rate'];
    double attendanceRate = 0.0;
    if (rawAtt != null) {
      if (rawAtt is num) {
        attendanceRate = rawAtt.toDouble();
      } else if (rawAtt is String) {
        attendanceRate = double.tryParse(rawAtt) ?? 0.0;
      }
    }

    final rawScore = stats?['average_score'];
    double averageScore = 0.0;
    if (rawScore != null) {
      if (rawScore is num) {
        averageScore = rawScore.toDouble();
      } else if (rawScore is String) {
        averageScore = double.tryParse(rawScore) ?? 0.0;
      }
    }
    final classRank      = stats?['class_rank'] ?? 0;
    final pendingHw      = stats?['pending_homework'] ?? 0;
    final auth           = context.watch<AuthProvider>();
    final user           = auth.user;
    final photoUrl       = auth.getReachableUrl(user?.profilePhotoUrl);

    final isCompact = MediaQuery.of(context).size.width < 500;
    final activeTerm = stats?['current_term'] ?? '1';

    final breakdown = stats?['grades_breakdown'] as Map<String, dynamic>?;
    final extra = breakdown?['extra'] as Map<String, dynamic>?;
    final totalDays = extra?['total_days'] ?? 0;
    final presentDays = extra?['present_days'] ?? 0;
    final totalSubjectsVal = extra?['total_subjects'] ?? 0;
    final overdueHomework = extra?['overdue_homework'] ?? 0;
    final classmatesCount = stats?['classmates_count'] ?? 0;
    final gradesMap = (breakdown?['grades'] as Map<String, dynamic>?) ?? {};

    final statCards = [
      _LaravelGradientStatCard(
        value: '${attendanceRate.toStringAsFixed(1)}%',
        label: 'Attendance',
        subLabel: '$presentDays/$totalDays days',
        icon: Icons.check_circle_rounded,
        gradientColors: const [Color(0xFF34D399), Color(0xFF10B981)], 
        onTap: () => context.push('/student-attendance'),
      ),
      _LaravelGradientStatCard(
        value: '${averageScore.toStringAsFixed(1)}%',
        label: 'Average Score',
        subLabel: '$totalSubjectsVal subjects',
        icon: Icons.bar_chart_rounded,
        gradientColors: const [Color(0xFF3B82F6), Color(0xFF4F46E5)], 
        onTap: () => context.push('/performance'),
      ),
      _LaravelGradientStatCard(
        value: classRank > 0 ? '#$classRank' : 'N/A',
        label: 'Class Position',
        subLabel: 'of $classmatesCount students',
        icon: Icons.stars_rounded,
        gradientColors: const [Color(0xFFFBBF24), Color(0xFFF97316)],
      ),
      _LaravelGradientStatCard(
        value: '$pendingHw',
        label: 'Pending HW',
        subLabel: overdueHomework > 0 ? '$overdueHomework overdue' : 'All up to date',
        icon: Icons.assignment_turned_in_rounded,
        gradientColors: overdueHomework > 0
            ? const [Color(0xFFEF4444), Color(0xFFE11D48)] 
            : const [Color(0xFF8B5CF6), Color(0xFF7C3AED)], 
        onTap: () => _selectTabByLabel(activeTabs, 'Schedule'),
      ),
    ];

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      children: [
        // ── Laravel-style Student Header Card ───────────────────────────
        ClipRRect(
          borderRadius: BorderRadius.circular(16),
          child: Container(
            width: double.infinity,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  Color(0xFFFF2D20), 
                  Color(0xFFFF5247), 
                  Color(0xFFFF7A70),
                ],
              ),
            ),
            child: Stack(
              children: [
                Positioned(
                  right: -30,
                  top: -30,
                  child: Container(
                    width: 140,
                    height: 140,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.white.withValues(alpha: 0.1),
                    ),
                  ),
                ),
                Positioned(
                  right: 40,
                  bottom: -30,
                  child: Container(
                    width: 90,
                    height: 90,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.white.withValues(alpha: 0.06),
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(20),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Container(
                                        width: 6,
                                        height: 6,
                                        decoration: const BoxDecoration(
                                          shape: BoxShape.circle,
                                          color: Color(0xFF34D399), 
                                        ),
                                      ),
                                      const SizedBox(width: 6),
                                      Text(
                                        'STUDENT PORTAL',
                                        style: GoogleFonts.inter(
                                          color: Colors.white,
                                          fontSize: 9,
                                          fontWeight: FontWeight.bold,
                                          letterSpacing: 1.0,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                GestureDetector(
                                  onTap: _triggerRefresh,
                                  child: Container(
                                    padding: const EdgeInsets.all(8),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.15),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: const Icon(Icons.sync_rounded,
                                        color: Colors.white, size: 16),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 14),
                            Text(
                              'Welcome, ${user?.name.split(' ')[0] ?? 'Student'}!',
                              style: GoogleFonts.inter(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                                letterSpacing: -0.5,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              stats?['class_name'] ?? 'Class Details',
                              style: GoogleFonts.inter(
                                fontSize: 13,
                                fontWeight: FontWeight.w500,
                                color: Colors.white.withValues(alpha: 0.8), 
                              ),
                            ),
                            const SizedBox(height: 16),
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.badge_outlined, color: Colors.white, size: 12),
                                      const SizedBox(width: 4),
                                      Text(
                                        user?.email ?? '',
                                        style: GoogleFonts.inter(
                                          fontSize: 11,
                                          fontWeight: FontWeight.bold,
                                          color: Colors.white,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.class_outlined, color: Colors.white, size: 12),
                                      const SizedBox(width: 4),
                                      Text(
                                        'Term $activeTerm',
                                        style: GoogleFonts.inter(
                                          fontSize: 11,
                                          fontWeight: FontWeight.bold,
                                          color: Colors.white,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      if (photoUrl != null) ...[
                        const SizedBox(width: 12),
                        Container(
                          width: 80,
                          height: 100,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.white24, width: 2),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.15),
                                blurRadius: 8,
                                offset: const Offset(0, 4),
                              )
                            ],
                          ),
                          child: ClipRRect(
                            child: Image.network(
                              photoUrl,
                              fit: BoxFit.cover,
                              alignment: Alignment.topCenter,
                              errorBuilder: (_, __, ___) => Container(
                                color: Colors.white12,
                                child: const Icon(Icons.person, color: Colors.white30, size: 36),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
        )
            .animate()
            .fade(duration: 400.ms)
            .slideY(begin: 0.1, end: 0, duration: 400.ms, curve: Curves.easeOutQuad),
        const SizedBox(height: 20),

        // ── Laravel-style 4 Gradient Stat Cards (Responsive) ──────────────────
        (isCompact
            ? Column(
                children: [
                  Row(
                    children: [
                      Expanded(child: statCards[0]),
                      const SizedBox(width: 12),
                      Expanded(child: statCards[1]),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(child: statCards[2]),
                      const SizedBox(width: 12),
                      Expanded(child: statCards[3]),
                    ],
                  ),
                ],
              )
            : Row(
                children: [
                  Expanded(child: statCards[0]),
                  const SizedBox(width: 12),
                  Expanded(child: statCards[1]),
                  const SizedBox(width: 12),
                  Expanded(child: statCards[2]),
                  const SizedBox(width: 12),
                  Expanded(child: statCards[3]),
                ],
              ))
            .animate()
            .fade(duration: 400.ms, delay: 100.ms)
            .slideY(begin: 0.1, end: 0, duration: 400.ms, curve: Curves.easeOutQuad),
        const SizedBox(height: 24),

        // ── Priority strip ───────────────────────────────────────────────
        if (_cbtExams.isNotEmpty) ...[
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppColors.warning.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                  color: AppColors.warning.withValues(alpha: 0.25)),
            ),
            child: Row(
              children: [
                const Icon(Icons.notification_important_rounded,
                    color: AppColors.warning, size: 18),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    '${_cbtExams.length} active CBT exam${_cbtExams.length == 1 ? '' : 's'} pending completion.',
                    style: GoogleFonts.inter(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: AppColors.warning),
                  ),
                ),
                GestureDetector(
                  onTap: () => _selectTabByLabel(activeTabs, 'Exams'),
                  child: Text('View',
                      style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: AppColors.warning)),
                ),
              ],
            ),
          )
              .animate()
              .fade(duration: 400.ms, delay: 150.ms),
          const SizedBox(height: 16),
        ],

        // ── Two Columns: Quick Actions & Grade Distribution ──────────────────
        LayoutBuilder(
          builder: (context, constraints) {
            final useVerticalLayout = constraints.maxWidth < 650;
            final quickActionsWidget = Container(
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.borderLight),
                boxShadow: AppColors.subtleShadow,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Quick Actions',
                          style: GoogleFonts.inter(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Navigate to your portal sections',
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            color: AppColors.textMuted,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Divider(height: 1, color: AppColors.borderLight),
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Column(
                      children: [
                        _QuickActionRow(
                          label: 'Homework',
                          sub: 'Check & submit assignments',
                          icon: Icons.assignment_outlined,
                          gradientColors: const [Color(0xFF8B5CF6), Color(0xFF7C3AED)], 
                          onTap: () => _selectTabByLabel(activeTabs, 'Schedule'),
                        ),
                        _QuickActionRow(
                          label: 'Results',
                          sub: 'View your term scores',
                          icon: Icons.bar_chart_outlined,
                          gradientColors: const [Color(0xFF3B82F6), Color(0xFF4F46E5)], 
                          onTap: () => _selectTabByLabel(activeTabs, 'Results'),
                        ),
                        _QuickActionRow(
                          label: 'Attendance',
                          sub: 'View your attendance record',
                          icon: Icons.calendar_today_rounded,
                          gradientColors: const [Color(0xFF2DD4BF), Color(0xFF0D9488)], 
                          onTap: () => context.push('/student-attendance'),
                        ),
                        _QuickActionRow(
                          label: 'Performance',
                          sub: 'Track your progress',
                          icon: Icons.insights_rounded,
                          gradientColors: const [Color(0xFFFBBF24), Color(0xFFF97316)], 
                          onTap: () => context.push('/performance'),
                        ),
                        _QuickActionRow(
                          label: 'Exams',
                          sub: 'Upcoming CBT exams',
                          icon: Icons.computer_rounded,
                          gradientColors: const [Color(0xFFF43F5E), Color(0xFFD946EF)], 
                          onTap: () => _selectTabByLabel(activeTabs, 'Exams'),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );

            final gradeKeys = ['A', 'B', 'C', 'D', 'E', 'F'];
            final activeColors = {
              'A': [const Color(0xFFECFDF5), const Color(0xFF6EE7B7), const Color(0xFF059669)],
              'B': [const Color(0xFFEFF6FF), const Color(0xFF93C5FD), const Color(0xFF2563EB)],
              'C': [const Color(0xFFFEF3C7), const Color(0xFFFCD34D), const Color(0xFFD97706)],
              'D': [const Color(0xFFFFF7ED), const Color(0xFFFDBA74), const Color(0xFFEA580C)],
              'E': [const Color(0xFFFEF2F2), const Color(0xFFFCA5A5), const Color(0xFFEF4444)],
              'F': [const Color(0xFFFEF2F2), const Color(0xFFFCA5A5), const Color(0xFFDC2626)],
            };

            final gradeDistributionWidget = Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.borderLight),
                boxShadow: AppColors.subtleShadow,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Grade Distribution',
                            style: GoogleFonts.inter(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimary,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Current term performance by grade',
                            style: GoogleFonts.inter(
                              fontSize: 11,
                              color: AppColors.textMuted,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF0FDF4),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          'This Term',
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF16A34A),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 3,
                      crossAxisSpacing: 10,
                      mainAxisSpacing: 10,
                      childAspectRatio: 1.1,
                    ),
                    itemCount: gradeKeys.length,
                    itemBuilder: (ctx, idx) {
                      final grade = gradeKeys[idx];
                      final count = gradesMap[grade] ?? 0;
                      final colors = activeColors[grade]!;
                      final hasCount = count > 0;

                      return Container(
                        decoration: BoxDecoration(
                          color: hasCount ? colors[0] : AppColors.background,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: hasCount ? colors[1] : AppColors.borderLight,
                            width: 1.5,
                          ),
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              grade,
                              style: GoogleFonts.inter(
                                fontSize: 20,
                                fontWeight: FontWeight.w900,
                                color: hasCount ? colors[2] : AppColors.textSecondary,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              '$count subj',
                              style: GoogleFonts.inter(
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                                color: hasCount ? colors[2] : AppColors.textMuted,
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 20),
                  // Progress bars for major grades
                  ...['A', 'B', 'C'].map((g) {
                    final cnt = gradesMap[g] ?? 0;
                    final total = totalSubjectsVal > 0 ? totalSubjectsVal : 1;
                    final percentage = (cnt / total * 100).clamp(0.0, 100.0);
                    final colors = activeColors[g]!;

                    if (cnt == 0) return const SizedBox.shrink();

                    return Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: Row(
                        children: [
                          SizedBox(
                            width: 20,
                            child: Text(
                              g,
                              style: GoogleFonts.inter(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ),
                          Expanded(
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(4),
                              child: LinearProgressIndicator(
                                value: percentage / 100.0,
                                color: colors[2],
                                backgroundColor: AppColors.background,
                                minHeight: 6,
                              ),
                            ),
                          ),
                          const SizedBox(width: 10),
                          SizedBox(
                            width: 32,
                            child: Text(
                              '${percentage.toStringAsFixed(0)}%',
                              textAlign: TextAlign.right,
                              style: GoogleFonts.inter(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  }),
                ],
              ),
            );

            if (useVerticalLayout) {
              return Column(
                children: [
                  quickActionsWidget,
                  const SizedBox(height: 20),
                  gradeDistributionWidget,
                ],
              );
            } else {
              return Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(flex: 3, child: quickActionsWidget),
                  const SizedBox(width: 20),
                  Expanded(flex: 4, child: gradeDistributionWidget),
                ],
              );
            }
          },
        )
            .animate()
            .fade(duration: 400.ms, delay: 200.ms)
            .slideY(begin: 0.1, end: 0, duration: 400.ms, curve: Curves.easeOutQuad),
        const SizedBox(height: 24),

        // ── Announcements ────────────────────────────────────────────────
        const SectionHeader(title: 'School Bulletin'),
        const SizedBox(height: 12),
        _announcements.isEmpty
            ? _emptyState('No announcements yet.')
            : Column(
                children: _announcements.take(3).map((a) {
                  final date = (a['published_at'] as String?)?.substring(0, 10) ?? '';
                  return Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: AppColors.borderLight),
                      boxShadow: AppColors.subtleShadow,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(a['title'] ?? '',
                                  style: GoogleFonts.inter(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                      color: AppColors.textPrimary)),
                            ),
                            Text(date,
                                style: GoogleFonts.inter(
                                    fontSize: 11,
                                    color: AppColors.textMuted)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(a['body'] ?? '',
                            style: GoogleFonts.inter(
                                fontSize: 12,
                                color: AppColors.textSecondary,
                                height: 1.4)),
                      ],
                    ),
                  );
                }).toList(),
              ),
      ],
    );
  }



  // ─── Results Tab ───────────────────────────────────────────────────────────
  Widget _buildResults(Color primary) {
    if (_reportSubjects.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          const SizedBox(height: 80),
          _emptyState('No academic results recorded yet.'),
        ],
      );
    }

    return ListView.separated(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      itemCount: _reportSubjects.length,
      separatorBuilder: (_, _) => const SizedBox(height: 10),
      itemBuilder: (ctx, i) {
        final s = _reportSubjects[i];
        final grade = s['grade'] as String? ?? 'F';
        final total = s['total'] ?? 0;
        final gradeColor = _gradeColor(grade);

        return Container(
          padding: const EdgeInsets.all(16),
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
                    Text(s['subject_name'] ?? 'Subject',
                        style: GoogleFonts.inter(
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                            color: AppColors.textPrimary)),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        _scorePill('CA1', s['ca1']),
                        const SizedBox(width: 6),
                        _scorePill('CA2', s['ca2']),
                        const SizedBox(width: 6),
                        _scorePill('Exam', s['exam']),
                      ],
                    ),
                    const SizedBox(height: 8),
                    // Progress bar
                    ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: LinearProgressIndicator(
                        value: (total is num ? total.toDouble() : 0.0) / 100.0,
                        backgroundColor: AppColors.surface2,
                        color: gradeColor,
                        minHeight: 4,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 16),
              Column(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: gradeColor.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                          color: gradeColor.withValues(alpha: 0.3)),
                    ),
                    alignment: Alignment.center,
                    child: Text(grade,
                        style: GoogleFonts.inter(
                            color: gradeColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 18)),
                  ),
                  const SizedBox(height: 4),
                  Text('$total pts',
                      style: GoogleFonts.inter(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textSecondary)),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Color _gradeColor(String g) {
    switch (g) {
      case 'A': return AppColors.success;
      case 'B': return AppColors.info;
      case 'C': return AppColors.primary;
      case 'F': return AppColors.error;
      default: return AppColors.warning;
    }
  }

  Widget _scorePill(String label, dynamic value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: AppColors.surface2,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: Text('$label: ${value ?? 0}',
          style: GoogleFonts.inter(
              fontSize: 10,
              color: AppColors.textSecondary,
              fontWeight: FontWeight.w600)),
    );
  }

  // ─── E-Learning Tab ────────────────────────────────────────────────────────
  Widget _buildELearning(Color primary) {
    final filteredNotes = _elearningNotes.where((note) {
      final title   = (note['title'] as String? ?? '').toLowerCase();
      final subject = (note['subject_name'] as String? ?? '').toLowerCase();
      final q       = _noteSearchQuery.toLowerCase();
      final matchSearch  = title.contains(q) || subject.contains(q);
      final matchSubject = _selectedSubjectFilter == null ||
          note['subject_name'] == _selectedSubjectFilter;
      return matchSearch && matchSubject;
    }).toList();

    final subjectsSet =
        _elearningNotes.map((n) => n['subject_name'] as String).toSet().toList();

    return Column(
      children: [
        // Search + filter bar
        Container(
          color: AppColors.surface,
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
          child: Column(
            children: [
              TextField(
                style: GoogleFonts.inter(
                    color: AppColors.textPrimary, fontSize: 14),
                decoration: InputDecoration(
                  hintText: 'Search study resources...',
                  prefixIcon: Icon(Icons.search_rounded,
                      size: 20, color: AppColors.textSecondary),
                  contentPadding: const EdgeInsets.symmetric(
                      horizontal: 12, vertical: 10),
                ),
                onChanged: (v) => setState(() => _noteSearchQuery = v),
              ),
              if (subjectsSet.isNotEmpty) ...[
                const SizedBox(height: 10),
                SizedBox(
                  height: 32,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    children: [
                      _filterChip('All', _selectedSubjectFilter == null,
                          () => setState(() => _selectedSubjectFilter = null),
                          primary),
                      ...subjectsSet.map((sub) => _filterChip(
                          sub,
                          _selectedSubjectFilter == sub,
                          () => setState(() => _selectedSubjectFilter = sub),
                          primary)),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
        Expanded(
          child: filteredNotes.isEmpty
              ? ListView(children: [
                  const SizedBox(height: 80),
                  _emptyState('No resources matched.'),
                ])
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
                  itemCount: filteredNotes.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 10),
                  itemBuilder: (_, i) =>
                      NotesDownloadTile(note: filteredNotes[i]),
                ),
        ),
      ],
    );
  }

  Widget _filterChip(
      String label, bool selected, VoidCallback onTap, Color primary) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 150),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: selected
                ? primary.withValues(alpha: 0.12)
                : AppColors.surface2,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
                color:
                    selected ? primary.withValues(alpha: 0.5) : AppColors.borderLight),
          ),
          child: Text(label,
              style: GoogleFonts.inter(
                  fontSize: 11,
                  fontWeight: selected ? FontWeight.bold : FontWeight.w500,
                  color: selected ? primary : AppColors.textSecondary)),
        ),
      ),
    );
  }

  // ─── CBT Exams Tab ─────────────────────────────────────────────────────────
  Widget _buildCbtExams(Color primary) {
    if (_cbtExams.isEmpty) {
      return ListView(children: [
        const SizedBox(height: 80),
        _emptyState('No active CBT exams resolved.'),
      ]);
    }

    return ListView.separated(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      itemCount: _cbtExams.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (ctx, i) {
        final exam          = _cbtExams[i];
        final duration      = exam['duration_minutes'] ?? 0;
        final passPercent   = exam['pass_percentage'] ?? 50.0;
        final instructions  = exam['instructions'] as String? ?? '';

        return Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: primary.withValues(alpha: 0.08),
                  borderRadius: const BorderRadius.vertical(
                      top: Radius.circular(14)),
                  border: Border(
                      bottom: BorderSide(color: AppColors.borderLight)),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: primary.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(Icons.computer_rounded,
                          color: primary, size: 18),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(exam['title'] ?? 'Exam',
                          style: GoogleFonts.inter(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimary)),
                    ),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        _examMeta(Icons.timer_outlined, '$duration mins'),
                        const SizedBox(width: 16),
                        _examMeta(Icons.emoji_events_outlined,
                            'Pass: $passPercent%'),
                      ],
                    ),
                    if (instructions.isNotEmpty) ...[
                      const SizedBox(height: 10),
                      Text(instructions,
                          style: GoogleFonts.inter(
                              fontSize: 12,
                              color: AppColors.textSecondary,
                              height: 1.4)),
                    ],
                    const SizedBox(height: 14),
                    SizedBox(
                      width: double.infinity,
                      height: 44,
                      child: ElevatedButton.icon(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (_) =>
                                    CbtExamScreen(exam: exam)),
                          ).then((_) => _loadData());
                        },
                        icon: const Icon(Icons.play_arrow_rounded,
                            size: 18),
                        label: Text('Start Offline Attempt',
                            style: GoogleFonts.inter(
                                fontSize: 13,
                                fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primary,
                          foregroundColor: Colors.black,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10)),
                          elevation: 0,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _examMeta(IconData icon, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 13, color: AppColors.textSecondary),
        const SizedBox(width: 4),
        Text(label,
            style: GoogleFonts.inter(
                fontSize: 12, color: AppColors.textSecondary)),
      ],
    );
  }

  // ─── Schedule / Homework Tab ───────────────────────────────────────────────
  Widget _buildSchedule(Color primary) {
    final now = DateTime.now().toIso8601String().substring(0, 10);
    final hasHomework = context.watch<AuthProvider>().isPluginActive('homework');

    final timetableWidget = _timetable.isEmpty
        ? ListView(physics: const AlwaysScrollableScrollPhysics(), children: [
            const SizedBox(height: 80),
            _emptyState('No timetable entries found.'),
          ])
        : ListView.separated(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
            itemCount: _timetable.length,
            separatorBuilder: (_, _) => const SizedBox(height: 8),
            itemBuilder: (_, i) {
              final t = _timetable[i];
              return Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.borderLight),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 3,
                      height: 40,
                      decoration: BoxDecoration(
                        color: primary,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(t['subject_name'] ?? 'Subject',
                              style: GoogleFonts.inter(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                  color: AppColors.textPrimary)),
                          const SizedBox(height: 2),
                          Text(
                              '${t['day'] ?? ''} • ${t['start_time'] ?? ''} – ${t['end_time'] ?? ''}',
                              style: GoogleFonts.inter(
                                  fontSize: 11,
                                  color: AppColors.textSecondary)),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            },
          );

    if (!hasHomework) {
      return timetableWidget;
    }

    return DefaultTabController(
      length: 2,
      child: Column(
        children: [
          Container(
            color: AppColors.surface,
            child: TabBar(
              tabs: const [
                Tab(text: 'Timetable'),
                Tab(text: 'Homework'),
              ],
              labelColor: primary,
              unselectedLabelColor: AppColors.textSecondary,
              indicatorColor: primary,
              dividerColor: AppColors.borderLight,
            ),
          ),
          Expanded(
            child: TabBarView(
              children: [
                timetableWidget,
                // Homework
                _homework.isEmpty
                    ? ListView(children: [
                        const SizedBox(height: 80),
                        _emptyState('No homework assigned yet.'),
                      ])
                    : ListView.separated(
                        physics: const AlwaysScrollableScrollPhysics(),
                        padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
                        itemCount: _homework.length,
                        separatorBuilder: (_, _) =>
                            const SizedBox(height: 8),
                        itemBuilder: (_, i) {
                          final h = _homework[i];
                          final due = h['due_date'] as String? ?? '';
                          final overdue = due.isNotEmpty &&
                              due.compareTo(now) < 0;
                          return GestureDetector(
                            onTap: () {
                              HomeworkSubmitSheet.show(context, h);
                            },
                            child: Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: AppColors.surface,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                    color: overdue
                                        ? AppColors.error.withValues(alpha: 0.3)
                                        : AppColors.borderLight),
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(8),
                                    decoration: BoxDecoration(
                                      color: AppColors.parentAccent.withValues(alpha: 0.1),
                                      borderRadius:
                                          BorderRadius.circular(8),
                                    ),
                                    child: const Icon(
                                        Icons.assignment_outlined,
                                        color: AppColors.parentAccent,
                                        size: 18),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(h['title'] ?? '',
                                            style: GoogleFonts.inter(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 13,
                                                color: AppColors.textPrimary)),
                                        const SizedBox(height: 2),
                                        Text(h['subject_name'] ?? '',
                                            style: GoogleFonts.inter(
                                                fontSize: 11,
                                                color: AppColors.textSecondary)),
                                        const SizedBox(height: 4),
                                        Text('Due: $due',
                                            style: GoogleFonts.inter(
                                                fontSize: 11,
                                                fontWeight: overdue
                                                    ? FontWeight.bold
                                                    : FontWeight.normal,
                                                color: overdue
                                                    ? AppColors.error
                                                    : AppColors.textSecondary)),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _emptyState(String msg) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.inbox_outlined,
              size: 48, color: AppColors.textMuted),
          const SizedBox(height: 12),
          Text(msg,
              style: GoogleFonts.inter(
                  color: AppColors.textSecondary, fontSize: 14),
              textAlign: TextAlign.center),
        ],
      ),
    );
  }
}





class _LaravelGradientStatCard extends StatelessWidget {
  final String value;
  final String label;
  final String subLabel;
  final IconData icon;
  final List<Color> gradientColors;
  final VoidCallback? onTap;

  const _LaravelGradientStatCard({
    required this.value,
    required this.label,
    required this.subLabel,
    required this.icon,
    required this.gradientColors,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: gradientColors,
            ),
          ),
          child: Stack(
            children: [
              Positioned(
                right: -24,
                top: -24,
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white.withValues(alpha: 0.08),
                  ),
                ),
              ),
              Positioned(
                right: 16,
                bottom: -16,
                child: Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white.withValues(alpha: 0.06),
                  ),
                ),
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          value,
                          style: GoogleFonts.inter(
                            fontSize: 22,
                            fontWeight: FontWeight.w900,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          label,
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: Colors.white.withValues(alpha: 0.85),
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 1),
                        Text(
                          subLabel,
                          style: GoogleFonts.inter(
                            fontSize: 9,
                            color: Colors.white.withValues(alpha: 0.65),
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 4),
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(icon, color: Colors.white, size: 16),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _QuickActionRow extends StatelessWidget {
  final String label;
  final String sub;
  final IconData icon;
  final List<Color> gradientColors;
  final VoidCallback onTap;

  const _QuickActionRow({
    required this.label,
    required this.sub,
    required this.icon,
    required this.gradientColors,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: gradientColors,
                ),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: Colors.white, size: 18),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    label,
                    style: GoogleFonts.inter(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  Text(
                    sub,
                    style: GoogleFonts.inter(
                      fontSize: 10.5,
                      color: AppColors.textMuted,
                    ),
                  ),
                ],
              ),
            ),
            Icon(Icons.chevron_right_rounded, color: AppColors.textMuted.withValues(alpha: 0.6), size: 16),
          ],
        ),
      ),
    );
  }
}
