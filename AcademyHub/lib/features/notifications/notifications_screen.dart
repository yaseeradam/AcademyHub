import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final _db = DatabaseHelper();
  List<Map<String, dynamic>> _notifications = [];
  bool _loading = true;
  int? _expandedId;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    if (mounted) setState(() => _loading = true);
    try {
      final list = await _db.getNotifications();
      if (mounted) {
        setState(() {
          _notifications = list;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _triggerSync() async {
    final auth = context.read<AuthProvider>();
    try {
      // Run the background sync process to fetch/upload notifications
      await auth.syncService.syncNow();
      // Also trigger role-based background refresh directly
      if (auth.user != null) {
        await auth.syncService.backgroundRefresh(auth.user!.role);
      }
      await _loadNotifications();
    } catch (e) {
      if (!mounted) return;
      CustomToast.show(
        context: context,
        message: 'Refresh failed: $e',
        type: 'error',
      );
    }
  }

  Future<void> _markRead(int id) async {
    try {
      final auth = context.read<AuthProvider>();
      await _db.markNotificationReadLocally(id);
      await _loadNotifications();
      // Fire sync in background silently
      auth.syncService.syncNow();
    } catch (_) {}
  }

  Future<void> _markAllRead() async {
    final unread = _notifications.where((n) => n['read_at'] == null);
    if (unread.isEmpty) return;

    final auth = context.read<AuthProvider>();
    final primaryColor = auth.tenantPrimaryColor;

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Mark All as Read',
          style: GoogleFonts.spaceGrotesk(
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        content: Text(
          'Are you sure you want to mark all notifications as read?',
          style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(
              'Cancel',
              style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary),
            ),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: primaryColor,
              foregroundColor: Colors.black,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: Text(
              'Confirm',
              style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
    );

    if (confirm == true) {
      try {
        for (final n in unread) {
          final id = n['id'] as int;
          await _db.markNotificationReadLocally(id);
        }
        await _loadNotifications();
        // Sync to server
        auth.syncService.syncNow();
        if (!mounted) return;
        CustomToast.show(
          context: context,
          message: 'All notifications marked as read.',
          type: 'success',
        );
      } catch (_) {}
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;
    final hasUnread = _notifications.any((n) => n['read_at'] == null);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(
          'Notifications',
          style: GoogleFonts.spaceGrotesk(
            fontWeight: FontWeight.bold,
            fontSize: 16,
            color: AppColors.textPrimary,
          ),
        ),
        backgroundColor: AppColors.surface,
        foregroundColor: AppColors.textPrimary,
        elevation: 0,
        shape: Border(bottom: BorderSide(color: AppColors.borderLight)),
        actions: [
          if (hasUnread)
            IconButton(
              icon: Icon(Icons.done_all_rounded, color: primary),
              tooltip: 'Mark all as read',
              onPressed: _markAllRead,
            ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _triggerSync,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _triggerSync,
        color: primary,
        child: _loading && _notifications.isEmpty
            ? const Center(child: CircularProgressIndicator())
            : _notifications.isEmpty
                ? _buildEmptyState()
                : ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: _notifications.length,
                    separatorBuilder: (_, index) => const SizedBox(height: 10),
                    itemBuilder: (context, idx) {
                      final n = _notifications[idx];
                      final id = n['id'] as int;
                      final isUnread = n['read_at'] == null;
                      final title = n['title'] ?? 'Notification';
                      final body = n['body'] ?? '';
                      final createdAt = n['created_at'] != null
                          ? DateTime.tryParse(n['created_at'])?.toLocal()
                          : null;
                      final isExpanded = _expandedId == id;

                      String timeStr = '';
                      if (createdAt != null) {
                        timeStr = '${createdAt.day}/${createdAt.month} ${createdAt.hour}:${createdAt.minute.toString().padLeft(2, '0')}';
                      }

                      return GestureDetector(
                        onTap: () {
                          if (isUnread) {
                            _markRead(id);
                          }
                          setState(() {
                            _expandedId = isExpanded ? null : id;
                          });
                        },
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 200),
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(
                              color: isUnread
                                  ? primary.withValues(alpha: 0.35)
                                  : AppColors.borderLight,
                              width: isUnread ? 1.5 : 1.0,
                            ),
                            boxShadow: isUnread
                                ? [
                                    BoxShadow(
                                      color: primary.withValues(alpha: 0.05),
                                      blurRadius: 10,
                                      offset: const Offset(0, 4),
                                    )
                                  ]
                                : null,
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Indicator dot / icon
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: isUnread
                                      ? primary.withValues(alpha: 0.12)
                                      : AppColors.surface2,
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  isUnread
                                      ? Icons.notifications_active_rounded
                                      : Icons.notifications_none_rounded,
                                  color: isUnread ? primary : AppColors.textSecondary,
                                  size: 18,
                                ),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Expanded(
                                          child: Text(
                                            title,
                                            style: GoogleFonts.spaceGrotesk(
                                              fontWeight: isUnread
                                                  ? FontWeight.bold
                                                  : FontWeight.w600,
                                              fontSize: 13,
                                              color: AppColors.textPrimary,
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        Text(
                                          timeStr,
                                          style: GoogleFonts.spaceGrotesk(
                                            fontSize: 10,
                                            color: AppColors.textMuted,
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      body,
                                      style: GoogleFonts.spaceGrotesk(
                                        fontSize: 12,
                                        color: isUnread
                                            ? AppColors.textPrimary
                                            : AppColors.textSecondary,
                                        height: 1.4,
                                      ),
                                      maxLines: isExpanded ? null : 2,
                                      overflow: isExpanded
                                          ? null
                                          : TextOverflow.ellipsis,
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        SizedBox(height: MediaQuery.of(context).size.height * 0.25),
        Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.notifications_off_outlined,
                size: 48,
                color: AppColors.textMuted,
              ),
              const SizedBox(height: 12),
              Text(
                'No notifications yet.',
                style: GoogleFonts.spaceGrotesk(
                  color: AppColors.textSecondary,
                  fontSize: 14,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Pull to check for updates.',
                style: GoogleFonts.spaceGrotesk(
                  color: AppColors.textMuted,
                  fontSize: 12,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
