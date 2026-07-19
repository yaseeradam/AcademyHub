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

  // Parent Role state
  List<dynamic> _parentChildren = [];
  int? _activeChildId;
  String _activeChildName = '';
  double _outstandingFees = 45000.0;
  String? _checkoutUrl;

  // Admin Role state
  List<dynamic> _backups = [];
  bool _isLoadingBackups = false;
  String _activeSessionName = '2024/2025';
  String _activeTermName = 'Term 2';

  // Admin Students state
  List<dynamic> _adminStudentsList = [];
  bool _isLoadingAdminStudents = false;
  String _adminStudentsSearchQuery = '';

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
        final termVal = response.data['term']?.toString() ?? '2';
        final sessionVal = response.data['session']?.toString() ?? '2024/2025';
        if (mounted) {
          setState(() {
            _activePlugins = plugins;
            _activeTermName = 'Term $termVal';
            _activeSessionName = sessionVal;
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
    } else if (_userRole == 'parent') {
      _loadParentChildren();
    } else if (_userRole == 'admin') {
      _loadAdminHomeData();
    }
  }

  Future<void> _loadAdminHomeData() async {
    _loadBackups();
    _loadAdminStudents();
  }

  Future<void> _loadAdminStudents() async {
    if (!mounted) return;
    setState(() {
      _isLoadingAdminStudents = true;
    });
    try {
      final response = await apiClient.dio.get('/students');
      if (response.statusCode == 200 && response.data != null) {
        if (mounted) {
          setState(() {
            _adminStudentsList = List<dynamic>.from(response.data['data'] ?? []);
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading admin students: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoadingAdminStudents = false;
        });
      }
    }
  }

  Future<void> _loadBackups() async {
    if (!mounted) return;
    setState(() {
      _isLoadingBackups = true;
    });
    try {
      final response = await apiClient.dio.get('/admin/backups');
      if (response.statusCode == 200 && response.data != null) {
        if (mounted) {
          setState(() {
            _backups = List<dynamic>.from(response.data);
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading backups: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoadingBackups = false;
        });
      }
    }
  }

  Future<void> _triggerBackup() async {
    if (!mounted) return;
    setState(() {
      _isLoadingBackups = true;
    });
    try {
      final response = await apiClient.dio.post('/admin/backups');
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('✓ New database backup triggered successfully.'),
              backgroundColor: AppColors.successGreen,
            ),
          );
        }
        await _loadBackups();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to trigger database backup: $e'),
            backgroundColor: AppColors.dangerRed,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoadingBackups = false;
        });
      }
    }
  }

  Future<void> _loadParentChildren() async {
    try {
      final response = await apiClient.dio.get('/students');
      if (response.statusCode == 200 && response.data != null) {
        final list = List<dynamic>.from(response.data['data'] ?? []);
        if (mounted) {
          setState(() {
            _parentChildren = list;
            if (list.isNotEmpty) {
              _activeChildId = list.first['id'];
              _activeChildName = '${list.first['first_name']} ${list.first['last_name']}';
            }
          });
          if (_activeChildId != null) {
            _fetchParentBillingDetails(_activeChildId!);
          }
        }
      }
    } catch (e) {
      debugPrint('Error loading parent children: $e');
    }
  }

  Future<void> _fetchParentBillingDetails(int studentId) async {
    try {
      final response = await apiClient.dio.get(
        '/billing/checkout-url',
        queryParameters: {'student_id': studentId},
      );
      if (response.statusCode == 200 && response.data != null) {
        if (mounted) {
          setState(() {
            _outstandingFees = double.tryParse(response.data['outstanding_balance']?.toString() ?? '') ?? 0.0;
            _checkoutUrl = response.data['checkout_url'];
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading child billing info: $e');
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

  List<Color> get _roleGradient {
    switch (_userRole) {
      case 'teacher': return [const Color(0xFF0F766E), const Color(0xFF134E4A)];
      case 'parent':  return [const Color(0xFF6B4FA0), const Color(0xFF4C1D95)];
      case 'admin':   return [const Color(0xFF92400E), const Color(0xFF78350F)];
      default:        return [const Color(0xFFB45309), const Color(0xFF92400E)];
    }
  }

  String get _roleLabel {
    switch (_userRole) {
      case 'teacher': return 'Teacher';
      case 'parent':  return 'Parent';
      case 'admin':   return 'Administrator';
      default:        return 'Student';
    }
  }

  String get _greeting {
    final h = DateTime.now().hour;
    if (h < 12) return 'Good morning';
    if (h < 17) return 'Good afternoon';
    return 'Good evening';
  }

  Widget _buildRoleHeader() {
    final initials = _userName.trim().split(' ')
        .where((w) => w.isNotEmpty).map((w) => w[0].toUpperCase()).take(2).join();
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 16, 16, 0),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(colors: _roleGradient, begin: Alignment.topLeft, end: Alignment.bottomRight),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [BoxShadow(color: _roleGradient.first.withOpacity(0.35), blurRadius: 18, offset: const Offset(0, 8))],
      ),
      child: Row(
        children: [
          Container(
            width: 54, height: 54,
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              shape: BoxShape.circle,
              border: Border.all(color: Colors.white.withOpacity(0.4), width: 2),
            ),
            child: Center(
              child: Text(initials.isEmpty ? 'U' : initials,
                  style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('$_greeting 👋', style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 12)),
                const SizedBox(height: 2),
                Text(_userName,
                    style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                    maxLines: 1, overflow: TextOverflow.ellipsis),
                const SizedBox(height: 6),
                Wrap(
                  spacing: 6,
                  children: [
                    _headerBadge(_roleLabel, Icons.verified_user_outlined),
                    _headerBadge('$_activeSessionName · $_activeTermName', Icons.calendar_today_outlined),
                  ],
                ),
              ],
            ),
          ),
          GestureDetector(
            onTap: _showSyncStatusSheet,
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(color: Colors.white.withOpacity(0.15), shape: BoxShape.circle),
              child: _getSyncDot(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _headerBadge(String label, IconData icon) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: Colors.white.withOpacity(0.18), borderRadius: BorderRadius.circular(20)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 10, color: Colors.white),
          const SizedBox(width: 4),
          Text(label, style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w600)),
        ],
      ),
    );
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
    final actions = _getQuickActions();
    if (actions.isEmpty) return const SizedBox.shrink();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('QUICK ACTIONS',
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.amberPrimary, letterSpacing: 1.2)),
        const SizedBox(height: 12),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: actions.length,
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 4,
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            childAspectRatio: 0.82,
          ),
          itemBuilder: (context, i) {
            final act = actions[i];
            final color = act['color'] as Color;
            return GestureDetector(
              onTap: () {
                if (act.containsKey('route')) {
                  context.push(act['route'] as String);
                } else if (act.containsKey('tab')) {
                  setState(() => _currentIndex = act['tab'] as int);
                }
              },
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 52, height: 52,
                    decoration: BoxDecoration(
                      color: color.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: color.withValues(alpha: 0.2)),
                    ),
                    child: Icon(act['icon'] as IconData, color: color, size: 24),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    act['label'] as String,
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            );
          },
        ),
        const SizedBox(height: 24),
      ],
    );
  }

  List<Map<String, dynamic>> _getQuickActions() {
    switch (_userRole) {
      case 'student':
        return [
          {'label': 'Results', 'icon': Icons.assignment_turned_in_rounded, 'color': const Color(0xFF3B82F6), 'tab': 1},
          {'label': 'Homework', 'icon': Icons.menu_book_rounded, 'color': const Color(0xFF7C3AED), 'tab': 2},
          {'label': 'CBT Exam', 'icon': Icons.quiz_rounded, 'color': const Color(0xFFF59E0B), 'route': '/cbt-exam'},
          {'label': 'Timetable', 'icon': Icons.table_chart_rounded, 'color': const Color(0xFF10B981), 'tab': 0},
          {'label': 'Attendance', 'icon': Icons.fact_check_rounded, 'color': const Color(0xFF0F766E), 'tab': 0},
          {'label': 'Messages', 'icon': Icons.chat_bubble_rounded, 'color': const Color(0xFFEC4899), 'tab': 0},
          {'label': 'Events', 'icon': Icons.event_rounded, 'color': const Color(0xFFF97316), 'tab': 0},
          {'label': 'Profile', 'icon': Icons.person_rounded, 'color': const Color(0xFF64748B), 'tab': 3},
        ];
      case 'teacher':
        return [
          {'label': 'Attendance', 'icon': Icons.how_to_reg_rounded, 'color': const Color(0xFF3B82F6), 'tab': 1},
          {'label': 'Scores', 'icon': Icons.grade_rounded, 'color': const Color(0xFFF59E0B), 'tab': 2},
          {'label': 'Homework', 'icon': Icons.assignment_rounded, 'color': const Color(0xFF7C3AED), 'tab': 0},
          {'label': 'Timetable', 'icon': Icons.table_chart_rounded, 'color': const Color(0xFF10B981), 'tab': 0},
          {'label': 'Class Notes', 'icon': Icons.menu_book_rounded, 'color': const Color(0xFF0F766E), 'tab': 0},
          {'label': 'Messages', 'icon': Icons.chat_bubble_rounded, 'color': const Color(0xFFEC4899), 'tab': 0},
          {'label': 'CBT', 'icon': Icons.quiz_rounded, 'color': const Color(0xFFF97316), 'tab': 0},
          {'label': 'Profile', 'icon': Icons.person_rounded, 'color': const Color(0xFF64748B), 'tab': 3},
        ];
      case 'parent':
        return [
          {'label': 'Children', 'icon': Icons.people_alt_rounded, 'color': const Color(0xFF6B4FA0), 'tab': 1},
          {'label': 'Pay Fees', 'icon': Icons.payment_rounded, 'color': const Color(0xFF10B981), 'route': '/fee-payments'},
          {'label': 'Chat', 'icon': Icons.chat_bubble_rounded, 'color': const Color(0xFFEC4899), 'tab': 2},
          {'label': 'Results', 'icon': Icons.assignment_turned_in_rounded, 'color': const Color(0xFF3B82F6), 'tab': 1},
          {'label': 'Attendance', 'icon': Icons.fact_check_rounded, 'color': const Color(0xFF0F766E), 'tab': 1},
          {'label': 'Homework', 'icon': Icons.menu_book_rounded, 'color': const Color(0xFF7C3AED), 'tab': 1},
          {'label': 'Events', 'icon': Icons.event_rounded, 'color': const Color(0xFFF97316), 'tab': 0},
          {'label': 'Profile', 'icon': Icons.person_rounded, 'color': const Color(0xFF64748B), 'tab': 3},
        ];
      case 'admin':
        return [
          {'label': 'Students', 'icon': Icons.people_rounded, 'color': const Color(0xFF3B82F6), 'tab': 1},
          {'label': 'Announce', 'icon': Icons.campaign_rounded, 'color': const Color(0xFF7C3AED), 'tab': 2},
          {'label': 'Backup', 'icon': Icons.storage_rounded, 'color': const Color(0xFF10B981), 'tab': 0},
          {'label': 'Timetable', 'icon': Icons.table_chart_rounded, 'color': const Color(0xFF0F766E), 'tab': 0},
          {'label': 'Results', 'icon': Icons.assignment_turned_in_rounded, 'color': const Color(0xFFF59E0B), 'tab': 0},
          {'label': 'Attendance', 'icon': Icons.fact_check_rounded, 'color': const Color(0xFFF97316), 'tab': 0},
          {'label': 'Messages', 'icon': Icons.chat_bubble_rounded, 'color': const Color(0xFFEC4899), 'tab': 0},
          {'label': 'Profile', 'icon': Icons.person_rounded, 'color': const Color(0xFF64748B), 'tab': 3},
        ];
      default:
        return [];
    }
  }

  Widget _buildOverviewStats() {
    final List<_StatCard> cards = _buildStatCards();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'TODAY\'S OVERVIEW',
          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.amberPrimary, letterSpacing: 1.2),
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 100,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: cards.length,
            separatorBuilder: (_, __) => const SizedBox(width: 12),
            itemBuilder: (context, i) => _buildStatCardWidget(cards[i]),
          ),
        ),
        const SizedBox(height: 24),
      ],
    );
  }

  List<_StatCard> _buildStatCards() {
    switch (_userRole) {
      case 'student':
        return [
          _StatCard('Subjects', '${_studentResults.isNotEmpty ? _studentResults.length : 8}', Icons.menu_book_rounded, const Color(0xFF3B82F6)),
          _StatCard('Term', _activeTermName, Icons.calendar_month_rounded, AppColors.amberPrimary),
          _StatCard('Session', _activeSessionName, Icons.school_rounded, const Color(0xFF10B981)),
          _StatCard('Status', 'Active', Icons.verified_rounded, const Color(0xFF7C3AED)),
        ];
      case 'teacher':
        return [
          _StatCard('Classes', '${_teacherClasses.length}', Icons.class_rounded, const Color(0xFF0F766E)),
          _StatCard('Term', _activeTermName, Icons.calendar_month_rounded, AppColors.amberPrimary),
          _StatCard('Session', _activeSessionName, Icons.school_rounded, const Color(0xFF3B82F6)),
          _StatCard('Status', 'Active', Icons.verified_rounded, const Color(0xFF10B981)),
        ];
      case 'parent':
        return [
          _StatCard('Children', '${_parentChildren.length}', Icons.people_alt_rounded, const Color(0xFF6B4FA0)),
          _StatCard('Balance', '₦${_outstandingFees.toStringAsFixed(0)}', Icons.account_balance_wallet_rounded, const Color(0xFFF43F5E)),
          _StatCard('Term', _activeTermName, Icons.calendar_month_rounded, AppColors.amberPrimary),
          _StatCard('Sync', _isOnline ? 'Online' : 'Offline', Icons.cloud_done_rounded, const Color(0xFF10B981)),
        ];
      case 'admin':
        return [
          _StatCard('Students', '${_adminStudentsList.length}', Icons.people_rounded, const Color(0xFF7C3AED)),
          _StatCard('Session', _activeSessionName, Icons.school_rounded, const Color(0xFF92400E)),
          _StatCard('Term', _activeTermName, Icons.calendar_month_rounded, AppColors.amberPrimary),
          _StatCard('Backups', '${_backups.length}', Icons.storage_rounded, const Color(0xFF3B82F6)),
        ];
      default:
        return [];
    }
  }

  Widget _buildStatCardWidget(_StatCard card) {
    return Container(
      width: 110,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(color: card.color.withValues(alpha: 0.12), blurRadius: 10, offset: const Offset(0, 4)),
        ],
        border: Border.all(color: card.color.withValues(alpha: 0.15)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Container(
            width: 32, height: 32,
            decoration: BoxDecoration(
              color: card.color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(card.icon, color: card.color, size: 18),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(card.value,
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: card.color),
                  maxLines: 1, overflow: TextOverflow.ellipsis),
              Text(card.label,
                  style: const TextStyle(fontSize: 10, color: AppColors.textSecondary),
                  maxLines: 1, overflow: TextOverflow.ellipsis),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTeacherHomeView() {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _buildRoleHeader(),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _buildOverviewStats(),
                _buildQuickActions(),
                // My Classes
                const Text('MY CLASSES', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.amberPrimary, letterSpacing: 1.2)),
                const SizedBox(height: 12),
                if (_isLoadingClasses)
                  const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary))
                else if (_teacherClasses.isEmpty)
                  _emptyCard('No classes allocated yet.')
                else
                  ...(_teacherClasses.take(3).map((cls) => _buildClassCard(cls))),
                const SizedBox(height: 24),
                // Announcements
                _buildAnnouncementsSection(),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildClassCard(Map<String, dynamic> cls) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFF0F766E).withValues(alpha: 0.15)),
        boxShadow: [BoxShadow(color: const Color(0xFF0F766E).withValues(alpha: 0.06), blurRadius: 8, offset: const Offset(0, 3))],
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        leading: Container(
          width: 42, height: 42,
          decoration: BoxDecoration(
            color: const Color(0xFF0F766E).withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Icon(Icons.class_rounded, color: Color(0xFF0F766E), size: 20),
        ),
        title: Text(cls['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary)),
        subtitle: Text('Level ${cls['level'] ?? ''} · Tap to take attendance', style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
        trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: AppColors.textSecondary),
        onTap: () => Navigator.push(context, MaterialPageRoute(
          builder: (_) => AttendanceScreen(classId: cls['id'], className: cls['name'] ?? ''),
        )),
      ),
    );
  }

  Widget _buildParentHomeView() {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _buildRoleHeader(),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _buildOverviewStats(),
                // Child switcher
                if (_parentChildren.isNotEmpty) ...[
                  const Text('SELECT CHILD', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.amberPrimary, letterSpacing: 1.2)),
                  const SizedBox(height: 10),
                  SizedBox(
                    height: 80,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      itemCount: _parentChildren.length,
                      itemBuilder: (context, i) {
                        final child = _parentChildren[i];
                        final cId = child['id'] as int;
                        final name = '${child['first_name'] ?? ''}';
                        final isSelected = _activeChildId == cId;
                        return GestureDetector(
                          onTap: () {
                            setState(() {
                              _activeChildId = cId;
                              _activeChildName = '${child['first_name']} ${child['last_name']}';
                            });
                            _fetchParentBillingDetails(cId);
                          },
                          child: Container(
                            margin: const EdgeInsets.only(right: 12),
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            decoration: BoxDecoration(
                              color: isSelected ? const Color(0xFF6B4FA0) : Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: isSelected ? const Color(0xFF6B4FA0) : AppColors.divider),
                              boxShadow: isSelected ? [const BoxShadow(color: Color(0x336B4FA0), blurRadius: 8, offset: Offset(0, 3))] : [],
                            ),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(name.isNotEmpty ? name[0].toUpperCase() : '?',
                                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: isSelected ? Colors.white : AppColors.textPrimary)),
                                const SizedBox(height: 4),
                                Text(name, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: isSelected ? Colors.white70 : AppColors.textSecondary)),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
                // Fee card
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(colors: [Color(0xFF10B981), Color(0xFF059669)], begin: Alignment.topLeft, end: Alignment.bottomRight),
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [BoxShadow(color: const Color(0xFF10B981).withValues(alpha: 0.3), blurRadius: 12, offset: const Offset(0, 6))],
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Outstanding Balance', style: TextStyle(color: Colors.white70, fontSize: 12)),
                            const SizedBox(height: 4),
                            Text('₦${_outstandingFees.toStringAsFixed(0)}',
                                style: const TextStyle(color: Colors.white, fontSize: 26, fontWeight: FontWeight.bold)),
                            const SizedBox(height: 4),
                            Text(_activeChildName.isNotEmpty ? _activeChildName : 'Select a child',
                                style: const TextStyle(color: Colors.white70, fontSize: 11)),
                          ],
                        ),
                      ),
                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: const Color(0xFF059669),
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        ),
                        onPressed: () => context.push('/fee-payments'),
                        child: const Text('PAY NOW', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),
                _buildQuickActions(),
                // Academic tracker
                const Text('ACADEMIC TRACKER', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.amberPrimary, letterSpacing: 1.2)),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(child: _buildTrackerTile('Report Card', Icons.assignment_turned_in_rounded, const Color(0xFF3B82F6), () {
                      if (_activeChildId != null) _showChildResultsModal(_activeChildId!, _activeChildName);
                    })),
                    const SizedBox(width: 10),
                    Expanded(child: _buildTrackerTile('Attendance', Icons.fact_check_rounded, const Color(0xFF10B981), () {})),
                    const SizedBox(width: 10),
                    Expanded(child: _buildTrackerTile('Homework', Icons.menu_book_rounded, const Color(0xFF7C3AED), () {})),
                  ],
                ),
                const SizedBox(height: 24),
                _buildAnnouncementsSection(),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTrackerTile(String label, IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: color.withValues(alpha: 0.2)),
          boxShadow: [BoxShadow(color: color.withValues(alpha: 0.08), blurRadius: 8, offset: const Offset(0, 3))],
        ),
        child: Column(
          children: [
            Container(
              width: 36, height: 36,
              decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(10)),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(height: 8),
            Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textPrimary), textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }

  Widget _buildStudentHomeView() {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _buildRoleHeader(),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _buildOverviewStats(),
                _buildQuickActions(),
                // Results preview
                if (_studentResults.isNotEmpty) ...[
                  const Text('LATEST RESULTS', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.amberPrimary, letterSpacing: 1.2)),
                  const SizedBox(height: 12),
                  ...(_studentResults.take(3).map((res) {
                    final total = int.tryParse(res['total']?.toString() ?? '0') ?? 0;
                    final grade = (res['grade'] ?? 'F').toString();
                    Color gc;
                    if (total >= 80) gc = AppColors.successGreen;
                    else if (total >= 70) gc = const Color(0xFF3B82F6);
                    else if (total >= 50) gc = AppColors.amberPrimary;
                    else gc = AppColors.dangerRed;
                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: gc.withValues(alpha: 0.2)),
                      ),
                      child: Row(
                        children: [
                          Expanded(child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(res['subject_name'] ?? 'Subject', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              const SizedBox(height: 4),
                              LinearProgressIndicator(value: total / 100, color: gc, backgroundColor: AppColors.divider,
                                  borderRadius: BorderRadius.circular(4), minHeight: 4),
                            ],
                          )),
                          const SizedBox(width: 12),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(color: gc.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(20)),
                            child: Text(grade, style: TextStyle(color: gc, fontWeight: FontWeight.bold, fontSize: 13)),
                          ),
                        ],
                      ),
                    );
                  })),
                  const SizedBox(height: 24),
                ],
                _buildAnnouncementsSection(),
                const SizedBox(height: 16),
                const Text('TIMETABLE', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.amberPrimary, letterSpacing: 1.2)),
                const SizedBox(height: 12),
                const TimetableView(),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAnnouncementsSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('ANNOUNCEMENTS', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.amberPrimary, letterSpacing: 1.2)),
        const SizedBox(height: 12),
        if (_isLoadingAnnouncements)
          const Center(child: LinearProgressIndicator(color: AppColors.amberPrimary))
        else if (_announcements.isEmpty)
          _emptyCard('No active announcements posted.')
        else
          ...(_announcements.take(3).map((ann) {
            final title = ann['title'] ?? '';
            final body = ann['body'] ?? '';
            return Container(
              margin: const EdgeInsets.only(bottom: 10),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: const Border(left: BorderSide(color: AppColors.amberPrimary, width: 3)),
                boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 6, offset: const Offset(0, 2))],
              ),
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('📢 $title', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary)),
                    const SizedBox(height: 6),
                    Text(body, style: const TextStyle(color: AppColors.textSecondary, fontSize: 12, height: 1.4), maxLines: 2, overflow: TextOverflow.ellipsis),
                  ],
                ),
              ),
            );
          })),
      ],
    );
  }

  Widget _emptyCard(String message) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.divider),
      ),
      child: Center(child: Text(message, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13))),
    );
  }

  Widget _buildHomeView() {
    if (_userRole == 'teacher') {
      return _buildTeacherHomeView();
    } else if (_userRole == 'parent') {
      return _buildParentHomeView();
    } else if (_userRole == 'admin') {
      return _buildAdminHomeView();
    } else {
      return _buildStudentHomeView();
    }
  }

  Widget _buildAdminHomeView() {
    const violetAccent = Color(0xFF7C3AED);
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _buildRoleHeader(),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _buildOverviewStats(),
                _buildQuickActions(),
                // Backups
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('DATABASE BACKUPS', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.amberPrimary, letterSpacing: 1.2)),
                    TextButton.icon(
                      style: TextButton.styleFrom(foregroundColor: violetAccent, padding: EdgeInsets.zero),
                      onPressed: _isLoadingBackups ? null : _triggerBackup,
                      icon: const Icon(Icons.backup_rounded, size: 16),
                      label: const Text('Backup Now', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: violetAccent.withValues(alpha: 0.15)),
                  ),
                  child: _isLoadingBackups
                      ? const Padding(padding: EdgeInsets.all(20), child: Center(child: CircularProgressIndicator(color: AppColors.amberPrimary)))
                      : _backups.isEmpty
                          ? const Padding(
                              padding: EdgeInsets.all(20),
                              child: Center(child: Text('No backups found.', style: TextStyle(color: AppColors.textSecondary))),
                            )
                          : ListView.separated(
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                              itemCount: _backups.length > 4 ? 4 : _backups.length,
                              separatorBuilder: (_, __) => const Divider(height: 1),
                              itemBuilder: (context, idx) {
                                final b = _backups[idx];
                                final kb = ((b['size'] ?? 0) / 1024).toStringAsFixed(1);
                                return ListTile(
                                  leading: Container(
                                    width: 36, height: 36,
                                    decoration: BoxDecoration(color: violetAccent.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                                    child: const Icon(Icons.storage_rounded, color: violetAccent, size: 18),
                                  ),
                                  title: Text(b['filename'] ?? 'backup', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold), maxLines: 1, overflow: TextOverflow.ellipsis),
                                  subtitle: Text('$kb KB · ${b['created'] ?? ''}', style: const TextStyle(fontSize: 10, color: AppColors.textSecondary)),
                                  trailing: IconButton(
                                    icon: const Icon(Icons.download_rounded, size: 18, color: violetAccent),
                                    onPressed: () => ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(content: Text('Downloading ${b['filename']}'), backgroundColor: violetAccent)),
                                  ),
                                );
                              },
                            ),
                ),
                const SizedBox(height: 24),
                // Active plugins
                const Text('ACTIVE PLUGINS', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.amberPrimary, letterSpacing: 1.2)),
                const SizedBox(height: 10),
                _activePlugins.isEmpty
                    ? _emptyCard('No active marketplace plugins.')
                    : Wrap(
                        spacing: 8, runSpacing: 8,
                        children: _activePlugins.map((p) => Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: violetAccent.withValues(alpha: 0.08),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: violetAccent.withValues(alpha: 0.2)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.extension_rounded, size: 13, color: violetAccent),
                              const SizedBox(width: 5),
                              Text(p.toUpperCase(), style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: violetAccent)),
                            ],
                          ),
                        )).toList(),
                      ),
                const SizedBox(height: 24),
                _buildAnnouncementsSection(),
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
                  leading: const Icon(Icons.settings),
                  title: const Text('Settings & Offline Sync'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 14),
                  onTap: () {
                    context.push('/settings');
                  },
                ),
                const Divider(),
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

  Widget _buildAdminStudentsView() {
    var filtered = _adminStudentsList;
    if (_adminStudentsSearchQuery.isNotEmpty) {
      filtered = filtered.where((s) {
        final fName = s['first_name']?.toString().toLowerCase() ?? '';
        final lName = s['last_name']?.toString().toLowerCase() ?? '';
        final admNum = s['admission_number']?.toString().toLowerCase() ?? '';
        final query = _adminStudentsSearchQuery.toLowerCase();
        return fName.contains(query) || lName.contains(query) || admNum.contains(query);
      }).toList();
    }

    return Column(
      children: [
        // Search bar
        Padding(
          padding: const EdgeInsets.all(16.0),
          child: TextField(
            style: const TextStyle(fontSize: 13),
            decoration: InputDecoration(
              hintText: 'Search students by name or admission number...',
              fillColor: Colors.white,
              filled: true,
              prefixIcon: const Icon(Icons.search, size: 18),
              contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.divider)),
              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.divider)),
            ),
            onChanged: (val) {
              setState(() {
                _adminStudentsSearchQuery = val.trim();
              });
            },
          ),
        ),

        // Students list
        Expanded(
          child: _isLoadingAdminStudents
              ? const Center(child: CircularProgressIndicator())
              : filtered.isEmpty
                  ? const Center(
                      child: Text('No students found.'),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      itemCount: filtered.length,
                      itemBuilder: (context, index) {
                        final student = filtered[index];
                        final fName = student['first_name'] ?? '';
                        final lName = student['last_name'] ?? '';
                        final fullName = '$fName $lName'.trim();
                        final admNum = student['admission_number'] ?? '';
                        final className = student['school_class']?['name'] ?? 'Unassigned';

                        String initials = '';
                        if (fName.isNotEmpty) initials += fName[0].toUpperCase();
                        if (lName.isNotEmpty) initials += lName[0].toUpperCase();
                        if (initials.isEmpty) initials = '?';

                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: AppColors.amberPrimary.withOpacity(0.12),
                              child: Text(
                                initials,
                                style: const TextStyle(color: AppColors.amberPrimary, fontWeight: FontWeight.bold),
                              ),
                            ),
                            title: Text(fullName, style: const TextStyle(fontWeight: FontWeight.bold)),
                            subtitle: Text('Class: $className · Admission No: $admNum'),
                            trailing: const Icon(Icons.arrow_forward_ios, size: 12),
                            onTap: () {
                              _showAdminStudentDetailSheet(student);
                            },
                          ),
                        );
                      },
                    ),
        ),
      ],
    );
  }

  void _showAdminStudentDetailSheet(dynamic student) {
    final fName = student['first_name'] ?? '';
    final lName = student['last_name'] ?? '';
    final fullName = '$fName $lName'.trim();
    final admNum = student['admission_number'] ?? '';
    final className = student['school_class']?['name'] ?? 'Unassigned';
    final sectionName = student['section']?['name'] ?? 'Unassigned';
    final dob = student['date_of_birth'] ?? 'N/A';
    final gender = student['gender'] ?? 'N/A';
    final phone = student['guardian_phone'] ?? 'N/A';
    final email = student['guardian_email'] ?? 'N/A';
    final active = student['status'] ?? 'Active';

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.all(20.0),
          child: SingleChildScrollView(
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
                Text(
                  fullName,
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 4),
                Text(
                  'Admission Number: $admNum',
                  style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
                const Divider(),
                const SizedBox(height: 8),
                _buildDetailRow('Class', className),
                _buildDetailRow('Section', sectionName),
                _buildDetailRow('Date of Birth', dob),
                _buildDetailRow('Gender', gender.toUpperCase()),
                _buildDetailRow('Guardian Phone', phone),
                _buildDetailRow('Guardian Email', email),
                _buildDetailRow('Account Status', active),
                const SizedBox(height: 24),
                ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.amberPrimary,
                    foregroundColor: Colors.white,
                    elevation: 0,
                  ),
                  onPressed: () {
                    Navigator.pop(context);
                    _showChildResultsModal(student['id'], fullName);
                  },
                  icon: const Icon(Icons.analytics_outlined),
                  label: const Text('View Report Card / Term Results', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
                const SizedBox(height: 12),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildDetailRow(String label, String val) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13, fontWeight: FontWeight.w600)),
          Text(val, style: const TextStyle(color: AppColors.textPrimary, fontSize: 13, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  Widget _buildAdminAnnouncementsView() {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(16.0),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Published Announcements',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary),
              ),
              Text(
                '${_announcements.length} Total',
                style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
              ),
            ],
          ),
        ),
        Expanded(
          child: _isLoadingAnnouncements
              ? const Center(child: CircularProgressIndicator())
              : _announcements.isEmpty
                  ? const Center(
                      child: Text('No active announcements. Tap + to publish.'),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      itemCount: _announcements.length,
                      itemBuilder: (context, index) {
                        final ann = _announcements[index];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: const Color(0xFF7C3AED).withOpacity(0.12),
                              child: const Icon(Icons.campaign, color: Color(0xFF7C3AED)),
                            ),
                            title: Text(ann['title'] ?? 'Announcement', style: const TextStyle(fontWeight: FontWeight.bold)),
                            subtitle: Text(ann['body'] ?? ''),
                            trailing: const Icon(Icons.arrow_forward_ios, size: 12),
                          ),
                        );
                      },
                    ),
        ),
      ],
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
    final isAdminAnnounceTab = _currentIndex == 2 && _userRole == 'admin';
    final isAdminStudentsTab = _currentIndex == 1 && _userRole == 'admin';

    return Scaffold(
      backgroundColor: AppColors.appBackground,
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              width: 28, height: 28,
              decoration: BoxDecoration(
                gradient: LinearGradient(colors: _roleGradient),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.school_rounded, color: Colors.white, size: 16),
            ),
            const SizedBox(width: 8),
            Text(
              isHomeTab ? 'AcademyHub' : (navItems[_currentIndex].label ?? ''),
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: AppColors.textPrimary),
            ),
          ],
        ),
        actions: [
          Stack(
            alignment: Alignment.center,
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined, color: AppColors.textPrimary),
                onPressed: () {},
              ),
              Positioned(
                top: 10, right: 10,
                child: Container(
                  width: 8, height: 8,
                  decoration: const BoxDecoration(color: AppColors.dangerRed, shape: BoxShape.circle),
                ),
              ),
            ],
          ),
        ],
        elevation: 0,
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.white,
      ),
      body: Column(
        children: [
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
                                    ? const HomeworkView()
                                    : isChildrenTab
                                        ? _buildParentChildrenView()
                                        : isChatTab
                                            ? const ChatView()
                                            : isAdminAnnounceTab
                                                ? _buildAdminAnnouncementsView()
                                                : isAdminStudentsTab
                                                    ? _buildAdminStudentsView()
                                                    : Center(
                                                        child: Text(
                                                          '${navItems[_currentIndex].label} module is ready.',
                                                          style: const TextStyle(fontSize: 16, color: AppColors.textSecondary),
                                                        ),
                                                      ),
          ),
        ],
      ),
      floatingActionButton: isAdminAnnounceTab
          ? FloatingActionButton(
              backgroundColor: const Color(0xFF7C3AED),
              foregroundColor: Colors.white,
              onPressed: () => context.push('/broadcast-creator'),
              child: const Icon(Icons.add),
            )
          : null,
      bottomNavigationBar: SafeArea(
        child: Container(
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          height: 72,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.06),
                blurRadius: 20,
                offset: const Offset(0, 4),
              ),
            ],
            border: Border.all(color: AppColors.divider),
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
                else if (_userRole == 'admin') iconData = Icons.people_rounded;
              } else if (idx == 2) {
                if (_userRole == 'student') iconData = Icons.menu_book_rounded;
                else if (_userRole == 'parent') iconData = Icons.chat_rounded;
                else if (_userRole == 'teacher') iconData = Icons.grade_rounded;
                else if (_userRole == 'admin') iconData = Icons.campaign_rounded;
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
                    color: isSelected ? AppColors.amberPrimary.withOpacity(0.1) : Colors.transparent,
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
}

class _StatCard {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  const _StatCard(this.label, this.value, this.icon, this.color);
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
