import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'auth_provider.dart';

class SyncDot extends StatefulWidget {
  const SyncDot({super.key});

  @override
  State<SyncDot> createState() => _SyncDotState();
}

class _SyncDotState extends State<SyncDot> with SingleTickerProviderStateMixin {
  late AnimationController _blink;
  late Animation<double> _opacity;

  @override
  void initState() {
    super.initState();
    _blink = AnimationController(vsync: this, duration: const Duration(milliseconds: 800))
      ..repeat(reverse: true);
    _opacity = Tween<double>(begin: 0.2, end: 1.0).animate(_blink);
  }

  @override
  void dispose() {
    _blink.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final syncService = context.read<AuthProvider>().syncService;

    return StreamBuilder<int>(
      stream: syncService.pendingCountStream,
      initialData: 0,
      builder: (context, snapshot) {
        final hasPending = (snapshot.data ?? 0) > 0;

        if (hasPending) {
          return Tooltip(
            message: '${snapshot.data} change(s) pending sync',
            child: FadeTransition(
              opacity: _opacity,
              child: _dot(const Color(0xFFEF4444)),
            ),
          );
        }

        return Tooltip(
          message: 'All data synced',
          child: _dot(const Color(0xFF22C55E)),
        );
      },
    );
  }

  Widget _dot(Color color) => Container(
        width: 10,
        height: 10,
        decoration: BoxDecoration(
          color: color,
          shape: BoxShape.circle,
          boxShadow: [BoxShadow(color: color.withValues(alpha: 0.4), blurRadius: 4, spreadRadius: 1)],
        ),
      );
}

// Keep old widget name as alias so nothing else breaks
class SyncStatusWidget extends SyncDot {
  const SyncStatusWidget({super.key});
}
