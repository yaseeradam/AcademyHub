import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'auth_provider.dart';
import '../features/auth/login_screen_new.dart' as login;
import '../features/auth/initial_sync_screen.dart';
import '../features/auth/tenant_selection_screen.dart';
import '../features/splash/splash_screen.dart';
import '../features/student/student_home.dart';
import '../features/student/student_attendance_screen.dart';
import '../features/teacher/teacher_home.dart';
import '../features/teacher/teacher_attendance_screen.dart';
import '../features/teacher/teacher_scores_screen.dart';
import '../features/teacher/teacher_homework_screen.dart';
import '../features/parent/parent_home.dart';
import '../features/admin/admin_home.dart';
import '../features/analytics/performance_analytics_screen.dart';
import '../features/analytics/analytics_dashboard_screen.dart';
import '../features/admin/students_list_screen.dart';
import '../features/admin/cbt_management_screen.dart';
import '../features/parent/parent_attendance_screen.dart';
import '../features/chat/chat_screen.dart';
import '../features/notifications/notifications_screen.dart';
import '../features/teacher/csv_scores_importer.dart';
import '../features/admin/admin_sessions_screen.dart';
import '../features/admin/admin_users_screen.dart';
import '../features/admin/admin_backups_screen.dart';
import '../features/admin/admin_broadcast_screen.dart';

class AppRouter {
  static GoRouter router(AuthProvider authProvider) {
    return GoRouter(
      initialLocation: '/splash',
      refreshListenable: authProvider,
      redirect: (context, state) {
        // While auth is initializing, hold everything at splash
        if (authProvider.isLoading) {
          return state.uri.path == '/splash' ? null : '/splash';
        }

        final path          = state.uri.path;
        final goingToSplash = path == '/splash';

        // Redirect away from splash now that loading is done
        if (goingToSplash) {
          if (!authProvider.hasTenant) return '/tenant-select';
          if (!authProvider.isAuthenticated) return '/login';
          return authProvider.initialSyncDone ? '/' : '/sync';
        }

        final hasTenant     = authProvider.tenantSlug != null;
        final loggedIn      = authProvider.isAuthenticated;
        final syncDone      = authProvider.initialSyncDone;
        final goingToTenant = path == '/tenant-select';
        final goingToLogin  = path == '/login';
        final goingToSync   = path == '/sync';

        if (!hasTenant && !goingToTenant) return '/tenant-select';
        if (hasTenant && goingToTenant) return '/login';

        if (hasTenant && !loggedIn && !goingToLogin) return '/login';
        if (loggedIn  && goingToLogin)  return syncDone ? '/' : '/sync';

        if (loggedIn && !syncDone && !goingToSync) return '/sync';

        return null;
      },
      routes: [
        GoRoute(
          path: '/splash',
          builder: (_, _) => const SplashScreen(),
        ),
        GoRoute(
          path: '/tenant-select',
          builder: (_, _) => const TenantSelectionScreen(),
        ),
        GoRoute(
          path: '/login',
          builder: (_, _) => const login.LoginScreen(),
        ),
        GoRoute(
          path: '/sync',
          builder: (_, _) => const InitialSyncScreen(),
        ),
        GoRoute(
          path: '/',
          builder: (context, state) {
            if (authProvider.isLoading) {
              return const Scaffold(body: Center(child: CircularProgressIndicator()));
            }
            return switch (authProvider.user?.role) {
              'student' => const StudentHome(),
              'teacher' => const TeacherHome(),
              'parent'  => const ParentHome(),
              'admin'   => const AdminHome(),
              'bursar'  => const AdminHome(),
              _         => const Scaffold(body: Center(child: Text('Unknown role'))),
            };
          },
        ),
        GoRoute(
          path: '/attendance',
          builder: (_, _) => const TeacherAttendanceScreen(),
        ),
        GoRoute(
          path: '/scores',
          builder: (_, _) => const TeacherScoresScreen(),
        ),
        GoRoute(
          path: '/homework',
          builder: (_, _) => const TeacherHomeworkScreen(),
        ),
        GoRoute(
          path: '/students',
          builder: (_, _) => const StudentsListScreen(),
        ),
        GoRoute(
          path: '/cbt',
          builder: (_, _) => const CbtManagementScreen(),
        ),
        GoRoute(
          path: '/performance',
          builder: (context, state) {
            final extra = state.extra as Map<String, dynamic>?;
            return PerformanceAnalyticsScreen(
              studentId: extra?['studentId'] as int?,
              studentName: extra?['studentName'] as String?,
              admissionNumber: extra?['admissionNumber'] as String?,
            );
          },
        ),
        GoRoute(
          path: '/parent-attendance',
          builder: (_, _) => const ParentAttendanceScreen(),
        ),
        GoRoute(
          path: '/student-attendance',
          builder: (_, _) => const StudentAttendanceScreen(),
        ),
        GoRoute(
          path: '/chat',
          builder: (_, _) => const ChatScreen(),
        ),
        GoRoute(
          path: '/notifications',
          builder: (_, _) => const NotificationsScreen(),
        ),
        GoRoute(
          path: '/csv-import',
          builder: (context, state) {
            final extra = state.extra as Map<String, dynamic>;
            return CSVScoresImporter(
              classId: extra['classId'] as int,
              subjectId: extra['subjectId'] as int,
              className: extra['className'] as String,
              subjectName: extra['subjectName'] as String,
            );
          },
        ),
        GoRoute(
          path: '/admin-sessions',
          builder: (_, _) => const AdminSessionsScreen(),
        ),
        GoRoute(
          path: '/admin-users',
          builder: (_, _) => const AdminUsersScreen(),
        ),
        GoRoute(
          path: '/admin-backups',
          builder: (_, _) => const AdminBackupsScreen(),
        ),
        GoRoute(
          path: '/admin-broadcast',
          builder: (_, _) => const AdminBroadcastScreen(),
        ),
        GoRoute(
          path: '/analytics-dashboard',
          builder: (_, _) => const AnalyticsDashboardScreen(),
        ),
      ],
    );
  }
}
