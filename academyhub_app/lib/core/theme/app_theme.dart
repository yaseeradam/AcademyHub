import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

class AppColors {
  // ── Obsidian Theme Palette ─────────────────────────────
  static const Color background              = Color(0xFF0B1326);
  static const Color surface                 = Color(0xFF0B1326);
  static const Color surfaceContainerLow     = Color(0xFF131B2E);
  static const Color surfaceContainer        = Color(0xFF171F33);
  static const Color surfaceContainerHigh    = Color(0xFF222A3D);
  static const Color surfaceContainerHighest = Color(0xFF2D3449);

  // ── Brand Accents ──────────────────────────────────────
  static const Color primary                 = Color(0xFFFFC174); // Tech Gold
  static const Color primaryContainer        = Color(0xFFF59E0B); // Deep Amber
  static const Color secondary               = Color(0xFFADC6FF); // Ice Blue
  static const Color secondaryContainer      = Color(0xFF0566D9); // Royal Blue
  static const Color tertiary                = Color(0xFFD8C3FF); // Lavender

  // ── Text & Content ─────────────────────────────────────
  static const Color onSurface               = Color(0xFFDAE2FD);
  static const Color onSurfaceVariant        = Color(0xFFD8C3AD);
  static const Color textPrimary             = Color(0xFFDAE2FD);
  static const Color textSecondary           = Color(0xFFD8C3AD);
  static const Color textDisabled            = Color(0xFF534434);

  // ── Borders & Outlines ──────────────────────────────────
  static const Color outline                 = Color(0xFFA08E7A);
  static const Color outlineVariant          = Color(0xFF534434);
  static const Color divider                 = Color(0xFF222A3D);
  static const Color inputFill               = Color(0xFF131B2E);

  // ── Status & Feedback ───────────────────────────────────
  static const Color error                   = Color(0xFFFFB4AB);
  static const Color errorContainer          = Color(0xFF93000A);
  static const Color successGreen            = Color(0xFF22C55E);
  static const Color warningOrange           = Color(0xFFF97316);
  static const Color dangerRed               = Color(0xFFEF4444);
  static const Color infoBlue                = Color(0xFF3B82F6);

  // ── Legacy Aliases (For Backward Compatibility) ────────
  static const Color primaryBlue    = Color(0xFF171F33);
  static const Color softBlue       = Color(0xFFADC6FF);
  static const Color accentAmber    = Color(0xFFFFC174);
  static const Color amberPrimary   = Color(0xFFFFC174);
  static const Color amberDark      = Color(0xFFF59E0B);
  static const Color appBackground  = Color(0xFF0B1326);
  static const Color cardSurface    = Color(0xFF171F33);

  // ── Role Accent Colors ──────────────────────────────────
  static const Color roleStudent    = Color(0xFFADC6FF);
  static const Color roleParent     = Color(0xFFD8C3FF);
  static const Color roleStaff      = Color(0xFFFFC174);
  static const Color roleAdmin      = Color(0xFFF59E0B);

  static Color rolePrimary(String role) {
    switch (role.toLowerCase()) {
      case 'parent':  return roleParent;
      case 'staff':
      case 'teacher': return roleStaff;
      case 'admin':   return roleAdmin;
      default:        return roleStudent;
    }
  }

  static Color role3DShadowColor(String role) {
    switch (role.toLowerCase()) {
      case 'parent':  return const Color(0xFF5B21B6);
      case 'staff':
      case 'teacher': return const Color(0xFF92400E);
      case 'admin':   return const Color(0xFF020617);
      default:        return const Color(0xFF1E40AF);
    }
  }

  static String roleQuote(String role) {
    switch (role.toLowerCase()) {
      case 'parent':  return 'Together, we nurture growth, every day.';
      case 'staff':
      case 'teacher': return 'Lead with vision, manage with purpose.';
      case 'admin':   return 'Excellence in leadership & operational governance.';
      default:        return 'Learn today, lead tomorrow.';
    }
  }

  static String roleDesc(String role) {
    switch (role.toLowerCase()) {
      case 'parent':  return 'Stay connected with your child\'s academic journey.';
      case 'staff':
      case 'teacher': return 'Manage student performance, grades & attendance.';
      case 'admin':   return 'System metrics, staff directory & broadcasts.';
      default:        return 'Access exams, assignments & your schedule.';
    }
  }

  // ── Soft 3D Decorations ──────────────────────────────────
  static BoxDecoration soft3DCardDecoration({
    Color? color,
    double borderRadius = 22.0,
    Color? borderColor,
  }) {
    return BoxDecoration(
      color: color ?? surfaceContainer,
      borderRadius: BorderRadius.circular(borderRadius),
      border: Border.all(color: borderColor ?? outlineVariant.withValues(alpha: 0.3)),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withValues(alpha: 0.25),
          blurRadius: 16,
          spreadRadius: -2,
          offset: const Offset(0, 8),
        ),
        BoxShadow(
          color: Colors.white.withValues(alpha: 0.05),
          blurRadius: 2,
          offset: const Offset(0, 1),
        ),
      ],
    );
  }

  static BoxDecoration soft3DButtonDecoration({
    required Color color,
    required Color shadowColor,
    double borderRadius = 16.0,
  }) {
    return BoxDecoration(
      color: color,
      borderRadius: BorderRadius.circular(borderRadius),
      boxShadow: [
        BoxShadow(
          color: shadowColor.withValues(alpha: 0.4),
          blurRadius: 12,
          offset: const Offset(0, 6),
        ),
      ],
    );
  }
}

class AppTheme {
  static ThemeData get lightTheme => obsidianDarkTheme;

  static ThemeData get obsidianDarkTheme {
    final textTheme = GoogleFonts.plusJakartaSansTextTheme();

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      scaffoldBackgroundColor: AppColors.background,
      primaryColor: AppColors.primary,

      colorScheme: const ColorScheme.dark(
        primary: AppColors.primary,
        onPrimary: Color(0xFF472A00),
        primaryContainer: AppColors.primaryContainer,
        onPrimaryContainer: Color(0xFF613B00),
        secondary: AppColors.secondary,
        onSecondary: Color(0xFF002E6A),
        secondaryContainer: AppColors.secondaryContainer,
        onSecondaryContainer: Color(0xFFE6ECFF),
        tertiary: AppColors.tertiary,
        surface: AppColors.surface,
        onSurface: AppColors.onSurface,
        onSurfaceVariant: AppColors.onSurfaceVariant,
        outline: AppColors.outline,
        outlineVariant: AppColors.outlineVariant,
        error: AppColors.error,
        errorContainer: AppColors.errorContainer,
      ),

      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.surfaceContainerLow,
        foregroundColor: AppColors.onSurface,
        elevation: 0,
        centerTitle: false,
        systemOverlayStyle: SystemUiOverlayStyle.light,
        titleTextStyle: GoogleFonts.plusJakartaSans(
          fontSize: 18,
          fontWeight: FontWeight.w700,
          color: AppColors.onSurface,
        ),
        iconTheme: const IconThemeData(color: AppColors.onSurface),
      ),

      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: AppColors.surfaceContainerLow,
        selectedItemColor: AppColors.primary,
        unselectedItemColor: AppColors.onSurfaceVariant,
        elevation: 12,
        type: BottomNavigationBarType.fixed,
        selectedLabelStyle: TextStyle(fontWeight: FontWeight.w700, fontSize: 12),
        unselectedLabelStyle: TextStyle(fontWeight: FontWeight.w500, fontSize: 12),
      ),

      cardTheme: CardThemeData(
        color: AppColors.surfaceContainer,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: const BorderSide(color: Color(0x1AFFFFFF)),
        ),
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
        contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.outlineVariant),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.outlineVariant),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.primary, width: 2),
        ),
        hintStyle: GoogleFonts.plusJakartaSans(color: AppColors.onSurfaceVariant, fontSize: 14),
        labelStyle: GoogleFonts.plusJakartaSans(color: AppColors.onSurface, fontSize: 14),
      ),

      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primaryContainer,
          foregroundColor: const Color(0xFF2A1700),
          elevation: 0,
          minimumSize: const Size.fromHeight(52),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          textStyle: GoogleFonts.plusJakartaSans(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.2,
          ),
        ),
      ),

      textTheme: textTheme.copyWith(
        displayLarge: GoogleFonts.plusJakartaSans(fontSize: 32, fontWeight: FontWeight.w800, color: AppColors.primary, letterSpacing: -0.5),
        titleLarge:   GoogleFonts.plusJakartaSans(fontSize: 22, fontWeight: FontWeight.w700, color: AppColors.onSurface),
        titleMedium:  GoogleFonts.plusJakartaSans(fontSize: 17, fontWeight: FontWeight.w600, color: AppColors.onSurface),
        titleSmall:   GoogleFonts.plusJakartaSans(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.onSurface),
        bodyLarge:    GoogleFonts.plusJakartaSans(fontSize: 15, fontWeight: FontWeight.w400, color: AppColors.onSurface, height: 1.5),
        bodyMedium:   GoogleFonts.plusJakartaSans(fontSize: 13, fontWeight: FontWeight.w400, color: AppColors.onSurfaceVariant),
        labelSmall:   GoogleFonts.plusJakartaSans(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.onSurfaceVariant),
      ),
    );
  }
}

