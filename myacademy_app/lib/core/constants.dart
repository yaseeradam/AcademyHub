import 'package:flutter/material.dart';

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
  // Use 10.0.2.2 for Android emulator interacting with localhost on host machine
  // Or the machine's IP address if testing on physical device
  static const String baseUrl = 'http://10.0.2.2:8000/api';
}
