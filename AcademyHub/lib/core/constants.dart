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
      return 'http://localhost/api';
    }
    // Point directly to your computer's wireless IP on your active local network
    // This allows BOTH physical Android devices and Emulators to connect successfully!
    return 'http://192.168.43.92/api';
  }
}
