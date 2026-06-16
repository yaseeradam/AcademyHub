import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'auth_provider.dart';
import 'sync_status_widget.dart';
import 'toast_utility.dart';
import 'constants.dart';

// ─── MobileLayout ─────────────────────────────────────────────────────────────
// Thin wrapper — each role manages its own bottom-nav internally.
// This shell just provides a safe-area scaffold and a top mini-bar.
class MobileLayout extends StatelessWidget {
  final Widget child;
  final String title;

  const MobileLayout({
    super.key,
    required this.child,
    required this.title,
  });

  @override
  Widget build(BuildContext context) {
    // If the current screen is a sub-page (can pop), show a simple back-only header.
    final canPop = Navigator.of(context).canPop();
    if (canPop) {
      return _SubPageScaffold(title: title, child: child);
    }
    // Otherwise the role home screen renders its own layout (bottom nav etc.).
    return child;
  }
}

// ─── Sub-page scaffold (used for attendance / scores / homework etc.) ──────────
class _SubPageScaffold extends StatelessWidget {
  final String title;
  final Widget child;
  const _SubPageScaffold({required this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(64),
        child: _DarkAppBar(title: title, showBack: true),
      ),
      body: child,
    );
  }
}

// ─── Dark App Bar ──────────────────────────────────────────────────────────────
class _DarkAppBar extends StatelessWidget {
  final String title;
  final bool showBack;
  const _DarkAppBar({required this.title, this.showBack = false});

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final userInitial = (user != null && user.name.trim().isNotEmpty)
        ? user.name.trim().substring(0, 1).toUpperCase()
        : 'U';
    final auth = context.watch<AuthProvider>();

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border(bottom: BorderSide(color: AppColors.borderLight)),
      ),
      child: SafeArea(
        child: SizedBox(
          height: 64,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                if (showBack)
                  _IconBtn(
                    icon: Icons.arrow_back_rounded,
                    onTap: () => Navigator.of(context).pop(),
                  )
                else
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: AppColors.surface2,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: AppColors.borderLight),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(4),
                      child: Image.asset('lib/Alogo.png'),
                    ),
                  ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    title,
                    style: GoogleFonts.spaceGrotesk(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                      letterSpacing: -0.3,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SyncDot(),
                const SizedBox(width: 8),
                _IconBtn(
                  icon: auth.themeMode == ThemeMode.dark
                      ? Icons.light_mode_outlined
                      : Icons.dark_mode_outlined,
                  onTap: () => auth.toggleTheme(),
                ),
                const SizedBox(width: 8),
                // In-App Notifications
                _IconBtn(
                  icon: Icons.notifications_outlined,
                  onTap: () => context.push('/notifications'),
                ),
                if (user?.role != 'student') ...[
                  const SizedBox(width: 8),
                  // Direct Chat Messaging
                  _IconBtn(
                    icon: Icons.chat_bubble_outline_rounded,
                    onTap: () => context.push('/chat'),
                  ),
                ],
                const SizedBox(width: 8),
                // Profile avatar
                GestureDetector(
                  onTap: () => _showProfileSheet(context, auth),
                  child: CircleAvatar(
                    radius: 18,
                    backgroundColor: auth.tenantPrimaryColor.withValues(alpha: 0.15),
                    backgroundImage: auth.getReachableUrl(user?.profilePhotoUrl) != null
                        ? NetworkImage(auth.getReachableUrl(user?.profilePhotoUrl)!)
                        : null,
                    child: auth.getReachableUrl(user?.profilePhotoUrl) != null
                        ? null
                        : Text(
                            userInitial,
                            style: TextStyle(
                              color: auth.tenantPrimaryColor,
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showProfileSheet(BuildContext context, AuthProvider auth) {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => _ProfileSheet(auth: auth),
    );
  }
}

class _IconBtn extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  const _IconBtn({required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: AppColors.surface2,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.borderLight),
        ),
        child: Icon(icon, color: AppColors.textSecondary, size: 20),
      ),
    );
  }
}

// ─── Profile / Settings Bottom Sheet ─────────────────────────────────────────
class _ProfileSheet extends StatefulWidget {
  final AuthProvider auth;
  const _ProfileSheet({required this.auth});

  @override
  State<_ProfileSheet> createState() => _ProfileSheetState();
}

class _ProfileSheetState extends State<_ProfileSheet> {
  bool _isSyncing = false;

  Future<void> _handleSync() async {
    setState(() => _isSyncing = true);
    try {
      await widget.auth.syncService.syncNow();
      if (!mounted) return;
      CustomToast.show(
        context: context,
        message: 'Data synchronized successfully!',
        type: 'success',
      );
    } catch (_) {
      if (!mounted) return;
      CustomToast.show(
        context: context,
        message: 'Sync failed. Check your connection.',
        type: 'error',
      );
    } finally {
      if (mounted) setState(() => _isSyncing = false);
    }
  }

  void _showLogout() {
    Navigator.pop(context);
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('Confirm Logout',
            style: GoogleFonts.spaceGrotesk(
                color: AppColors.textPrimary, fontWeight: FontWeight.bold)),
        content: Text('Are you sure you want to logout?',
            style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text('Cancel',
                style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary)),
          ),
          ElevatedButton(
            onPressed: () async {
              final router = GoRouter.of(ctx);
              Navigator.pop(ctx);
              await widget.auth.logout();
              router.go('/login');
            },
            style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.error,
                foregroundColor: Colors.white),
            child: Text('Logout',
                style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = widget.auth.user;
    final primary = widget.auth.tenantPrimaryColor;
    final userInitial = (user != null && user.name.trim().isNotEmpty)
        ? user.name.trim().substring(0, 1).toUpperCase()
        : 'U';
    final plugins = widget.auth.allPlugins.where((p) {
      final slug = p['slug'] as String? ?? '';
      return widget.auth.isPluginActive(slug);
    }).toList();

    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Drag handle
          Center(
            child: Container(
              width: 36,
              height: 4,
              margin: const EdgeInsets.only(bottom: 20),
              decoration: BoxDecoration(
                color: AppColors.borderLight,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          // User profile card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.surface2,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.borderLight),
            ),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 28,
                  backgroundColor: primary.withValues(alpha: 0.15),
                  backgroundImage: widget.auth.getReachableUrl(user?.profilePhotoUrl) != null
                      ? NetworkImage(widget.auth.getReachableUrl(user?.profilePhotoUrl)!)
                      : null,
                  child: widget.auth.getReachableUrl(user?.profilePhotoUrl) != null
                      ? null
                      : Text(
                          userInitial,
                          style: TextStyle(
                            color: primary,
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        user?.name ?? 'User',
                        style: GoogleFonts.spaceGrotesk(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: primary.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          (user?.role ?? 'user').toUpperCase(),
                          style: GoogleFonts.spaceGrotesk(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: primary,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Actions
          _sheetAction(
            Icons.sync_rounded,
            _isSyncing ? 'Syncing...' : 'Sync Data',
            AppColors.success,
            _isSyncing ? null : _handleSync,
          ),
          const SizedBox(height: 8),
          _sheetAction(
            Icons.logout_rounded,
            'Sign Out',
            AppColors.error,
            _showLogout,
          ),

          // Plugins section
          if (plugins.isNotEmpty) ...[
            const SizedBox(height: 24),
            Text(
              'MARKETPLACE PLUGINS',
              style: GoogleFonts.spaceGrotesk(
                fontSize: 10,
                fontWeight: FontWeight.bold,
                color: AppColors.textMuted,
                letterSpacing: 1.2,
              ),
            ),
            const SizedBox(height: 12),
            ...plugins.map((p) {
              final slug = p['slug'] as String? ?? '';
              final isActive = widget.auth.isPluginActive(slug);
              return Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.surface2,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                    color: isActive
                        ? AppColors.success.withValues(alpha: 0.3)
                        : AppColors.borderLight,
                  ),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: isActive
                            ? AppColors.success.withValues(alpha: 0.1)
                            : AppColors.surface3,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(
                        slug == 'cbt'
                            ? Icons.computer_rounded
                            : slug == 'e-learning'
                                ? Icons.book_online_rounded
                                : Icons.people_outline_rounded,
                        color: isActive ? AppColors.success : AppColors.textSecondary,
                        size: 16,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            p['name'] ?? 'Plugin',
                            style: GoogleFonts.spaceGrotesk(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimary,
                            ),
                          ),
                          Text(
                            p['description'] ?? '',
                            style: GoogleFonts.spaceGrotesk(
                              fontSize: 11,
                              color: AppColors.textSecondary,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: isActive
                            ? AppColors.success.withValues(alpha: 0.1)
                            : AppColors.error.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        isActive ? 'ACTIVE' : 'LOCKED',
                        style: GoogleFonts.spaceGrotesk(
                          fontSize: 9,
                          fontWeight: FontWeight.w900,
                          color: isActive ? AppColors.success : AppColors.error,
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }),
          ],
        ],
      ),
    );
  }

  Widget _sheetAction(
      IconData icon, String label, Color color, VoidCallback? onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface2,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.borderLight),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(width: 14),
            Text(
              label,
              style: GoogleFonts.spaceGrotesk(
                fontSize: 14,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Role Shell — wraps a role's content with top bar + no drawer ─────────────
class RoleShell extends StatelessWidget {
  final String title;
  final Widget body;
  final Widget? floatingActionButton;
  const RoleShell({super.key, required this.title, required this.body, this.floatingActionButton});

  @override
  Widget build(BuildContext context) {
    final isDark = context.watch<AuthProvider>().themeMode == ThemeMode.dark;
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: isDark ? Brightness.light : Brightness.dark,
      ),
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: PreferredSize(
          preferredSize: const Size.fromHeight(64),
          child: _DarkAppBar(title: title),
        ),
        body: body,
        floatingActionButton: floatingActionButton,
      ),
    );
  }
}

// ─── Bottom Nav Item ──────────────────────────────────────────────────────────
class AHBottomNav extends StatelessWidget {
  final List<AHNavItem> items;
  final int selectedIndex;
  final ValueChanged<int> onTap;
  final Color accentColor;

  const AHBottomNav({
    super.key,
    required this.items,
    required this.selectedIndex,
    required this.onTap,
    required this.accentColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.borderLight)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
          child: Row(
            children: List.generate(items.length, (i) {
              final item = items[i];
              final selected = i == selectedIndex;
              return Expanded(
                child: GestureDetector(
                  onTap: () => onTap(i),
                  behavior: HitTestBehavior.opaque,
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    curve: Curves.easeInOut,
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    decoration: BoxDecoration(
                      color: selected
                          ? accentColor.withValues(alpha: 0.12)
                          : Colors.transparent,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          selected ? item.activeIcon : item.icon,
                          color: selected ? accentColor : AppColors.textSecondary,
                          size: 22,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          item.label,
                          style: GoogleFonts.spaceGrotesk(
                            fontSize: 10,
                            fontWeight: selected ? FontWeight.bold : FontWeight.w500,
                            color: selected ? accentColor : AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            }),
          ),
        ),
      ),
    );
  }
}

class AHNavItem {
  final IconData icon;
  final IconData activeIcon;
  final String label;
  const AHNavItem(
      {required this.icon,
      required this.activeIcon,
      required this.label});
}

// ─── Glassmorphism Hero Card ──────────────────────────────────────────────────
class GlassHeroCard extends StatelessWidget {
  final Widget child;
  final List<Color> gradientColors;
  final double borderRadius;

  const GlassHeroCard({
    super.key,
    required this.child,
    required this.gradientColors,
    this.borderRadius = 20,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: gradientColors,
        ),
        borderRadius: BorderRadius.circular(borderRadius),
        boxShadow: [
          BoxShadow(
            color: gradientColors.first.withValues(alpha: 0.3),
            blurRadius: 24,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: child,
    );
  }
}

// ─── Dark Stat Card ───────────────────────────────────────────────────────────
class DarkStatCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  final VoidCallback? onTap;

  const DarkStatCard({
    super.key,
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.borderLight),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.all(7),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(icon, color: color, size: 18),
                ),
                if (onTap != null)
                  Icon(Icons.arrow_forward_ios_rounded,
                      color: AppColors.textMuted, size: 11),
              ],
            ),
            const Spacer(),
            Text(
              value,
              style: GoogleFonts.spaceGrotesk(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: GoogleFonts.spaceGrotesk(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Section Header ───────────────────────────────────────────────────────────
class SectionHeader extends StatelessWidget {
  final String title;
  final String? actionLabel;
  final VoidCallback? onAction;

  const SectionHeader({
    super.key,
    required this.title,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          title,
          style: GoogleFonts.spaceGrotesk(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        if (actionLabel != null && onAction != null)
          GestureDetector(
            onTap: onAction,
            child: Text(
              actionLabel!,
              style: GoogleFonts.spaceGrotesk(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: AppColors.primary,
              ),
            ),
          ),
      ],
    );
  }
}

// ─── Action Row Item ──────────────────────────────────────────────────────────
class DarkActionRow extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  const DarkActionRow({
    super.key,
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title,
                      style: GoogleFonts.spaceGrotesk(
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                          color: AppColors.textPrimary)),
                  const SizedBox(height: 2),
                  Text(subtitle,
                      style: GoogleFonts.spaceGrotesk(
                          fontSize: 11,
                          color: AppColors.textSecondary)),
                ],
              ),
            ),
            Icon(Icons.arrow_forward_ios_rounded,
                color: AppColors.textMuted, size: 11),
          ],
        ),
      ),
    );
  }
}

// ─── AHLoadingButton ──────────────────────────────────────────────────────────
// Reusable action button with built-in loading state for ALL action CTAs.
// Usage: AHLoadingButton(label: 'Save', isLoading: _saving, onTap: _save)
class AHLoadingButton extends StatelessWidget {
  final String label;
  final bool isLoading;
  final VoidCallback? onTap;
  final Color? color;
  final Color? foregroundColor;
  final IconData? icon;
  final double height;
  final double? width;
  final double borderRadius;

  const AHLoadingButton({
    super.key,
    required this.label,
    required this.isLoading,
    required this.onTap,
    this.color,
    this.foregroundColor,
    this.icon,
    this.height = 48,
    this.width,
    this.borderRadius = 12,
  });

  @override
  Widget build(BuildContext context) {
    final bg = color ?? AppColors.primary;
    final fg = foregroundColor ?? Colors.black;

    return SizedBox(
      height: height,
      width: width ?? double.infinity,
      child: ElevatedButton(
        onPressed: isLoading ? null : onTap,
        style: ElevatedButton.styleFrom(
          backgroundColor: bg,
          foregroundColor: fg,
          disabledBackgroundColor: bg.withValues(alpha: 0.5),
          elevation: 0,
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(borderRadius)),
        ),
        child: isLoading
            ? SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(fg.withValues(alpha: 0.7)),
                ),
              )
            : Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (icon != null) ...[
                    Icon(icon, size: 18),
                    const SizedBox(width: 8),
                  ],
                  Text(
                    label,
                    style: GoogleFonts.spaceGrotesk(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}