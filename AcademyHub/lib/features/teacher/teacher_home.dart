import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
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
    final auth = context.read<AuthProvider>();
    auth.refreshPlugins();

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

    final isFirstLoad = !_wasLoaded;
    if (isFirstLoad) {
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
              _loading = _classes.isEmpty;

              _cachedClasses = _classes;
              _cachedTotalStudents = _totalStudents;
              _cachedTotalSubjects = _totalSubjects;
              _wasLoaded = !_loading;
            });
          }
        }
      } catch (_) {}
    }

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

    if (mounted) {
      setState(() => _loading = false);
    }
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
          icon: Icons.home_outlined,
          activeIcon: Icons.home_rounded,
          label: 'Today'),
    ];

    if (isClassTeacher) {
      activeTabs.add(const AHNavItem(
          icon: Icons.how_to_reg_outlined,
          activeIcon: Icons.how_to_reg_rounded,
          label: 'Attendance'));
    }

    activeTabs.add(const AHNavItem(
        icon: Icons.edit_note_outlined,
        activeIcon: Icons.edit_note_rounded,
        label: 'Scores'));

    if (hasHomework) {
      activeTabs.add(const AHNavItem(
          icon: Icons.assignment_outlined,
          activeIcon: Icons.assignment_rounded,
          label: 'Homework'));
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
      body: Column(
        children: [
          if (_loading) LinearProgressIndicator(color: primary, minHeight: 2),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _load,
              color: primary,
              child: activePages[currentTab],
            ),
          ),
          AHBottomNav(
            items: activeTabs,
            selectedIndex: currentTab,
            onTap: (i) => setState(() => _selectedTab = i),
            accentColor: accent,
          ),
        ],
      ),
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
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      children: [
        // Hero card
        GlassHeroCard(
          gradientColors: [
            AppColors.teacherAccent.withValues(alpha: 0.85),
            const Color(0xFF065F46),
          ],
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Welcome back,',
                          style: GoogleFonts.spaceGrotesk(
                              fontSize: 12, color: Colors.white60)),
                      Text(user?.name ?? 'Teacher',
                          style: GoogleFonts.spaceGrotesk(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: Colors.white)),
                    ],
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
                          color: Colors.white, size: 18),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              // Metric pills
              Row(
                children: [
                  _metricPill(
                      '$_totalStudents', 'Students', Icons.people_rounded,
                      onTap: () => context.push('/students')),
                  const SizedBox(width: 10),
                  _metricPill(
                      '$_totalSubjects', 'Subjects', Icons.book_rounded),
                  const SizedBox(width: 10),
                  _metricPill(
                      '${_classes.length}', 'Classes', Icons.class_rounded),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // Analytics & KPI Card
        Container(
          margin: const EdgeInsets.only(bottom: 20),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.borderLight),
            boxShadow: [
              BoxShadow(
                color: primary.withValues(alpha: 0.05),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
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
                      style: GoogleFonts.spaceGrotesk(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Explore detailed school averages, rankings, and CBT exam trends.',
                      style: GoogleFonts.spaceGrotesk(
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
                    style: GoogleFonts.spaceGrotesk(
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
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.borderLight),
              boxShadow: [
                BoxShadow(
                  color: primary.withValues(alpha: 0.05),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
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
                        style: GoogleFonts.spaceGrotesk(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Manage questions, view scores, and configure active online tests.',
                        style: GoogleFonts.spaceGrotesk(
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
                      style: GoogleFonts.spaceGrotesk(
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
                        style: GoogleFonts.spaceGrotesk(
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

  Widget _metricPill(String value, String label, IconData icon, {VoidCallback? onTap}) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        behavior: HitTestBehavior.opaque,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Row(
            children: [
              Icon(icon, color: Colors.white70, size: 14),
              const SizedBox(width: 6),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(value,
                      style: GoogleFonts.spaceGrotesk(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Colors.white)),
                  Text(label,
                      style: GoogleFonts.spaceGrotesk(
                          fontSize: 9, color: Colors.white60)),
                ],
              ),
            ],
          ),
        ),
      ),
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
                    style: GoogleFonts.spaceGrotesk(
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
                      style: GoogleFonts.spaceGrotesk(
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
                style: GoogleFonts.spaceGrotesk(
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
                style: GoogleFonts.spaceGrotesk(
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
                style: GoogleFonts.spaceGrotesk(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary),
                textAlign: TextAlign.center),
            const SizedBox(height: 8),
            Text(subtitle,
                style: GoogleFonts.spaceGrotesk(
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
                    style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold)),
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
                style: GoogleFonts.spaceGrotesk(
                    color: AppColors.textSecondary, fontSize: 13)),
          ],
        ),
      ),
    );
  }
}
