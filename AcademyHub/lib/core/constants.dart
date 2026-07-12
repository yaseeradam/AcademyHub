import 'package:flutter/material.dart';

class AppColors {
  // Theme State
  static bool isDark = false; // default is light theme

  // ── Theme colors ──────────────────────────────────────────────────────
  static const Color primary    = Color(0xFFFF2D20); // Laravel Red
  static const Color primaryDark = Color(0xFFE0241B); // Laravel Red Dark

  // Backgrounds — warm white like Laravel's bg-slate-50/amber-50 tint
  static Color get background  => isDark ? const Color(0xFF0D1117) : const Color(0xFFFAFAFC);
  static Color get surface     => isDark ? const Color(0xFF161B22) : const Color(0xFFFFFFFF);
  static Color get surface2    => isDark ? const Color(0xFF21262D) : const Color(0xFFF8FAFC); // slate-50
  static Color get surface3    => isDark ? const Color(0xFF30363D) : const Color(0xFFF1F5F9); // slate-100

  // Text
  static Color get textPrimary   => isDark ? const Color(0xFFF0F6FC) : const Color(0xFF0F172A); // slate-900
  static Color get textSecondary => isDark ? const Color(0xFF8B949E) : const Color(0xFF475569); // slate-600
  static Color get textMuted     => isDark ? const Color(0xFF484F58) : const Color(0xFF94A3B8); // slate-400

  // Borders — softer, like Laravel ring-slate-100
  static Color get borderLight      => isDark ? const Color(0xFF30363D) : const Color(0xFFE8EDF2);
  static Color get borderExtraLight => isDark ? const Color(0xFF21262D) : const Color(0xFFF1F5F9);

  // Semantic
  static const Color success = Color(0xFF10B981); // emerald-500
  static const Color error   = Color(0xFFEF4444); // red-500
  static const Color info    = Color(0xFF3B82F6); // blue-500
  static const Color warning = Color(0xFFF59E0B); // amber-500

  // Role accent colors (aligned with Laravel portal)
  static const Color studentAccent = Color(0xFF6366F1); // indigo-500
  static const Color teacherAccent = Color(0xFF0EA5E9); // sky-500
  static const Color adminAccent   = Color(0xFF8B5CF6); // violet-500
  static const Color bursarAccent  = Color(0xFF10B981); // emerald-500
  static const Color parentAccent  = Color(0xFFEC4899); // pink-500

  // Layout tokens — rounder like Laravel rounded-2xl
  static const double radiusMedium = 14.0;
  static const double radiusSmall  = 10.0;
  static const double radiusLarge  = 18.0;
  static const double radiusXL     = 24.0;

  // Soft shadow matching Laravel shadow-md
  static List<BoxShadow> get subtleShadow => [
        BoxShadow(
          color: isDark
              ? Colors.black.withValues(alpha: 0.25)
              : const Color(0xFF0F172A).withValues(alpha: 0.06),
          blurRadius: 16,
          spreadRadius: 0,
          offset: const Offset(0, 4),
        ),
        BoxShadow(
          color: isDark
              ? Colors.black.withValues(alpha: 0.1)
              : const Color(0xFF0F172A).withValues(alpha: 0.03),
          blurRadius: 4,
          spreadRadius: 0,
          offset: const Offset(0, 1),
        ),
      ];

  static List<BoxShadow> get glowShadow => [
        BoxShadow(
          color: primary.withValues(alpha: 0.25),
          blurRadius: 20,
          spreadRadius: 0,
          offset: const Offset(0, 6),
        ),
      ];
}

class ApiConstants {
  static String get baseUrl {
    // Production VPS endpoint (SSL secured)
    return 'https://academyhub.com.ng/api';
    
    // Fallback Production VPS raw IP (in case SSL is not yet active)
    // return 'http://150.75.248.123/api';

    /* Local development fallback:
    if (kIsWeb) {
      return 'http://localhost:8000/api';
    }

    if (defaultTargetPlatform == TargetPlatform.linux ||
        defaultTargetPlatform == TargetPlatform.macOS ||
        defaultTargetPlatform == TargetPlatform.windows) {
      return 'http://10.142.155.125:8000/api';
    }

    return 'http://10.142.155.125:8000/api';
    */
  }
}
