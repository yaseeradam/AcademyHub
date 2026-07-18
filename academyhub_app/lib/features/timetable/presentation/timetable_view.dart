import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class TimetablePeriod {
  final String title;
  final String teacher;
  final String room;
  final TimeOfDay startTime;
  final TimeOfDay endTime;

  const TimetablePeriod({
    required this.title,
    required this.teacher,
    required this.room,
    required this.startTime,
    required this.endTime,
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

class TimetableView extends StatelessWidget {
  final List<TimetablePeriod> periods = const [
    TimetablePeriod(
      title: 'Mathematics',
      teacher: 'Mr. Benson',
      room: 'Room 102',
      startTime: TimeOfDay(hour: 8, minute: 0),
      endTime: TimeOfDay(hour: 9, minute: 0),
    ),
    TimetablePeriod(
      title: 'English Lit.',
      teacher: 'Mrs. Davis',
      room: 'Room 204',
      startTime: TimeOfDay(hour: 9, minute: 0),
      endTime: TimeOfDay(hour: 10, minute: 0),
    ),
    TimetablePeriod(
      title: 'Break',
      teacher: 'Recess',
      room: 'Cafeteria',
      startTime: TimeOfDay(hour: 10, minute: 0),
      endTime: TimeOfDay(hour: 10, minute: 30),
    ),
    TimetablePeriod(
      title: 'Chemistry',
      teacher: 'Dr. Alabi',
      room: 'Science Lab',
      startTime: TimeOfDay(hour: 10, minute: 30),
      endTime: TimeOfDay(hour: 11, minute: 30),
    ),
    TimetablePeriod(
      title: 'History',
      teacher: 'Mr. Greenwood',
      room: 'Room 105',
      startTime: TimeOfDay(hour: 11, minute: 30),
      endTime: TimeOfDay(hour: 12, minute: 30),
    ),
  ];

  const TimetableView({super.key});

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          "Today's Schedule",
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        ),
        const SizedBox(height: 12),
        ListView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: periods.length,
          itemBuilder: (context, idx) {
            final period = periods[idx];
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
                          color: AppColors.accentAmber.withOpacity(0.15),
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
                              Text(
                                period.teacher,
                                style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                              ),
                              const SizedBox(width: 12),
                              const Icon(Icons.place_outlined, size: 12, color: AppColors.textSecondary),
                              const SizedBox(width: 4),
                              Text(
                                period.room,
                                style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
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
                          color: AppColors.accentAmber.withOpacity(0.12),
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
