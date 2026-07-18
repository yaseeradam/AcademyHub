import 'dart:async';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';
import 'package:academyhub_app/core/network/sync_processor.dart';
import 'package:academyhub_app/features/results/presentation/results_chart.dart';
import 'package:academyhub_app/features/timetable/presentation/timetable_view.dart';

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

  final List<Map<String, dynamic>> _sampleResults = [
    {'subject': 'Mathematics', 'subject_code': 'MTH', 'ca1': 16, 'ca2': 18, 'exam': 54, 'total': 88, 'grade': 'A'},
    {'subject': 'English Language', 'subject_code': 'ENG', 'ca1': 14, 'ca2': 15, 'exam': 48, 'total': 77, 'grade': 'B'},
    {'subject': 'Basic Science', 'subject_code': 'SCI', 'ca1': 17, 'ca2': 16, 'exam': 50, 'total': 83, 'grade': 'A'},
    {'subject': 'History', 'subject_code': 'HIS', 'ca1': 12, 'ca2': 14, 'exam': 42, 'total': 68, 'grade': 'C'},
  ];

  @override
  void initState() {
    super.initState();
    _loadRole();
    _checkConnectivity();
    
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

  Future<void> _loadRole() async {
    final role = await SecureStorage.instance.getRole();
    setState(() {
      _userRole = role ?? 'student';
    });
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
        return const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
          BottomNavigationBarItem(icon: Icon(Icons.child_care), label: 'Children'),
          BottomNavigationBarItem(icon: Icon(Icons.message), label: 'Chat'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Me'),
        ];
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
                  _userRole.toUpperCase(),
                  style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
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
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              '📢 Term Holidays Announcement',
                              style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                            ),
                            Text('2h ago', style: TextStyle(color: AppColors.textSecondary, fontSize: 11)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'The school will be closing early this Friday for the midterm break. All buses will depart at 1:00 PM.',
                          style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),
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
          const Center(
            child: Text(
              'Amina Hassan',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
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
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          ResultsChart(subjectResults: _sampleResults),
          const SizedBox(height: 16),
          const Text(
            'Subject Breakdown',
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
                            res['subject'],
                            style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: gradeColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              res['grade'],
                              style: TextStyle(fontWeight: FontWeight.bold, color: gradeColor),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('CA1: ${res['ca1']}/20', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                          Text('CA2: ${res['ca2']}/20', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                          Text('Exam: ${res['exam']}/60', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                          Text('Total: ${res['total']}/100', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
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

  @override
  Widget build(BuildContext context) {
    final navItems = _getNavItems();
    final isMeTab = _currentIndex == navItems.length - 1;
    final isHomeTab = _currentIndex == 0;
    final isResultsTab = _currentIndex == 1 && (_userRole == 'student' || _userRole == 'parent');

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
