import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';
import 'cbt_exam_screen.dart';
import 'notes_download_tile.dart';

class StudentHome extends StatefulWidget {
  const StudentHome({super.key});

  @override
  State<StudentHome> createState() => _StudentHomeState();
}

class _StudentHomeState extends State<StudentHome> with SingleTickerProviderStateMixin {
  final _db = DatabaseHelper();
  late TabController _tabs;

  Map<String, dynamic>? _studentStats;
  List<Map<String, dynamic>> _reportSubjects = [];
  List<Map<String, dynamic>> _homework      = [];
  List<Map<String, dynamic>> _timetable     = [];
  List<Map<String, dynamic>> _announcements = [];
  List<Map<String, dynamic>> _cbtExams      = [];
  List<Map<String, dynamic>> _elearningNotes = [];

  bool _loading = true;
  String _noteSearchQuery = '';
  String? _selectedSubjectFilter;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 5, vsync: this);
    _loadData();
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    final auth = context.read<AuthProvider>();
    final admissionNo = auth.user?.email ?? '';

    setState(() {
      _loading = true;
    });

    try {
      _studentStats = await _db.getStudentStats(admissionNo);
      _cbtExams = await _db.getCbtExams();
      _elearningNotes = await _db.getELearningNotes();
      _homework = await _db.getAllHomework();
      _timetable = await _db.getTimetable();
      _announcements = await _db.getAnnouncements();

      // Read report card values from cached scores
      _reportSubjects = await _db.getScores(0, 1, ''); // Read from general caches

    } catch (_) {
      // Safe offline fallback
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _triggerRefresh() async {
    setState(() => _loading = true);
    final auth = context.read<AuthProvider>();
    try {
      await auth.syncService.backgroundRefresh('student');
      await _loadData();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Sync failed: ${e.toString()}')),
        );
      }
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    final primary = auth.tenantPrimaryColor;

    return MobileLayout(
      title: 'Student Portal',
      child: RefreshIndicator(
        onRefresh: _triggerRefresh,
        color: primary,
        child: Column(
          children: [
            // Welcome Header Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [primary, primary.withValues(alpha: 0.85)],
                ),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 24,
                    backgroundColor: Colors.white.withValues(alpha: 0.2),
                    child: Text(
                      user?.name.substring(0, 1).toUpperCase() ?? 'S',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Welcome back,',
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.75),
                            fontSize: 12,
                          ),
                        ),
                        Text(
                          user?.name ?? 'Student',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.sync_rounded, color: Colors.white),
                    onPressed: _triggerRefresh,
                  ),
                ],
              ),
            ),
            // Custom Navigation Bar
            Container(
              color: Colors.white,
              child: TabBar(
                controller: _tabs,
                isScrollable: true,
                labelColor: primary,
                unselectedLabelColor: const Color(0xFF64748B),
                indicatorColor: primary,
                labelStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                tabs: const [
                  Tab(icon: Icon(Icons.dashboard_outlined, size: 20), text: 'Dashboard'),
                  Tab(icon: Icon(Icons.bar_chart_rounded, size: 20), text: 'Results'),
                  Tab(icon: Icon(Icons.menu_book_rounded, size: 20), text: 'E-Learning'),
                  Tab(icon: Icon(Icons.computer_rounded, size: 20), text: 'CBT Exams'),
                  Tab(icon: Icon(Icons.event_note_rounded, size: 20), text: 'Timetable & Homework'),
                ],
              ),
            ),
            if (_loading) LinearProgressIndicator(color: primary),
            Expanded(
              child: TabBarView(
                controller: _tabs,
                children: [
                  _buildDashboard(primary),
                  _buildResults(primary),
                  _buildELearning(primary),
                  _buildCbtExams(primary),
                  _buildTimetableHomework(primary),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ─── Dashboard ─────────────────────────────────────────────────────────────

  Widget _buildDashboard(Color primary) {
    final stats = _studentStats;
    final double attendanceRate = (stats?['attendance_rate'] as num?)?.toDouble() ?? 0.0;
    final double averageScore = (stats?['average_score'] as num?)?.toDouble() ?? 0.0;
    final int classRank = stats?['class_rank'] ?? 0;
    final int pendingHomework = stats?['pending_homework'] ?? 0;

    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        // Quick Stats Summary Row
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                title: 'Attendance',
                value: '${attendanceRate.toStringAsFixed(1)}%',
                icon: Icons.how_to_reg_rounded,
                color: const Color(0xFF10B981), // Emerald
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildStatCard(
                title: 'Average Score',
                value: '${averageScore.toStringAsFixed(1)}%',
                icon: Icons.auto_awesome_rounded,
                color: const Color(0xFF3B82F6), // Blue
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                title: 'Class Rank',
                value: classRank > 0 ? '#$classRank' : 'N/A',
                icon: Icons.emoji_events_rounded,
                color: const Color(0xFFF59E0B), // Amber
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildStatCard(
                title: 'Pending Homework',
                value: '$pendingHomework',
                icon: Icons.assignment_late_rounded,
                color: const Color(0xFFEF4444), // Rose
              ),
            ),
          ],
        ),
        const SizedBox(height: 24),
        // Live CBT Exams alert card
        if (_cbtExams.isNotEmpty) ...[
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFEF3C7),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFFDE68A)),
            ),
            child: Row(
              children: [
                const Icon(Icons.notification_important_rounded, color: Color(0xFFD97706)),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Active Exams Scheduled',
                        style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF92400E)),
                      ),
                      Text(
                        'You have ${_cbtExams.length} live exams waiting. Complete them before they expire.',
                        style: const TextStyle(fontSize: 12, color: Color(0xFFB45309)),
                      ),
                    ],
                  ),
                ),
                TextButton(
                  onPressed: () => _tabs.animateTo(3),
                  child: const Text('Exams Tab', style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF92400E))),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
        ],
        // Announcements Section
        const Text(
          'Latest Bulletins',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
        ),
        const SizedBox(height: 12),
        _announcements.isEmpty
            ? const Center(child: Text('No announcements recorded.', style: TextStyle(color: Color(0xFF64748B))))
            : Column(
                children: _announcements.take(3).map((a) {
                  final date = (a['published_at'] as String?)?.substring(0, 10) ?? '';
                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFF1F5F9)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                a['title'] ?? '',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                              ),
                            ),
                            Text(date, style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text(
                          a['body'] ?? '',
                          style: const TextStyle(fontSize: 13, color: Color(0xFF475569)),
                        ),
                      ],
                    ),
                  );
                }).toList(),
              ),
      ],
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x05000000),
            blurRadius: 10,
            offset: Offset(0, 4),
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
                title,
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
              ),
              Icon(icon, color: color, size: 20),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            value,
            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
          ),
        ],
      ),
    );
  }

  // ─── Results ───────────────────────────────────────────────────────────────

  Widget _buildResults(Color primary) {
    if (_reportSubjects.isEmpty) {
      return const Center(child: Text('No academic results recorded yet.', style: TextStyle(color: Color(0xFF64748B))));
    }
    return ListView.separated(
      padding: const EdgeInsets.all(20),
      itemCount: _reportSubjects.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (context, i) {
        final s = _reportSubjects[i];
        final grade = s['grade'] as String? ?? 'F';
        final total = s['total'] ?? 0;
        final gradeColor = grade == 'A'
            ? const Color(0xFF10B981)
            : grade == 'B'
                ? const Color(0xFF3B82F6)
                : grade == 'F'
                    ? const Color(0xFFEF4444)
                    : const Color(0xFFF59E0B);
        return Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFF1F5F9)),
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      s['subject_name'] ?? 'Subject',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'CA1: ${s['ca1'] ?? 0}   CA2: ${s['ca2'] ?? 0}   Exam: ${s['exam'] ?? 0}',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
              Column(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: gradeColor.withValues(alpha: 0.1),
                      shape: BoxShape.circle,
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      grade,
                      style: TextStyle(color: gradeColor, fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Total: $total',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  // ─── E-Learning Notes ──────────────────────────────────────────────────────

  Widget _buildELearning(Color primary) {
    final filteredNotes = _elearningNotes.where((note) {
      final title = (note['title'] as String? ?? '').toLowerCase();
      final subject = (note['subject_name'] as String? ?? '').toLowerCase();
      final matchesSearch = title.contains(_noteSearchQuery.toLowerCase()) ||
          subject.contains(_noteSearchQuery.toLowerCase());
      final matchesSubject = _selectedSubjectFilter == null ||
          note['subject_name'] == _selectedSubjectFilter;
      return matchesSearch && matchesSubject;
    }).toList();

    final subjectsSet = _elearningNotes.map((n) => n['subject_name'] as String).toSet().toList();

    return Column(
      children: [
        // Search & Subject Filter Bar
        Container(
          padding: const EdgeInsets.all(16),
          color: Colors.white,
          child: Column(
            children: [
              TextField(
                decoration: InputDecoration(
                  hintText: 'Search study resources...',
                  prefixIcon: const Icon(Icons.search_rounded),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onChanged: (val) => setState(() => _noteSearchQuery = val),
              ),
              if (subjectsSet.isNotEmpty) ...[
                const SizedBox(height: 12),
                SizedBox(
                  height: 38,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    children: [
                      Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ChoiceChip(
                          label: const Text('All Subjects'),
                          selected: _selectedSubjectFilter == null,
                          onSelected: (_) => setState(() => _selectedSubjectFilter = null),
                          selectedColor: primary.withValues(alpha: 0.15),
                          checkmarkColor: primary,
                        ),
                      ),
                      ...subjectsSet.map((sub) => Padding(
                            padding: const EdgeInsets.only(right: 8),
                            child: ChoiceChip(
                              label: Text(sub),
                              selected: _selectedSubjectFilter == sub,
                              onSelected: (_) => setState(() => _selectedSubjectFilter = sub),
                              selectedColor: primary.withValues(alpha: 0.15),
                              checkmarkColor: primary,
                            ),
                          )),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
        // Notes list
        Expanded(
          child: filteredNotes.isEmpty
              ? const Center(child: Text('No e-learning resources matched.', style: TextStyle(color: Color(0xFF64748B))))
              : ListView.separated(
                  padding: const EdgeInsets.all(20),
                  itemCount: filteredNotes.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 12),
                  itemBuilder: (_, i) => NotesDownloadTile(note: filteredNotes[i]),
                ),
        ),
      ],
    );
  }

  // ─── CBT Exams ─────────────────────────────────────────────────────────────

  Widget _buildCbtExams(Color primary) {
    if (_cbtExams.isEmpty) {
      return const Center(child: Text('No active CBT exams resolved.', style: TextStyle(color: Color(0xFF64748B))));
    }
    return ListView.separated(
      padding: const EdgeInsets.all(20),
      itemCount: _cbtExams.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (context, i) {
        final exam = _cbtExams[i];
        final duration = exam['duration_minutes'] ?? 0;
        final passPercentage = exam['pass_percentage'] ?? 50.0;
        return Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFF1F5F9)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                exam['title'] ?? 'Exam',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Icon(Icons.timer_outlined, size: 14, color: primary),
                  const SizedBox(width: 4),
                  Text('$duration mins', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                  const SizedBox(width: 16),
                  Icon(Icons.emoji_events_outlined, size: 14, color: primary),
                  const SizedBox(width: 4),
                  Text('Pass: $passPercentage%', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                ],
              ),
              if ((exam['instructions'] as String?)?.isNotEmpty == true) ...[
                const SizedBox(height: 12),
                Text(
                  exam['instructions'],
                  style: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                ),
              ],
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => CbtExamScreen(exam: exam),
                      ),
                    ).then((_) => _loadData());
                  },
                  icon: const Icon(Icons.play_circle_outline_rounded, size: 18),
                  label: const Text('Start Offline Attempt'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: primary,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  // ─── Timetable & Homework ──────────────────────────────────────────────────

  Widget _buildTimetableHomework(Color primary) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        // Homework summary section
        const Text(
          'Homework Assignments',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
        ),
        const SizedBox(height: 12),
        _homework.isEmpty
            ? const Center(child: Text('No homework assigned.', style: TextStyle(color: Color(0xFF64748B))))
            : Column(
                children: _homework.map((h) {
                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
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
                              Text(h['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                              Text(h['subject_name'] ?? '', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                              Text('Due: ${h['due_date']}', style: const TextStyle(fontSize: 11, color: Color(0xFFEF4444))),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                }).toList(),
              ),
        const SizedBox(height: 24),
        // Timetable Section
        const Text(
          'Weekly Timetable',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
        ),
        const SizedBox(height: 12),
        _timetable.isEmpty
            ? const Center(child: Text('No timetable entries recorded.', style: TextStyle(color: Color(0xFF64748B))))
            : _buildTimetableGrid(primary),
      ],
    );
  }

  Widget _buildTimetableGrid(Color primary) {
    const days = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    final grouped = <int, List<Map<String, dynamic>>>{};
    for (final e in _timetable) {
      grouped.putIfAbsent(e['day_of_week'] as int? ?? 1, () => []).add(e);
    }
    return Column(
      children: grouped.entries.map((entry) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Text(
              days[entry.key],
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF475569)),
            ),
          ),
          ...entry.value.map((e) => Container(
            margin: const EdgeInsets.only(bottom: 6),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFF1F5F9)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: primary.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    '${e['starts_at']}',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: primary),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(e['subject_name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      Text(e['teacher_name'] ?? '', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                    ],
                  ),
                ),
                if ((e['room'] as String?)?.isNotEmpty == true)
                  Text(e['room'] as String, style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
              ],
            ),
          )),
        ],
      )).toList(),
    );
  }
}
