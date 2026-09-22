import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';

class TimetablePeriod {
  final String title;
  final String teacher;
  final String room;
  final TimeOfDay startTime;
  final TimeOfDay endTime;
  final int dayOfWeek;

  const TimetablePeriod({
    required this.title,
    required this.teacher,
    required this.room,
    required this.startTime,
    required this.endTime,
    required this.dayOfWeek,
  });

  bool isActiveNow(DateTime now) {
    final startMinutes = startTime.hour * 60 + startTime.minute;
    final endMinutes = endTime.hour * 60 + endTime.minute;
    final currentMinutes = now.hour * 60 + now.minute;

    return currentMinutes >= startMinutes && currentMinutes <= endMinutes;
  }

  String formatTimeRange() {
    String pad(int n) => n.toString().padLeft(2, '0');
    return '${pad(startTime.hour)}:${pad(startTime.minute)} - ${pad(endTime.hour)}:${pad(endTime.minute)}';
  }
}

class TimetableView extends StatefulWidget {
  const TimetableView({super.key});

  @override
  State<TimetableView> createState() => _TimetableViewState();
}

class _TimetableViewState extends State<TimetableView> {
  List<TimetablePeriod> _periods = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadTimetable();
  }

  TimeOfDay _parseTime(String timeStr) {
    try {
      final parts = timeStr.split(':');
      final hour = int.parse(parts[0]);
      final minute = int.parse(parts[1]);
      return TimeOfDay(hour: hour, minute: minute);
    } catch (e) {
      return const TimeOfDay(hour: 8, minute: 0);
    }
  }

  Future<void> _loadTimetable() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
    });
    try {
      final response = await apiClient.dio.get('/timetable');
      if (response.statusCode == 200 && response.data != null) {
        final list = List<dynamic>.from(response.data['data'] ?? []);
        final parsed = list.map((item) {
          return TimetablePeriod(
            title: item['subject']?['name'] ?? 'General Class',
            teacher: item['teacher']?['name'] ?? 'Instructor',
            room: item['room'] ?? 'General Room',
            startTime: _parseTime(item['starts_at'] ?? '08:00'),
            endTime: _parseTime(item['ends_at'] ?? '09:00'),
            dayOfWeek: int.tryParse(item['day_of_week']?.toString() ?? '1') ?? 1,
          );
        }).toList();

        // Filter to only show entries matching the current day of the week
        // DateTime weekday: 1 = Monday, 7 = Sunday
        final currentWeekday = DateTime.now().weekday;
        final todaysPeriods = parsed.where((p) => p.dayOfWeek == currentWeekday).toList();

        // Sort by start time
        todaysPeriods.sort((a, b) {
          final minutesA = a.startTime.hour * 60 + a.startTime.minute;
          final minutesB = b.startTime.hour * 60 + b.startTime.minute;
          return minutesA.compareTo(minutesB);
        });

        if (mounted) {
          setState(() {
            _periods = todaysPeriods;
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading timetable: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              "Today's Schedule",
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            IconButton(
              icon: const Icon(Icons.refresh, size: 18, color: AppColors.primaryBlue),
              onPressed: _loadTimetable,
              constraints: const BoxConstraints(),
              padding: EdgeInsets.zero,
            ),
          ],
        ),
        const SizedBox(height: 12),
        if (_isLoading)
          const Center(
            child: Padding(
              padding: EdgeInsets.symmetric(vertical: 24.0),
              child: CircularProgressIndicator(),
            ),
          )
        else if (_periods.isEmpty)
          const Card(
            child: Padding(
              padding: EdgeInsets.all(24.0),
              child: Center(
                child: Text(
                  'No classes scheduled for today.',
                  style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                ),
              ),
            ),
          )
        else
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _periods.length,
            itemBuilder: (context, idx) {
              final period = _periods[idx];
              final isActive = period.isActiveNow(now);

              return AnimatedContainer(
                duration: const Duration(milliseconds: 300),
                margin: const EdgeInsets.only(bottom: 10),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isActive ? AppColors.accentAmber : AppColors.divider,
                    width: isActive ? 2.0 : 1.0,
                  ),
                  boxShadow: isActive
                      ? [
                          BoxShadow(
                            color: AppColors.accentAmber.withValues(alpha: 0.15),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ]
                      : [],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Row(
                    children: [
                      // Time block
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            period.formatTimeRange(),
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: isActive ? AppColors.accentAmber : AppColors.textSecondary,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Period ${idx + 1}',
                            style: const TextStyle(fontSize: 11, color: AppColors.textDisabled),
                          ),
                        ],
                      ),
                      const SizedBox(width: 24),
                      // Class info block
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              period.title,
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                const Icon(Icons.person_outline, size: 12, color: AppColors.textSecondary),
                                const SizedBox(width: 4),
                                Flexible(
                                  child: Text(
                                    period.teacher,
                                    style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                const SizedBox(width: 12),
                                const Icon(Icons.place_outlined, size: 12, color: AppColors.textSecondary),
                                const SizedBox(width: 4),
                                Flexible(
                                  child: Text(
                                    period.room,
                                    style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      if (isActive)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.accentAmber.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Text(
                            'ACTIVE',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: AppColors.accentAmber,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              );
            },
          ),
      ],
    );
  }
}
