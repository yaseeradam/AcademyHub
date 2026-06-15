import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';

class ParentAttendanceScreen extends StatefulWidget {
  const ParentAttendanceScreen({super.key});

  @override
  State<ParentAttendanceScreen> createState() => _ParentAttendanceScreenState();
}

class _ParentAttendanceScreenState extends State<ParentAttendanceScreen> {
  List<dynamic> _attendanceList = [];
  List<dynamic> _filteredList = [];
  List<String> _students = [];
  String _selectedStudent = 'All';
  String _selectedStatus = 'All';
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadAttendance();
  }

  Future<void> _loadAttendance() async {
    final auth = context.read<AuthProvider>();
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final data = await auth.apiService.getWithCache('/parent/attendance');
      if (mounted) {
        setState(() {
          _attendanceList = data as List;
          _students = ['All', ..._attendanceList.map((item) => item['student_name'] as String).toSet()];
          _filterList();
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'Failed to load attendance logs: $e';
          _loading = false;
        });
      }
    }
  }

  void _filterList() {
    setState(() {
      _filteredList = _attendanceList.where((item) {
        final matchStudent = _selectedStudent == 'All' || item['student_name'] == _selectedStudent;
        final matchStatus = _selectedStatus == 'All' || item['status'] == _selectedStatus;
        return matchStudent && matchStatus;
      }).toList();
      // Sort by date descending
      _filteredList.sort((a, b) {
        final ad = DateTime.tryParse(a['date'] ?? '') ?? DateTime(1970);
        final bd = DateTime.tryParse(b['date'] ?? '') ?? DateTime(1970);
        return bd.compareTo(ad);
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Attendance History',
            style: GoogleFonts.spaceGrotesk(
                fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
        backgroundColor: AppColors.surface,
        elevation: 0,
        iconTheme: IconThemeData(color: AppColors.textPrimary),
        actions: [
          IconButton(
            icon: Icon(Icons.refresh, color: AppColors.textPrimary),
            onPressed: _loadAttendance,
          )
        ],
      ),
      body: Column(
        children: [
          if (_loading) LinearProgressIndicator(color: primary, minHeight: 2),
          _buildFilters(primary),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _error != null
                    ? _buildErrorState()
                    : _filteredList.isEmpty
                        ? _buildEmptyState()
                        : _buildAttendanceList(),
          ),
        ],
      ),
    );
  }

  Widget _buildFilters(Color primary) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      color: AppColors.surface,
      child: Column(
        children: [
          // Student Dropdown
          Row(
            children: [
              Expanded(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    color: AppColors.surface2,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.borderLight),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: _selectedStudent,
                      dropdownColor: AppColors.surface,
                      items: _students.map((String student) {
                        return DropdownMenuItem<String>(
                          value: student,
                          child: Text(student,
                              style: GoogleFonts.spaceGrotesk(fontSize: 13, color: AppColors.textPrimary)),
                        );
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) {
                          setState(() {
                            _selectedStudent = val;
                            _filterList();
                          });
                        }
                      },
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              // Status Dropdown
              Expanded(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    color: AppColors.surface2,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.borderLight),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: _selectedStatus,
                      dropdownColor: AppColors.surface,
                      items: <String>['All', 'Present', 'Absent', 'Late', 'Excused'].map((String status) {
                        return DropdownMenuItem<String>(
                          value: status,
                          child: Text(status,
                              style: GoogleFonts.spaceGrotesk(fontSize: 13, color: AppColors.textPrimary)),
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
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildAttendanceList() {
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 30),
      itemCount: _filteredList.length,
      itemBuilder: (ctx, index) {
        final item = _filteredList[index];
        final name = item['student_name'] ?? 'Student';
        final date = item['date'] ?? 'N/A';
        final status = item['status'] ?? 'Present';
        final note = item['note'];
        final term = item['term'] ?? 1;
        final session = item['session'] ?? '';

        Color badgeColor;
        switch (status) {
          case 'Present':
            badgeColor = AppColors.success;
            break;
          case 'Absent':
            badgeColor = AppColors.error;
            break;
          case 'Late':
            badgeColor = AppColors.warning;
            break;
          case 'Excused':
            badgeColor = AppColors.info;
            break;
          default:
            badgeColor = AppColors.success;
        }

        return Container(
          margin: const EdgeInsets.only(bottom: 12),
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
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(name,
                        style: GoogleFonts.spaceGrotesk(
                            fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: badgeColor.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(status,
                        style: GoogleFonts.spaceGrotesk(
                            fontSize: 11, fontWeight: FontWeight.bold, color: badgeColor)),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Row(
                children: [
                  Icon(Icons.calendar_today_outlined, size: 12, color: AppColors.textMuted),
                  const SizedBox(width: 4),
                  Text(date,
                      style: GoogleFonts.spaceGrotesk(fontSize: 12, color: AppColors.textSecondary)),
                  const Spacer(),
                  Text('Term $term ($session)',
                      style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textMuted)),
                ],
              ),
              if (note != null && note.toString().trim().isNotEmpty) ...[
                const SizedBox(height: 10),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.surface2,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text('Note: $note',
                      style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary, fontStyle: FontStyle.italic)),
                )
              ],
            ],
          ),
        );
      },
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.calendar_today, size: 48, color: AppColors.textMuted),
          const SizedBox(height: 12),
          Text('No matching logs found.',
              style: GoogleFonts.spaceGrotesk(fontSize: 14, color: AppColors.textSecondary)),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.error_outline, size: 48, color: AppColors.error),
            const SizedBox(height: 12),
            Text(_error ?? 'An error occurred',
                textAlign: TextAlign.center,
                style: GoogleFonts.spaceGrotesk(fontSize: 13, color: AppColors.textSecondary)),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _loadAttendance,
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.parentAccent),
              child: Text('Retry', style: GoogleFonts.spaceGrotesk(fontSize: 12, color: Colors.white)),
            )
          ],
        ),
      ),
    );
  }
}
