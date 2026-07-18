import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class HomeworkItem {
  final String subject;
  final String title;
  final String description;
  final DateTime dueDate;
  final String status; // 'pending', 'submitted'

  const HomeworkItem({
    required this.subject,
    required this.title,
    required this.description,
    required this.dueDate,
    required this.status,
  });
}

class HomeworkView extends StatelessWidget {
  final List<HomeworkItem> assignments = [
    HomeworkItem(
      subject: 'Mathematics',
      title: 'Quadratic Equations Worksheet',
      description: 'Solve questions 1 through 10 on page 42 of the textbook. Show all workings clearly on a paper sheet.',
      dueDate: DateTime.now().add(const Duration(days: 2)),
      status: 'pending',
    ),
    HomeworkItem(
      subject: 'English Lit.',
      title: 'Shakespearian Essay Outline',
      description: 'Create an outline summarizing the main themes in Macbeth Act 1. Minimum 300 words.',
      dueDate: DateTime.now().add(const Duration(days: 4)),
      status: 'submitted',
    ),
    HomeworkItem(
      subject: 'Chemistry',
      title: 'Stoichiometry Questions',
      description: 'Complete the stoichiometry exercise sheet uploaded in the resource portal.',
      dueDate: DateTime.now().add(const Duration(days: 1)),
      status: 'pending',
    ),
  ];

  HomeworkView({super.key});

  void _showHomeworkDetails(BuildContext context, HomeworkItem item) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        final isPending = item.status == 'pending';
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
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    item.subject,
                    style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.softBlue, fontSize: 13),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: (isPending ? AppColors.accentAmber : AppColors.successGreen).withOpacity(0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      item.status.toUpperCase(),
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: isPending ? AppColors.accentAmber : AppColors.successGreen,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                item.title,
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 16),
              const Text(
                'Instructions',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 6),
              Text(
                item.description,
                style: const TextStyle(color: AppColors.textSecondary, fontSize: 13, height: 1.4),
              ),
              const SizedBox(height: 24),
              if (isPending)
                ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('✓ Homework submitted successfully!'),
                        backgroundColor: AppColors.successGreen,
                      ),
                    );
                  },
                  child: const Text('Submit Assignment'),
                ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: assignments.length,
      itemBuilder: (context, idx) {
        final item = assignments[idx];
        final isPending = item.status == 'pending';
        final diff = item.dueDate.difference(DateTime.now()).inDays;

        return Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: (isPending ? AppColors.accentAmber : AppColors.successGreen).withOpacity(0.12),
              child: Icon(
                isPending ? Icons.pending_actions : Icons.check_circle,
                color: isPending ? AppColors.accentAmber : AppColors.successGreen,
              ),
            ),
            title: Text(
              item.title,
              style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.subject,
                  style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                ),
                const SizedBox(height: 4),
                Text(
                  'Due in $diff days',
                  style: const TextStyle(color: AppColors.textDisabled, fontSize: 11),
                ),
              ],
            ),
            trailing: const Icon(Icons.arrow_forward_ios, size: 14),
            onTap: () => _showHomeworkDetails(context, item),
          ),
        );
      },
    );
  }
}
