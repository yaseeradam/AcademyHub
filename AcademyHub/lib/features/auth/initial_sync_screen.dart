import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';

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

  @override
  void initState() {
    super.initState();
    _pulse = AnimationController(vsync: this, duration: const Duration(seconds: 2))
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

    // Listen to progress updates
    auth.syncService.progressStream.listen((p) {
      if (mounted) setState(() { _message = p.message; _progress = p.progress; });
    });

    try {
      await auth.syncService.initialSync(auth.user?.role ?? 'teacher');
      auth.markSyncDone();
      if (mounted) context.go('/');
    } catch (e) {
      if (mounted) setState(() => _failed = true);
    }
  }

  Future<void> _retry() async {
    setState(() { _failed = false; _progress = 0.0; _message = 'Retrying...'; });
    _startSync();
  }

  @override
  Widget build(BuildContext context) {
    final user = context.read<AuthProvider>().user;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
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
                      color: const Color(0xFF3B82F6).withValues(alpha: 0.1),
                      shape: BoxShape.circle,
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Image.asset('assets/images/Alogo.png'),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 32),
              Text(
                'Setting up for ${user?.name ?? 'you'}',
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              const Text(
                'Downloading your data so the app works offline too.',
                style: TextStyle(fontSize: 14, color: Color(0xFF64748B)),
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
                    backgroundColor: const Color(0xFFE2E8F0),
                    valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF3B82F6)),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  _message,
                  style: const TextStyle(fontSize: 13, color: Color(0xFF64748B)),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),
                Text(
                  '${(_progress * 100).toInt()}%',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF3B82F6)),
                ),
              ] else ...[
                // Error state
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFFECACA)),
                  ),
                  child: Column(
                    children: [
                      const Icon(Icons.wifi_off_rounded, size: 40, color: Color(0xFFEF4444)),
                      const SizedBox(height: 12),
                      const Text(
                        'Could not connect to server',
                        style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Check your internet connection and try again.',
                        style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton(
                              onPressed: () {
                                context.read<AuthProvider>().markSyncDone();
                                context.go('/');
                              },
                              child: const Text('Skip for now'),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: ElevatedButton(
                              onPressed: _retry,
                              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF3B82F6)),
                              child: const Text('Retry', style: TextStyle(color: Colors.white)),
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
