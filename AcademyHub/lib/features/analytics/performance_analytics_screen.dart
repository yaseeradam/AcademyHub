import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/constants.dart';

class PerformanceAnalyticsScreen extends StatefulWidget {
  final int? studentId;
  final String? studentName;
  final String? admissionNumber;

  const PerformanceAnalyticsScreen({
    super.key,
    this.studentId,
    this.studentName,
    this.admissionNumber,
  });

  @override
  State<PerformanceAnalyticsScreen> createState() => _PerformanceAnalyticsScreenState();
}

class _PerformanceAnalyticsScreenState extends State<PerformanceAnalyticsScreen> {
  final _db = DatabaseHelper();
  bool _loading = true;

  int _selectedTerm = 1;
  String _selectedSession = '';
  List<String> _sessionsList = [];

  // Computed metrics
  double _averageScore = 0.0;
  double _attendanceRate = 100.0;
  int _completedHomeworkCount = 0;
  int _totalHomeworkCount = 0;
  int _cbtExamsCount = 0;
  double _cbtAverage = 0.0;

  List<Map<String, dynamic>> _strengths = [];
  List<Map<String, dynamic>> _weaknesses = [];
  List<Map<String, dynamic>> _allSubjects = [];
  Map<String, dynamic>? _studentStats;

  @override
  void initState() {
    super.initState();
    _initTermAndSession().then((_) => _loadPerformanceData());
  }

  Future<void> _initTermAndSession() async {
    final prefs = await SharedPreferences.getInstance();
    _selectedTerm = prefs.getInt('active_term') ?? 1;
    _selectedSession = prefs.getString('active_session') ?? '2026/2027';
    _sessionsList = [_selectedSession];
  }

  Future<void> _loadPerformanceData() async {
    if (mounted) setState(() => _loading = true);

    try {
      final auth = context.read<AuthProvider>();
      final isStudent = auth.user?.role == 'student';
      
      // Resolve target student identity
      final targetStudentId = widget.studentId ?? (isStudent ? auth.user?.id : 0) ?? 0;
      final targetAdmissionNo = widget.admissionNumber ?? (isStudent ? auth.user?.email : '') ?? '';

      // 1. Fetch general student stats
      if (targetAdmissionNo.isNotEmpty) {
        _studentStats = await _db.getStudentStats(targetAdmissionNo);
      }

      // 2. Fetch academic scores
      final classId = isStudent ? 0 : 0; // Use general score class_id = 0 for student results
      final scores = await _db.getScores(classId, _selectedTerm, _selectedSession);

      // Filter scores specifically for this student
      final studentScores = scores.where((s) => s['student_id'] == targetStudentId).toList();

      // Extract unique sessions for dropdown
      final allLocalScores = await _db.database.then(
        (db) => db.query('local_scores', columns: ['session'], distinct: true)
      );
      final sessions = allLocalScores.map((s) => s['session'] as String).where((s) => s.isNotEmpty).toList();
      if (sessions.isNotEmpty && !sessions.contains(_selectedSession)) {
        sessions.add(_selectedSession);
      }

      // 3. Fetch attendance
      final localAttendance = await _db.database.then(
        (db) => db.query('local_attendance', where: 'student_id = ?', whereArgs: [targetStudentId])
      );

      // 4. Fetch homework submissions
      final localSubmissions = await _db.database.then(
        (db) => db.query('local_submissions', where: 'student_id = ?', whereArgs: [targetStudentId])
      );
      final homeworkList = await _db.getAllHomework();

      // 5. Fetch CBT attempts
      final cbtAttempts = await _db.database.then(
        (db) => db.query('local_cbt_attempts', where: 'student_id = ?', whereArgs: [targetStudentId])
      );

      // Compute stats on the fly
      double totalAcademic = 0.0;
      int scoresCount = 0;
      List<Map<String, dynamic>> strengthsList = [];
      List<Map<String, dynamic>> weaknessesList = [];
      List<Map<String, dynamic>> subjectList = [];

      for (final s in studentScores) {
        final total = (s['total'] as num?)?.toDouble() ?? 0.0;
        totalAcademic += total;
        scoresCount++;

        final subInfo = {
          'subject_name': s['subject_name'] ?? 'Subject',
          'total': total,
          'grade': s['grade'] ?? 'F',
          'ca1': s['ca1'] ?? 0,
          'ca2': s['ca2'] ?? 0,
          'exam': s['exam'] ?? 0,
        };

        subjectList.add(subInfo);
        if (total >= 70.0) {
          strengthsList.add(subInfo);
        } else if (total < 60.0) {
          weaknessesList.add(subInfo);
        }
      }

      // Attendance
      double attRate = 100.0;
      if (localAttendance.isNotEmpty) {
        final present = localAttendance.where((a) => a['status'] == 'present').length;
        final lateCount = localAttendance.where((a) => a['status'] == 'late').length;
        attRate = ((present + lateCount) / localAttendance.length) * 100.0;
      } else if (_studentStats != null) {
        attRate = (_studentStats!['attendance_rate'] as num?)?.toDouble() ?? 100.0;
      }

      // Homework completion
      int completedHw = localSubmissions.length;
      int totalHw = homeworkList.length;

      // CBT
      int cbtCount = cbtAttempts.length;
      double cbtSum = 0.0;
      for (final attempt in cbtAttempts) {
        cbtSum += (attempt['score'] as num?)?.toDouble() ?? 0.0;
      }

      if (mounted) {
        setState(() {
          _averageScore = scoresCount > 0 ? (totalAcademic / scoresCount) : ((_studentStats?['average_score'] as num?)?.toDouble() ?? 0.0);
          _attendanceRate = attRate;
          _completedHomeworkCount = completedHw;
          _totalHomeworkCount = totalHw > completedHw ? totalHw : completedHw;
          _cbtExamsCount = cbtCount;
          _cbtAverage = cbtCount > 0 ? (cbtSum / cbtCount) : 0.0;
          _strengths = strengthsList;
          _weaknesses = weaknessesList;
          _allSubjects = subjectList;
          if (sessions.isNotEmpty) {
            _sessionsList = sessions;
          }
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;
    final displayName = widget.studentName ?? (auth.user?.role == 'student' ? auth.user?.name : 'Student Performance');

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(displayName!, style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary)),
        backgroundColor: AppColors.surface,
        foregroundColor: AppColors.textPrimary,
        elevation: 0,
        shape: Border(bottom: BorderSide(color: AppColors.borderLight)),
        actions: [
          IconButton(
            icon: const Icon(Icons.sync_rounded),
            onPressed: _loadPerformanceData,
          ),
        ],
      ),
      body: _loading
          ? Center(child: CircularProgressIndicator(color: primary))
          : Column(
              children: [
                // Term / Session Selector
                Container(
                  color: AppColors.surface,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  child: Row(
                    children: [
                      Expanded(
                        child: DropdownButtonFormField<String>(
                          initialValue: _selectedSession,
                          dropdownColor: AppColors.surface2,
                          style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontSize: 13),
                          decoration: _inputDecoration('Academic Session'),
                          items: _sessionsList
                              .map((s) => DropdownMenuItem(value: s, child: Text(s, style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary))))
                              .toList(),
                          onChanged: (val) {
                            if (val != null) {
                              setState(() => _selectedSession = val);
                              _loadPerformanceData();
                            }
                          },
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: DropdownButtonFormField<int>(
                          initialValue: _selectedTerm,
                          dropdownColor: AppColors.surface2,
                          style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontSize: 13),
                          decoration: _inputDecoration('Term'),
                          items: [
                            DropdownMenuItem(value: 1, child: Text('1st Term', style: TextStyle(color: AppColors.textPrimary))),
                            DropdownMenuItem(value: 2, child: Text('2nd Term', style: TextStyle(color: AppColors.textPrimary))),
                            DropdownMenuItem(value: 3, child: Text('3rd Term', style: TextStyle(color: AppColors.textPrimary))),
                          ],
                          onChanged: (val) {
                            if (val != null) {
                              setState(() => _selectedTerm = val);
                              _loadPerformanceData();
                            }
                          },
                        ),
                      ),
                    ],
                  ),
                ),

                Expanded(
                  child: ListView(
                    padding: const EdgeInsets.all(20),
                    children: [
                      // Overall average & attendance circular indicators
                      Row(
                        children: [
                          Expanded(
                            child: _buildMetricCircleCard(
                              title: 'GPA Average',
                              value: '${_averageScore.toStringAsFixed(1)}%',
                              progress: _averageScore / 100.0,
                              color: primary,
                              subtitle: _averageScore >= 70 ? 'Excellent' : _averageScore >= 50 ? 'Average' : 'Needs Focus',
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: _buildMetricCircleCard(
                              title: 'Attendance',
                              value: '${_attendanceRate.toStringAsFixed(1)}%',
                              progress: _attendanceRate / 100.0,
                              color: AppColors.success,
                              subtitle: _attendanceRate >= 85 ? 'Excellent' : 'Risk of Lockout',
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),

                      // Attendance Correlation Smart Insight Box
                      if (_attendanceRate < 80.0 && _averageScore < 60.0)
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppColors.error.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: AppColors.error.withValues(alpha: 0.25)),
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Icon(Icons.warning_amber_rounded, color: AppColors.error),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Attendance Impact Warning',
                                      style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, color: AppColors.error),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'Low attendance is negatively impacting this student\'s academic performance. Immediate classroom presence is advised.',
                                      style: GoogleFonts.spaceGrotesk(fontSize: 12, color: AppColors.textPrimary),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        )
                      else if (_averageScore >= 80.0)
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppColors.success.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: AppColors.success.withValues(alpha: 0.25)),
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Icon(Icons.star_rounded, color: AppColors.success),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Honor Roll Status',
                                      style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, color: AppColors.success),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'Exceptional performance! Outstanding understanding demonstrated across subjects.',
                                      style: GoogleFonts.spaceGrotesk(fontSize: 12, color: AppColors.textPrimary),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      const SizedBox(height: 20),

                      // CBT & Homework Stats
                      Row(
                        children: [
                          Expanded(
                            child: _buildMetricCard(
                              title: 'Homework',
                              value: '$_completedHomeworkCount/$_totalHomeworkCount',
                              icon: Icons.assignment_turned_in_rounded,
                              color: AppColors.parentAccent,
                              subtitle: 'Completion: ${((_totalHomeworkCount > 0 ? _completedHomeworkCount / _totalHomeworkCount : 1.0) * 100.0).toStringAsFixed(0)}%',
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: _buildMetricCard(
                              title: 'CBT Exam Avg',
                              value: _cbtExamsCount > 0 ? '${_cbtAverage.toStringAsFixed(1)}%' : 'N/A',
                              icon: Icons.computer_rounded,
                              color: AppColors.primary,
                              subtitle: '$_cbtExamsCount exam attempts',
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 24),

                      // Subject Strengths & Weaknesses
                      Text(
                        'Strengths & Improvement Areas',
                        style: GoogleFonts.spaceGrotesk(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: _buildListCard(
                              title: 'Strengths (70%+)',
                              items: _strengths,
                              color: AppColors.success,
                              icon: Icons.trending_up_rounded,
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: _buildListCard(
                              title: 'Needs Work (<60%)',
                              items: _weaknesses,
                              color: AppColors.error,
                              icon: Icons.trending_down_rounded,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 24),

                      // Subject Scores List
                      Text(
                        'Subject Score Details',
                        style: GoogleFonts.spaceGrotesk(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                      const SizedBox(height: 12),
                      _allSubjects.isEmpty
                          ? Center(child: Padding(
                              padding: const EdgeInsets.symmetric(vertical: 20),
                              child: Text('No subject scores loaded for this term.', style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 13)),
                            ))
                          : Container(
                              decoration: BoxDecoration(
                                color: AppColors.surface,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: AppColors.borderLight),
                              ),
                              child: ListView.separated(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                itemCount: _allSubjects.length,
                                separatorBuilder: (_, index) => Divider(height: 1, color: AppColors.borderLight),
                                itemBuilder: (context, i) {
                                  final s = _allSubjects[i];
                                  final total = s['total'] ?? 0;
                                  final grade = s['grade'] ?? '';
                                  final isGood = total >= 70;
                                  final isBad = total < 60;
                                  return Padding(
                                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                    child: Row(
                                      children: [
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(s['subject_name'] ?? 'Subject', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary)),
                                              const SizedBox(height: 4),
                                              Text('CA1: ${s['ca1']} | CA2: ${s['ca2']} | Exam: ${s['exam']}', style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary)),
                                            ],
                                          ),
                                        ),
                                        Column(
                                          crossAxisAlignment: CrossAxisAlignment.end,
                                          children: [
                                            Text(
                                              '$total%',
                                              style: GoogleFonts.spaceGrotesk(
                                                fontWeight: FontWeight.bold,
                                                color: isGood ? AppColors.success : isBad ? AppColors.error : AppColors.textSecondary,
                                                fontSize: 14,
                                              ),
                                            ),
                                            Text(grade, style: GoogleFonts.spaceGrotesk(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.textMuted)),
                                          ],
                                        ),
                                      ],
                                    ),
                                  );
                                },
                              ),
                            ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildMetricCircleCard({
    required String title,
    required String value,
    required double progress,
    required Color color,
    required String subtitle,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: Column(
        children: [
          Text(title, style: GoogleFonts.spaceGrotesk(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
          const SizedBox(height: 16),
          Stack(
            alignment: Alignment.center,
            children: [
              SizedBox(
                width: 76,
                height: 76,
                child: CircularProgressIndicator(
                  value: progress.isNaN || progress.isInfinite ? 0.0 : progress,
                  backgroundColor: AppColors.surface2,
                  color: color,
                  strokeWidth: 8,
                ),
              ),
              Text(
                value,
                style: GoogleFonts.spaceGrotesk(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(subtitle, style: GoogleFonts.spaceGrotesk(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }

  Widget _buildMetricCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
    required String subtitle,
  }) {
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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Icon(icon, color: color, size: 20),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(8)),
                child: Text(
                  title,
                  style: GoogleFonts.spaceGrotesk(color: color, fontSize: 9, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Text(value, style: GoogleFonts.spaceGrotesk(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
          const SizedBox(height: 4),
          Text(subtitle, style: GoogleFonts.spaceGrotesk(fontSize: 10, color: AppColors.textSecondary)),
        ],
      ),
    );
  }

  Widget _buildListCard({
    required String title,
    required List<Map<String, dynamic>> items,
    required Color color,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: color, size: 16),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  title,
                  style: GoogleFonts.spaceGrotesk(fontSize: 11, fontWeight: FontWeight.bold, color: color),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          items.isEmpty
              ? Text('None recorded', style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textMuted))
              : Column(
                  children: items.map((itm) => Padding(
                    padding: const EdgeInsets.only(bottom: 6),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            itm['subject_name'] ?? '',
                            style: GoogleFonts.spaceGrotesk(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.textPrimary),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        Text(
                          '${itm['total'].toStringAsFixed(0)}%',
                          style: GoogleFonts.spaceGrotesk(fontSize: 12, fontWeight: FontWeight.bold, color: color),
                        ),
                      ],
                    ),
                  )).toList(),
                ),
        ],
      ),
    );
  }

  InputDecoration _inputDecoration(String label) => InputDecoration(
        labelText: label,
        labelStyle: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 11),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: AppColors.borderLight)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: AppColors.borderLight)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: AppColors.borderLight)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        isDense: true,
        fillColor: AppColors.surface2,
        filled: true,
      );
}
