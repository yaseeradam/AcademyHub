import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';

// Import shell views (to be created next)
import 'package:academyhub_app/features/auth/presentation/school_finder_screen.dart';
import 'package:academyhub_app/features/auth/presentation/login_screen.dart';
import 'package:academyhub_app/features/dashboard/presentation/dashboard_screen.dart';

final GoRouter appRouter = GoRouter(
  initialLocation: '/',
  routes: [
    GoRoute(
      path: '/',
      builder: (context, state) => const SchoolFinderScreen(),
    ),
    GoRoute(
      path: '/login',
      builder: (context, state) => const LoginScreen(),
    ),
    GoRoute(
      path: '/dashboard',
      builder: (context, state) => const DashboardScreen(),
    ),
  ],
  redirect: (context, state) async {
    final token = await SecureStorage.instance.getToken();
    final slug = await SecureStorage.instance.getSchoolSlug();
    
    final loggingIn = state.matchedLocation == '/login' || state.matchedLocation == '/';

    // If no school slug resolved yet, force user to School Finder screen
    if (slug == null) {
      return '/';
    }

    // If no auth token, redirect to login page
    if (token == null) {
      return '/login';
    }

    // If logged in and on auth screens, route directly to dashboard
    if (loggingIn) {
      return '/dashboard';
    }

    return null;
  },
);
