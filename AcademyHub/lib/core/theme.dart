import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'constants.dart';

class AppTheme {
  static ThemeData themeData(Brightness brightness, [Color? customPrimary]) {
    final primaryColor = customPrimary ?? AppColors.primary;
    final isDark = brightness == Brightness.dark;

    return ThemeData(
      useMaterial3: true,
      brightness: brightness,
      colorScheme: isDark
          ? ColorScheme.dark(
              primary: primaryColor,
              onPrimary: Colors.black,
              secondary: AppColors.studentAccent,
              surface: AppColors.surface,
              onSurface: AppColors.textPrimary,
              error: AppColors.error,
              outline: AppColors.borderLight,
            )
          : ColorScheme.light(
              primary: primaryColor,
              onPrimary: Colors.white,
              secondary: AppColors.studentAccent,
              surface: AppColors.surface,
              onSurface: AppColors.textPrimary,
              error: AppColors.error,
              outline: AppColors.borderLight,
            ),
      scaffoldBackgroundColor: AppColors.background,
      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.surface,
        elevation: 0,
        centerTitle: false,
        shadowColor: Colors.transparent,
        surfaceTintColor: Colors.transparent,
        systemOverlayStyle: SystemUiOverlayStyle(
          statusBarColor: Colors.transparent,
          statusBarIconBrightness: isDark ? Brightness.light : Brightness.dark,
        ),
        iconTheme: IconThemeData(color: AppColors.textPrimary),
        titleTextStyle: GoogleFonts.inter(
          color: AppColors.textPrimary,
          fontSize: 18,
          fontWeight: FontWeight.w900,
          letterSpacing: -0.3,
        ),
      ),
      textTheme: GoogleFonts.interTextTheme().copyWith(
        displayLarge: GoogleFonts.inter(
            color: AppColors.textPrimary, fontWeight: FontWeight.bold),
        titleLarge: GoogleFonts.inter(
            color: AppColors.textPrimary, fontWeight: FontWeight.bold),
        bodyLarge: GoogleFonts.inter(color: AppColors.textPrimary),
        bodyMedium: GoogleFonts.inter(color: AppColors.textSecondary),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryColor,
          foregroundColor: isDark ? Colors.black : Colors.white,
          elevation: 0,
          shadowColor: Colors.transparent,
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 24),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(AppColors.radiusMedium)),
          textStyle: GoogleFonts.inter(
              fontWeight: FontWeight.w800, fontSize: 15, letterSpacing: 0.1),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: primaryColor,
          side: BorderSide(color: primaryColor.withValues(alpha: 0.4)),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(AppColors.radiusMedium)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: isDark ? AppColors.surface2 : const Color(0xFFF8FAFC),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: AppColors.borderLight),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: AppColors.borderLight),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: primaryColor, width: 1.8),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: AppColors.error, width: 1.2),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: AppColors.error, width: 1.8),
        ),
        hintStyle: GoogleFonts.inter(color: AppColors.textMuted, fontSize: 13, fontWeight: FontWeight.w400),
        labelStyle: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 13),
        prefixIconColor: WidgetStateColor.resolveWith((states) =>
          states.contains(WidgetState.focused) ? primaryColor : AppColors.textSecondary),
        suffixIconColor: WidgetStateColor.resolveWith((states) =>
          states.contains(WidgetState.focused) ? primaryColor : AppColors.textSecondary),
      ),
      cardTheme: CardThemeData(
        color: AppColors.surface,
        elevation: 0,
        shadowColor: Colors.transparent,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(AppColors.radiusLarge)),
          side: BorderSide(color: AppColors.borderLight),
        ),
      ),
      dividerTheme: DividerThemeData(
        color: AppColors.borderLight,
        thickness: 0.8,
      ),
      iconTheme: IconThemeData(color: AppColors.textSecondary),
      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        backgroundColor: AppColors.surface,
        selectedItemColor: primaryColor,
        unselectedItemColor: AppColors.textSecondary,
        elevation: 0,
        type: BottomNavigationBarType.fixed,
        selectedLabelStyle: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 10),
        unselectedLabelStyle: GoogleFonts.inter(fontWeight: FontWeight.w500, fontSize: 10),
      ),
      progressIndicatorTheme: ProgressIndicatorThemeData(
        color: primaryColor,
        linearTrackColor: AppColors.borderLight,
      ),
      tabBarTheme: TabBarThemeData(
        labelColor: primaryColor,
        unselectedLabelColor: AppColors.textSecondary,
        indicatorColor: primaryColor,
        dividerColor: AppColors.borderLight,
        labelStyle: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 12),
        unselectedLabelStyle: GoogleFonts.inter(fontWeight: FontWeight.w500, fontSize: 12),
        indicatorSize: TabBarIndicatorSize.label,
      ),
      chipTheme: ChipThemeData(
        backgroundColor: AppColors.surface2,
        labelStyle: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 11),
        side: BorderSide(color: AppColors.borderLight),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: AppColors.surface,
        elevation: 0,
        shadowColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppColors.radiusXL)),
        titleTextStyle: GoogleFonts.inter(
            color: AppColors.textPrimary, fontSize: 18, fontWeight: FontWeight.w900),
        contentTextStyle: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 14),
      ),
    );
  }

  static ThemeData darkTheme([Color? customPrimary]) => themeData(Brightness.dark, customPrimary);
  static ThemeData lightTheme([Color? customPrimary]) => themeData(Brightness.light, customPrimary);
}
