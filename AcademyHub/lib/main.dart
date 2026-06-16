import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'core/theme.dart';
import 'core/auth_provider.dart';
import 'core/router.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const AcademyHubApp());
}

class AcademyHubApp extends StatelessWidget {
  const AcademyHubApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => AuthProvider(),
      child: Consumer<AuthProvider>(
        builder: (context, authProvider, _) {
          return MaterialApp.router(
            title: 'AcademyHub',
            theme: AppTheme.lightTheme(authProvider.tenantPrimaryColor),
            darkTheme: AppTheme.darkTheme(authProvider.tenantPrimaryColor),
            themeMode: authProvider.themeMode,
            routerConfig: AppRouter.router(authProvider),
            debugShowCheckedModeBanner: false,
          );
        },
      ),
    );
  }
}
