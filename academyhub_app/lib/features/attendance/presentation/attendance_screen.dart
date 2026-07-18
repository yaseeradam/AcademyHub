import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/database/local_db.dart';
import 'package:academyhub_app/core/network/api_client.dart';

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

    return Card(
      color: Colors.white,
      elevation: 2,
      shadowColor: Colors.black.withOpacity(0.06),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Column(
          children: [
            Row(
              children: [
                CircleAvatar(
                  backgroundColor: const Color(0xFFFEF3C7),
                  child: Text(
                    student['first_name'].substring(0, 1).toUpperCase() +
                    student['last_name'].substring(0, 1).toUpperCase(),
                    style: const TextStyle(color: Color(0xFFF59E0B), fontWeight: FontWeight.bold),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        name,
                        style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 15),
                      ),
                      Text(
                        admission,
                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          '${widget.className} Attendance',
          style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        elevation: 0,
        backgroundColor: const Color(0xFF1E293B),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary))
          : Column(
              children: [
                if (!_isOnline)
                  Container(
                    height: 36,
                    color: const Color(0xFFF59E0B),
                    child: const Center(
                      child: Text(
                        'OFFLINE — QUEUEING CHANGES',
                        style: TextStyle(color: Color(0xFF0F172A), fontSize: 13, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                      ),
                    ),
                  ),
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Row(
                    children: [
                      Expanded(
                        child: SizedBox(
                          height: 44,
                          child: OutlinedButton(
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: Color(0xFF10B981), width: 1.5),
                              foregroundColor: const Color(0xFF10B981),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                            onPressed: () => _markAll('present'),
                            child: const Text('Mark All Present', style: TextStyle(fontWeight: FontWeight.bold)),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: SizedBox(
                          height: 44,
                          child: OutlinedButton(
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: Color(0xFFF43F5E), width: 1.5),
                              foregroundColor: const Color(0xFFF43F5E),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                            onPressed: () => _markAll('absent'),
                            child: const Text('Mark All Absent', style: TextStyle(fontWeight: FontWeight.bold)),
                          ),
                        ),
                      ),
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
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Container(
                        height: 52,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: AppColors.amberPrimary.withOpacity(0.35),
                              blurRadius: 16,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.amberPrimary,
                            foregroundColor: Colors.white,
                            elevation: 0,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          onPressed: _students.isEmpty ? null : _saveAttendance,
                          child: const Text(
                            'SAVE ATTENDANCE',
                            style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
                          ),
                        ),
                      ),
                      if (!_isOnline) ...[
                        const SizedBox(height: 8),
                        const Text(
                          '📡 Will sync when connected',
                          style: TextStyle(color: AppColors.amberPrimary, fontSize: 11, fontWeight: FontWeight.bold),
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
