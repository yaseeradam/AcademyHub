import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';
import 'package:academyhub_app/features/students/presentation/student_detail_screen.dart';

class ChildrenView extends StatefulWidget {
  final Function(int id, String name) onViewResults;
  final VoidCallback onMessageTeacher;
  final bool isMessagingEnabled;

  const ChildrenView({
    super.key,
    required this.onViewResults,
    required this.onMessageTeacher,
    this.isMessagingEnabled = true,
  });

  @override
  State<ChildrenView> createState() => _ChildrenViewState();
}

class _ChildrenViewState extends State<ChildrenView> {
  List<dynamic> _children = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadChildren();
  }

  Future<void> _loadChildren() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
    });
    try {
      final response = await apiClient.dio.get('/students');
      if (response.statusCode == 200 && response.data != null) {
        // Paginated list -> 'data' key contains the array
        final list = List<dynamic>.from(response.data['data'] ?? []);
        if (mounted) {
          setState(() {
            _children = list;
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading parent children: $e');
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
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_children.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24.0),
          child: Text(
            'No children profiles linked to your account.',
            style: TextStyle(color: AppColors.textSecondary, fontSize: 15),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _children.length,
      itemBuilder: (context, idx) {
        final child = _children[idx];
        final firstName = child['first_name'] ?? '';
        final lastName = child['last_name'] ?? '';
        final fullName = '$firstName $lastName'.trim();
        final className = child['school_class']?['name'] ?? 'Unassigned';
        final sectionName = child['section']?['name'] ?? '';
        final displayClass = sectionName.isNotEmpty ? '$className ($sectionName)' : className;

        String initials = '';
        if (firstName.isNotEmpty) initials += firstName[0].toUpperCase();
        if (lastName.isNotEmpty) initials += lastName[0].toUpperCase();
        if (initials.isEmpty) initials = '?';

        return Card(
          clipBehavior: Clip.antiAlias,
          child: InkWell(
            onTap: () {
              if (child['id'] != null) {
                StudentDetailScreen.show(context, studentId: child['id'], studentName: fullName);
              }
            },
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
                        initials,
                        style: const TextStyle(color: AppColors.primaryBlue, fontWeight: FontWeight.bold),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            fullName,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary),
                          ),
                          Text(
                            'Class: $displayClass',
                            style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                          ),
                        ],
                      ),
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
                        onPressed: () => widget.onViewResults(child['id'], fullName),
                        child: const Text('View Results', style: TextStyle(color: AppColors.primaryBlue, fontSize: 13)),
                      ),
                    ),
                    if (widget.isMessagingEnabled) ...[
                      const SizedBox(width: 8),
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primaryBlue,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            padding: const EdgeInsets.symmetric(vertical: 10),
                          ),
                          onPressed: widget.onMessageTeacher,
                          child: const Text('Message Teacher', style: TextStyle(fontSize: 13)),
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ),
        ),
      );
    },
    );
  }
}
