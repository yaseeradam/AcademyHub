import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';

class BroadcastCreatorScreen extends StatefulWidget {
  const BroadcastCreatorScreen({super.key});

  @override
  State<BroadcastCreatorScreen> createState() => _BroadcastCreatorScreenState();
}

class _BroadcastCreatorScreenState extends State<BroadcastCreatorScreen> {
  bool _isLoading = false;
  String _selectedTarget = 'parents'; // 'parents', 'staff', 'all'
  final TextEditingController _titleController = TextEditingController();
  final TextEditingController _contentController = TextEditingController();
  
  // Delivery channels states
  bool _channelPush = true;
  bool _channelWhatsapp = true;

  int _characterCount = 0;

  @override
  void initState() {
    super.initState();
    _contentController.addListener(() {
      setState(() {
        _characterCount = _contentController.text.length;
      });
    });
  }

  @override
  void dispose() {
    _titleController.dispose();
    _contentController.dispose();
    super.dispose();
  }

  Future<void> _publishAnnouncement() async {
    final title = _titleController.text.trim();
    final body = _contentController.text.trim();

    if (title.isEmpty || body.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in both the Title and Message Content.'), backgroundColor: AppColors.dangerRed),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final response = await apiClient.dio.post(
        '/admin/broadcast',
        data: {
          'title': title,
          'target': _selectedTarget,
          'message': body,
          'channel_push': _channelPush,
          'channel_whatsapp': _channelWhatsapp,
        },
      );

      if (response.statusCode == 200 && response.data != null) {
        final serverMsg = response.data['message'] ?? 'Broadcast dispatched successfully!';
        if (mounted) {
          showDialog(
            context: context,
            builder: (context) => AlertDialog(
              title: const Row(
                children: [
                  Icon(Icons.check_circle, color: AppColors.successGreen),
                  SizedBox(width: 8),
                  Text('Broadcast Published'),
                ],
              ),
              content: Text(serverMsg),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.pop(context); // Close dialog
                    Navigator.pop(context); // Go back to previous screen
                  },
                  child: const Text('OK'),
                )
              ],
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: const Text('Failed to send broadcast. Please try again.'), backgroundColor: AppColors.dangerRed),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Widget _buildChannelItem(IconData icon, String label, bool value, ValueChanged<bool?> onChanged) {
    const violetAccent = Color(0xFF7C3AED);
    return Card(
      color: value ? violetAccent.withValues(alpha: 0.06) : Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
          color: value ? violetAccent : const Color(0xFFE2E8F0),
          width: value ? 1.5 : 1.0,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 20, color: value ? violetAccent : const Color(0xFF64748B)),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: value ? FontWeight.bold : FontWeight.normal,
                color: value ? violetAccent : const Color(0xFF64748B),
              ),
            ),
            Checkbox(
              value: value,
              onChanged: onChanged,
              activeColor: violetAccent,
              materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    const violetAccent = Color(0xFF7C3AED);

    return Scaffold(
      backgroundColor: AppColors.appBackground,
      appBar: AppBar(
        backgroundColor: AppColors.rolePrimary('admin'),
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
        title: const Text('New Broadcast Announcement', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Recipient Card
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'SEND TO',
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 1.0),
                          ),
                          const SizedBox(height: 10),
                          DropdownButtonFormField<String>(
                            initialValue: _selectedTarget,
                            decoration: InputDecoration(
                              fillColor: AppColors.appBackground,
                              filled: true,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            items: const [
                              DropdownMenuItem(value: 'parents', child: Text('All Parents')),
                              DropdownMenuItem(value: 'staff', child: Text('All Teachers & Staff')),
                              DropdownMenuItem(value: 'all', child: Text('All Users (Broadcast)')),
                            ],
                            onChanged: (val) {
                              if (val != null) {
                                setState(() {
                                  _selectedTarget = val;
                                });
                              }
                            },
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Delivery Channels
                  const Text(
                    'DELIVERY CHANNELS',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 1.0),
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    children: [
                      _buildChannelItem(Icons.notifications_active_outlined, 'Push Notification', _channelPush, (val) {
                        setState(() {
                          _channelPush = val ?? false;
                        });
                      }),
                      _buildChannelItem(Icons.chat_bubble_outline, 'WhatsApp Bot', _channelWhatsapp, (val) {
                        setState(() {
                          _channelWhatsapp = val ?? false;
                        });
                      }),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // Message Composer Card
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const Text(
                            'TITLE / SUBJECT',
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 1.0),
                          ),
                          const SizedBox(height: 8),
                          TextField(
                            controller: _titleController,
                            decoration: InputDecoration(
                              hintText: 'e.g. Urgent: Parent-Teacher Meeting Postponed',
                              fillColor: AppColors.appBackground,
                              filled: true,
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                          const SizedBox(height: 20),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'MESSAGE CONTENT',
                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 1.0),
                              ),
                              Text(
                                '$_characterCount / 5000',
                                style: TextStyle(fontSize: 11, color: _characterCount > 4800 ? AppColors.dangerRed : AppColors.textDisabled),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          TextField(
                            controller: _contentController,
                            maxLines: 8,
                            maxLength: 5000,
                            buildCounter: (context, {required currentLength, required isFocused, maxLength}) => const SizedBox.shrink(),
                            decoration: InputDecoration(
                              hintText: 'Type your announcement details here...',
                              fillColor: AppColors.appBackground,
                              filled: true,
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Send Button
                  Container(
                    height: 52,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color: violetAccent.withValues(alpha: 0.35),
                          blurRadius: 16,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: violetAccent,
                        foregroundColor: Colors.white,
                        elevation: 0,
                      ),
                      onPressed: _isLoading ? null : _publishAnnouncement,
                      icon: const Icon(Icons.send),
                      label: const Text('PUBLISH ANNOUNCEMENT', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
