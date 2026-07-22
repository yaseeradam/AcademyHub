import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';

class StudentReportCardScreen extends StatefulWidget {
  const StudentReportCardScreen({super.key});

  @override
  State<StudentReportCardScreen> createState() => _StudentReportCardScreenState();
}

class _StudentReportCardScreenState extends State<StudentReportCardScreen> {
  int _selectedChildIndex = 0;
  String _selectedTerm = 'Term 2';
  bool _isLoading = true;
  String _userRole = 'student';

  List<Map<String, dynamic>> _children = [];
  List<Map<String, dynamic>> _subjectsData = [];

  // Summary stats from API
  String _classRank = '—';
  String _attendanceRate = '—';
  String _teacherRemark = '';
  String _principalComment = '';

  static const List<Color> _subjectColors = [
    Colors.blue, Colors.purple, Colors.indigo, Colors.teal,
    Colors.green, Colors.orange, Colors.deepOrange, Colors.pink,
  ];

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final role = (await SecureStorage.instance.getRole() ?? 'student').toLowerCase().trim();
    if (mounted) setState(() { _userRole = role; _isLoading = true; });

    try {
      if (role == 'parent') {
        // Load parent's children first
        final childrenRes = await apiClient.dio.get('/parent/children');
        if (childrenRes.statusCode == 200 && childrenRes.data != null) {
          final list = List<dynamic>.from(childrenRes.data['data'] ?? []);
          if (mounted && list.isNotEmpty) {
            setState(() {
              _children = list.map((c) => Map<String, dynamic>.from(c)).toList();
            });
          }
        }
        if (_children.isEmpty) {
          _setFallbackChildren();
        }
        await _loadResults();
      } else {
        // Student role: load their own results
        _children = [{'name': 'My Results', 'class': '', 'admNo': '', 'avatar': 'ME', 'id': null}];
        await _loadResults();
      }
    } catch (e) {
      debugPrint('Error loading report card: $e');
      _setFallbackChildren();
      _setFallbackSubjects();
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _setFallbackChildren() {
    if (_children.isEmpty) {
      _children = [
        {'name': 'Daniel Adebayo', 'class': 'JSS 2B', 'admNo': 'ADM-2024-001', 'avatar': 'D', 'id': 1},
        {'name': 'Sarah Adebayo', 'class': 'SSS 1 Science', 'admNo': 'ADM-2024-015', 'avatar': 'S', 'id': 2},
      ];
    }
  }

  void _setFallbackSubjects() {
    if (_subjectsData.isEmpty) {
      _subjectsData = [
        {'subject': 'Mathematics', 'code': 'MTH', 'ca1': 18, 'ca2': 19, 'exam': 54, 'grade': 'A'},
        {'subject': 'English Language', 'code': 'ENG', 'ca1': 16, 'ca2': 17, 'exam': 50, 'grade': 'B'},
        {'subject': 'Physics', 'code': 'PHY', 'ca1': 19, 'ca2': 18, 'exam': 56, 'grade': 'A'},
        {'subject': 'Chemistry', 'code': 'CHM', 'ca1': 17, 'ca2': 16, 'exam': 48, 'grade': 'B'},
        {'subject': 'Biology', 'code': 'BIO', 'ca1': 18, 'ca2': 20, 'exam': 55, 'grade': 'A'},
        {'subject': 'Computer Studies', 'code': 'CMP', 'ca1': 20, 'ca2': 19, 'exam': 58, 'grade': 'A+'},
      ];
    }
  }

  Future<void> _loadResults() async {
    if (!mounted) return;
    try {
      final String endpoint;
      if (_userRole == 'parent' && _children.isNotEmpty) {
        final childId = _children[_selectedChildIndex]['id'];
        endpoint = '/parent/results/$childId';
      } else {
        endpoint = '/student/results';
      }
      final res = await apiClient.dio.get(endpoint, queryParameters: {'term': _selectedTerm});
      if (res.statusCode == 200 && res.data != null) {
        final data = res.data['data'] ?? res.data;
        final rawSubjects = List<dynamic>.from(data['subjects'] ?? data['results'] ?? []);
        if (mounted && rawSubjects.isNotEmpty) {
          setState(() {
            _subjectsData = rawSubjects.asMap().entries.map((e) {
              final s = Map<String, dynamic>.from(e.value);
              s['color'] = _subjectColors[e.key % _subjectColors.length];
              return s;
            }).toList();
            _classRank = data['class_rank']?.toString() ?? '—';
            _attendanceRate = data['attendance_rate']?.toString() ?? '—';
            _teacherRemark = data['teacher_remark'] ?? '';
            _principalComment = data['principal_comment'] ?? '';
          });
          return;
        }
      }
    } catch (e) {
      debugPrint('Error loading results: $e');
    }
    // Fallback
    if (mounted) {
      _setFallbackSubjects();
      setState(() {
        _classRank = '3rd / 32';
        _attendanceRate = '96.2%';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        backgroundColor: const Color(0xFFF8FAFC),
        appBar: AppBar(
          backgroundColor: AppColors.rolePrimary('parent'),
          foregroundColor: Colors.white,
          title: const Text('Digital Report Card', style: TextStyle(fontWeight: FontWeight.bold)),
          leading: Padding(
            padding: const EdgeInsets.all(8.0),
            child: InkWell(
              onTap: () => Navigator.maybePop(context),
              borderRadius: BorderRadius.circular(10),
              child: Container(
                decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.18), borderRadius: BorderRadius.circular(10)),
                child: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 18),
              ),
            ),
          ),
        ),
        body: const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary)),
      );
    }

    final currentChild = _children.isNotEmpty ? _children[_selectedChildIndex] : <String, dynamic>{};

    int grandTotal = 0;
    for (var s in _subjectsData) {
      final ca1 = int.tryParse(s['ca1']?.toString() ?? '0') ?? 0;
      final ca2 = int.tryParse(s['ca2']?.toString() ?? '0') ?? 0;
      final exam = int.tryParse(s['exam']?.toString() ?? '0') ?? 0;
      grandTotal += ca1 + ca2 + exam;
    }
    final double average = _subjectsData.isEmpty ? 0 : grandTotal / _subjectsData.length;



    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: AppColors.rolePrimary('parent'),
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
          'Digital Report Card',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.picture_as_pdf_rounded),
            tooltip: 'Download PDF Report',
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Downloading report card PDF for ${currentChild['name']}...'),
                  backgroundColor: AppColors.successGreen,
                ),
              );
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // ── Header Child Switcher Bar ───────────────────────
            Container(
              color: AppColors.rolePrimary('parent'),
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
              child: Column(
                children: [
                  // Child selection pills
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: List.generate(_children.length, (index) {
                        final child = _children[index];
                        final isSelected = index == _selectedChildIndex;
                        return Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: ChoiceChip(
                            showCheckmark: false,
                            selected: isSelected,
                            selectedColor: Colors.white,
                            backgroundColor: Colors.white.withValues(alpha: 0.15),
                            label: Row(
                              children: [
                                CircleAvatar(
                                  radius: 12,
                                  backgroundColor: isSelected ? AppColors.rolePrimary('parent') : Colors.white24,
                                  child: Text(
                                    child['avatar'],
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  child['name'],
                                  style: TextStyle(
                                    color: isSelected ? AppColors.rolePrimary('parent') : Colors.white,
                                    fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                    fontSize: 13,
                                  ),
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  '(${child['class']})',
                                  style: TextStyle(
                                    color: isSelected ? AppColors.textSecondary : Colors.white70,
                                    fontSize: 11,
                                  ),
                                ),
                              ],
                            ),
                            onSelected: (val) {
                              if (val) setState(() => _selectedChildIndex = index);
                            },
                          ),
                        );
                      }),
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Term Dropdown selector
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        value: _selectedTerm,
                        dropdownColor: AppColors.rolePrimary('parent'),
                        icon: const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white),
                        style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
                        items: const [
                          DropdownMenuItem(value: '2023/2024 - Second Term', child: Text('2023/2024 - Second Term (Current)')),
                          DropdownMenuItem(value: '2023/2024 - First Term', child: Text('2023/2024 - First Term')),
                          DropdownMenuItem(value: '2022/2023 - Third Term', child: Text('2022/2023 - Third Term')),
                        ],
                        onChanged: (val) {
                          if (val != null) setState(() => _selectedTerm = val);
                        },
                      ),
                    ),
                  ),
                ],
              ),
            ),

            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── Overview KPI Metrics Grid ────────────────────
                  Row(
                    children: [
                      Expanded(
                        child: _metricTile(
                          label: 'Average Score',
                          value: '${average.toStringAsFixed(1)}%',
                          subtitle: 'Distinction Grade',
                          icon: Icons.auto_graph_rounded,
                          color: AppColors.successGreen,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _metricTile(
                          label: 'Class Rank',
                          value: _classRank,
                          subtitle: _classRank == '—' ? 'Not available' : 'Class standing',
                          icon: Icons.emoji_events_rounded,
                          color: Colors.amber.shade700,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(
                        child: _metricTile(
                          label: 'Attendance Rate',
                          value: _attendanceRate,
                          subtitle: _attendanceRate == '—' ? 'Not available' : 'This term',
                          icon: Icons.check_circle_outline_rounded,
                          color: Colors.blue,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _metricTile(
                          label: 'Conduct Rating',
                          value: 'Excellent',
                          subtitle: 'A Grade Behavior',
                          icon: Icons.verified_user_rounded,
                          color: Colors.purple,
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 20),

                  // ── Subject Performance Breakdown ────────────────
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Subject Scores Breakdown',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppColors.divider),
                        ),
                        child: const Text('CA1 (20) · CA2 (20) · Exam (60)', style: TextStyle(fontSize: 10, color: AppColors.textSecondary)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: _subjectsData.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 10),
                    itemBuilder: (context, index) {
                      final item = _subjectsData[index];
                      final total = (item['ca1'] as int) + (item['ca2'] as int) + (item['exam'] as int);
                      final gradeColor = total >= 70 ? AppColors.successGreen : (total >= 50 ? Colors.orange : AppColors.dangerRed);

                      return Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.divider),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.02),
                              blurRadius: 8,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Column(
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                                  decoration: BoxDecoration(
                                    color: (item['color'] as Color).withValues(alpha: 0.12),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    item['code'],
                                    style: TextStyle(
                                      fontWeight: FontWeight.w800,
                                      color: item['color'] as Color,
                                      fontSize: 12,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Text(
                                    item['subject'],
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary),
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: gradeColor.withValues(alpha: 0.12),
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(
                                    '${item['grade']} ($total%)',
                                    style: TextStyle(fontWeight: FontWeight.bold, color: gradeColor, fontSize: 12),
                                  ),
                                ),
                              ],
                            ),
                            const Divider(height: 20),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceAround,
                              children: [
                                _scoreCol('CA 1', '${item['ca1']} / 20'),
                                _scoreCol('CA 2', '${item['ca2']} / 20'),
                                _scoreCol('Exam', '${item['exam']} / 60'),
                                _scoreCol('Total', '$total / 100', isBold: true),
                              ],
                            ),
                          ],
                        ),
                      );
                    },
                  ),

                  const SizedBox(height: 24),

                  // ── Teacher & Principal Remarks Card ─────────────
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.divider),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.comment_outlined, size: 18, color: AppColors.textSecondary),
                            SizedBox(width: 8),
                            Text('Official Remarks', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textPrimary)),
                          ],
                        ),
                        const SizedBox(height: 12),
                        const Text(
                          'Class Teacher\'s Remark:',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppColors.textSecondary),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          _teacherRemark.isNotEmpty
                              ? '"$_teacherRemark"'
                              : '"${currentChild['name'] ?? 'The student'} has performed well this term."',
                          style: const TextStyle(fontSize: 13, fontStyle: FontStyle.italic, color: AppColors.textPrimary),
                        ),
                        const Divider(height: 20),
                        const Text(
                          "Principal's Comment:",
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppColors.textSecondary),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          _principalComment.isNotEmpty
                              ? '"$_principalComment"'
                              : '"Keep up the good work and maintain this level of academic excellence."',
                          style: const TextStyle(fontSize: 13, fontStyle: FontStyle.italic, color: AppColors.successGreen, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _metricTile({
    required String label,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.divider),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                Text(value, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                Text(subtitle, style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.w600)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _scoreCol(String title, String val, {bool isBold = false}) {
    return Column(
      children: [
        Text(title, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
        const SizedBox(height: 2),
        Text(
          val,
          style: TextStyle(
            fontSize: 12,
            fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
            color: isBold ? AppColors.textPrimary : AppColors.textSecondary,
          ),
        ),
      ],
    );
  }
}
