import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/mobile_layout.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';

class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return MobileLayout(
      title: 'Dashboard',
      child: const DashboardContent(),
    );
  }
}

class DashboardContent extends StatelessWidget {
  const DashboardContent({super.key});

  @override
  Widget build(BuildContext context) {
    final auth    = context.watch<AuthProvider>();
    final user    = auth.user;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Welcome hero
          GlassHeroCard(
            gradientColors: [
              const Color(0xFF6366F1),
              const Color(0xFF8B5CF6),
            ],
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Welcome back, ${user?.name ?? 'User'}!',
                  style: GoogleFonts.inter(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    (user?.role ?? 'user').toUpperCase(),
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: Colors.white70,
                    ),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'AcademyHub Dashboard',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    color: Colors.white70,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          Text(
            'Quick Overview',
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 12),

          GridView.count(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisCount: 2,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 1.2,
            children: [
              DarkStatCard(
                label: 'Students',
                value: '0',
                icon: Icons.people_rounded,
                color: AppColors.info,
              ),
              DarkStatCard(
                label: 'Classes',
                value: '0',
                icon: Icons.class_rounded,
                color: AppColors.success,
              ),
              DarkStatCard(
                label: 'Subjects',
                value: '0',
                icon: Icons.book_rounded,
                color: AppColors.primary,
              ),
              DarkStatCard(
                label: 'Teachers',
                value: '0',
                icon: Icons.school_rounded,
                color: AppColors.error,
              ),
            ],
          ),

          const SizedBox(height: 24),

          Text(
            'Recent Activity',
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 12),

          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.borderLight),
            ),
            child: Column(
              children: [
                Icon(Icons.inbox_outlined,
                    size: 40, color: AppColors.textMuted),
                const SizedBox(height: 12),
                Text(
                  'No recent activity',
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Activity will appear here as you use the system',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: AppColors.textMuted,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}