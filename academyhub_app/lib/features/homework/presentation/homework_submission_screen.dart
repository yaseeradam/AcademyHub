import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class HomeworkSubmissionScreen extends StatefulWidget {
  const HomeworkSubmissionScreen({super.key});

  @override
  State<HomeworkSubmissionScreen> createState() => _HomeworkSubmissionScreenState();
}

class _HomeworkSubmissionScreenState extends State<HomeworkSubmissionScreen> {
  final _textCtrl = TextEditingController();
  bool _isSubmitting = false;

  @override
  void dispose() {
    _textCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surfaceContainerLow,
        title: Text('Homework Submission', style: GoogleFonts.plusJakartaSans(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 18)),
        leading: IconButton(
          icon: const Icon(Icons.close_rounded, color: AppColors.onSurface),
          onPressed: () => context.pop(),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Task Header Banner
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainer,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.primary.withValues(alpha: 0.3)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(color: AppColors.primary.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(10)),
                        child: Text('Mathematics', style: GoogleFonts.plusJakartaSans(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.primary)),
                      ),
                      Text('Due: Tomorrow, 11:59 PM', style: GoogleFonts.plusJakartaSans(fontSize: 11, color: AppColors.warningOrange, fontWeight: FontWeight.w600)),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Text('Quadratic Equations & Graph Plotting', style: GoogleFonts.plusJakartaSans(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
                  const SizedBox(height: 4),
                  Text('Solve Questions 1 to 15 on Page 142. Show all step-by-step working.', style: GoogleFonts.plusJakartaSans(fontSize: 13, color: AppColors.onSurfaceVariant)),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Solution Text Field
            Text('Your Answer / Solution Notes', style: GoogleFonts.plusJakartaSans(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
            const SizedBox(height: 8),
            TextField(
              controller: _textCtrl,
              maxLines: 5,
              style: GoogleFonts.plusJakartaSans(color: AppColors.onSurface, fontSize: 14),
              decoration: InputDecoration(
                hintText: 'Type your solution details or paste online document links...',
                fillColor: AppColors.surfaceContainer,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: AppColors.outlineVariant)),
              ),
            ),
            const SizedBox(height: 20),

            // File Attachment Box
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.4), style: BorderStyle.solid),
              ),
              child: Row(
                children: [
                  const Icon(Icons.cloud_upload_rounded, color: AppColors.primary, size: 28),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Attach Photo or PDF Document', style: GoogleFonts.plusJakartaSans(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
                        Text('Max file size: 20MB (.pdf, .jpg, .png)', style: GoogleFonts.plusJakartaSans(fontSize: 11, color: AppColors.onSurfaceVariant)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),

            // Submit Button
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primaryContainer,
                foregroundColor: const Color(0xFF2A1700),
                minimumSize: const Size.fromHeight(54),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
              onPressed: _isSubmitting ? null : () {
                final messenger = ScaffoldMessenger.of(context);
                final router = GoRouter.of(context);
                setState(() => _isSubmitting = true);
                Future.delayed(const Duration(milliseconds: 600), () {
                  if (mounted) {
                    messenger.showSnackBar(
                      SnackBar(
                        backgroundColor: AppColors.successGreen,
                        content: Text('Homework submitted successfully!', style: GoogleFonts.plusJakartaSans(color: Colors.white, fontWeight: FontWeight.bold)),
                      ),
                    );
                    router.pop();
                  }
                });
              },
              child: _isSubmitting
                  ? const CircularProgressIndicator(color: Color(0xFF2A1700))
                  : Text('Submit Homework Assignment', style: GoogleFonts.plusJakartaSans(fontSize: 15, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }
}
