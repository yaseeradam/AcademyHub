import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/database/local_db.dart';
import 'package:academyhub_app/core/network/api_client.dart';

// Teacher role color
const _rc = AppColors.roleStaff;
const _rcDark = Color(0xFF134E4A);

class AttendanceScreen extends StatefulWidget {
  final int classId;
  final String className;
  
  const AttendanceScreen({
    super.key,
    required this.classId,
    required this.className,
  });

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  bool _isLoading = false;
  bool _isOnline = true;
  List<Map<String, dynamic>> _students = [];
  final Map<int, String> _attendanceMap = {}; // studentId -> status ('present', 'absent', 'late')

  @override
  void initState() {
    super.initState();
    _checkConnectivity();
    _loadStudents();
  }

  Future<void> _checkConnectivity() async {
    final result = await Connectivity().checkConnectivity();
    setState(() {
      _isOnline = !result.contains(ConnectivityResult.none);
    });
  }

  Future<void> _loadStudents() async {
    setState(() {
      _isLoading = true;
    });

    try {
      List<Map<String, dynamic>> studentsList = [];
      // Offline-first: check cached students
      final cached = await LocalDatabase.instance.getStudents();
      final classCached = cached.where((s) => s['class_id'] == widget.classId).toList();

      if (classCached.isNotEmpty) {
        studentsList = classCached;
      } else if (_isOnline) {
        // Fetch from API
        final response = await apiClient.dio.get('/teacher/classes/${widget.classId}/students');
        if (response.statusCode == 200 && response.data != null) {
          final rawList = response.data['data'] ?? response.data;
          studentsList = List<Map<String, dynamic>>.from(rawList);

          // Cache locally
          for (var s in studentsList) {
            await LocalDatabase.instance.insertStudent({
              'id': s['id'],
              'first_name': s['first_name'] ?? '',
              'last_name': s['last_name'] ?? '',
              'admission_number': s['admission_number'] ?? '',
              'class_id': widget.classId,
              'class_name': widget.className,
              'status': 'Active',
            });
          }
        }
      }

      // Initialize default
      final Map<int, String> initialAttendance = {};
      for (var s in studentsList) {
        initialAttendance[s['id']] = 'present';
      }

      // If online, fetch existing attendance sheet for pre-population
      if (_isOnline) {
        final todayStr = DateTime.now().toIso8601String().substring(0, 10);
        final response = await apiClient.dio.get(
          '/teacher/classes/${widget.classId}/attendance',
          queryParameters: {
            'date': todayStr,
            'term': 2,
            'session': '2024/2025',
          },
        );
        if (response.statusCode == 200 && response.data != null && response.data['data'] != null) {
          final sheet = response.data['data'] ?? {};
          final marks = List<dynamic>.from(sheet['marks'] ?? []);
          for (var m in marks) {
            final studentId = m['student_id'] as int?;
            final status = m['status']?.toString().toLowerCase();
            if (studentId != null && status != null) {
              initialAttendance[studentId] = status;
            }
          }
        }
      }

      setState(() {
        _students = studentsList;
        _attendanceMap.addAll(initialAttendance);
      });
    } catch (e) {
      debugPrint('Error loading attendance students: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _markAll(String status) {
    setState(() {
      for (var s in _students) {
        _attendanceMap[s['id']] = status;
      }
    });
  }

  Future<void> _saveAttendance() async {
    setState(() {
      _isLoading = true;
    });

    final List<Map<String, dynamic>> marksList = [];
    _attendanceMap.forEach((studentId, status) {
      // Capitalize status to match backend validation rule validation
      final capitalizedStatus = status.substring(0, 1).toUpperCase() + status.substring(1);
      marksList.add({
        'student_id': studentId,
        'status': capitalizedStatus,
        'note': null,
      });
    });

    final payload = {
      'class_id': widget.classId,
      'date': DateTime.now().toIso8601String().substring(0, 10),
      'term': 2,
      'session': '2024/2025',
      'marks': marksList,
    };

    try {
      if (_isOnline) {
        final response = await apiClient.dio.post('/teacher/attendance', data: payload);
        if (response.statusCode == 200 || response.statusCode == 201) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('✓ Attendance synced with school servers successfully!'),
                backgroundColor: AppColors.successGreen,
              ),
            );
          }
        }
      } else {
        // Queue locally
        await LocalDatabase.instance.addToQueue(
          'attendance',
          '/teacher/attendance',
          jsonEncode(payload),
        );
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('📡 Saved locally. Attendance will sync once online.'),
              backgroundColor: AppColors.warningOrange,
            ),
          );
        }
      }
      if (mounted) {
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to save attendance: $e'),
            backgroundColor: AppColors.dangerRed,
          ),
        );
      }
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Widget _buildStudentRow(Map<String, dynamic> student) {
    final int id = student['id'];
    final name = '${student['first_name']} ${student['last_name']}';
    final admission = student['admission_number'];
    final status = _attendanceMap[id] ?? 'present';
    final initials = student['first_name'].substring(0, 1).toUpperCase() +
        student['last_name'].substring(0, 1).toUpperCase();

    final Color statusColor = status == 'present'
        ? const Color(0xFF10B981)
        : status == 'absent'
            ? const Color(0xFFF43F5E)
            : const Color(0xFFF97316);

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: statusColor.withOpacity(0.2)),
        boxShadow: [BoxShadow(color: statusColor.withOpacity(0.06), blurRadius: 8, offset: const Offset(0, 3))],
      ),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
        child: Column(
          children: [
            Row(
              children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 250),
                  width: 44, height: 44,
                  decoration: BoxDecoration(
                    color: statusColor.withOpacity(0.12),
                    shape: BoxShape.circle,
                    border: Border.all(color: statusColor.withOpacity(0.35), width: 1.5),
                  ),
                  child: Center(
                    child: Text(initials,
                        style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 15)),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(name,
                          style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 14)),
                      const SizedBox(height: 2),
                      Text(admission,
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 11)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: statusColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    status[0].toUpperCase() + status.substring(1),
                    style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                _buildStatusButton(id, 'present', 'Present'),
                _buildStatusButton(id, 'absent', 'Absent'),
                _buildStatusButton(id, 'late', 'Late'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusButton(int studentId, String status, String label) {
    final isSelected = _attendanceMap[studentId] == status;
    Color bg;
    Color text;
    BorderSide border;

    if (status == 'present') {
      if (isSelected) {
        bg = const Color(0xFF10B981);
        text = Colors.white;
        border = BorderSide.none;
      } else {
        bg = const Color(0xFFF0FDF4);
        text = const Color(0xFF10B981);
        border = const BorderSide(color: Color(0xFF10B981), width: 0.5);
      }
    } else if (status == 'absent') {
      if (isSelected) {
        bg = const Color(0xFFF43F5E);
        text = Colors.white;
        border = BorderSide.none;
      } else {
        bg = const Color(0xFFFFF1F2);
        text = const Color(0xFFF43F5E);
        border = const BorderSide(color: Color(0xFFF43F5E), width: 0.5);
      }
    } else { // late
      if (isSelected) {
        bg = const Color(0xFFF97316);
        text = Colors.white;
        border = BorderSide.none;
      } else {
        bg = const Color(0xFFFFF7ED);
        text = const Color(0xFFF97316);
        border = const BorderSide(color: Color(0xFFF97316), width: 0.5);
      }
    }

    return Expanded(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 4.0),
        child: SizedBox(
          height: 36,
          child: OutlinedButton(
            style: OutlinedButton.styleFrom(
              backgroundColor: bg,
              side: border,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              padding: EdgeInsets.zero,
            ),
            onPressed: () {
              setState(() {
                _attendanceMap[studentId] = status;
              });
            },
            child: Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: text,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _summaryChip(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.25)),
      ),
      child: Text(label, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold)),
    );
  }

  Widget _markAllButton(String status, String label, IconData icon, Color color) {
    return Expanded(
      child: GestureDetector(
        onTap: () => _markAll(status),
        child: Container(
          height: 40,
          decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: color.withOpacity(0.3)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, color: color, size: 14),
              const SizedBox(width: 5),
              Text(label, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeroHeader() {
    final today = DateTime.now();
    final dateStr = '${_weekday(today.weekday)}, ${today.day} ${_month(today.month)} ${today.year}';
    return SafeArea(
      bottom: false,
      child: Container(
        decoration: const BoxDecoration(
          gradient: const LinearGradient(
            colors: [_rc, _rcDark],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                GestureDetector(
                  onTap: () => Navigator.pop(context),
                  child: Container(
                    width: 36, height: 36,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.arrow_back_rounded, color: Colors.white, size: 20),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(widget.className,
                          style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 2),
                      Text(dateStr,
                          style: TextStyle(color: Colors.white.withOpacity(0.75), fontSize: 12)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: _isOnline
                        ? Colors.white.withValues(alpha: 0.2)
                        : const Color(0xFFF59E0B).withValues(alpha: 0.9),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        _isOnline ? Icons.cloud_done_rounded : Icons.cloud_off_rounded,
                        color: Colors.white, size: 12,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        _isOnline ? 'Online' : 'Offline',
                        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                _heroBadge(Icons.people_rounded, '${_students.length}', 'Students'),
                const SizedBox(width: 10),
                _heroBadge(Icons.check_circle_rounded, '${_countStatus("present")}', 'Present'),
                const SizedBox(width: 10),
                _heroBadge(Icons.cancel_rounded, '${_countStatus("absent")}', 'Absent'),
                const SizedBox(width: 10),
                _heroBadge(Icons.watch_later_rounded, '${_countStatus("late")}', 'Late'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _heroBadge(IconData icon, String value, String label) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.12),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Icon(icon, color: Colors.white, size: 16),
            const SizedBox(height: 3),
            Text(value, style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold)),
            Text(label, style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 9)),
          ],
        ),
      ),
    );
  }

  int _countStatus(String status) =>
      _attendanceMap.values.where((v) => v == status).length;

  String _weekday(int d) => ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'][d - 1];
  String _month(int m) => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][m - 1];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.appBackground,
      appBar: null,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary))
          : Column(
              children: [
                _buildHeroHeader(),
                if (!_isOnline)
                  Container(
                    height: 32,
                    color: const Color(0xFFF59E0B),
                    child: const Center(
                      child: Text(
                        'OFFLINE — CHANGES WILL SYNC WHEN CONNECTED',
                        style: TextStyle(color: Color(0xFF0F172A), fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.4),
                      ),
                    ),
                  ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 14, 16, 4),
                  child: Row(
                    children: [
                      _markAllButton('present', 'All Present', Icons.check_circle_rounded, const Color(0xFF10B981)),
                      const SizedBox(width: 8),
                      _markAllButton('absent', 'All Absent', Icons.cancel_rounded, const Color(0xFFF43F5E)),
                      const SizedBox(width: 8),
                      _markAllButton('late', 'All Late', Icons.watch_later_rounded, const Color(0xFFF97316)),
                    ],
                  ),
                ),
                Expanded(
                  child: ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: _students.length,
                    itemBuilder: (context, index) {
                      return _buildStudentRow(_students[index]);
                    },
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Summary row
                      if (_students.isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              _summaryChip('${_countStatus("present")} Present', const Color(0xFF10B981)),
                              const SizedBox(width: 8),
                              _summaryChip('${_countStatus("absent")} Absent', const Color(0xFFF43F5E)),
                              const SizedBox(width: 8),
                              _summaryChip('${_countStatus("late")} Late', const Color(0xFFF97316)),
                            ],
                          ),
                        ),
                      Container(
                        height: 52,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(14),
                          gradient: const LinearGradient(
                            colors: [_rc, _rcDark],
                          ),
                          boxShadow: [BoxShadow(color: _rc, blurRadius: 14, offset: const Offset(0, 4))],
                        ),
                        child: ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            shadowColor: Colors.transparent,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                          onPressed: _students.isEmpty ? null : _saveAttendance,
                          icon: const Icon(Icons.save_rounded, size: 20),
                          label: Text(
                            _isOnline ? 'SAVE & SYNC ATTENDANCE' : 'SAVE LOCALLY',
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
                          ),
                        ),
                      ),
                      if (!_isOnline) ...[
                        const SizedBox(height: 6),
                        const Text(
                          '📡 Will sync automatically when back online',
                          style: TextStyle(color: Color(0xFFF97316), fontSize: 11, fontWeight: FontWeight.bold),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}
