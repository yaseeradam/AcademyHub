import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class TeacherDashboardScreen extends StatelessWidget {
  const TeacherDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surfaceContainerLow,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Faculty Portal', style: GoogleFonts.plusJakartaSans(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 18)),
            Text('St. Peter\'s College · Staff Mode', style: GoogleFonts.plusJakartaSans(color: AppColors.onSurfaceVariant, fontSize: 11)),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_none_rounded, color: AppColors.onSurface),
            onPressed: () {},
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Welcome Card
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
                    radius: 28,
                    backgroundColor: AppColors.primary.withValues(alpha: 0.2),
                    child: const Icon(Icons.person_rounded, color: AppColors.primary, size: 30),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Welcome back, Dr. Aris', style: GoogleFonts.plusJakartaSans(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
                        Text('Head of Mathematics · Grade 10 Lead', style: GoogleFonts.plusJakartaSans(fontSize: 12, color: AppColors.onSurfaceVariant)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Quick Actions Grid
            Text('Teacher Operations', style: GoogleFonts.plusJakartaSans(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
            const SizedBox(height: 12),
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 1.5,
              children: [
                _actionCard(context, 'Scores Entry', 'Enter term grades', Icons.grid_on_rounded, AppColors.primary, '/scores-entry'),
                _actionCard(context, 'Attendance', 'Mark class roll call', Icons.fact_check_rounded, AppColors.secondary, '/classes'),
                _actionCard(context, 'Broadcast Notice', 'Send admin announcement', Icons.campaign_rounded, AppColors.tertiary, '/broadcast-creator'),
                _actionCard(context, 'Student List', 'View class directory', Icons.groups_rounded, AppColors.warningOrange, '/students'),
              ],
            ),
            const SizedBox(height: 24),

            // Today's Teaching Schedule
            Text('Today\'s Classes', style: GoogleFonts.plusJakartaSans(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
            const SizedBox(height: 12),
            _classScheduleCard('08:30 AM - 09:45 AM', 'Grade 10-A Mathematics', 'Room 302', '34 Students'),
            _classScheduleCard('10:15 AM - 11:30 AM', 'Grade 11 Physics', 'Lab 2', '28 Students'),
            _classScheduleCard('01:30 PM - 02:45 PM', 'Grade 9 Further Math', 'Room 105', '30 Students'),
          ],
        ),
      ),
    );
  }

  Widget _actionCard(BuildContext context, String title, String subtitle, IconData icon, Color color, String route) {
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

  Widget _classScheduleCard(String time, String title, String room, String count) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainer,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
            decoration: BoxDecoration(color: AppColors.surfaceContainerLow, borderRadius: BorderRadius.circular(12)),
            child: Text(time, style: GoogleFonts.plusJakartaSans(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.primary)),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.onSurface, fontSize: 14)),
                Text('$room · $count', style: GoogleFonts.plusJakartaSans(color: AppColors.onSurfaceVariant, fontSize: 12)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
