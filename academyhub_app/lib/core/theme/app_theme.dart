import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

class AppColors {
  // ── Primary Brand ──────────────────────────────────────
  static const Color primaryBlue    = Color(0xFF1E3A5F);
  static const Color softBlue       = Color(0xFF2D6A9F);
  static const Color accentAmber    = Color(0xFFF59E0B);
  static const Color amberPrimary   = Color(0xFFF59E0B);
  static const Color amberDark      = Color(0xFFD97706);

  // ── Status ─────────────────────────────────────────────
  static const Color successGreen   = Color(0xFF22C55E);
  static const Color warningOrange  = Color(0xFFF97316);
  static const Color dangerRed      = Color(0xFFEF4444);
  static const Color infoBlue       = Color(0xFF3B82F6);

  // ── Surface ────────────────────────────────────────────
  static const Color appBackground  = Color(0xFFF5F7FB);
  static const Color cardSurface    = Color(0xFFFFFFFF);
  static const Color surface        = Color(0xFFFFFFFF);
  static const Color divider        = Color(0xFFEDF0F7);
  static const Color inputFill      = Color(0xFFF3F6FA);
  static const Color white          = Color(0xFFFFFFFF);

  // ── Text ───────────────────────────────────────────────
  static const Color textPrimary    = Color(0xFF1A2B45);
  static const Color textSecondary  = Color(0xFF6B7A99);
  static const Color textDisabled   = Color(0xFFB0BAD0);

  // ── Role Colors ────────────────────────────────────────
  static const Color roleStudent    = Color(0xFF1D4ED8); // Solid Deep Blue
  static const Color roleParent     = Color(0xFF7C3AED); // Solid Rich Purple
  static const Color roleStaff      = Color(0xFFD97706); // Solid Warm Amber
  static const Color roleAdmin      = Color(0xFF0F172A); // Solid Dark Slate

  static Color rolePrimary(String role) {
    switch (role) {
      case 'parent':            return roleParent;
      case 'staff':
      case 'teacher':           return roleStaff;
      case 'admin':             return roleAdmin;
      default:                  return roleStudent;
    }
  }

  static Color role3DShadowColor(String role) {
    switch (role) {
      case 'parent':            return const Color(0xFF5B21B6); // Deep Violet 3D Base
      case 'staff':
      case 'teacher':           return const Color(0xFF92400E); // Deep Amber 3D Base
      case 'admin':             return const Color(0xFF020617); // Darkest Slate 3D Base
      default:                  return const Color(0xFF1E40AF); // Deep Blue 3D Base
    }
  }

  // Solid 3D Color palette (No gradients)
  static List<Color> roleGradient(String role) {
    final color = rolePrimary(role);
    return [color, color];
  }

  static Color roleHeaderColor(String role) => roleGradient(role).first;

  // Role quote — mirrors the web login
  static String roleQuote(String role) {
    switch (role) {
      case 'parent':  return 'Together, we nurture growth, every day.';
      case 'staff':
      case 'teacher': return 'Lead with vision, manage with purpose.';
      default:        return 'Learn today, lead tomorrow.';
    }
  }

  static String roleDesc(String role) {
    switch (role) {
      case 'parent':  return 'Stay connected with your child\'s education.';
      case 'staff':
      case 'teacher': return 'Manage your school with efficiency.';
      default:        return 'Continue your learning journey.';
    }
  }
}

class AppTheme {
  static ThemeData get lightTheme {
    final base = GoogleFonts.interTextTheme();

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      primaryColor: AppColors.primaryBlue,
      scaffoldBackgroundColor: AppColors.appBackground,

      colorScheme: const ColorScheme.light(
        primary:     AppColors.primaryBlue,
        secondary:   AppColors.softBlue,
        tertiary:    AppColors.successGreen,
        surface:     AppColors.white,
        error:       AppColors.dangerRed,
        onPrimary:   AppColors.white,
        onSecondary: AppColors.white,
        onSurface:   AppColors.textPrimary,
      ),

      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.white,
        foregroundColor: AppColors.textPrimary,
        elevation: 0,
        shadowColor: AppColors.divider,
        centerTitle: false,
        systemOverlayStyle: SystemUiOverlayStyle.dark,
        titleTextStyle: GoogleFonts.inter(
          fontSize: 17,
          fontWeight: FontWeight.w700,
          color: AppColors.textPrimary,
        ),
        iconTheme: const IconThemeData(color: AppColors.textPrimary),
      ),

      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: AppColors.white,
        selectedItemColor: AppColors.primaryBlue,
        unselectedItemColor: AppColors.textDisabled,
        elevation: 8,
        type: BottomNavigationBarType.fixed,
        selectedLabelStyle: TextStyle(fontWeight: FontWeight.w700, fontSize: 11),
        unselectedLabelStyle: TextStyle(fontWeight: FontWeight.w500, fontSize: 11),
      ),

      cardTheme: CardThemeData(
        color: AppColors.white,
        elevation: 0,
        shadowColor: Color(0x0C1E3A5F),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        margin: const EdgeInsets.only(bottom: 12),
      ),

      dividerTheme: const DividerThemeData(
        color: AppColors.divider,
        thickness: 1,
        space: 0,
      ),

      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.inputFill,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: AppColors.divider),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: AppColors.primaryBlue, width: 1.5),
        ),
        hintStyle: GoogleFonts.inter(color: AppColors.textDisabled, fontSize: 14),
        labelStyle: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 14),
      ),

      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primaryBlue,
          foregroundColor: AppColors.white,
          elevation: 0,
          minimumSize: const Size.fromHeight(52),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          textStyle: GoogleFonts.inter(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.3,
          ),
        ),
      ),

      textTheme: base.copyWith(
        displayLarge: GoogleFonts.inter(fontSize: 28, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
        titleLarge:   GoogleFonts.inter(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        titleMedium:  GoogleFonts.inter(fontSize: 17, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        titleSmall:   GoogleFonts.inter(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
        bodyLarge:    GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w400, color: AppColors.textPrimary, height: 1.5),
        bodyMedium:   GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.textSecondary),
        labelSmall:   GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.w400, color: AppColors.textSecondary),
      ),
    );
  }
}
