import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/constants.dart';

class StudentAttendanceScreen extends StatefulWidget {
  const StudentAttendanceScreen({super.key});

  @override
  State<StudentAttendanceScreen> createState() => _StudentAttendanceScreenState();
}

class _StudentAttendanceScreenState extends State<StudentAttendanceScreen> {
  final _db = DatabaseHelper();
  List<Map<String, dynamic>> _attendanceList = [];
  List<Map<String, dynamic>> _filteredList = [];
  String _selectedStatus = 'All';
  bool _loading = true;

  // Computed summary metrics
  int _totalDays = 0;
  int _presentCount = 0;
  int _absentCount = 0;
  int _lateCount = 0;
  double _attendanceRate = 100.0;

  @override
  void initState() {
    super.initState();
    _loadAttendance();
  }

  Future<void> _loadAttendance() async {
    if (mounted) setState(() => _loading = true);

    try {
      final auth = context.read<AuthProvider>();
      final studentId = auth.user?.id ?? 0;

      // Query local sqlite copy of attendance
      final db = await _db.database;
      final data = await db.query(
        'local_attendance',
        where: 'student_id = ?',
        whereArgs: [studentId],
      );

      if (mounted) {
        setState(() {
          _attendanceList = data;
          _computeMetrics();
          _filterList();
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _computeMetrics() {
    _totalDays = _attendanceList.length;
    _presentCount = _attendanceList.where((a) => a['status'] == 'present' || a['status'] == 'Present').length;
    _absentCount = _attendanceList.where((a) => a['status'] == 'absent' || a['status'] == 'Absent').length;
    _lateCount = _attendanceList.where((a) => a['status'] == 'late' || a['status'] == 'Late').length;

    if (_totalDays > 0) {
      _attendanceRate = ((_presentCount + _lateCount) / _totalDays) * 100.0;
    } else {
      _attendanceRate = 100.0;
    }
  }

  void _filterList() {
    setState(() {
      _filteredList = _attendanceList.where((item) {
        if (_selectedStatus == 'All') return true;
        final status = (item['status'] as String? ?? '').toLowerCase();
        return status == _selectedStatus.toLowerCase();
      }).toList();

      // Sort by date descending
      _filteredList.sort((a, b) {
        final ad = DateTime.tryParse(a['date'] ?? '') ?? DateTime(1970);
        final bd = DateTime.tryParse(b['date'] ?? '') ?? DateTime(1970);
        return bd.compareTo(ad);
      });
    });
  }

  Future<void> _handleRefresh() async {
    final auth = context.read<AuthProvider>();
    try {
      await auth.syncService.backgroundRefresh('student');
    } catch (_) {}
    await _loadAttendance();
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;
    const accent = AppColors.studentAccent;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(
          'My Attendance',
          style: GoogleFonts.spaceGrotesk(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        backgroundColor: AppColors.surface,
        elevation: 0,
        iconTheme: IconThemeData(color: AppColors.textPrimary),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadAttendance,
          )
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _handleRefresh,
        color: primary,
        child: Column(
          children: [
            if (_loading) LinearProgressIndicator(color: primary, minHeight: 2),
            
            // Metrics Header Panel
            _buildMetricsHeader(accent),
            
            // Status Filter Bar
            _buildFilterBar(),

            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : _filteredList.isEmpty
                      ? _buildEmptyState()
                      : _buildAttendanceList(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMetricsHeader(Color accent) {
    return Container(
      padding: const EdgeInsets.all(16),
      color: AppColors.surface,
      child: Column(
        children: [
          // Circular attendance rate ring & mini stats
          Row(
            children: [
              Container(
                width: 76,
                height: 76,
                padding: const EdgeInsets.all(4),
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    SizedBox(
                      width: 76,
                      height: 76,
                      child: CircularProgressIndicator(
                        value: _attendanceRate / 100.0,
                        backgroundColor: AppColors.surface2,
                        color: _attendanceRate >= 85 ? AppColors.success : AppColors.error,
                        strokeWidth: 8,
                      ),
                    ),
                    Text(
                      '${_attendanceRate.toStringAsFixed(0)}%',
                      style: GoogleFonts.spaceGrotesk(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 20),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Attendance Summary',
                      style: GoogleFonts.spaceGrotesk(
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Overall status: ${_attendanceRate >= 85 ? "Good Standings" : "At Risk of Lockout"}',
                      style: GoogleFonts.spaceGrotesk(
                        fontSize: 11,
                        color: _attendanceRate >= 85 ? AppColors.success : AppColors.error,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        _miniStat('Total Days', '$_totalDays'),
                        _miniStat('Present', '$_presentCount'),
                        _miniStat('Late', '$_lateCount'),
                        _miniStat('Absent', '$_absentCount'),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _miniStat(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          value,
          style: GoogleFonts.spaceGrotesk(
            fontSize: 12,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        Text(
          label,
          style: GoogleFonts.spaceGrotesk(
            fontSize: 9,
            color: AppColors.textSecondary,
          ),
        ),
      ],
    );
  }

  Widget _buildFilterBar() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border(bottom: BorderSide(color: AppColors.borderLight)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            'Presence Log',
            style: GoogleFonts.spaceGrotesk(
              fontSize: 13,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10),
            decoration: BoxDecoration(
              color: AppColors.surface2,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: AppColors.borderLight),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: _selectedStatus,
                dropdownColor: AppColors.surface,
                style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontSize: 12),
                items: <String>['All', 'Present', 'Absent', 'Late', 'Excused'].map((String s) {
                  return DropdownMenuItem<String>(
                    value: s,
                    child: Text(s, style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary)),
                  );
                }).toList(),
                onChanged: (val) {
                  if (val != null) {
                    setState(() {
                      _selectedStatus = val;
                      _filterList();
                    });
                  }
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAttendanceList() {
    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 30),
      itemCount: _filteredList.length,
      itemBuilder: (ctx, index) {
        final item = _filteredList[index];
        final date = item['date'] ?? 'N/A';
        final statusStr = item['status'] ?? 'Present';
        final note = item['note'];
        final term = item['term'] ?? 1;
        final session = item['session'] ?? '';

        Color badgeColor;
        switch (statusStr.toString().toLowerCase()) {
          case 'present':
            badgeColor = AppColors.success;
            break;
          case 'absent':
            badgeColor = AppColors.error;
            break;
          case 'late':
            badgeColor = AppColors.warning;
            break;
          case 'excused':
            badgeColor = AppColors.info;
            break;
          default:
            badgeColor = AppColors.success;
        }

        return Container(
          margin: const EdgeInsets.only(bottom: 8),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(Icons.calendar_today_outlined, size: 13, color: AppColors.textSecondary),
                      const SizedBox(width: 8),
                      Text(
                        date,
                        style: GoogleFonts.spaceGrotesk(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                    ],
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: badgeColor.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      statusStr.toString().toUpperCase(),
                      style: GoogleFonts.spaceGrotesk(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: badgeColor,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Term $term • $session',
                    style: GoogleFonts.spaceGrotesk(
                      fontSize: 11,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
              if (note != null && note.toString().trim().isNotEmpty) ...[
                const SizedBox(height: 8),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.surface2,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    'Remark: $note',
                    style: GoogleFonts.spaceGrotesk(
                      fontSize: 11,
                      color: AppColors.textSecondary,
                      fontStyle: FontStyle.italic,
                    ),
                  ),
                )
              ],
            ],
          ),
        );
      },
    );
  }

  Widget _buildEmptyState() {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        const SizedBox(height: 100),
        Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.calendar_today_rounded, size: 40, color: AppColors.textMuted),
              const SizedBox(height: 12),
              Text(
                'No attendance logs recorded.',
                style: GoogleFonts.spaceGrotesk(
                  fontSize: 13,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
