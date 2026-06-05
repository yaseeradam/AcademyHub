import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'auth_provider.dart';
import '../features/auth/login_screen_new.dart' as login;
import '../features/auth/initial_sync_screen.dart';
import '../features/auth/tenant_selection_screen.dart';
import '../features/student/student_home.dart';
import '../features/teacher/teacher_home.dart';
import '../features/teacher/teacher_attendance_screen.dart';
import '../features/teacher/teacher_scores_screen.dart';
import '../features/teacher/teacher_homework_screen.dart';
import '../features/parent/parent_home.dart';
import '../features/admin/admin_home.dart';
import '../features/analytics/performance_analytics_screen.dart';
import '../features/admin/students_list_screen.dart';
import '../features/admin/cbt_management_screen.dart';

class AppRouter {
  static GoRouter router(AuthProvider authProvider) {
    return GoRouter(
      initialLocation: '/',
      refreshListenable: authProvider,
      redirect: (context, state) {
        if (authProvider.isLoading) return null;

        final hasTenant     = authProvider.tenantSlug != null;
        final loggedIn      = authProvider.isAuthenticated;
        final syncDone      = authProvider.initialSyncDone;
        final path          = state.uri.path;
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
      ],
    );
  }
}
