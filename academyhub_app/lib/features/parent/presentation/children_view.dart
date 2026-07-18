import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class ChildItem {
  final int id;
  final String name;
  final String className;
  final String attendanceRate;
  final String teacherName;

  const ChildItem({
    required this.id,
    required this.name,
    required this.className,
    required this.attendanceRate,
    required this.teacherName,
  });
}

class ChildrenView extends StatelessWidget {
  final List<ChildItem> childrenList = const [
    ChildItem(
      id: 1,
      name: 'David Hassan',
      className: 'Grade 10A',
      attendanceRate: '96.4%',
      teacherName: 'Mr. Benson',
    ),
    ChildItem(
      id: 2,
      name: 'Sarah Hassan',
      className: 'Grade 8B',
      attendanceRate: '98.1%',
      teacherName: 'Mrs. Fowler',
    ),
  ];

  final VoidCallback onViewResults;
  final VoidCallback onMessageTeacher;
  final bool isMessagingEnabled;

  const ChildrenView({
    super.key,
    required this.onViewResults,
    required this.onMessageTeacher,
    this.isMessagingEnabled = true,
  });

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: childrenList.length,
      itemBuilder: (context, idx) {
        final child = childrenList[idx];
        return Card(
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    CircleAvatar(
                      radius: 24,
                      backgroundColor: AppColors.primaryBlue.withOpacity(0.12),
                      child: Text(
                        child.name.substring(0, 1) + child.name.split(' ')[1].substring(0, 1),
                        style: const TextStyle(color: AppColors.primaryBlue, fontWeight: FontWeight.bold),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            child.name,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary),
                          ),
                          Text(
                            'Class: ${child.className} · Teacher: ${child.teacherName}',
                            style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Attendance Rate',
                      style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                    ),
                    Text(
                      child.attendanceRate,
                      style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.successGreen, fontSize: 14),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                const Divider(),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        style: OutlinedButton.styleFrom(
                          side: const BorderSide(color: AppColors.primaryBlue),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          padding: const EdgeInsets.symmetric(vertical: 10),
                        ),
                        onPressed: onViewResults,
                        child: const Text('View Results', style: TextStyle(color: AppColors.primaryBlue, fontSize: 13)),
                      ),
                    ),
                    if (isMessagingEnabled) ...[
                      const SizedBox(width: 8),
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primaryBlue,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            padding: const EdgeInsets.symmetric(vertical: 10),
                          ),
                          onPressed: onMessageTeacher,
                          child: const Text('Message Teacher', style: TextStyle(fontSize: 13)),
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
