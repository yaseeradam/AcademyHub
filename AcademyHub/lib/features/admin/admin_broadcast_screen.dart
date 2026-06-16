import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

class AdminBroadcastScreen extends StatefulWidget {
  const AdminBroadcastScreen({super.key});

  @override
  State<AdminBroadcastScreen> createState() => _AdminBroadcastScreenState();
}

class _AdminBroadcastScreenState extends State<AdminBroadcastScreen> {
  final TextEditingController _msgController = TextEditingController();
  String _selectedTarget = 'all';
  bool _sending = false;

  @override
  void dispose() {
    _msgController.dispose();
    super.dispose();
  }

  Future<void> _sendBroadcast() async {
    final message = _msgController.text.trim();
    if (message.isEmpty) {
      CustomToast.show(context: context, message: 'Please write a message to broadcast.', type: 'warning');
      return;
    }

    final auth = context.read<AuthProvider>();
    setState(() => _sending = true);

    try {
      final response = await auth.apiService.dio.post('/admin/broadcast', data: {
        'target': _selectedTarget,
        'message': message,
      });

      if (mounted) {
        setState(() => _sending = false);
        _msgController.clear();
        CustomToast.show(
          context: context,
          message: response.data['message'] ?? 'Broadcast dispatched successfully!',
          type: 'success',
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _sending = false);
        CustomToast.show(context: context, message: 'Failed to send broadcast: $e', type: 'error');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Announcement Broadcaster',
            style: GoogleFonts.spaceGrotesk(
                fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
        backgroundColor: AppColors.surface,
        elevation: 0,
        iconTheme: IconThemeData(color: AppColors.textPrimary),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.borderLight),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Dispatch Channel',
                      style: GoogleFonts.spaceGrotesk(
                          fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                  const SizedBox(height: 4),
                  Text('Send instant automated WhatsApp broadcasts to parents and staff.',
                      style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary)),
                  const SizedBox(height: 16),
                  Text('Recipient Target Audience',
                      style: GoogleFonts.spaceGrotesk(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
                  const SizedBox(height: 8),
                  DropdownButtonFormField<String>(
                    initialValue: _selectedTarget,
                    dropdownColor: AppColors.surface,
                    style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary),
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: AppColors.surface2,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    ),
                    items: const [
                      DropdownMenuItem(value: 'all', child: Text('All Subscribed (Parents & Staff)')),
                      DropdownMenuItem(value: 'parents', child: Text('Parents Only')),
                      DropdownMenuItem(value: 'staff', child: Text('Staff Only')),
                    ],
                    onChanged: (val) {
                      if (val != null) {
                        setState(() => _selectedTarget = val);
                      }
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            Text('Broadcast Message Body',
                style: GoogleFonts.spaceGrotesk(
                    fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
            const SizedBox(height: 8),
            TextField(
              controller: _msgController,
              maxLines: 6,
              style: GoogleFonts.spaceGrotesk(fontSize: 13, color: AppColors.textPrimary),
              decoration: InputDecoration(
                hintText: 'Type broadcast message text here...',
                hintStyle: GoogleFonts.spaceGrotesk(color: AppColors.textMuted),
                filled: true,
                fillColor: AppColors.surface,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: AppColors.borderLight),
                ),
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: _sending ? null : _sendBroadcast,
                style: ElevatedButton.styleFrom(
                  backgroundColor: primary,
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                icon: const Icon(Icons.campaign),
                label: _sending
                    ? const CircularProgressIndicator(color: Colors.black)
                    : Text('Send Broadcast Notice',
                        style: GoogleFonts.spaceGrotesk(
                            fontWeight: FontWeight.bold, fontSize: 13)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
