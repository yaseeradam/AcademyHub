import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';

class HomeworkView extends StatefulWidget {
  const HomeworkView({super.key});

  @override
  State<HomeworkView> createState() => _HomeworkViewState();
}

class _HomeworkViewState extends State<HomeworkView> {
  List<dynamic> _assignments = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadHomework();
  }

  Future<void> _loadHomework() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
    });
    try {
      final response = await apiClient.dio.get('/student/homework');
      if (response.statusCode == 200 && response.data != null) {
        final list = List<dynamic>.from(response.data['data'] ?? []);
        if (mounted) {
          setState(() {
            _assignments = list;
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading homework: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _submitAssignment(int homeworkId, String submissionText) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator()),
    );
    try {
      final response = await apiClient.dio.post(
        '/homework/$homeworkId/submit',
        data: {'submission': submissionText},
      );
      if (mounted) {
        Navigator.pop(context); // Close loading dialog
      }
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('✓ Assignment submitted successfully!'),
              backgroundColor: AppColors.successGreen,
            ),
          );
        }
        _loadHomework(); // Reload list to update status
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to submit assignment: $e'),
            backgroundColor: AppColors.dangerRed,
          ),
        );
      }
    }
  }

  void _showSubmitDialog(BuildContext context, int homeworkId) {
    final controller = TextEditingController();
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Submit Assignment'),
          content: TextField(
            controller: controller,
            maxLines: 4,
            decoration: const InputDecoration(
              hintText: 'Enter your assignment answers or notes here...',
              border: OutlineInputBorder(),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              onPressed: () {
                final text = controller.text.trim();
                if (text.isEmpty) return;
                Navigator.pop(context);
                _submitAssignment(homeworkId, text);
              },
              child: const Text('Submit'),
            ),
          ],
        );
      },
    );
  }

  void _showHomeworkDetails(BuildContext context, dynamic item) {
    final submissions = List<dynamic>.from(item['submissions'] ?? []);
    final isSubmitted = submissions.isNotEmpty;
    final subjectName = item['subject']?['name'] ?? 'General';
    final teacherName = item['teacher']?['name'] ?? 'Teacher';
    final title = item['title'] ?? '';
    final content = item['content'] ?? '';

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
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '$subjectName · By $teacherName',
                    style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.softBlue, fontSize: 13),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: (!isSubmitted ? AppColors.accentAmber : AppColors.successGreen).withOpacity(0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      isSubmitted ? 'SUBMITTED' : 'PENDING',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: !isSubmitted ? AppColors.accentAmber : AppColors.successGreen,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                title,
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 16),
              const Text(
                'Instructions',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 6),
              Text(
                content,
                style: const TextStyle(color: AppColors.textSecondary, fontSize: 13, height: 1.4),
              ),
              const SizedBox(height: 24),
              if (!isSubmitted)
                ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _showSubmitDialog(context, item['id']);
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
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_assignments.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24.0),
          child: Text(
            'No homework assigned to your class yet.',
            style: TextStyle(color: AppColors.textSecondary, fontSize: 15),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _assignments.length,
      itemBuilder: (context, idx) {
        final item = _assignments[idx];
        final submissions = List<dynamic>.from(item['submissions'] ?? []);
        final isSubmitted = submissions.isNotEmpty;
        final subjectName = item['subject']?['name'] ?? 'General';
        final title = item['title'] ?? '';
        
        DateTime? dueDate;
        if (item['due_date'] != null) {
          dueDate = DateTime.tryParse(item['due_date'].toString());
        }

        String dueText = '';
        if (dueDate != null) {
          final diff = dueDate.difference(DateTime.now()).inDays;
          if (diff < 0) {
            dueText = 'Overdue by ${diff.abs()} days';
          } else {
            dueText = 'Due in $diff days';
          }
        }

        return Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: (!isSubmitted ? AppColors.accentAmber : AppColors.successGreen).withOpacity(0.12),
              child: Icon(
                !isSubmitted ? Icons.pending_actions : Icons.check_circle,
                color: !isSubmitted ? AppColors.accentAmber : AppColors.successGreen,
              ),
            ),
            title: Text(
              title,
              style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  subjectName,
                  style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                ),
                if (dueText.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    dueText,
                    style: TextStyle(
                      color: !isSubmitted && dueDate != null && dueDate.isBefore(DateTime.now()) 
                        ? AppColors.dangerRed 
                        : AppColors.textDisabled, 
                      fontSize: 11,
                      fontWeight: !isSubmitted && dueDate != null && dueDate.isBefore(DateTime.now())
                        ? FontWeight.bold
                        : FontWeight.normal,
                    ),
                  ),
                ],
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
