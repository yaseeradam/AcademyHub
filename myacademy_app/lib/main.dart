import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'core/theme.dart';
import 'core/auth_provider.dart';
import 'features/splash/splash_screen.dart';
import 'features/auth/login_screen_new.dart';
import 'features/dashboard/dashboard_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MyAcademyApp());
}

class MyAcademyApp extends StatelessWidget {
  const MyAcademyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
      ],
      child: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          return MaterialApp(
            title: 'MyAcademy',
            theme: AppTheme.lightTheme,
            home: authProvider.isLoading
                ? const SplashScreen()
                : authProvider.isAuthenticated
                    ? const DashboardScreen()
                    : const SplashScreen(),
            routes: {
              '/login': (context) => const LoginScreen(),
              '/dashboard': (context) => const DashboardScreen(),
            },
            debugShowCheckedModeBanner: false,
          );
        },
      ),
    );
  }
}
