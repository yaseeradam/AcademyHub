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
        backgroundColor: AppColors.background,
        elevation: 0,
        centerTitle: false,
        systemOverlayStyle: SystemUiOverlayStyle(
          statusBarColor: Colors.transparent,
          statusBarIconBrightness: isDark ? Brightness.light : Brightness.dark,
        ),
        iconTheme: IconThemeData(color: AppColors.textPrimary),
        titleTextStyle: GoogleFonts.inter(
          color: AppColors.textPrimary,
          fontSize: 20,
          fontWeight: FontWeight.bold,
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
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 24),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(AppColors.radiusMedium)),
          textStyle: GoogleFonts.inter(
              fontWeight: FontWeight.bold, fontSize: 15),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: primaryColor,
          side: BorderSide(color: primaryColor.withValues(alpha: 0.5)),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(AppColors.radiusSmall)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.surface2,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppColors.radiusSmall),
          borderSide: BorderSide(color: AppColors.borderLight),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppColors.radiusSmall),
          borderSide: BorderSide(color: AppColors.borderLight),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppColors.radiusSmall),
          borderSide: BorderSide(color: primaryColor, width: 2),
        ),
        hintStyle: GoogleFonts.inter(color: AppColors.textMuted),
        labelStyle: GoogleFonts.inter(color: AppColors.textSecondary),
      ),
      cardTheme: CardThemeData(
        color: AppColors.surface,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: const BorderRadius.all(Radius.circular(AppColors.radiusMedium)),
          side: BorderSide(color: AppColors.borderLight),
        ),
      ),
      dividerTheme: DividerThemeData(
        color: AppColors.borderLight,
        thickness: 1,
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
        linearTrackColor: AppColors.surface2,
      ),
      tabBarTheme: TabBarThemeData(
        labelColor: primaryColor,
        unselectedLabelColor: AppColors.textSecondary,
        indicatorColor: primaryColor,
        dividerColor: AppColors.borderLight,
        labelStyle: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 11),
        unselectedLabelStyle: GoogleFonts.inter(fontWeight: FontWeight.w500, fontSize: 11),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: AppColors.surface2,
        labelStyle: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 11),
        side: BorderSide(color: AppColors.borderLight),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppColors.radiusLarge)),
        titleTextStyle: GoogleFonts.inter(
            color: AppColors.textPrimary, fontSize: 18, fontWeight: FontWeight.bold),
        contentTextStyle: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 14),
      ),
    );
  }

  static ThemeData darkTheme([Color? customPrimary]) => themeData(Brightness.dark, customPrimary);
  static ThemeData lightTheme([Color? customPrimary]) => themeData(Brightness.light, customPrimary);
}
