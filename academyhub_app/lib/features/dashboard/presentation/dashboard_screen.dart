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
  String? _schoolName;

  Future<void> _loadRole() async {
    final role = await SecureStorage.instance.getRole();
    final name = await SecureStorage.instance.getUserName();
    final school = await SecureStorage.instance.getSchoolName();
    setState(() {
      _userRole = role ?? 'student';
      _userName = name ?? 'User';
      _schoolName = school;
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
      backgroundColor: const Color(0xFF1E293B),
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
                    color: const Color(0xFF334155),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Sync Status',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
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
                    style: const TextStyle(fontWeight: FontWeight.w600, color: Colors.white),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              const Text(
                'Last synced: just now',
                style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.amberPrimary,
                  foregroundColor: Colors.white,
                ),
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

  Widget _buildQuickActions() {
    List<Map<String, dynamic>> actions = [];
    if (_userRole == 'student') {
      actions = [
        {'label': 'Report Card', 'icon': Icons.assignment_turned_in_rounded, 'tab': 1},
        {'label': 'Homework', 'icon': Icons.menu_book_rounded, 'tab': 2},
      ];
    } else if (_userRole == 'parent') {
      actions = [
        {'label': 'My Children', 'icon': Icons.people_alt_rounded, 'tab': 1},
        {'label': 'Chat Teacher', 'icon': Icons.chat_rounded, 'tab': 2},
      ];
    } else if (_userRole == 'teacher') {
      actions = [
        {'label': 'Record Attendance', 'icon': Icons.how_to_reg_rounded, 'tab': 1},
        {'label': 'Term Scores', 'icon': Icons.grade_rounded, 'tab': 2},
      ];
    }

    if (actions.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'QUICK ACTIONS',
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: AppColors.amberPrimary,
            letterSpacing: 1.2,
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: actions.map((act) {
            return Expanded(
              child: Card(
                color: Colors.white,
                elevation: 2,
                shadowColor: Colors.black.withOpacity(0.04),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: InkWell(
                  onTap: () {
                    setState(() {
                      _currentIndex = act['tab'];
                    });
                  },
                  borderRadius: BorderRadius.circular(16),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 16.0),
                    child: Column(
                      children: [
                        Icon(act['icon'] as IconData, color: AppColors.amberPrimary, size: 28),
                        const SizedBox(height: 8),
                        Text(
                          act['label'] as String,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            );
          }).toList(),
        ),
        const SizedBox(height: 24),
      ],
    );
  }

  Widget _buildOverviewStats() {
    String title1 = '';
    String val1 = '';
    String title2 = '';
    String val2 = '';

    if (_userRole == 'student') {
      title1 = 'Enrolled Subjects';
      val1 = '${_studentResults.isNotEmpty ? _studentResults.length : 8} Courses';
      title2 = 'Academic Status';
      val2 = 'Active Term';
    } else if (_userRole == 'parent') {
      title1 = 'Connected Children';
      val1 = '2 Student Profiles';
      title2 = 'School Status';
      val2 = 'Fully Synced';
    } else if (_userRole == 'teacher') {
      title1 = 'Allocated Classes';
      val1 = '${_teacherClasses.length} Active Classrooms';
      title2 = 'Session Status';
      val2 = 'Term 2 Active';
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'TODAY\'S OVERVIEW',
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: AppColors.amberPrimary,
            letterSpacing: 1.2,
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.04),
                      blurRadius: 4,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title1, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                    const SizedBox(height: 4),
                    Text(val1, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                  ],
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.04),
                      blurRadius: 4,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title2, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                    const SizedBox(height: 4),
                    Text(val2, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                  ],
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 24),
      ],
    );
  }

  Widget _buildHomeView() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Floating Greeting Header Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF1E293B).withOpacity(0.2),
                  blurRadius: 15,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 26,
                  backgroundColor: AppColors.amberPrimary.withOpacity(0.15),
                  child: Text(
                    _userName.isNotEmpty ? _userName.substring(0, 1).toUpperCase() : 'U',
                    style: const TextStyle(color: AppColors.amberPrimary, fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Good morning 👋',
                        style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        _userName,
                        style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${_schoolName ?? "AcademyHub"} · Term 2',
                        style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // Quick Actions
          _buildQuickActions(),

          // Overview Statistics
          _buildOverviewStats(),
          
          // Announcements Section
          const Text(
            'QUICK ANNOUNCEMENTS',
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: AppColors.amberPrimary,
              letterSpacing: 1.2,
            ),
          ),
          const SizedBox(height: 12),
          if (_isLoadingAnnouncements)
            const Center(child: LinearProgressIndicator(color: AppColors.amberPrimary))
          else if (_announcements.isEmpty)
            const Card(
              color: Colors.white,
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
              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: const Border(left: BorderSide(color: AppColors.amberPrimary, width: 3)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.04),
                      blurRadius: 4,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
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
              backgroundColor: AppColors.amberPrimary,
              child: Icon(Icons.person, size: 48, color: Colors.white),
            ),
          ),
          const SizedBox(height: 16),
          Center(
            child: Text(
              _userName,
              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
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
      return const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary));
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
              final grade = (res['grade'] ?? 'F').toString().toUpperCase();
              Color gradeColor;
              if (grade.startsWith('A')) {
                gradeColor = AppColors.successGreen;
              } else if (grade.startsWith('B')) {
                gradeColor = const Color(0xFF3B82F6); // Blue
              } else if (grade.startsWith('C')) {
                gradeColor = AppColors.amberPrimary;
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
                              color: gradeColor.withOpacity(0.12),
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
              backgroundColor: AppColors.amberPrimary.withOpacity(0.12),
              child: const Icon(Icons.class_, color: AppColors.amberPrimary),
            ),
            title: Text(
              cls['name'] ?? '',
              style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            subtitle: Text(
              'Level: ${cls['level'] ?? ''}',
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
            ),
            trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: AppColors.amberPrimary),
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
              backgroundColor: AppColors.amberPrimary.withOpacity(0.12),
              child: const Icon(Icons.edit_note, color: AppColors.amberPrimary),
            ),
            title: Text(
              cls['name'] ?? '',
              style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            subtitle: Text(
              'Level: ${cls['level'] ?? ''}',
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
            ),
            trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: AppColors.amberPrimary),
            onTap: () => _loadSubjectsAndNavigate(cls['id'], cls['name'] ?? ''),
          ),
        );
      },
    );
  }

  void _showChildResultsModal(int studentId, String childName) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator()),
    );
    try {
      final response = await apiClient.dio.get('/students/$studentId/report-card');
      if (mounted) {
        Navigator.pop(context); // Close loading dialog
      }
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data['data'] ?? {};
        final published = data['is_published'] as bool? ?? false;
        final rawSubjects = List<dynamic>.from(data['subjects'] ?? []);
        
        final List<Map<String, dynamic>> subjectsList = rawSubjects.map((s) {
          return {
            'subject_name': s['subject'] ?? 'Subject',
            'ca1': s['ca1'],
            'ca2': s['ca2'],
            'exam': s['exam'],
            'total': s['total'],
            'grade': s['grade'] ?? 'F',
          };
        }).toList();

        if (mounted) {
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
                        if (!published)
                          const Center(
                            child: Padding(
                              padding: EdgeInsets.symmetric(vertical: 40.0),
                              child: Column(
                                children: [
                                  Icon(Icons.lock_outline, size: 48, color: AppColors.textDisabled),
                                  SizedBox(height: 12),
                                  Text(
                                    'Results Not Published',
                                    style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                  ),
                                  SizedBox(height: 6),
                                  Text(
                                    'Report cards for this term are not published yet.',
                                    style: TextStyle(color: AppColors.textSecondary, fontSize: 12),
                                  ),
                                ],
                              ),
                            ),
                          )
                        else if (subjectsList.isEmpty)
                          const Center(
                            child: Padding(
                              padding: EdgeInsets.symmetric(vertical: 40.0),
                              child: Text(
                                'No academic records found for this term.',
                                style: TextStyle(color: AppColors.textSecondary),
                              ),
                            ),
                          )
                        else ...[
                          ResultsChart(subjectResults: subjectsList),
                          const SizedBox(height: 24),
                          const Text(
                            'Subject Scores',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                          ),
                          const SizedBox(height: 12),
                          ListView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: subjectsList.length,
                            itemBuilder: (context, idx) {
                              final res = subjectsList[idx];
                              final total = int.tryParse(res['total']?.toString() ?? '0') ?? 0;
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
                                            res['subject_name'] ?? '',
                                            style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                          ),
                                          Text(
                                            res['grade'] ?? 'F',
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
                      ],
                    ),
                  );
                },
              );
            },
          );
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context); // Safe dismiss
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to load child results: $e')),
        );
      }
    }
  }

  Widget _buildParentChildrenView() {
    return ChildrenView(
      onViewResults: (id, name) => _showChildResultsModal(id, name),
      onMessageTeacher: () {
        setState(() {
          _currentIndex = 2; // Jump to Chat tab
        });
      },
      isMessagingEnabled: _activePlugins.contains('messages'),
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
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: Text(
          isHomeTab ? 'AcademyHub' : navItems[_currentIndex].label!,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: Colors.white),
        ),
        actions: [
          // Sync dot
          IconButton(
            icon: _getGetSyncDotIcon(),
            onPressed: _showSyncStatusSheet,
          ),
          // Notification bell
          IconButton(
            icon: const Icon(Icons.notifications_none, color: Colors.white),
            onPressed: () {},
          ),
        ],
        elevation: 0,
        backgroundColor: const Color(0xFF1E293B),
      ),
      body: Column(
        children: [
          // Offline Amber Alert Banner
          if (!_isOnline)
            Container(
              height: 36,
              color: const Color(0xFFF59E0B),
              child: const Center(
                child: Text(
                  'OFFLINE — CHANGES SAVED LOCALLY',
                  style: TextStyle(color: Color(0xFF0F172A), fontSize: 12, fontWeight: FontWeight.bold, letterSpacing: 0.5),
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
      bottomNavigationBar: SafeArea(
        child: Container(
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          height: 72,
          decoration: BoxDecoration(
            color: const Color(0xFF1E293B),
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.2),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: List.generate(navItems.length, (idx) {
              final item = navItems[idx];
              final isSelected = _currentIndex == idx;
              
              IconData iconData = Icons.home_rounded;
              if (idx == 0) iconData = Icons.home_rounded;
              else if (idx == 1) {
                if (_userRole == 'student') iconData = Icons.assignment_rounded;
                else if (_userRole == 'parent') iconData = Icons.people_alt_rounded;
                else if (_userRole == 'teacher') iconData = Icons.class_rounded;
              } else if (idx == 2) {
                if (_userRole == 'student') iconData = Icons.menu_book_rounded;
                else if (_userRole == 'parent') iconData = Icons.chat_rounded;
                else if (_userRole == 'teacher') iconData = Icons.grade_rounded;
              } else if (idx == navItems.length - 1) {
                iconData = Icons.person_rounded;
              }

              return InkWell(
                onTap: () => setState(() => _currentIndex = idx),
                borderRadius: BorderRadius.circular(24),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: isSelected ? AppColors.amberPrimary.withOpacity(0.15) : Colors.transparent,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        iconData,
                        color: isSelected ? AppColors.amberPrimary : const Color(0xFF94A3B8),
                        size: 24,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        item.label ?? '',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                          color: isSelected ? AppColors.amberPrimary : const Color(0xFF94A3B8),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }),
          ),
        ),
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
