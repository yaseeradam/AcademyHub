import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class LuminaAcademicScreen extends StatelessWidget {
  const LuminaAcademicScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surfaceContainerLow,
        title: Text(
          'Lumina Academic Analytics',
          style: GoogleFonts.plusJakartaSans(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 18),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: AppColors.onSurface),
          onPressed: () => context.pop(),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Overall Performance Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppColors.surfaceContainerHigh, AppColors.surfaceContainer],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: AppColors.primary.withValues(alpha: 0.3)),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('CURRENT CUMULATIVE GPA', style: GoogleFonts.plusJakartaSans(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.primary, letterSpacing: 1.0)),
                        const SizedBox(height: 6),
                        Text('3.84 / 4.0', style: GoogleFonts.plusJakartaSans(fontSize: 30, fontWeight: FontWeight.w900, color: AppColors.onSurface)),
                        const SizedBox(height: 4),
                        Text('Top 5% in Grade 10 · First Class Distinction', style: GoogleFonts.plusJakartaSans(fontSize: 12, color: AppColors.successGreen, fontWeight: FontWeight.w600)),
                      ],
                    ),
                  ),
                  Container(
                    width: 64,
                    height: 64,
                    decoration: BoxDecoration(
                      color: AppColors.primary.withValues(alpha: 0.15),
                      shape: BoxShape.circle,
                      border: Border.all(color: AppColors.primary.withValues(alpha: 0.4)),
                    ),
                    child: const Icon(Icons.auto_graph_rounded, color: AppColors.primary, size: 32),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            Text('Subject Proficiency Distribution', style: GoogleFonts.plusJakartaSans(fontSize: 17, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
            const SizedBox(height: 12),

            // Subject Bar Progress List
            _subjectProgressItem('Mathematics', 92, 'A+', AppColors.primary),
            _subjectProgressItem('Physics', 88, 'A', AppColors.secondary),
            _subjectProgressItem('English Literature', 84, 'A', AppColors.tertiary),
            _subjectProgressItem('Chemistry', 76, 'B+', AppColors.warningOrange),
            _subjectProgressItem('Computer Science', 95, 'A+', AppColors.successGreen),

            const SizedBox(height: 24),

            // Academic Insights Banner
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.4)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.lightbulb_rounded, color: AppColors.primary, size: 28),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Lumina AI Recommendation', style: GoogleFonts.plusJakartaSans(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.onSurface)),
                        const SizedBox(height: 2),
                        Text('Strong analytical skills in CS & Math. Spend 20 mins extra reviewing Organic Chemistry before Mid-Terms.', style: GoogleFonts.plusJakartaSans(fontSize: 12, color: AppColors.onSurfaceVariant)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _subjectProgressItem(String name, int score, String grade, Color color) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
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
              Text(name, style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.onSurface, fontSize: 14)),
              Row(
                children: [
                  Text('$score%', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w800, color: color, fontSize: 14)),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(color: color.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(8)),
                    child: Text(grade, style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: color, fontSize: 11)),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: score / 100,
              minHeight: 8,
              backgroundColor: AppColors.surfaceContainerLow,
              valueColor: AlwaysStoppedAnimation<Color>(color),
            ),
          ),
        ],
      ),
    );
  }
}
