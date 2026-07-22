import 'package:go_router/go_router.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';

// Import shell views (to be created next)
import 'package:academyhub_app/features/auth/presentation/school_finder_screen.dart';
import 'package:academyhub_app/features/auth/presentation/login_screen.dart';
import 'package:academyhub_app/features/dashboard/presentation/dashboard_screen.dart';
import 'package:academyhub_app/features/cbt/presentation/cbt_exam_screen.dart';
import 'package:academyhub_app/features/settings/presentation/settings_screen.dart';
import 'package:academyhub_app/features/admin/presentation/broadcast_creator_screen.dart';
import 'package:academyhub_app/features/parent/presentation/fee_payments_screen.dart';
import 'package:academyhub_app/features/parent/presentation/student_report_card_screen.dart';
import 'package:academyhub_app/features/homework/presentation/homework_tracker_screen.dart';
import 'package:academyhub_app/features/timetable/presentation/student_timetable_screen.dart';

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
    GoRoute(
      path: '/cbt-exam',
      builder: (context, state) => const CbtExamScreen(),
    ),
    GoRoute(
      path: '/settings',
      builder: (context, state) => const SettingsScreen(),
    ),
    GoRoute(
      path: '/broadcast-creator',
      builder: (context, state) => const BroadcastCreatorScreen(),
    ),
    GoRoute(
      path: '/fee-payments',
      builder: (context, state) => const FeePaymentsScreen(),
    ),
    GoRoute(
      path: '/student-report-card',
      builder: (context, state) => const StudentReportCardScreen(),
    ),
    GoRoute(
      path: '/homework-tracker',
      builder: (context, state) => const HomeworkTrackerScreen(),
    ),
    GoRoute(
      path: '/student-timetable',
      builder: (context, state) => const StudentTimetableScreen(),
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
