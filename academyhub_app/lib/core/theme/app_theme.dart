import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppColors {
  // ── Brand ──────────────────────────────────────────────
  static const Color amberPrimary  = Color(0xFFF59E0B);
  static const Color amberDark     = Color(0xFFD97706);
  static const Color amberLight    = Color(0xFFFEF3C7);

  // ── Slate ──────────────────────────────────────────────
  static const Color slateDark     = Color(0xFF0F172A);
  static const Color slate900      = Color(0xFF1E293B);
  static const Color slate800      = Color(0xFF334155);
  static const Color slate600      = Color(0xFF475569);
  static const Color slate400      = Color(0xFF94A3B8);
  static const Color slate100      = Color(0xFFF1F5F9);

  // ── Status ─────────────────────────────────────────────
  static const Color successGreen  = Color(0xFF10B981);
  static const Color dangerRed     = Color(0xFFF43F5E);
  static const Color warningOrange = Color(0xFFF97316);
  static const Color infoBlue      = Color(0xFF3B82F6);

  // ── Surface ────────────────────────────────────────────
  static const Color white         = Color(0xFFFFFFFF);
  static const Color cardSurface   = Color(0xFFFFFFFF);
  static const Color appBackground = Color(0xFFF1F5F9);
  static const Color inputFill     = Color(0xFFF1F5F9);
  static const Color divider       = Color(0xFFE2E8F0);

  // ── Text ───────────────────────────────────────────────
  static const Color textPrimary   = Color(0xFF0F172A);
  static const Color textSecondary = Color(0xFF64748B);
  static const Color textDisabled  = Color(0xFF94A3B8);

  // ── Legacy aliases (keep so existing code compiles) ────
  static const Color primaryBlue   = slate900;
  static const Color softBlue      = slate800;
  static const Color accentAmber   = amberPrimary;

  // ── Role gradient end colours ──────────────────────────
  static const Color studentEnd    = Color(0xFF2D6A9F);
  static const Color parentEnd     = Color(0xFF6B4FA0);
  static const Color teacherEnd    = Color(0xFF0F766E);
  static const Color adminEnd      = Color(0xFFB45309);
}

class AppTheme {
  static ThemeData get lightTheme {
    final base = GoogleFonts.spaceGroteskTextTheme();

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      primaryColor: AppColors.amberPrimary,
      scaffoldBackgroundColor: AppColors.slate100,

      colorScheme: const ColorScheme.light(
        primary:    AppColors.amberPrimary,
        secondary:  AppColors.slate900,
        tertiary:   AppColors.successGreen,
        surface:    AppColors.white,
        error:      AppColors.dangerRed,
        onPrimary:  AppColors.white,
        onSecondary: AppColors.white,
        onSurface:  AppColors.textPrimary,
      ),

      // ── AppBar ──────────────────────────────────────────
      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.slate900,
        foregroundColor: AppColors.white,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: GoogleFonts.spaceGrotesk(
          fontSize: 17,
          fontWeight: FontWeight.w700,
          color: AppColors.white,
        ),
        iconTheme: const IconThemeData(color: AppColors.white),
      ),

      // ── Bottom Nav ──────────────────────────────────────
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: AppColors.slate900,
        selectedItemColor: AppColors.amberPrimary,
        unselectedItemColor: AppColors.slate400,
        elevation: 0,
        type: BottomNavigationBarType.fixed,
        selectedLabelStyle: TextStyle(fontWeight: FontWeight.w700, fontSize: 11),
        unselectedLabelStyle: TextStyle(fontWeight: FontWeight.w500, fontSize: 11),
      ),

      // ── Cards ───────────────────────────────────────────
      // ── Cards ───────────────────────────────────────────
      cardTheme: CardThemeData(
        color: AppColors.white,
        elevation: 2,
        shadowColor: Colors.black.withOpacity(0.08),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        margin: const EdgeInsets.only(bottom: 10),
      ),

      // ── Divider ─────────────────────────────────────────
      dividerTheme: const DividerThemeData(
        color: AppColors.divider,
        thickness: 1,
        space: 0,
      ),

      // ── Inputs ──────────────────────────────────────────
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.inputFill,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.amberPrimary, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.dangerRed, width: 1.5),
        ),
        hintStyle: GoogleFonts.spaceGrotesk(color: AppColors.textDisabled, fontSize: 14),
        labelStyle: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 14),
      ),

      // ── Elevated Button ─────────────────────────────────
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.amberPrimary,
          foregroundColor: AppColors.white,
          elevation: 4,
          shadowColor: AppColors.amberPrimary.withOpacity(0.35),
          minimumSize: const Size.fromHeight(52),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          textStyle: GoogleFonts.spaceGrotesk(
            fontSize: 15,
            fontWeight: FontWeight.bold,
            letterSpacing: 0.5,
          ),
        ),
      ),

      // ── Text ────────────────────────────────────────────
      textTheme: base.copyWith(
        displayLarge: GoogleFonts.spaceGrotesk(fontSize: 28, fontWeight: FontWeight.w800, color: AppColors.slate900),
        titleLarge:   GoogleFonts.spaceGrotesk(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.slate900),
        titleMedium:  GoogleFonts.spaceGrotesk(fontSize: 17, fontWeight: FontWeight.bold, color: AppColors.slate900),
        titleSmall:   GoogleFonts.spaceGrotesk(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.slate900),
        bodyLarge:    GoogleFonts.spaceGrotesk(fontSize: 14, fontWeight: FontWeight.w400, color: AppColors.slate900, height: 1.5),
        bodyMedium:   GoogleFonts.spaceGrotesk(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.slate900),
        labelSmall:   GoogleFonts.spaceGrotesk(fontSize: 11, fontWeight: FontWeight.w400, color: AppColors.slate900),
      ),
    );
  }
}
