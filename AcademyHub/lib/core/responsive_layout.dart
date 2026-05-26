import 'package:flutter/material.dart';

class ResponsiveBreakpoints {
  static const double mobile = 640;
  static const double tablet = 768;
  static const double desktop = 1024;
  static const double largeDesktop = 1280;
}

class ResponsiveLayout extends StatelessWidget {
  final Widget mobile;
  final Widget? tablet;
  final Widget desktop;

  const ResponsiveLayout({
    super.key,
    required this.mobile,
    this.tablet,
    required this.desktop,
  });

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        if (constraints.maxWidth >= ResponsiveBreakpoints.desktop) {
          return desktop;
        } else if (constraints.maxWidth >= ResponsiveBreakpoints.tablet) {
          return tablet ?? mobile;
        } else {
          return mobile;
        }
      },
    );
  }
}

class ResponsiveHelper {
  static bool isMobile(BuildContext context) {
    return MediaQuery.of(context).size.width < ResponsiveBreakpoints.tablet;
  }

  static bool isTablet(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    return width >= ResponsiveBreakpoints.tablet && width < ResponsiveBreakpoints.desktop;
  }

  static bool isDesktop(BuildContext context) {
    return MediaQuery.of(context).size.width >= ResponsiveBreakpoints.desktop;
  }

  static double getContentPadding(BuildContext context) {
    if (isMobile(context)) return 16.0;
    if (isTablet(context)) return 24.0;
    return 32.0;
  }

  static int getGridColumns(BuildContext context) {
    if (isMobile(context)) return 1;
    if (isTablet(context)) return 2;
    return 3;
  }

  static double getCardWidth(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    if (isMobile(context)) return width - 32;
    if (isTablet(context)) return (width - 64) / 2;
    return (width - 96) / 3;
  }
}