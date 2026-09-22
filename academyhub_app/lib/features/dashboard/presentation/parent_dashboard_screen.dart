import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class ParentDashboardScreen extends StatelessWidget {
  const ParentDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surfaceContainerLow,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Parent Companion Portal', style: GoogleFonts.plusJakartaSans(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 18)),
            Text('St. Peter\'s College · Guardian Mode', style: GoogleFonts.plusJakartaSans(color: AppColors.onSurfaceVariant, fontSize: 11)),
          ],
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Ward Profile Selector Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainer,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.4)),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 26,
                    backgroundColor: AppColors.secondary.withValues(alpha: 0.2),
                    child: Text('A', style: GoogleFonts.plusJakartaSans(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.secondary)),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Alexander Wright', style: GoogleFonts.plusJakartaSans(fontSize: 17, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
                        Text('Grade 10-A · Reg: STU2024001', style: GoogleFonts.plusJakartaSans(fontSize: 12, color: AppColors.onSurfaceVariant)),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.successGreen.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text('96% Attendance', style: GoogleFonts.plusJakartaSans(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.successGreen)),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Quick Portal Access Grid
            Text('Guardian Actions', style: GoogleFonts.plusJakartaSans(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
            const SizedBox(height: 12),
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 1.4,
              children: [
                _parentTile(context, 'Fee Payments', 'View balance & receipts', Icons.account_balance_wallet_rounded, AppColors.primary, '/fee-payments'),
                _parentTile(context, 'Report Cards', 'Academic results & GPA', Icons.assessment_rounded, AppColors.secondary, '/student-report-card'),
                _parentTile(context, 'Homework Tracker', 'Assigned work & status', Icons.menu_book_rounded, AppColors.tertiary, '/homework-tracker'),
                _parentTile(context, 'Class Timetable', 'Weekly schedule', Icons.schedule_rounded, AppColors.warningOrange, '/student-timetable'),
              ],
            ),
            const SizedBox(height: 24),

            // Recent School Notices
            Text('Recent Announcements', style: GoogleFonts.plusJakartaSans(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
            const SizedBox(height: 12),
            _noticeCard('PTA General Meeting', 'Scheduled for Friday 4:00 PM in the Main Auditorium.', 'Yesterday'),
            _noticeCard('Mid-Term Examination Dates', 'Exams commence on October 12th. Please review timetable.', '3 Days ago'),
          ],
        ),
      ),
    );
  }

  Widget _parentTile(BuildContext context, String title, String subtitle, IconData icon, Color color, String route) {
    return GestureDetector(
      onTap: () => context.push(route),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainer,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.4)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: color, size: 26),
            const SizedBox(height: 8),
            Text(title, style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.onSurface, fontSize: 14)),
            Text(subtitle, style: GoogleFonts.plusJakartaSans(color: AppColors.onSurfaceVariant, fontSize: 11)),
          ],
        ),
      ),
    );
  }

  Widget _noticeCard(String title, String body, String time) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainer,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(title, style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.onSurface, fontSize: 14)),
              Text(time, style: GoogleFonts.plusJakartaSans(fontSize: 11, color: AppColors.onSurfaceVariant)),
            ],
          ),
          const SizedBox(height: 4),
          Text(body, style: GoogleFonts.plusJakartaSans(fontSize: 12, color: AppColors.onSurfaceVariant)),
        ],
      ),
    );
  }
}
