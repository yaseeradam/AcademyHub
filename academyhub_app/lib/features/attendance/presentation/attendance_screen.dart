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
      // Offline-first: check cached students
      final cached = await LocalDatabase.instance.getStudents();
      final classCached = cached.where((s) => s['class_id'] == widget.classId).toList();

      if (classCached.isNotEmpty) {
        setState(() {
          _students = classCached;
          for (var s in _students) {
            _attendanceMap[s['id']] = 'present'; // Default
          }
        });
      } else if (_isOnline) {
        // Fetch from API
        final response = await apiClient.dio.get('/teacher/classes/${widget.classId}/students');
        if (response.statusCode == 200 && response.data != null) {
          final list = List<Map<String, dynamic>>.from(response.data);
          setState(() {
            _students = list;
            for (var s in _students) {
              _attendanceMap[s['id']] = 'present';
            }
          });

          // Cache locally
          for (var s in list) {
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
    } catch (e) {
      // Fallback
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

    final payload = {
      'class_id': widget.classId,
      'date': DateTime.now().toIso8601String().substring(0, 10),
      'attendance': _attendanceMap.map((key, value) => MapEntry(key.toString(), value)),
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
      // Handle error
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Widget _buildStudentRow(Map<String, dynamic> student) {
    final int id = student['id'];
    final currentStatus = _attendanceMap[id] ?? 'present';
    final name = '${student['first_name']} ${student['last_name']}';
    final admission = student['admission_number'];

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Column(
          children: [
            Row(
              children: [
                CircleAvatar(
                  backgroundColor: AppColors.softBlue.withOpacity(0.12),
                  child: Text(
                    student['first_name'].substring(0, 1).toUpperCase() +
                    student['last_name'].substring(0, 1).toUpperCase(),
                    style: const TextStyle(color: AppColors.softBlue, fontWeight: FontWeight.bold),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        name,
                        style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                      Text(
                        admission,
                        style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
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
                _buildStatusButton(id, 'present', 'Present', AppColors.successGreen),
                _buildStatusButton(id, 'absent', 'Absent', AppColors.dangerRed),
                _buildStatusButton(id, 'late', 'Late', AppColors.warningOrange),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusButton(int studentId, String status, String label, Color color) {
    final isSelected = _attendanceMap[studentId] == status;
    return Expanded(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 4.0),
        child: OutlinedButton(
          style: OutlinedButton.styleFrom(
            backgroundColor: isSelected ? color : Colors.white,
            side: BorderSide(color: isSelected ? color : AppColors.divider),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            padding: const EdgeInsets.symmetric(vertical: 8),
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
              color: isSelected ? Colors.white : AppColors.textSecondary,
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
        title: Text('${widget.className} Attendance'),
        elevation: 0.5,
        backgroundColor: AppColors.cardSurface,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                if (!_isOnline)
                  Container(
                    height: 32,
                    color: const Color(0xFFFEF3C7),
                    child: const Center(
                      child: Text(
                        '📡 Offline Mode — Queueing Changes',
                        style: TextStyle(color: Color(0xFF92400E), fontSize: 13, fontWeight: FontWeight.w600),
                      ),
                    ),
                  ),
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Row(
                    children: [
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.successGreen.withOpacity(0.12),
                            foregroundColor: AppColors.successGreen,
                            minimumSize: const Size.fromHeight(40),
                          ),
                          onPressed: () => _markAll('present'),
                          child: const Text('Mark All Present'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.dangerRed.withOpacity(0.12),
                            foregroundColor: AppColors.dangerRed,
                            minimumSize: const Size.fromHeight(40),
                          ),
                          onPressed: () => _markAll('absent'),
                          child: const Text('Mark All Absent'),
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
                  child: ElevatedButton(
                    onPressed: _students.isEmpty ? null : _saveAttendance,
                    child: const Text('Save Attendance'),
                  ),
                ),
              ],
            ),
    );
  }
}
