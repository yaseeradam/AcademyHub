import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';

class AnnouncementCreateDialog extends StatefulWidget {
  const AnnouncementCreateDialog({super.key});

  @override
  State<AnnouncementCreateDialog> createState() => _AnnouncementCreateDialogState();
}

class _AnnouncementCreateDialogState extends State<AnnouncementCreateDialog> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _bodyController = TextEditingController();
  String _selectedAudience = 'all';
  bool _submitting = false;

  @override
  void dispose() {
    _titleController.dispose();
    _bodyController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _submitting = true);

    try {
      final auth = context.read<AuthProvider>();
      
      final payload = {
        'title': _titleController.text.trim(),
        'body': _bodyController.text.trim(),
        'audience': _selectedAudience,
      };

      // Use ApiService offline queue mechanism
      final online = await auth.apiService.queueableMutation('/announcements', 'POST', payload);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(online 
              ? 'Announcement published successfully!' 
              : 'Offline: Announcement queued for sync.'
            ),
            backgroundColor: online ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
          ),
        );
        Navigator.of(context).pop(true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to publish: ${e.toString()}')),
        );
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;

    return AlertDialog(
      title: const Text('Create Announcement', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
      content: Form(
        key: _formKey,
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextFormField(
                controller: _titleController,
                decoration: const InputDecoration(
                  labelText: 'Title',
                  border: OutlineInputBorder(),
                ),
                validator: (val) => val == null || val.trim().isEmpty ? 'Title is required' : null,
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: _selectedAudience,
                decoration: const InputDecoration(
                  labelText: 'Target Audience',
                  border: OutlineInputBorder(),
                ),
                items: const [
                  DropdownMenuItem(value: 'all', child: Text('All')),
                  DropdownMenuItem(value: 'parents', child: Text('Parents Only')),
                  DropdownMenuItem(value: 'students', child: Text('Students Only')),
                  DropdownMenuItem(value: 'staff', child: Text('Staff Only')),
                ],
                onChanged: (val) {
                  if (val != null) {
                    setState(() => _selectedAudience = val);
                  }
                },
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _bodyController,
                decoration: const InputDecoration(
                  labelText: 'Announcement Details',
                  border: OutlineInputBorder(),
                  alignLabelWithHint: true,
                ),
                maxLines: 4,
                validator: (val) => val == null || val.trim().isEmpty ? 'Body is required' : null,
              ),
            ],
          ),
        ),
      ),
      actions: [
        TextButton(
          onPressed: _submitting ? null : () => Navigator.of(context).pop(),
          child: const Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: _submitting ? null : _submit,
          style: ElevatedButton.styleFrom(backgroundColor: primary),
          child: _submitting
              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
              : const Text('Publish', style: TextStyle(color: Colors.white)),
        ),
      ],
    );
  }
}
