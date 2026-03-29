import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'auth_provider.dart';
import 'sync_service.dart';

class SyncStatusWidget extends StatelessWidget {
  const SyncStatusWidget({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.read<AuthProvider>();
    
    return StreamBuilder<SyncStatus>(
      stream: auth.syncService.syncStatusStream,
      initialData: SyncStatus.synced,
      builder: (context, snapshot) {
        final status = snapshot.data ?? SyncStatus.synced;
        
        IconData icon;
        Color color;
        String tooltip;

        switch (status) {
          case SyncStatus.syncing:
            icon = Icons.sync;
            color = Colors.amber;
            tooltip = 'Syncing offline changes...';
            break;
          case SyncStatus.offline:
            icon = Icons.cloud_off;
            color = Colors.red;
            tooltip = 'Offline (changes stored locally)';
            break;
          case SyncStatus.error:
            icon = Icons.error_outline;
            color = Colors.orange;
            tooltip = 'Sync failed. Will retry later.';
            break;
          case SyncStatus.synced:
            icon = Icons.cloud_done;
            color = Colors.green;
            tooltip = 'All data synced';
            break;
        }

        return Tooltip(
          message: tooltip,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            child: Icon(
              icon,
              color: color,
              size: 20,
            ),
          ),
        );
      },
    );
  }
}
