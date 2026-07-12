import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/auth_provider.dart';
import '../../core/mobile_layout.dart';
import '../../core/constants.dart';
import '../admin/announcement_create_dialog.dart';

class TeacherHome extends StatefulWidget {
  const TeacherHome({super.key});

  @override
  State<TeacherHome> createState() => _TeacherHomeState();
}

class _TeacherHomeState extends State<TeacherHome> {
  StreamSubscription? _syncSub;

  // Static cache to preserve data across page reconstructions
  static List<Map<String, dynamic>> _cachedClasses = [];
  static int                        _cachedTotalStudents = 0;
  static int                        _cachedTotalSubjects = 0;
  static bool                       _wasLoaded = false;
  static String                     _lastUserKey = '';

  List<Map<String, dynamic>> _classes = _cachedClasses;
  int _totalStudents = _cachedTotalStudents;
  int _totalSubjects = _cachedTotalSubjects;
  late bool _loading = !_wasLoaded;
  int _selectedTab = 0;
  String _currentTermName = 'First Term 2026/2027';
  bool _isLoadingData = false;

  @override
  void initState() {
    super.initState();
    _load();
    final auth = context.read<AuthProvider>();
    _syncSub = auth.syncService.syncStatusStream.listen((status) {
      if (status == SyncStatus.synced && mounted) {
        _load();
      }
    });
  }

  @override
  void dispose() {
    _syncSub?.cancel();
    super.dispose();
  }

  Future<void> _load() async {
    if (_isLoadingData) return;
    _isLoadingData = true;
    final auth = context.read<AuthProvider>();
    auth.refreshPlugins();

    try {
      final prefs = await SharedPreferences.getInstance();
      final termInt = prefs.getInt('active_term') ?? 1;
      final sessionStr = prefs.getString('active_session') ?? '';
      String termWord = 'First Term';
      if (termInt == 2) termWord = 'Second Term';
      if (termInt == 3) termWord = 'Third Term';
      final termName = sessionStr.isNotEmpty ? '$termWord $sessionStr' : termWord;
      setState(() {
        _currentTermName = termName;
      });
    } catch (_) {}

    final currentUserKey = '${auth.user?.id}_${auth.tenantSlug}';
    if (_lastUserKey != currentUserKey) {
      _cachedClasses = [];
      _cachedTotalStudents = 0;
      _cachedTotalSubjects = 0;
      _wasLoaded = false;
      _lastUserKey = currentUserKey;
      _classes = [];
      _totalStudents = 0;
      _totalSubjects = 0;
      _loading = true;
    }

    // Always show UI immediately from local DB cache
    if (!_wasLoaded) {
      try {
        final cachedClassesData = await auth.apiService.dbHelper.getCache('/teacher/classes');
        if (cachedClassesData != null) {
          final decoded = jsonDecode(cachedClassesData);
          final list = (decoded['data'] as List).cast<Map<String, dynamic>>();
          int students = 0;
          final subjectIds = <int>{};
          final extended = <Map<String, dynamic>>[];

          for (final c in list) {
            final cid = c['id'] as int;
            int classStudents = 0;
            int classSubjects = 0;

            final sdCache = await auth.apiService.dbHelper.getCache('/teacher/classes/$cid/students');
            if (sdCache != null) {
              final sdDecoded = jsonDecode(sdCache);
              classStudents = ((sdDecoded['data'] as List?) ?? []).length;
              students += classStudents;
            }

            final subDCache = await auth.apiService.dbHelper.getCache('/teacher/classes/$cid/subjects');
            if (subDCache != null) {
              final subDDecoded = jsonDecode(subDCache);
              final subs = subDDecoded['data'] as List? ?? [];
              classSubjects = subs.length;
              for (final s in subs) { subjectIds.add(s['id'] as int); }
            }

            extended.add({
              ...c,
              'student_count': classStudents,
              'subject_count': classSubjects,
            });
          }

          if (mounted) {
            setState(() {
              _classes = extended;
              _totalStudents = students;
              _totalSubjects = subjectIds.length;
              _cachedClasses = _classes;
              _cachedTotalStudents = _totalStudents;
              _cachedTotalSubjects = _totalSubjects;
              _wasLoaded = true;
            });
          }
        }
      } catch (_) {}
    }

    // Always dismiss loading immediately — show UI now, refresh silently below
    if (mounted) setState(() => _loading = false);

    try {
      final data = await auth.apiService.getWithCache('/teacher/classes');
      final list = (data['data'] as List).cast<Map<String, dynamic>>();

      int students = 0;
      final subjectIds = <int>{};
      final extended = <Map<String, dynamic>>[];

      for (final c in list) {
        final cid = c['id'] as int;
        int classStudents = 0;
        int classSubjects = 0;
        try {
          final sd = await auth.apiService.getWithCache('/teacher/classes/$cid/students');
          classStudents = ((sd['data'] as List?) ?? []).length;
          students += classStudents;
          final subD = await auth.apiService.getWithCache('/teacher/classes/$cid/subjects');
          final subs = subD['data'] as List? ?? [];
          classSubjects = subs.length;
          for (final s in subs) { subjectIds.add(s['id'] as int); }
        } catch (_) {}

        extended.add({
          ...c,
          'student_count': classStudents,
          'subject_count': classSubjects,
        });
      }

      if (mounted) {
        setState(() {
          _classes       = extended;
          _totalStudents = students;
          _totalSubjects = subjectIds.length;
        });
      }
    } catch (_) {}

    _cachedClasses = _classes;
    _cachedTotalStudents = _totalStudents;
    _cachedTotalSubjects = _totalSubjects;
    _wasLoaded = true;

    _isLoadingData = false;
    if (mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;
    const accent  = AppColors.teacherAccent;

    final isClassTeacher = auth.user?.isClassTeacher == true;
    final hasHomework = auth.isPluginActive('homework');
    final hasCbt = auth.isPluginActive('cbt');

    final activeTabs = <AHNavItem>[
      const AHNavItem(
        icon: Icons.dashboard_outlined,
        activeIcon: Icons.dashboard_rounded,
        label: 'Today',
        iconBg: Color(0xFFE0E7FF),
        iconColor: Color(0xFF4F46E5),
      ),
    ];

    if (isClassTeacher) {
      activeTabs.add(const AHNavItem(
        icon: Icons.how_to_reg_outlined,
        activeIcon: Icons.how_to_reg_rounded,
        label: 'Attendance',
        iconBg: Color(0xFFCCFBF1),
        iconColor: Color(0xFF0D9488),
      ));
    }

    activeTabs.add(const AHNavItem(
      icon: Icons.edit_note_outlined,
      activeIcon: Icons.edit_note_rounded,
      label: 'Scores',
      iconBg: Color(0xFFE0F2FE),
      iconColor: Color(0xFF0284C7),
    ));

    if (hasHomework) {
      activeTabs.add(const AHNavItem(
        icon: Icons.assignment_outlined,
        activeIcon: Icons.assignment_rounded,
        label: 'Homework',
        iconBg: Color(0xFFF3E8FF),
        iconColor: Color(0xFF7C3AED),
      ));
    }

    final activePages = <Widget>[
      _buildToday(primary, isClassTeacher, hasHomework, hasCbt),
    ];

    if (isClassTeacher) {
      activePages.add(_buildQuickLaunch(
          'Attendance Register',
          'Mark daily presence for all your classes',
          Icons.how_to_reg_rounded,
          AppColors.success,
          () => context.push('/attendance')));
    }

    activePages.add(_buildQuickLaunch(
        'Gradebook Scores',
        'Enter term test and exam assessment scores',
        Icons.edit_note_rounded,
        AppColors.info,
        () => context.push('/scores')));

    if (hasHomework) {
      activePages.add(_buildQuickLaunch(
          'Homework Assignments',
          'Publish and manage class homework',
          Icons.assignment_rounded,
          AppColors.parentAccent,
          () => context.push('/homework')));
    }

    int currentTab = _selectedTab;
    if (currentTab >= activeTabs.length) {
      currentTab = 0;
    }

    return RoleShell(
      title: 'Teacher Portal',
      navItems: activeTabs,
      selectedIndex: currentTab,
      onTabSelected: (i) => setState(() => _selectedTab = i),
      accentColor: accent,
      loading: _loading,
      onRefresh: _load,
      body: activePages[currentTab],
    );
  }

  Widget _buildSkeleton(Color primary) {
    return ListView(
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      children: [
        // Hero skeleton
        Container(
          height: 140,
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.borderLight),
          ),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(width: 100, height: 14, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4))),
              const SizedBox(height: 10),
              Container(width: 160, height: 22, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(6))),
              const Spacer(),
              Row(
                children: [
                  Container(width: 100, height: 32, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20))),
                  const SizedBox(width: 10),
                  Container(width: 100, height: 32, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20))),
                ],
              )
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Quick Link skeleton
        Container(
          height: 70,
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.borderLight),
          ),
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              Container(width: 32, height: 32, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8))),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(width: 120, height: 14, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(4))),
                    const SizedBox(height: 6),
                    Container(width: 180, height: 10, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(3))),
                  ],
                ),
              )
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Section header skeleton
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Container(width: 100, height: 18, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4))),
          ],
        ),
        const SizedBox(height: 12),

        // Grid/List of classes skeletons
        Column(
          children: List.generate(2, (_) => Container(
            margin: const EdgeInsets.only(bottom: 12),
            height: 130,
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.borderLight),
            ),
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(width: 120, height: 18, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(4))),
                    Container(width: 60, height: 18, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12))),
                  ],
                ),
                const SizedBox(height: 12),
                Container(width: 180, height: 12, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(3))),
                const Spacer(),
                Row(
                  children: [
                    Container(width: 70, height: 24, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6))),
                    const SizedBox(width: 10),
                    Container(width: 70, height: 24, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6))),
                  ],
                )
              ],
            ),
          )),
        ),
      ],
    )
    .animate(onPlay: (controller) => controller.repeat())
    .shimmer(duration: 1500.ms, color: Colors.white.withValues(alpha: 0.05));
  }

  Widget _buildToday(Color primary, bool isClassTeacher, bool hasHomework, bool hasCbt) {
    if (_loading) {
      return _buildSkeleton(primary);
    }
    final user = context.watch<AuthProvider>().user;

    final isCompact = MediaQuery.of(context).size.width < 500;

    final statCards = [
      _GradientStatCard(
        title: 'Assigned Classes',
        value: '${_classes.length}',
        icon: Icons.school_rounded,
        gradientColors: const [Color(0xFF60A5FA), Color(0xFF2563EB)], // blue
      ),
      _GradientStatCard(
        title: 'Active Students',
        value: '$_totalStudents',
        icon: Icons.people_rounded,
        gradientColors: const [Color(0xFF34D399), Color(0xFF0D9488)], // emerald
        onTap: () => context.push('/students'),
      ),
      _GradientStatCard(
        title: 'Assigned Subjects',
        value: '$_totalSubjects',
        icon: Icons.menu_book_rounded,
        gradientColors: const [Color(0xFFC084FC), Color(0xFF7C3AED)], // purple/violet
      ),
      _GradientStatCard(
        title: 'Pending Submissions',
        value: '0',
        icon: Icons.assignment_turned_in_rounded,
        gradientColors: const [Color(0xFFFBBF24), Color(0xFFEA580C)], // amber/orange
      ),
    ];

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      children: [
        // ── Laravel-style Teacher Header Card ───────────────────────────
        ClipRRect(
          borderRadius: BorderRadius.circular(16),
          child: Container(
            width: double.infinity,
            decoration: const BoxDecoration(
              color: Color(0xFF1A2E4A), // Deep navy
              gradient: RadialGradient(
                center: Alignment.topLeft,
                radius: 1.2,
                colors: [
                  Color(0xFF1E3A5F), // lighter navy
                  Color(0xFF1A2E4A), // deep navy
                ],
              ),
            ),
            child: Stack(
              children: [
                // Concentric background decorative circle top-right
                Positioned(
                  right: -40,
                  top: -20,
                  bottom: -20,
                  child: Opacity(
                    opacity: 0.1,
                    child: CustomPaint(
                      size: const Size(200, 200),
                      painter: _ConcentricCirclesPainter(),
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(20),
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
                                    color: Color(0xFF34D399), // emerald-400
                                  ),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  'TEACHER PORTAL',
                                  style: GoogleFonts.inter(
                                    color: const Color(0xFF93C5FD), // light blue
                                    fontSize: 9,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 1.0,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          GestureDetector(
                            onTap: _load,
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
                        'Welcome, ${user?.name ?? 'Teacher'}',
                        style: GoogleFonts.inter(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                          letterSpacing: -0.5,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Manage your classes, scores and attendance.',
                        style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.w500,
                          color: const Color(0xFF93C5FD),
                        ),
                      ),
                      const SizedBox(height: 16),
                      // Pill badges
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          _HeroPill(
                            icon: Icons.calendar_today_rounded,
                            label: _currentTermName,
                          ),
                          _HeroPill(
                            icon: Icons.people_alt_rounded,
                            label: '$_totalStudents Students',
                          ),
                          _HeroPill(
                            icon: Icons.access_time_filled_rounded,
                            label: _formattedDate(),
                          ),
                        ],
                      ),
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

        // ── Laravel-style Stat Cards Grid (Responsive) ──────────────────────
        (isCompact
            ? Column(
                children: [
                  statCards[0],
                  const SizedBox(height: 12),
                  statCards[1],
                  const SizedBox(height: 12),
                  statCards[2],
                  const SizedBox(height: 12),
                  statCards[3],
                ],
              )
            : Column(
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
              ))
            .animate()
            .fade(duration: 400.ms, delay: 100.ms)
            .slideY(begin: 0.1, end: 0, duration: 400.ms, curve: Curves.easeOutQuad),
        const SizedBox(height: 24),

        // ── Analytics & KPI Card ───────────────────────────────────────────
        Container(
          margin: const EdgeInsets.only(bottom: 20),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.borderLight),
            boxShadow: AppColors.subtleShadow,
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.info.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.analytics_outlined, color: AppColors.info, size: 24),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Analytics & KPI Console',
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Explore detailed school averages, rankings, and CBT exam trends.',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              SizedBox(
                height: 32,
                child: TextButton(
                  onPressed: () => context.push('/analytics-dashboard'),
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    backgroundColor: primary.withValues(alpha: 0.1),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                  child: Text(
                    'Analyze',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: primary,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),

        // CBT Exam Management Card for Teachers
        if (hasCbt) ...[
          Container(
            margin: const EdgeInsets.only(bottom: 20),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.borderLight),
              boxShadow: AppColors.subtleShadow,
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.success.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.computer_rounded, color: AppColors.success, size: 24),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'CBT Exam Center',
                        style: GoogleFonts.inter(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Manage questions, view scores, and configure active online tests.',
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                SizedBox(
                  height: 32,
                  child: TextButton(
                    onPressed: () => context.push('/cbt'),
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      backgroundColor: AppColors.success.withValues(alpha: 0.1),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: Text(
                      'Manage',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: AppColors.success,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],

        // Publish News button
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const SectionHeader(title: 'My Assigned Classes'),
            GestureDetector(
              onTap: () {
                showDialog(
                  context: context,
                  builder: (_) => const AnnouncementCreateDialog(),
                ).then((val) { if (val == true) _load(); });
              },
              child: Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: primary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                      color: primary.withValues(alpha: 0.3)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.campaign_outlined, size: 14, color: primary),
                    const SizedBox(width: 4),
                    Text('Publish News',
                        style: GoogleFonts.inter(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: primary)),
                  ],
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),

        // Class cards
        if (_loading)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 40),
            child: Center(child: CircularProgressIndicator()),
          )
        else if (_classes.isEmpty)
          _emptyState('No classes currently assigned.')
        else
          ...(_classes.map((cls) => _buildClassCard(cls, primary, isClassTeacher, hasHomework))),
      ],
    );
  }



  Widget _buildClassCard(Map<String, dynamic> cls, Color primary, bool isClassTeacher, bool hasHomework) {
    final name          = cls['name'] ?? 'Class';
    final studentCount  = cls['student_count'] ?? 0;
    final subjectCount  = cls['subject_count'] ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(name,
                    style: GoogleFonts.inter(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary)),
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.surface2,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: AppColors.borderLight),
                  ),
                  child: Text('$studentCount students',
                      style: GoogleFonts.inter(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textSecondary)),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
            child: Text('$subjectCount active curriculum subjects',
                style: GoogleFonts.inter(
                    fontSize: 12, color: AppColors.textSecondary)),
          ),
          Container(
            height: 1,
            color: AppColors.borderExtraLight,
          ),
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                if (isClassTeacher) ...[
                  Expanded(
                      child: _classAction('Attendance',
                          Icons.how_to_reg_rounded, AppColors.success,
                          () => context.push('/attendance'))),
                  const SizedBox(width: 8),
                ],
                Expanded(
                    child: _classAction('Scores',
                        Icons.edit_note_rounded, AppColors.info,
                        () => context.push('/scores'))),
                if (hasHomework) ...[
                  const SizedBox(width: 8),
                  Expanded(
                      child: _classAction('Homework',
                          Icons.assignment_outlined, AppColors.parentAccent,
                          () => context.push('/homework'))),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _classAction(
      String label, IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 16),
            const SizedBox(height: 4),
            Text(label,
                style: GoogleFonts.inter(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: color)),
          ],
        ),
      ),
    );
  }

  // ─── Quick Launch (for nav tabs that open full routes) ─────────────────────
  Widget _buildQuickLaunch(String title, String subtitle, IconData icon,
      Color color, VoidCallback onTap) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                shape: BoxShape.circle,
                border: Border.all(color: color.withValues(alpha: 0.3)),
              ),
              child: Icon(icon, color: color, size: 36),
            ),
            const SizedBox(height: 20),
            Text(title,
                style: GoogleFonts.inter(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary),
                textAlign: TextAlign.center),
            const SizedBox(height: 8),
            Text(subtitle,
                style: GoogleFonts.inter(
                    fontSize: 14, color: AppColors.textSecondary),
                textAlign: TextAlign.center),
            const SizedBox(height: 32),
            SizedBox(
              width: 220,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: onTap,
                icon: Icon(icon, size: 18),
                label: Text('Open $title',
                    style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: color,
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                  elevation: 0,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _emptyState(String msg) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.inbox_outlined, size: 40, color: AppColors.textMuted),
            const SizedBox(height: 12),
            Text(msg,
                style: GoogleFonts.inter(
                    color: AppColors.textSecondary, fontSize: 13)),
          ],
        ),
      ),
    );
  }

  String _formattedDate() {
    final now = DateTime.now();
    final days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    final months = [
      'January', 'February', 'March', 'April', 'May', 'June', 
      'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return '${days[now.weekday - 1]}, ${months[now.month - 1]} ${now.day}';
  }
}

class _ConcentricCirclesPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withValues(alpha: 0.08)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.0;
    
    final center = Offset(size.width * 0.8, size.height * 0.5);
    canvas.drawCircle(center, 130, paint);
    canvas.drawCircle(center, 90, paint);
    canvas.drawCircle(center, 50, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _HeroPill extends StatelessWidget {
  final IconData icon;
  final String label;

  const _HeroPill({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: Colors.white, size: 12),
          const SizedBox(width: 6),
          Text(
            label,
            style: GoogleFonts.inter(
              color: Colors.white,
              fontSize: 10,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

class _GradientStatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final List<Color> gradientColors;
  final VoidCallback? onTap;

  const _GradientStatCard({
    required this.title,
    required this.value,
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
          height: 100,
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: gradientColors,
            ),
            boxShadow: [
              BoxShadow(
                color: gradientColors.first.withValues(alpha: 0.3),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Stack(
            children: [
              Positioned(
                right: -20,
                top: -20,
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white.withValues(alpha: 0.1),
                  ),
                ),
              ),
              Positioned(
                right: 12,
                bottom: 8,
                child: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white.withValues(alpha: 0.08),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            value,
                            style: GoogleFonts.inter(
                              color: Colors.white,
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            title,
                            style: GoogleFonts.inter(
                              color: Colors.white.withValues(alpha: 0.85),
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 4),
                    Container(
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(icon, color: Colors.white, size: 20),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
