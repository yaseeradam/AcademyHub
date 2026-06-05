import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';

class AppColors {
  static const Color primary = Color(0xFFF59E0B); // Amber 500
  static const Color primaryDark = Color(0xFFD97706); // Amber 600
  static const Color background = Color(0xFFF8FAFC); // Slate 50
  static const Color surface = Colors.white;
  static const Color textPrimary = Color(0xFF0F172A); // Slate 900
  static const Color textSecondary = Color(0xFF64748B); // Slate 500

  static const Color success = Color(0xFF10B981); // Emerald 500
  static const Color error = Color(0xFFF43F5E); // Rose 500
  static const Color info = Color(0xFF3B82F6); // Blue 500
  static const Color warning = Color(0xFFF59E0B); // Amber 500
}

class ApiConstants {
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://localhost:8000/api';
    }

    // For desktop platform (Linux, macOS, Windows) or local host development
    if (defaultTargetPlatform == TargetPlatform.linux ||
        defaultTargetPlatform == TargetPlatform.macOS ||
        defaultTargetPlatform == TargetPlatform.windows) {
      return 'http://127.0.0.1:8000/api';
    }

    // Default to Android emulator loopback redirection port 8000,
    // or change to http://10.131.201.125:8000/api for physical devices on local network
    return 'http://10.131.201.125:8000/api';
  }
}
