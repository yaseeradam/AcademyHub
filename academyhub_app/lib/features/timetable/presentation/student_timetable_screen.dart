import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';

class StudentTimetableScreen extends StatefulWidget {
  const StudentTimetableScreen({super.key});

  @override
  State<StudentTimetableScreen> createState() => _StudentTimetableScreenState();
}

class _StudentTimetableScreenState extends State<StudentTimetableScreen>
    with SingleTickerProviderStateMixin {
  late TabController _dayTabController;
  String _userRole = 'student';

  final List<String> _days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

  final Map<String, List<Map<String, dynamic>>> _timetableData = {
    'Monday': [
      {'period': 1, 'time': '08:00 AM - 08:45 AM', 'subject': 'General Mathematics', 'code': 'MTH', 'teacher': 'Mrs. Florence Adebayo', 'room': 'Block A, Room 2', 'status': 'completed'},
      {'period': 2, 'time': '08:45 AM - 09:30 AM', 'subject': 'English Language', 'code': 'ENG', 'teacher': 'Mr. Chinedu Eze', 'room': 'Block A, Room 2', 'status': 'completed'},
      {'period': 3, 'time': '09:30 AM - 10:15 AM', 'subject': 'Physics', 'code': 'PHY', 'teacher': 'Miss Grace Danjuma', 'room': 'Lab Block 1', 'status': 'ongoing'},
      {'period': 0, 'time': '10:15 AM - 10:45 AM', 'subject': 'MORNING RECESSS & BREAK', 'code': 'BRK', 'teacher': 'Duty Teacher', 'room': 'School Cafeteria', 'status': 'break'},
      {'period': 4, 'time': '10:45 AM - 11:30 AM', 'subject': 'Chemistry', 'code': 'CHM', 'teacher': 'Mr. Tunde Bakare', 'room': 'Lab Block 2', 'status': 'upcoming'},
      {'period': 5, 'time': '11:30 AM - 12:15 PM', 'subject': 'Biology', 'code': 'BIO', 'teacher': 'Mr. Tunde Bakare', 'room': 'Lab Block 2', 'status': 'upcoming'},
      {'period': 6, 'time': '12:15 PM - 01:00 PM', 'subject': 'Computer Studies / ICT', 'code': 'CMP', 'teacher': 'Mr. Samuel Audu', 'room': 'ICT Lab', 'status': 'upcoming'},
    ],
    'Tuesday': [
      {'period': 1, 'time': '08:00 AM - 08:45 AM', 'subject': 'English Language', 'code': 'ENG', 'teacher': 'Mr. Chinedu Eze', 'room': 'Block A, Room 2', 'status': 'upcoming'},
      {'period': 2, 'time': '08:45 AM - 09:30 AM', 'subject': 'General Mathematics', 'code': 'MTH', 'teacher': 'Mrs. Florence Adebayo', 'room': 'Block A, Room 2', 'status': 'upcoming'},
      {'period': 3, 'time': '09:30 AM - 10:15 AM', 'subject': 'Civic Education', 'code': 'CIV', 'teacher': 'Mr. Ibrahim Yusuf', 'room': 'Block A, Room 2', 'status': 'upcoming'},
    ],
    'Wednesday': [
      {'period': 1, 'time': '08:00 AM - 08:45 AM', 'subject': 'Physics Lab Practical', 'code': 'PHY', 'teacher': 'Miss Grace Danjuma', 'room': 'Physics Lab', 'status': 'upcoming'},
      {'period': 2, 'time': '08:45 AM - 09:30 AM', 'subject': 'Chemistry Lab Practical', 'code': 'CHM', 'teacher': 'Mr. Tunde Bakare', 'room': 'Chemistry Lab', 'status': 'upcoming'},
    ],
    'Thursday': [
      {'period': 1, 'time': '08:00 AM - 08:45 AM', 'subject': 'Economics', 'code': 'ECO', 'teacher': 'Mrs. Ngozi Okeke', 'room': 'Block B, Room 1', 'status': 'upcoming'},
      {'period': 2, 'time': '08:45 AM - 09:30 AM', 'subject': 'Government', 'code': 'GOV', 'teacher': 'Mr. Ibrahim Yusuf', 'room': 'Block B, Room 1', 'status': 'upcoming'},
    ],
    'Friday': [
      {'period': 1, 'time': '08:00 AM - 08:45 AM', 'subject': 'Physical Education & Sports', 'code': 'PHE', 'teacher': 'Coach Johnson', 'room': 'School Sports Complex', 'status': 'upcoming'},
      {'period': 2, 'time': '08:45 AM - 09:30 AM', 'subject': 'Weekly Assembly & Quiz', 'code': 'GEN', 'teacher': 'School Principal', 'room': 'Main Auditorium', 'status': 'upcoming'},
    ],
  };

  @override
  void initState() {
    super.initState();
    _dayTabController = TabController(length: _days.length, vsync: this);
    _loadRole();
  }

  Future<void> _loadRole() async {
    final role = await SecureStorage.instance.getRole() ?? 'student';
    if (mounted) setState(() => _userRole = role);
  }

  @override
  void dispose() {
    _dayTabController.dispose();
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
          'Class & Exam Timetable',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        bottom: TabBar(
          controller: _dayTabController,
          isScrollable: true,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          tabs: _days.map((d) => Tab(text: d)).toList(),
        ),
      ),
      floatingActionButton: (_userRole.toLowerCase().trim() == 'student' || _userRole.toLowerCase().trim() == 'parent')
          ? null
          : FloatingActionButton.extended(
              backgroundColor: AppColors.rolePrimary(_userRole),
              foregroundColor: Colors.white,
              icon: const Icon(Icons.add_rounded),
              label: const Text('Add Period', style: TextStyle(fontWeight: FontWeight.bold)),
              onPressed: () {
                _showAddPeriodModal(context);
              },
            ),
      body: Column(
        children: [
          // ── Live Ongoing Class Banner ───────────────────────
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            color: AppColors.successGreen.withValues(alpha: 0.12),
            child: const Row(
              children: [
                Icon(Icons.play_circle_fill_rounded, color: AppColors.successGreen, size: 20),
                SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'ONGOING PERIOD (09:30 AM - 10:15 AM)',
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.successGreen),
                      ),
                      Text(
                        'Physics with Miss Grace Danjuma — Lab Block 1',
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // ── Schedule List per Day ───────────────────────────
          Expanded(
            child: TabBarView(
              controller: _dayTabController,
              children: _days.map((day) {
                final list = _timetableData[day] ?? [];
                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: list.length,
                  itemBuilder: (context, index) {
                    final item = list[index];
                    final bool isBreak = item['status'] == 'break';
                    final bool isOngoing = item['status'] == 'ongoing';
                    final bool isCompleted = item['status'] == 'completed';

                    if (isBreak) {
                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                        decoration: BoxDecoration(
                          color: Colors.amber.shade50,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.amber.shade300),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.free_breakfast_rounded, color: Colors.amber, size: 20),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item['subject'],
                                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.amber.shade900),
                                  ),
                                  Text(item['time'], style: TextStyle(fontSize: 11, color: Colors.amber.shade800)),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    }

                    return Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: isOngoing ? Colors.blue.shade50 : Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isOngoing ? Colors.blue : (isCompleted ? AppColors.divider : AppColors.divider),
                          width: isOngoing ? 2 : 1,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.02),
                            blurRadius: 6,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                            decoration: BoxDecoration(
                              color: isOngoing
                                  ? Colors.blue
                                  : (isCompleted ? AppColors.textDisabled : AppColors.rolePrimary(_userRole)),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Column(
                              children: [
                                Text(
                                  'P${item['period']}',
                                  style: const TextStyle(fontWeight: FontWeight.w900, color: Colors.white, fontSize: 14),
                                ),
                                Text(
                                  item['code'],
                                  style: const TextStyle(fontWeight: FontWeight.w600, color: Colors.white70, fontSize: 10),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        item['subject'],
                                        style: TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 14,
                                          color: isCompleted ? AppColors.textSecondary : AppColors.textPrimary,
                                        ),
                                      ),
                                    ),
                                    if (isOngoing)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: Colors.blue,
                                          borderRadius: BorderRadius.circular(10),
                                        ),
                                        child: const Text('Ongoing', style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.white)),
                                      ),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    const Icon(Icons.access_time_rounded, size: 12, color: AppColors.textSecondary),
                                    const SizedBox(width: 4),
                                    Text(item['time'], style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                                    const SizedBox(width: 12),
                                    const Icon(Icons.location_on_outlined, size: 12, color: AppColors.textSecondary),
                                    const SizedBox(width: 2),
                                    Text(item['room'], style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Teacher: ${item['teacher']}',
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textSecondary),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  void _showAddPeriodModal(BuildContext context) {
    String selectedDay = _days[_dayTabController.index];
    final subjectCtrl = TextEditingController(text: 'General Mathematics');
    final codeCtrl = TextEditingController(text: 'MTH');
    final teacherCtrl = TextEditingController(text: 'Mrs. Florence Adebayo');
    final roomCtrl = TextEditingController(text: 'Block A, Room 3');
    final timeCtrl = TextEditingController(text: '01:00 PM - 01:45 PM');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return Padding(
          padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(ctx).viewInsets.bottom + 20),
          child: StatefulBuilder(
            builder: (context, setModalState) {
              return Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Add Timetable Period',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    initialValue: selectedDay,
                    decoration: InputDecoration(
                      labelText: 'Select Day',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    items: _days.map((d) => DropdownMenuItem(value: d, child: Text(d))).toList(),
                    onChanged: (v) {
                      if (v != null) setModalState(() => selectedDay = v);
                    },
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: subjectCtrl,
                    decoration: InputDecoration(
                      labelText: 'Subject Name',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: codeCtrl,
                          decoration: InputDecoration(
                            labelText: 'Code (e.g. MTH)',
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: TextField(
                          controller: roomCtrl,
                          decoration: InputDecoration(
                            labelText: 'Classroom / Lab',
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: teacherCtrl,
                    decoration: InputDecoration(
                      labelText: 'Assigned Teacher',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: timeCtrl,
                    decoration: InputDecoration(
                      labelText: 'Time Slot (e.g. 01:00 PM - 01:45 PM)',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.rolePrimary(_userRole),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    onPressed: () {
                      if (subjectCtrl.text.trim().isNotEmpty) {
                        final newSlot = {
                          'period': (_timetableData[selectedDay]?.length ?? 0) + 1,
                          'time': timeCtrl.text.trim(),
                          'subject': subjectCtrl.text.trim(),
                          'code': codeCtrl.text.trim(),
                          'teacher': teacherCtrl.text.trim(),
                          'room': roomCtrl.text.trim(),
                          'status': 'upcoming',
                        };
                        setState(() {
                          _timetableData[selectedDay] = [...(_timetableData[selectedDay] ?? []), newSlot];
                        });
                        Navigator.pop(ctx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('✓ Added ${subjectCtrl.text.trim()} to $selectedDay timetable!'),
                            backgroundColor: AppColors.successGreen,
                          ),
                        );
                      }
                    },
                    child: const Text('Save Period Slot', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                  ),
                ],
              );
            },
          ),
        );
      },
    );
  }
}
