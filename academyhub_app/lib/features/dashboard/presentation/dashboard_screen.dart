import 'dart:async';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';
import 'package:academyhub_app/core/network/sync_processor.dart';
import 'package:academyhub_app/core/network/api_client.dart';
import 'package:academyhub_app/features/results/presentation/results_chart.dart';
import 'package:academyhub_app/features/timetable/presentation/timetable_view.dart';
import 'package:academyhub_app/features/attendance/presentation/attendance_screen.dart';
import 'package:academyhub_app/features/scores/presentation/scores_entry_screen.dart';
import 'package:academyhub_app/features/homework/presentation/homework_view.dart';
import 'package:academyhub_app/features/parent/presentation/children_view.dart';
import 'package:academyhub_app/features/messaging/presentation/chat_view.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  String _userRole = 'student';
  int _currentIndex = 0;
  bool _isOnline = true;
  String _syncStatus = 'synced'; // 'synced', 'pending', 'syncing'
  late StreamSubscription<List<ConnectivityResult>> _connectivitySubscription;
  List<String> _activePlugins = [];

  final List<Map<String, dynamic>> _sampleResults = [
    {'subject': 'Mathematics', 'subject_code': 'MTH', 'ca1': 16, 'ca2': 18, 'exam': 54, 'total': 88, 'grade': 'A'},
    {'subject': 'English Language', 'subject_code': 'ENG', 'ca1': 14, 'ca2': 15, 'exam': 48, 'total': 77, 'grade': 'B'},
    {'subject': 'Basic Science', 'subject_code': 'SCI', 'ca1': 17, 'ca2': 16, 'exam': 50, 'total': 83, 'grade': 'A'},
    {'subject': 'History', 'subject_code': 'HIS', 'ca1': 12, 'ca2': 14, 'exam': 42, 'total': 68, 'grade': 'C'},
  ];

  List<Map<String, dynamic>> _teacherClasses = [];
  bool _isLoadingClasses = false;
  List<Map<String, dynamic>> _studentResults = [];
  bool _isLoadingResults = false;
  bool _isResultsPublished = false;
  List<dynamic> _announcements = [];
  bool _isLoadingAnnouncements = false;

  @override
  void initState() {
    super.initState();
    _loadRole();
    _checkConnectivity();
    _fetchActivePlugins();
    _loadAnnouncements();
    
    // Wire up automatic queue background processor status changes
    SyncQueueProcessor.instance.onStatusChanged = (status) {
      if (mounted) {
        setState(() {
          _syncStatus = status;
        });
      }
    };
    SyncQueueProcessor.instance.startListening();
  }

  Future<void> _loadAnnouncements() async {
    if (!mounted) return;
    setState(() {
      _isLoadingAnnouncements = true;
    });
    try {
      final response = await apiClient.dio.get('/announcements');
      if (response.statusCode == 200 && response.data != null) {
        final list = List<dynamic>.from(response.data['data'] ?? []);
        if (mounted) {
          setState(() {
            _announcements = list;
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading announcements: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoadingAnnouncements = false;
        });
      }
    }
  }

  Future<void> _fetchActivePlugins() async {
    try {
      final response = await apiClient.dio.get('/term');
      if (response.statusCode == 200 && response.data != null) {
        final plugins = List<String>.from(response.data['active_plugins'] ?? []);
        if (mounted) {
          setState(() {
            _activePlugins = plugins;
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading active plugins: $e');
    }
  }

  String _userName = 'User';

  Future<void> _loadRole() async {
    final role = await SecureStorage.instance.getRole();
    final name = await SecureStorage.instance.getUserName();
    setState(() {
      _userRole = role ?? 'student';
      _userName = name ?? 'User';
    });
    if (_userRole == 'teacher') {
      _loadTeacherClasses();
    } else if (_userRole == 'student') {
      _loadStudentResults();
    }
  }

  Future<void> _loadStudentResults() async {
    if (!mounted) return;
    setState(() {
      _isLoadingResults = true;
    });
    try {
      final response = await apiClient.dio.get('/student/results');
      if (response.statusCode == 200 && response.data != null) {
        final list = List<Map<String, dynamic>>.from(response.data['results'] ?? []);
        final published = response.data['is_published'] as bool? ?? false;
        if (mounted) {
          setState(() {
            _studentResults = list;
            _isResultsPublished = published;
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading student results: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoadingResults = false;
        });
      }
    }
  }

  Future<void> _loadTeacherClasses() async {
    if (!mounted) return;
    setState(() {
      _isLoadingClasses = true;
    });
    try {
      final response = await apiClient.dio.get('/teacher/classes');
      if (response.statusCode == 200 && response.data != null) {
        final list = List<Map<String, dynamic>>.from(response.data['data'] ?? []);
        if (mounted) {
          setState(() {
            _teacherClasses = list;
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading teacher classes: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoadingClasses = false;
        });
      }
    }
  }

  void _checkConnectivity() {
    _connectivitySubscription = Connectivity().onConnectivityChanged.listen((results) {
      final isOnline = !results.contains(ConnectivityResult.none);
      setState(() {
        _isOnline = isOnline;
      });
    });
  }

  @override
  void dispose() {
    _connectivitySubscription.cancel();
    SyncQueueProcessor.instance.stopListening();
    super.dispose();
  }

  List<BottomNavigationBarItem> _getNavItems() {
    switch (_userRole) {
      case 'parent':
        final list = <BottomNavigationBarItem>[
          const BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
          const BottomNavigationBarItem(icon: Icon(Icons.child_care), label: 'Children'),
        ];
        if (_activePlugins.contains('messages')) {
          list.add(const BottomNavigationBarItem(icon: Icon(Icons.message), label: 'Chat'));
        }
        list.add(const BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Me'));
        return list;
      case 'teacher':
        return const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
          BottomNavigationBarItem(icon: Icon(Icons.check_circle), label: 'Attendance'),
          BottomNavigationBarItem(icon: Icon(Icons.edit_note), label: 'Scores'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Me'),
        ];
      case 'admin':
        return const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
          BottomNavigationBarItem(icon: Icon(Icons.people), label: 'Students'),
          BottomNavigationBarItem(icon: Icon(Icons.campaign), label: 'Announce'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Me'),
        ];
      case 'student':
      default:
        return const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
          BottomNavigationBarItem(icon: Icon(Icons.article), label: 'Results'),
          BottomNavigationBarItem(icon: Icon(Icons.book), label: 'Homework'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Me'),
        ];
    }
  }

  Widget _getSyncDot() {
    Color color;
    Widget child = const SizedBox.shrink();

    if (!_isOnline) {
      color = AppColors.dangerRed;
    } else {
      switch (_syncStatus) {
        case 'syncing':
          color = AppColors.accentAmber;
          // Animated pulsing dot
          return _PulsingSyncDot(color: color);
        case 'pending':
          color = AppColors.dangerRed;
          break;
        case 'synced':
        default:
          color = AppColors.successGreen;
          break;
      }
    }

    return Container(
      width: 10,
      height: 10,
      decoration: BoxDecoration(
        color: color,
        shape: BoxShape.circle,
      ),
    );
  }

  void _showSyncStatusSheet() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.divider,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Sync Status',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Icon(
                    _isOnline ? Icons.sync : Icons.cloud_off,
                    color: _isOnline ? AppColors.successGreen : AppColors.dangerRed,
                    size: 24,
                  ),
                  const SizedBox(width: 12),
                  Text(
                    _isOnline ? 'Everything is up to date' : 'Offline — Changes saved locally',
                    style: const TextStyle(fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              const Text(
                'Last synced: just now',
                style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  setState(() {
                    _syncStatus = 'syncing';
                  });
                  Future.delayed(const Duration(seconds: 2), () {
                    if (mounted) {
                      setState(() {
                        _syncStatus = 'synced';
                      });
                    }
                  });
                },
                child: const Text('Sync Now'),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildHomeView() {
    // Dynamic greeting gradient based on role
    LinearGradient headerGradient;
    switch (_userRole) {
      case 'parent':
        headerGradient = const LinearGradient(colors: [AppColors.primaryBlue, AppColors.parentEnd]);
        break;
      case 'teacher':
        headerGradient = const LinearGradient(colors: [AppColors.primaryBlue, AppColors.teacherEnd]);
        break;
      case 'admin':
        headerGradient = const LinearGradient(colors: [AppColors.primaryBlue, AppColors.adminEnd]);
        break;
      case 'student':
      default:
        headerGradient = const LinearGradient(colors: [AppColors.primaryBlue, AppColors.studentEnd]);
        break;
    }

    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Greeting Header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 32),
            decoration: BoxDecoration(
              gradient: headerGradient,
              borderRadius: const BorderRadius.only(
                bottomLeft: Radius.circular(24),
                bottomRight: Radius.circular(24),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Good morning 👋',
                  style: TextStyle(color: Colors.white, fontSize: 14),
                ),
                const SizedBox(height: 4),
                Text(
                  'Hello, $_userName',
                  style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Term 2 · 2024/2025',
                  style: TextStyle(color: Colors.white70, fontSize: 13),
                ),
              ],
            ),
          ),
          
          // Cards Listing Area
          Padding(
            padding: const EdgeInsets.all(20.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Quick Announcements',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                ),
                const SizedBox(height: 12),
                if (_isLoadingAnnouncements)
                  const Center(child: LinearProgressIndicator())
                else if (_announcements.isEmpty)
                  const Card(
                    child: Padding(
                      padding: EdgeInsets.all(16.0),
                      child: Center(
                        child: Text(
                          'No active announcements posted.',
                          style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                        ),
                      ),
                    ),
                  )
                else
                  ..._announcements.map((ann) {
                    final title = ann['title'] ?? '';
                    final body = ann['body'] ?? '';
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '📢 $title',
                              style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary, fontSize: 14),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              body,
                              style: const TextStyle(color: AppColors.textSecondary, fontSize: 13, height: 1.4),
                            ),
                          ],
                        ),
                      ),
                    );
                  }),
                const SizedBox(height: 12),
                const TimetableView(),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMeView() {
    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Center(
            child: CircleAvatar(
              radius: 40,
              backgroundColor: AppColors.primaryBlue,
              child: Icon(Icons.person, size: 48, color: Colors.white),
            ),
          ),
          const SizedBox(height: 16),
          Center(
            child: Text(
              _userName,
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
          ),
          const SizedBox(height: 32),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.notifications),
                  title: const Text('Notifications'),
                  trailing: const Text('On', style: TextStyle(color: AppColors.textSecondary)),
                  onTap: () {},
                ),
                const Divider(),
                ListTile(
                  leading: const Icon(Icons.exit_to_app, color: AppColors.dangerRed),
                  title: const Text('Sign Out', style: TextStyle(color: AppColors.dangerRed)),
                  onTap: () async {
                    await SecureStorage.instance.clearAll();
                    if (mounted) {
                      context.go('/');
                    }
                  },
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildResultsView() {
    if (_isLoadingResults) {
      return const Center(child: CircularProgressIndicator());
    }

    if (!_isResultsPublished) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.lock_outline, size: 64, color: AppColors.textDisabled),
              SizedBox(height: 16),
              Text(
                'Results Not Published',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              SizedBox(height: 8),
              Text(
                'Report cards for the active term are not published yet. Please check back later or contact your school administrator.',
                style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      );
    }

    if (_studentResults.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24.0),
          child: Text(
            'No academic results found for the active term.',
            style: TextStyle(color: AppColors.textSecondary, fontSize: 15),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          ResultsChart(subjectResults: _studentResults),
          const SizedBox(height: 16),
          const Text(
            'Subject Breakdown',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
          ),
          const SizedBox(height: 12),
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _studentResults.length,
            itemBuilder: (context, idx) {
              final res = _studentResults[idx];
              final total = int.tryParse(res['total']?.toString() ?? '0') ?? 0;
              Color gradeColor;
              if (total >= 80) {
                gradeColor = AppColors.successGreen;
              } else if (total >= 70) {
                gradeColor = AppColors.softBlue;
              } else if (total >= 50) {
                gradeColor = AppColors.accentAmber;
              } else {
                gradeColor = AppColors.dangerRed;
              }

              return Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            res['subject_name'] ?? 'General',
                            style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: gradeColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              res['grade'] ?? 'F',
                              style: TextStyle(fontWeight: FontWeight.bold, color: gradeColor),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('CA1: ${res['ca1'] ?? 0}/20', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                          Text('CA2: ${res['ca2'] ?? 0}/20', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                          Text('Exam: ${res['exam'] ?? 0}/60', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                          Text('Total: $total/100', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                        ],
                      ),
                      const SizedBox(height: 8),
                      LinearProgressIndicator(
                        value: total / 100.0,
                        backgroundColor: AppColors.divider,
                        color: gradeColor,
                        borderRadius: BorderRadius.circular(4),
                        minHeight: 6,
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildTeacherAttendanceView() {
    if (_isLoadingClasses) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_teacherClasses.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24.0),
          child: Text(
            'No classes allocated to you yet.',
            style: TextStyle(color: AppColors.textSecondary, fontSize: 15),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _teacherClasses.length,
      itemBuilder: (context, idx) {
        final cls = _teacherClasses[idx];
        return Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: AppColors.primaryBlue.withOpacity(0.12),
              child: const Icon(Icons.class_, color: AppColors.primaryBlue),
            ),
            title: Text(
              cls['name'] ?? '',
              style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            subtitle: Text(
              'Level: ${cls['level'] ?? ''}',
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
            ),
            trailing: const Icon(Icons.arrow_forward_ios, size: 14),
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => AttendanceScreen(
                    classId: cls['id'],
                    className: cls['name'] ?? '',
                  ),
                ),
              );
            },
          ),
        );
      },
    );
  }

  Future<void> _loadSubjectsAndNavigate(int classId, String className) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator()),
    );
    try {
      final response = await apiClient.dio.get('/teacher/classes/$classId/subjects');
      if (mounted) {
        Navigator.pop(context); // Close loading dialog
      }
      if (response.statusCode == 200 && response.data != null) {
        final subjects = List<Map<String, dynamic>>.from(response.data['data'] ?? []);
        if (subjects.isEmpty) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('No subjects allocated for this class.')),
            );
          }
          return;
        }
        if (subjects.length == 1) {
          final sub = subjects.first;
          if (mounted) {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => ScoresEntryScreen(
                  classId: classId,
                  className: className,
                  subjectId: sub['id'],
                  subjectName: sub['name'] ?? '',
                ),
              ),
            );
          }
        } else {
          // Show subject selection bottom sheet
          if (mounted) {
            showModalBottomSheet(
              context: context,
              shape: const RoundedRectangleBorder(
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              builder: (context) {
                return Padding(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const Text(
                        'Select Subject',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                      const SizedBox(height: 12),
                      ...subjects.map((sub) => ListTile(
                        leading: CircleAvatar(
                          backgroundColor: AppColors.accentAmber.withOpacity(0.12),
                          child: const Icon(Icons.book, color: AppColors.accentAmber),
                        ),
                        title: Text(sub['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 14),
                        onTap: () {
                          Navigator.pop(context); // Close bottom sheet
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => ScoresEntryScreen(
                                classId: classId,
                                className: className,
                                subjectId: sub['id'],
                                subjectName: sub['name'] ?? '',
                              ),
                            ),
                          );
                        },
                      )),
                    ],
                  ),
                );
              },
            );
          }
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context); // Safe dismiss
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to load subjects: $e')),
        );
      }
    }
  }

  Widget _buildTeacherScoresView() {
    if (_isLoadingClasses) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_teacherClasses.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24.0),
          child: Text(
            'No classes allocated to you yet.',
            style: TextStyle(color: AppColors.textSecondary, fontSize: 15),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _teacherClasses.length,
      itemBuilder: (context, idx) {
        final cls = _teacherClasses[idx];
        return Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: AppColors.accentAmber.withOpacity(0.12),
              child: const Icon(Icons.edit_note, color: AppColors.accentAmber),
            ),
            title: Text(
              cls['name'] ?? '',
              style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            subtitle: Text(
              'Level: ${cls['level'] ?? ''}',
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
            ),
            trailing: const Icon(Icons.arrow_forward_ios, size: 14),
            onTap: () => _loadSubjectsAndNavigate(cls['id'], cls['name'] ?? ''),
          ),
        );
      },
    );
  }

  void _showChildResultsModal(String childName) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return DraggableScrollableSheet(
          initialChildSize: 0.8,
          maxChildSize: 0.95,
          minChildSize: 0.5,
          expand: false,
          builder: (context, scrollController) {
            return SingleChildScrollView(
              controller: scrollController,
              padding: const EdgeInsets.all(20.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: AppColors.divider,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    "Academic Results: $childName",
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  ResultsChart(subjectResults: _sampleResults),
                  const SizedBox(height: 24),
                  const Text(
                    'Subject Scores',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                  ),
                  const SizedBox(height: 12),
                  ListView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: _sampleResults.length,
                    itemBuilder: (context, idx) {
                      final res = _sampleResults[idx];
                      final total = res['total'] as int;
                      Color gradeColor;
                      if (total >= 80) gradeColor = AppColors.successGreen;
                      else if (total >= 70) gradeColor = AppColors.softBlue;
                      else if (total >= 50) gradeColor = AppColors.accentAmber;
                      else gradeColor = AppColors.dangerRed;

                      return Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    res['subject'],
                                    style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                  ),
                                  Text(
                                    res['grade'],
                                    style: TextStyle(fontWeight: FontWeight.bold, color: gradeColor),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              LinearProgressIndicator(
                                value: total / 100.0,
                                backgroundColor: AppColors.divider,
                                color: gradeColor,
                                minHeight: 4,
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildParentChildrenView() {
    return ChildrenView(
      onViewResults: () => _showChildResultsModal('David Hassan'),
      onMessageTeacher: () {
        setState(() {
          _currentIndex = 2; // Jump to Chat tab
        });
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final navItems = _getNavItems();
    final isMeTab = _currentIndex == navItems.length - 1;
    final isHomeTab = _currentIndex == 0;
    final isResultsTab = _currentIndex == 1 && _userRole == 'student';
    final isTeacherAttendanceTab = _currentIndex == 1 && _userRole == 'teacher';
    final isTeacherScoresTab = _currentIndex == 2 && _userRole == 'teacher';
    final isHomeworkTab = _currentIndex == 2 && _userRole == 'student';
    final isChildrenTab = _currentIndex == 1 && _userRole == 'parent';
    final isChatTab = _currentIndex == 2 && _userRole == 'parent';

    return Scaffold(
      appBar: AppBar(
        title: Text(
          isHomeTab ? 'AcademyHub' : navItems[_currentIndex].label!,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: AppColors.textPrimary),
        ),
        actions: [
          // Sync dot
          IconButton(
            icon: _getGetSyncDotIcon(),
            onPressed: _showSyncStatusSheet,
          ),
          // Notification bell
          IconButton(
            icon: const Icon(Icons.notifications_none, color: AppColors.textPrimary),
            onPressed: () {},
          ),
        ],
        elevation: 0.5,
        backgroundColor: AppColors.cardSurface,
      ),
      body: Column(
        children: [
          // Offline Amber Alert Banner
          if (!_isOnline)
            Container(
              height: 32,
              color: const Color(0xFFFEF3C7),
              child: const Center(
                child: Text(
                  '📡 Offline — changes saved locally',
                  style: TextStyle(color: Color(0xFF92400E), fontSize: 13, fontWeight: FontWeight.w600),
                ),
              ),
            ),
          Expanded(
            child: _currentIndex == 0
                ? _buildHomeView()
                : isMeTab
                    ? _buildMeView()
                    : isResultsTab
                        ? _buildResultsView()
                        : isTeacherAttendanceTab
                            ? _buildTeacherAttendanceView()
                            : isTeacherScoresTab
                                ? _buildTeacherScoresView()
                                : isHomeworkTab
                                    ? HomeworkView()
                                    : isChildrenTab
                                        ? _buildParentChildrenView()
                                        : isChatTab
                                            ? const ChatView()
                                            : Center(
                                                child: Text(
                                                  '${navItems[_currentIndex].label} module is ready.',
                                                  style: const TextStyle(fontSize: 16, color: AppColors.textSecondary),
                                                ),
                                              ),
          ),
        ],
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
        type: BottomNavigationBarType.fixed,
        selectedItemColor: AppColors.primaryBlue,
        unselectedItemColor: AppColors.textDisabled,
        backgroundColor: AppColors.cardSurface,
        elevation: 12,
        items: navItems,
      ),
    );
  }

  Widget _getGetSyncDotIcon() {
    return Padding(
      padding: const EdgeInsets.all(4.0),
      child: Center(
        child: _getSyncDot(),
      ),
    );
  }
}

class _PulsingSyncDot extends StatefulWidget {
  final Color color;
  const _PulsingSyncDot({required this.color});

  @override
  State<_PulsingSyncDot> createState() => _PulsingSyncDotState();
}

class _PulsingSyncDotState extends State<_PulsingSyncDot> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    )..repeat(reverse: true);
    _animation = Tween<double>(begin: 0.5, end: 1.0).animate(_controller);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ScaleTransition(
      scale: _animation,
      child: Container(
        width: 10,
        height: 10,
        decoration: BoxDecoration(
          color: widget.color,
          shape: BoxShape.circle,
        ),
      ),
    );
  }
}
