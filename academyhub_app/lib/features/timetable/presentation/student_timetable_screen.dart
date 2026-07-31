import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';
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
  bool _isLoading = true;

  final List<String> _days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

  /// Maps day index (0=Monday … 4=Friday) to day name string.
  static const List<String> _dayNames = [
    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
  ];

  Map<String, List<Map<String, dynamic>>> _timetableData = {
    'Monday': [],
    'Tuesday': [],
    'Wednesday': [],
    'Thursday': [],
    'Friday': [],
  };

  @override
  void initState() {
    super.initState();
    _dayTabController = TabController(length: _days.length, vsync: this);
    _loadRoleAndTimetable();
  }

  Future<void> _loadRoleAndTimetable() async {
    final role = await SecureStorage.instance.getRole() ?? 'student';
    if (mounted) setState(() => _userRole = role);
    await _fetchTimetable();
  }

  Future<void> _fetchTimetable() async {
    if (!mounted) return;
    setState(() => _isLoading = true);

    try {
      final response = await apiClient.dio.get('/timetable');
      if (!mounted) return;

      if (response.statusCode == 200 && response.data != null) {
        final rawData = response.data;
        final List<dynamic> entries =
            (rawData is Map && rawData.containsKey('data'))
                ? List<dynamic>.from(rawData['data'])
                : (rawData is List ? rawData : []);

        // Reset map
        final Map<String, List<Map<String, dynamic>>> mapped = {
          for (final d in _days) d: [],
        };

        int periodCounters = 0;
        for (final entry in entries) {
          final dayOfWeek = entry['day_of_week'];
          if (dayOfWeek == null) continue;

          final int dayIndex = int.tryParse(dayOfWeek.toString()) ?? -1;
          if (dayIndex < 0 || dayIndex >= _dayNames.length) continue;

          final dayName = _dayNames[dayIndex];

          final subjectName =
              (entry['subject'] is Map ? entry['subject']['name'] : null) ??
                  entry['subject_name'] ??
                  'Unknown Subject';
          final teacherName =
              (entry['teacher'] is Map ? entry['teacher']['name'] : null) ??
                  entry['teacher_name'] ??
                  'Unknown Teacher';
          final room = entry['room']?.toString() ?? '—';
          final startsAt = entry['starts_at']?.toString() ?? '';
          final endsAt = entry['ends_at']?.toString() ?? '';
          final timeSlot =
              (startsAt.isNotEmpty && endsAt.isNotEmpty) ? '$startsAt - $endsAt' : '—';

          periodCounters++;
          final slot = {
            'period': periodCounters,
            'time': timeSlot,
            'subject': subjectName,
            'code': _initials(subjectName),
            'teacher': teacherName,
            'room': room,
            'status': 'upcoming',
          };

          mapped[dayName]!.add(slot);
        }

        setState(() {
          _timetableData = mapped;
        });
      }
    } catch (e) {
      debugPrint('Error fetching timetable: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  /// Returns up to 3 uppercase initials from a subject name (e.g. "General Mathematics" → "GM").
  String _initials(String name) {
    final parts = name.trim().split(RegExp(r'\s+'));
    return parts.take(3).map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();
  }

  @override
  void dispose() {
    _dayTabController.dispose();
    super.dispose();
  }

  bool get _isAdminOrTeacher =>
      !(_userRole.toLowerCase().trim() == 'student' ||
          _userRole.toLowerCase().trim() == 'parent');

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
      floatingActionButton: _isAdminOrTeacher
          ? FloatingActionButton.extended(
              backgroundColor: AppColors.rolePrimary(_userRole),
              foregroundColor: Colors.white,
              icon: const Icon(Icons.add_rounded),
              label: const Text('Add Period', style: TextStyle(fontWeight: FontWeight.bold)),
              onPressed: () {
                _showAddPeriodModal(context);
              },
            )
          : null,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary))
          : Column(
              children: [
                // ── Schedule List per Day ───────────────────────────
                Expanded(
                  child: TabBarView(
                    controller: _dayTabController,
                    children: _days.map((day) {
                      final list = _timetableData[day] ?? [];

                      if (list.isEmpty) {
                        return Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                width: 72,
                                height: 72,
                                decoration: BoxDecoration(
                                  color: AppColors.amberPrimary.withValues(alpha: 0.1),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(
                                  Icons.event_busy_rounded,
                                  size: 36,
                                  color: AppColors.amberPrimary,
                                ),
                              ),
                              const SizedBox(height: 16),
                              Text(
                                'No classes on $day',
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF0F172A),
                                ),
                              ),
                              const SizedBox(height: 6),
                              const Text(
                                'No timetable entries are scheduled for this day.',
                                style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
                                textAlign: TextAlign.center,
                              ),
                            ],
                          ),
                        );
                      }

                      return RefreshIndicator(
                        onRefresh: _fetchTimetable,
                        color: AppColors.amberPrimary,
                        child: ListView.builder(
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
                                            item['subject'] as String,
                                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.amber.shade900),
                                          ),
                                          Text(item['time'] as String, style: TextStyle(fontSize: 11, color: Colors.amber.shade800)),
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
                                  color: isOngoing ? Colors.blue : AppColors.divider,
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
                                          item['code'] as String,
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
                                                item['subject'] as String,
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
                                            Flexible(
                                              child: Text(
                                                item['time'] as String,
                                                style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            const Icon(Icons.location_on_outlined, size: 12, color: AppColors.textSecondary),
                                            const SizedBox(width: 2),
                                            Flexible(
                                              child: Text(
                                                item['room'] as String,
                                                style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
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
                        ),
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
    final subjectCtrl = TextEditingController();
    final codeCtrl = TextEditingController();
    final teacherCtrl = TextEditingController();
    final roomCtrl = TextEditingController();
    final timeCtrl = TextEditingController();

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
                          'code': codeCtrl.text.trim().isNotEmpty
                              ? codeCtrl.text.trim().toUpperCase()
                              : _initials(subjectCtrl.text.trim()),
                          'teacher': teacherCtrl.text.trim(),
                          'room': roomCtrl.text.trim(),
                          'status': 'upcoming',
                        };
                        setState(() {
                          _timetableData[selectedDay] = [
                            ...(_timetableData[selectedDay] ?? []),
                            newSlot,
                          ];
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
