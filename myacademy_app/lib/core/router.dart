import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'auth_provider.dart';
import '../features/auth/login_screen.dart';
import '../features/student/student_home.dart';
import '../features/teacher/teacher_home.dart';
import '../features/parent/parent_home.dart';
import '../features/admin/admin_home.dart';

class AppRouter {
  static GoRouter router(AuthProvider authProvider) {
    return GoRouter(
      initialLocation: '/',
      refreshListenable: authProvider,
      redirect: (context, state) {
        final isLoggedIn = authProvider.isAuthenticated;
        final isGoingToLogin = state.uri.path == '/login';

        if (authProvider.isLoading) return null; // Wait for initialization to finish

        if (!isLoggedIn && !isGoingToLogin) return '/login';
        if (isLoggedIn && isGoingToLogin) return '/';

        return null; // Let the route pass through
      },
      routes: [
        GoRoute(
          path: '/login',
          builder: (context, state) => const LoginScreen(),
        ),
        GoRoute(
          path: '/',
          builder: (context, state) {
            if (authProvider.isLoading) {
              return const Scaffold(
                body: Center(child: CircularProgressIndicator()),
              );
            }
            
            final role = authProvider.user?.role;
            switch (role) {
              case 'student':
                return const StudentHome();
              case 'teacher':
                return const TeacherHome();
              case 'parent':
                return const ParentHome();
              case 'admin':
              case 'bursar':
                return const AdminHome();
              default:
                return const Scaffold(
                  body: Center(child: Text('Unknown role')),
                );
            }
          },
        ),
      ],
    );
  }
}
