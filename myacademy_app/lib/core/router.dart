import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'auth_provider.dart';
import '../features/auth/login_screen.dart';
import '../features/auth/initial_sync_screen.dart';
import '../features/student/student_home.dart';
import '../features/teacher/teacher_home.dart';
import '../features/teacher/teacher_attendance_screen.dart';
import '../features/teacher/teacher_scores_screen.dart';
import '../features/teacher/teacher_homework_screen.dart';
import '../features/parent/parent_home.dart';
import '../features/admin/admin_home.dart';

class AppRouter {
  static GoRouter router(AuthProvider authProvider) {
    return GoRouter(
      initialLocation: '/',
      refreshListenable: authProvider,
      redirect: (context, state) {
        if (authProvider.isLoading) return null;

        final loggedIn      = authProvider.isAuthenticated;
        final syncDone      = authProvider.initialSyncDone;
        final path          = state.uri.path;
        final goingToLogin  = path == '/login';
        final goingToSync   = path == '/sync';

        if (!loggedIn && !goingToLogin) return '/login';
        if (loggedIn  && goingToLogin)  return syncDone ? '/' : '/sync';

        // After login: if sync not done yet, force /sync
        if (loggedIn && !syncDone && !goingToSync) return '/sync';

        return null;
      },
      routes: [
        GoRoute(
          path: '/login',
          builder: (_, __) => const LoginScreen(),
        ),
        GoRoute(
          path: '/sync',
          builder: (_, __) => const InitialSyncScreen(),
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
          builder: (_, __) => const TeacherAttendanceScreen(),
        ),
        GoRoute(
          path: '/scores',
          builder: (_, __) => const TeacherScoresScreen(),
        ),
        GoRoute(
          path: '/homework',
          builder: (_, __) => const TeacherHomeworkScreen(),
        ),
        GoRoute(
          path: '/students',
          builder: (_, __) => const Scaffold(
            body: Center(child: Text('Students — coming soon')),
          ),
        ),
        GoRoute(
          path: '/cbt',
          builder: (_, __) => const Scaffold(
            body: Center(child: Text('CBT Exams — coming soon')),
          ),
        ),
      ],
    );
  }
}
