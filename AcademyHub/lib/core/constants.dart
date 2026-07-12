import 'package:flutter/material.dart';

class AppColors {
  // Theme State
  static bool isDark = false; // default is light theme

  // ── Theme colors ──────────────────────────────────────────────────────
  static const Color primary    = Color(0xFFFF2D20); // Laravel Red
  static const Color primaryDark = Color(0xFFE0241B);

  // Backgrounds
  static Color get background  => isDark ? const Color(0xFF0F172A) : const Color(0xFFF3F4F6); // Laravel body background (#0F172A vs #F3F4F6)
  static Color get surface     => isDark ? const Color(0xFF18212F) : const Color(0xFFFFFFFF); // dark card vs pure white card
  static Color get surface2    => isDark ? const Color(0xFF243042) : const Color(0xFFF1F5F9); // elevated card vs slate-100
  static Color get surface3    => isDark ? const Color(0xFF2E3E54) : const Color(0xFFE2E8F0); // hover/pressed vs slate-200

  // Text
  static Color get textPrimary   => isDark ? const Color(0xFFF8FAFC) : const Color(0xFF111827); // slate-50 vs slate-900
  static Color get textSecondary => isDark ? const Color(0xFF94A3B8) : const Color(0xFF4B5563); // gray vs slate-600 (Accents/Typography)
  static Color get textMuted     => isDark ? const Color(0xFF64748B) : const Color(0xFF9CA3AF); // slate-500 vs zinc-400

  // Borders
  static Color get borderLight      => isDark ? const Color(0xFF2D3748) : const Color(0xFFE5E7EB); // zinc-800 vs zinc-200
  static Color get borderExtraLight => isDark ? const Color(0xFF1E293B) : const Color(0xFFF3F4F6);

  // Semantic
  static const Color success = Color(0xFF3FB950); // green
  static const Color error   = Color(0xFFF85149); // red
  static const Color info    = Color(0xFF58A6FF); // blue
  static const Color warning = Color(0xFFD29922); // yellow

  // Role accent colors (aligned with Laravel portal)
  static const Color studentAccent = Color(0xFF6366F1); // indigo
  static const Color teacherAccent = Color(0xFF0EA5E9); // sky
  static const Color adminAccent   = Color(0xFF8B5CF6); // violet
  static const Color bursarAccent  = Color(0xFF10B981); // emerald
  static const Color parentAccent  = Color(0xFFEC4899); // pink

  // Layout tokens
  static const double radiusMedium = 12.0;
  static const double radiusSmall  = 8.0;
  static const double radiusLarge  = 16.0;
  static const double radiusXL     = 20.0;

  static List<BoxShadow> get subtleShadow => [
        BoxShadow(
          color: isDark ? Colors.black.withValues(alpha: 0.3) : Colors.black.withValues(alpha: 0.04), // soft Tailwind shadow
          blurRadius: 12,
          offset: const Offset(0, 4),
        ),
      ];

  static List<BoxShadow> get glowShadow => [
        BoxShadow(
          color: primary.withValues(alpha: 0.15), // soft glow
          blurRadius: 16,
          spreadRadius: 0,
          offset: const Offset(0, 4),
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
