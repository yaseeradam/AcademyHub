import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
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
    final auth = context.read<AuthProvider>();
    final admissionNo = auth.user?.email ?? '';
    setState(() => _loading = true);
    try {
      _studentStats   = await _db.getStudentStats(admissionNo);
      _cbtExams       = await _db.getCbtExams();
      _elearningNotes = await _db.getELearningNotes();
      _homework       = await _db.getAllHomework();
      _timetable      = await _db.getTimetable();
      _announcements  = await _db.getAnnouncements();
      _reportSubjects = await _db.getScores(0, 1, '');
    } catch (_) {}
    finally { if (mounted) setState(() => _loading = false); }
  }

  Future<void> _triggerRefresh() async {
    setState(() => _loading = true);
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
      setState(() => _loading = false);
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
    final reachablePhotoUrl = auth.getReachableUrl(user?.profilePhotoUrl);
    final userInitial    = (user != null && user.name.trim().isNotEmpty)
        ? user.name.trim().substring(0, 1).toUpperCase()
        : 'S';

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      children: [
        // ── Glassmorphism hero card ──────────────────────────────────────
        GlassHeroCard(
          gradientColors: [
            accent.withValues(alpha: 0.9),
            const Color(0xFF4338CA),
          ],
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 22,
                    backgroundColor: Colors.white.withValues(alpha: 0.2),
                    backgroundImage: reachablePhotoUrl != null
                        ? NetworkImage(reachablePhotoUrl)
                        : null,
                    child: reachablePhotoUrl != null
                        ? null
                        : Text(userInitial,
                            style: const TextStyle(
                                color: Colors.white,
                                fontSize: 18,
                                fontWeight: FontWeight.bold)),
                  ),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Good ${_timeGreeting()}',
                          style: GoogleFonts.inter(
                              fontSize: 12,
                              color: Colors.white70)),
                      Text(user?.name ?? 'Student',
                          style: GoogleFonts.inter(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Colors.white)),
                    ],
                  ),
                  const Spacer(),
                  GestureDetector(
                    onTap: _triggerRefresh,
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
              // GPA Ring
              Row(
                children: [
                  _GpaRing(value: averageScore / 100.0, label: '${averageScore.toStringAsFixed(1)}%'),
                  const SizedBox(width: 20),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        GestureDetector(
                          onTap: () => context.push('/student-attendance'),
                          behavior: HitTestBehavior.opaque,
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              _heroStat('Attendance', '${attendanceRate.toStringAsFixed(1)}%'),
                              const SizedBox(width: 4),
                              const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white54, size: 10),
                            ],
                          ),
                        ),
                        const SizedBox(height: 10),
                        _heroStat('Class Rank', classRank > 0 ? '#$classRank' : 'N/A'),
                        const SizedBox(height: 10),
                        _heroStat('Pending HW', '$pendingHw tasks'),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

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
          ),
          const SizedBox(height: 16),
        ],

        // ── Quick action cards ───────────────────────────────────────────
        Row(
          children: [
            Expanded(
              child: _QuickCard(
                title: 'Pending Tasks',
                value: '$pendingHw homework',
                icon: Icons.assignment_outlined,
                color: AppColors.parentAccent,
                onTap: () => _selectTabByLabel(activeTabs, 'Schedule'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _QuickCard(
                title: 'Analytics',
                value: 'View trends',
                icon: Icons.insights_rounded,
                color: AppColors.info,
                onTap: () => context.push('/performance'),
              ),
            ),
          ],
        ),
        const SizedBox(height: 20),

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
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.borderLight),
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
                        const SizedBox(height: 6),
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

  Widget _heroStat(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
            style: GoogleFonts.inter(
                fontSize: 10, color: Colors.white60)),
        Text(value,
            style: GoogleFonts.inter(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: Colors.white)),
      ],
    );
  }

  String _timeGreeting() {
    final h = DateTime.now().hour;
    if (h < 12) return 'Morning';
    if (h < 17) return 'Afternoon';
    return 'Evening';
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

// ─── GPA Ring ─────────────────────────────────────────────────────────────────
class _GpaRing extends StatelessWidget {
  final double value;
  final String label;
  const _GpaRing({required this.value, required this.label});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 80,
      height: 80,
      child: Stack(
        alignment: Alignment.center,
        children: [
          CustomPaint(
            size: const Size(80, 80),
            painter: _RingPainter(value: value.clamp(0.0, 1.0)),
          ),
          Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(label,
                  style: GoogleFonts.inter(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: Colors.white)),
              Text('GPA',
                  style: GoogleFonts.inter(
                      fontSize: 9, color: Colors.white70)),
            ],
          ),
        ],
      ),
    );
  }
}

class _RingPainter extends CustomPainter {
  final double value;
  _RingPainter({required this.value});

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = (size.width - 10) / 2;
    const strokeWidth = 7.0;

    // Background ring
    canvas.drawCircle(
      center,
      radius,
      Paint()
        ..color = Colors.white.withValues(alpha: 0.15)
        ..style = PaintingStyle.stroke
        ..strokeWidth = strokeWidth,
    );

    // Progress arc
    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      -math.pi / 2,
      2 * math.pi * value,
      false,
      Paint()
        ..color = Colors.white
        ..style = PaintingStyle.stroke
        ..strokeWidth = strokeWidth
        ..strokeCap = StrokeCap.round,
    );
  }

  @override
  bool shouldRepaint(_RingPainter old) => old.value != value;
}

// ─── Quick Card ───────────────────────────────────────────────────────────────
class _QuickCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  const _QuickCard({
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.borderLight),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title,
                      style: GoogleFonts.inter(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textSecondary)),
                  Text(value,
                      style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary)),
                ],
              ),
            ),
            Icon(Icons.arrow_forward_ios_rounded,
                color: AppColors.textMuted, size: 11),
          ],
        ),
      ),
    );
  }
}
