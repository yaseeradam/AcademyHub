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
  // Laravel API Base URL
  // For Flutter Web (Chrome): Use full URL with port
  static const String baseUrl = 'http://localhost:8000/api';
  
  // Alternative URLs for different setups:
  // For mobile development: 'http://127.0.0.1:8000/api'
  // For physical device on same network: 'http://192.168.1.5:8000/api' (replace with your IP)
  // For Android emulator: 'http://10.0.2.2:8000/api'
  // For iOS simulator: 'http://127.0.0.1:8000/api'
  
  // Note: Flutter Web requires 'localhost' instead of '127.0.0.1' for CORS
}
