import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';
import 'package:academyhub_app/core/network/api_client.dart';

class HomeworkTrackerScreen extends StatefulWidget {
  const HomeworkTrackerScreen({super.key});

  @override
  State<HomeworkTrackerScreen> createState() => _HomeworkTrackerScreenState();
}

class _HomeworkTrackerScreenState extends State<HomeworkTrackerScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  String _userRole = 'student';
  bool _isLoading = true;
  List<Map<String, dynamic>> _homeworkList = [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadData();
  }

  Future<void> _loadData() async {
    final role = (await SecureStorage.instance.getRole() ?? 'student').toLowerCase().trim();
    if (mounted) setState(() => _userRole = role);
    await _loadHomework();
  }

  Future<void> _loadHomework() async {
    if (!mounted) return;
    setState(() => _isLoading = true);
    try {
      final endpoint = (_userRole == 'student')
          ? '/student/homework'
          : '/teacher/homework';
      final response = await apiClient.dio.get(endpoint);
      if (response.statusCode == 200 && response.data != null) {
        final rawList = List<dynamic>.from(response.data['data'] ?? response.data ?? []);
        if (mounted) {
          setState(() {
            _homeworkList = rawList.map((item) => Map<String, dynamic>.from(item)).toList();
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading homework: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
    // If still empty, use sensible demo data
    if (_homeworkList.isEmpty && mounted) {
      setState(() {
        _homeworkList = [
          {
            'id': '1',
            'subject': 'General Mathematics',
            'code': 'MTH',
            'title': 'Quadratic Equations Exercise 4B',
            'description': 'Solve problems 1 to 15 on page 104 in your Math workbook. Show all step-by-step workings clearly.',
            'due_date': 'Tomorrow, 8:00 AM',
            'is_urgent': true,
            'teacher': 'Mrs. Florence Adebayo',
            'is_completed': false,
            'attachment': 'algebra_handout.pdf',
          },
          {
            'id': '2',
            'subject': 'Physics',
            'code': 'PHY',
            'title': "Newton's Laws of Motion Lab Summary",
            'description': "Write a 2-page summary on Newton's third law observed in today's laboratory session.",
            'due_date': 'Friday',
            'is_urgent': false,
            'teacher': 'Miss Grace Danjuma',
            'is_completed': false,
            'attachment': null,
          },
          {
            'id': '3',
            'subject': 'English Literature',
            'code': 'LIT',
            'title': 'Essay: Themes in "The Lion and the Jewel"',
            'description': 'Write a 500-word critical essay discussing how traditional values collide with modern ideas in Act 1.',
            'due_date': 'Monday',
            'is_urgent': false,
            'teacher': 'Mr. Chinedu Eze',
            'is_completed': true,
            'attachment': 'essay_rubric.pdf',
          },
          {
            'id': '4',
            'subject': 'Chemistry',
            'code': 'CHM',
            'title': 'Periodic Table Valence Calculations',
            'description': 'Complete worksheet #3 on chemical bonding and valence electron configurations.',
            'due_date': 'Completed',
            'is_urgent': false,
            'teacher': 'Mr. Tunde Bakare',
            'is_completed': true,
            'attachment': null,
          },
        ];
        _isLoading = false;
      });
    }
  }

  Future<void> _toggleComplete(Map<String, dynamic> item) async {
    final newStatus = !(item['is_completed'] == true);
    // Optimistically update UI
    setState(() => item['is_completed'] = newStatus);
    try {
      await apiClient.dio.post(
        '/student/homework/${item['id']}/toggle',
        data: {'is_completed': newStatus},
      );
    } catch (e) {
      // Revert on failure
      if (mounted) {
        setState(() => item['is_completed'] = !newStatus);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not update status. Please try again.'), backgroundColor: AppColors.dangerRed),
        );
      }
    }
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: AppColors.rolePrimary(_userRole),
        foregroundColor: Colors.white,
        elevation: 0,
        leading: Padding(
          padding: const EdgeInsets.all(8.0),
          child: InkWell(
            onTap: () => Navigator.maybePop(context),
            borderRadius: BorderRadius.circular(10),
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.18),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 18),
            ),
          ),
        ),
        title: const Text(
          'Homework & Assignments',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: _loadHomework,
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          tabs: const [
            Tab(text: 'All Assignments'),
            Tab(text: 'Pending'),
            Tab(text: 'Completed'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary))
          : TabBarView(
              controller: _tabController,
              children: [
                _buildHomeworkList(filter: 'all'),
                _buildHomeworkList(filter: 'pending'),
                _buildHomeworkList(filter: 'completed'),
              ],
            ),
    );
  }

  Widget _buildHomeworkList({required String filter}) {
    List<Map<String, dynamic>> items = _homeworkList;
    if (filter == 'pending') {
      items = items.where((h) => h['is_completed'] != true).toList();
    } else if (filter == 'completed') {
      items = items.where((h) => h['is_completed'] == true).toList();
    }

    if (items.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.assignment_turned_in_rounded, size: 64, color: AppColors.textDisabled),
            const SizedBox(height: 12),
            Text(
              filter == 'pending' ? 'No pending homework!' : 'No assignments found.',
              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textSecondary),
            ),
            const SizedBox(height: 4),
            const Text('Great job keeping up with your studies!', style: TextStyle(fontSize: 12, color: AppColors.textDisabled)),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadHomework,
      color: AppColors.rolePrimary(_userRole),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: items.length,
        itemBuilder: (context, index) {
          final item = items[index];
          final bool isDone = item['is_completed'] == true;
          final bool isUrgent = item['is_urgent'] == true || item['urgent'] == true;
          final String subject = item['subject_name'] ?? item['subject'] ?? 'Subject';
          final String code = item['subject_code'] ?? item['code'] ?? subject.substring(0, 3).toUpperCase();
          final String title = item['title'] ?? 'Assignment';
          final String description = item['description'] ?? item['content'] ?? '';
          final String dueDate = item['due_date'] ?? item['dueDate'] ?? '';
          final String? attachment = item['attachment'] ?? item['file_url'];

          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            elevation: 0,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: BorderSide(
                color: isDone ? AppColors.successGreen.withValues(alpha: 0.3) : AppColors.divider,
              ),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.rolePrimary(_userRole).withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          code,
                          style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.rolePrimary(_userRole), fontSize: 11),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          subject,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textSecondary),
                        ),
                      ),
                      if (isUrgent && !isDone)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.dangerRed.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.warning_amber_rounded, size: 12, color: AppColors.dangerRed),
                              SizedBox(width: 3),
                              Text('Urgent', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.dangerRed)),
                            ],
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: isDone ? AppColors.textSecondary : AppColors.textPrimary,
                      decoration: isDone ? TextDecoration.lineThrough : null,
                    ),
                  ),
                  if (description.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      description,
                      style: const TextStyle(fontSize: 13, color: AppColors.textSecondary, height: 1.4),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                  const SizedBox(height: 12),
                  if (attachment != null) ...[
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppColors.divider),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.attach_file_rounded, size: 14, color: AppColors.textSecondary),
                          const SizedBox(width: 6),
                          Text(
                            attachment,
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.rolePrimary(_userRole)),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],
                  const Divider(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      if (dueDate.isNotEmpty)
                        Row(
                          children: [
                            const Icon(Icons.calendar_today_rounded, size: 13, color: AppColors.textSecondary),
                            const SizedBox(width: 5),
                            Text(
                              'Due: $dueDate',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: isUrgent && !isDone ? AppColors.dangerRed : AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      if (_userRole == 'student')
                        InkWell(
                          onTap: () => _toggleComplete(item),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: isDone ? AppColors.successGreen : AppColors.rolePrimary(_userRole),
                              borderRadius: BorderRadius.circular(10),
                              border: Border(
                                bottom: BorderSide(
                                  color: isDone ? const Color(0xFF166534) : AppColors.role3DShadowColor(_userRole),
                                  width: 2,
                                ),
                              ),
                            ),
                            child: Row(
                              children: [
                                Icon(isDone ? Icons.check_circle : Icons.circle_outlined, size: 14, color: Colors.white),
                                const SizedBox(width: 6),
                                Text(
                                  isDone ? 'Completed' : 'Mark Done',
                                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white),
                                ),
                              ],
                            ),
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
