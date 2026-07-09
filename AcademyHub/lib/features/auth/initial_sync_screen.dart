import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';

class InitialSyncScreen extends StatefulWidget {
  const InitialSyncScreen({super.key});

  @override
  State<InitialSyncScreen> createState() => _InitialSyncScreenState();
}

class _InitialSyncScreenState extends State<InitialSyncScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _pulse;
  String _message  = 'Preparing your data...';
  double _progress = 0.0;
  bool   _failed   = false;
  bool   _retrying = false;

  @override
  void initState() {
    super.initState();
    _pulse =
        AnimationController(vsync: this, duration: const Duration(seconds: 2))
          ..repeat(reverse: true);
    _startSync();
  }

  @override
  void dispose() {
    _pulse.dispose();
    super.dispose();
  }

  Future<void> _startSync() async {
    final auth = context.read<AuthProvider>();
    auth.syncService.progressStream.listen((p) {
      if (mounted) setState(() { _message = p.message; _progress = p.progress; });
    });
    try {
      await auth.syncService.initialSync(auth.user?.role ?? 'teacher');
      auth.markSyncDone();
      if (mounted) context.go('/');
    } catch (e) {
      if (mounted) setState(() { _failed = true; _retrying = false; });
    }
  }

  Future<void> _retry() async {
    setState(() { _failed = false; _retrying = true; _progress = 0.0; _message = 'Retrying...'; });
    _startSync();
  }

  Future<void> _skipSync() async {
    context.read<AuthProvider>().markSyncDone();
    context.go('/');
  }

  @override
  Widget build(BuildContext context) {
    final user    = context.read<AuthProvider>().user;
    final primary = context.read<AuthProvider>().tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Animated logo
              AnimatedBuilder(
                animation: _pulse,
                builder: (_, _) => Transform.scale(
                  scale: 0.95 + (_pulse.value * 0.05),
                  child: Container(
                    width: 96,
                    height: 96,
                    decoration: BoxDecoration(
                      color: primary.withValues(alpha: 0.1),
                      shape: BoxShape.circle,
                      border: Border.all(
                          color: primary.withValues(alpha: 0.3), width: 1.5),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Image.asset('lib/Alogo.png'),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 32),
              Text(
                'Setting up for ${user?.name ?? 'you'}',
                style: GoogleFonts.inter(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                'Downloading your data so the app works offline too.',
                style: GoogleFonts.inter(
                    fontSize: 14, color: AppColors.textSecondary),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 40),

              if (!_failed) ...[
                // Progress bar
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: LinearProgressIndicator(
                    value: _progress,
                    minHeight: 8,
                    backgroundColor: AppColors.surface2,
                    valueColor: AlwaysStoppedAnimation<Color>(primary),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  _message,
                  style: GoogleFonts.inter(
                      fontSize: 13, color: AppColors.textSecondary),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),
                Text(
                  '${(_progress * 100).toInt()}%',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: primary,
                  ),
                ),
              ] else ...[
                // Error state
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: AppColors.error.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                        color: AppColors.error.withValues(alpha: 0.25)),
                  ),
                  child: Column(
                    children: [
                      Icon(Icons.wifi_off_rounded,
                          size: 40, color: AppColors.error),
                      const SizedBox(height: 12),
                      Text(
                        'Could not connect to server',
                        style: GoogleFonts.inter(
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Check your internet connection and try again.',
                        style: GoogleFonts.inter(
                            fontSize: 13, color: AppColors.textSecondary),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton(
                              onPressed: _retrying ? null : _skipSync,
                              style: OutlinedButton.styleFrom(
                                foregroundColor: AppColors.textSecondary,
                                side: BorderSide(
                                    color: AppColors.borderLight),
                                padding: const EdgeInsets.symmetric(
                                    vertical: 14),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10)),
                              ),
                              child: Text('Skip for now',
                                  style: GoogleFonts.inter(
                                      fontWeight: FontWeight.bold)),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: ElevatedButton(
                              onPressed: _retrying ? null : _retry,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: primary,
                                foregroundColor: Colors.black,
                                padding: const EdgeInsets.symmetric(
                                    vertical: 14),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10)),
                                elevation: 0,
                              ),
                              child: _retrying
                                  ? const SizedBox(
                                      width: 18,
                                      height: 18,
                                      child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          color: Colors.black54))
                                  : Text('Retry',
                                      style: GoogleFonts.inter(
                                          fontWeight: FontWeight.bold)),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
