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
  final bool showMenuButton;
  const _DarkAppBar({
    required this.title,
    this.showBack = false,
    this.showMenuButton = false,
  });

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
                else if (showMenuButton)
                  _IconBtn(
                    icon: Icons.menu_rounded,
                    onTap: () => Scaffold.of(context).openDrawer(),
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
                    style: GoogleFonts.inter(
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
            style: GoogleFonts.inter(
                color: AppColors.textPrimary, fontWeight: FontWeight.bold)),
        content: Text('Are you sure you want to logout?',
            style: GoogleFonts.inter(color: AppColors.textSecondary)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text('Cancel',
                style: GoogleFonts.inter(color: AppColors.textSecondary)),
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
                style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
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
                        style: GoogleFonts.inter(
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
                          style: GoogleFonts.inter(
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
              style: GoogleFonts.inter(
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
                            style: GoogleFonts.inter(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimary,
                            ),
                          ),
                          Text(
                            p['description'] ?? '',
                            style: GoogleFonts.inter(
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
                        style: GoogleFonts.inter(
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
              style: GoogleFonts.inter(
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
  
  // New responsive navigation properties
  final List<AHNavItem>? navItems;
  final int? selectedIndex;
  final ValueChanged<int>? onTabSelected;
  final Color? accentColor;
  final bool loading;
  final RefreshCallback? onRefresh;

  const RoleShell({
    super.key,
    required this.title,
    required this.body,
    this.floatingActionButton,
    this.navItems,
    this.selectedIndex,
    this.onTabSelected,
    this.accentColor,
    this.loading = false,
    this.onRefresh,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = context.watch<AuthProvider>().themeMode == ThemeMode.dark;

    // If no navigation items are provided, fall back to simple layout
    if (navItems == null) {
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

    final isMobile = MediaQuery.of(context).size.width < 768;

    if (isMobile) {
      return AnnotatedRegion<SystemUiOverlayStyle>(
        value: SystemUiOverlayStyle(
          statusBarColor: Colors.transparent,
          statusBarIconBrightness: isDark ? Brightness.light : Brightness.dark,
        ),
        child: Scaffold(
          backgroundColor: AppColors.background,
          appBar: PreferredSize(
            preferredSize: const Size.fromHeight(64),
            child: _DarkAppBar(
              title: title,
              showMenuButton: true,
            ),
          ),
          drawer: _MobileDrawer(
            navItems: navItems!,
            selectedIndex: selectedIndex ?? 0,
            onTabSelected: onTabSelected ?? (_) {},
            accentColor: accentColor ?? AppColors.primary,
          ),
          body: Column(
            children: [
              if (loading) LinearProgressIndicator(color: accentColor ?? AppColors.primary, minHeight: 2),
              Expanded(
                child: onRefresh != null
                    ? RefreshIndicator(
                        onRefresh: onRefresh!,
                        color: accentColor ?? AppColors.primary,
                        child: body,
                      )
                    : body,
              ),
            ],
          ),
          floatingActionButton: floatingActionButton,
        ),
      );
    } else {
      // Tablet / Desktop responsive split layout
      return AnnotatedRegion<SystemUiOverlayStyle>(
        value: SystemUiOverlayStyle(
          statusBarColor: Colors.transparent,
          statusBarIconBrightness: isDark ? Brightness.light : Brightness.dark,
        ),
        child: Scaffold(
          backgroundColor: AppColors.background,
          body: Row(
            children: [
              // Left Sidebar
              _ResponsiveSidebar(
                title: title,
                navItems: navItems!,
                selectedIndex: selectedIndex ?? 0,
                onTabSelected: onTabSelected ?? (_) {},
                accentColor: accentColor ?? AppColors.primary,
              ),
              // Right Main Workspace
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Desktop Header
                    _DesktopHeader(
                      title: navItems![selectedIndex ?? 0].label,
                      accentColor: accentColor ?? AppColors.primary,
                    ),
                    if (loading) LinearProgressIndicator(color: accentColor ?? AppColors.primary, minHeight: 2),
                    Expanded(
                      child: ClipRect(
                        child: onRefresh != null
                            ? RefreshIndicator(
                                onRefresh: onRefresh!,
                                color: accentColor ?? AppColors.primary,
                                child: _buildConstrainedBody(body),
                              )
                            : _buildConstrainedBody(body),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          floatingActionButton: floatingActionButton,
        ),
      );
    }
  }

  Widget _buildConstrainedBody(Widget child) {
    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 1100),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 16.0),
          child: child,
        ),
      ),
    );
  }
}

// ─── Responsive Left Sidebar ──────────────────────────────────────────────────
class _ResponsiveSidebar extends StatelessWidget {
  final String title;
  final List<AHNavItem> navItems;
  final int selectedIndex;
  final ValueChanged<int> onTabSelected;
  final Color accentColor;

  const _ResponsiveSidebar({
    required this.title,
    required this.navItems,
    required this.selectedIndex,
    required this.onTabSelected,
    required this.accentColor,
  });

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    final isDark = auth.themeMode == ThemeMode.dark;

    final schoolLogo = auth.tenantLogoUrl;
    final schoolName = auth.tenantName ?? 'AcademyHub';
    final userInitial = (user != null && user.name.trim().isNotEmpty)
        ? user.name.trim().substring(0, 1).toUpperCase()
        : 'U';

    final roleLabel = (user?.role ?? 'User').toUpperCase();

    return Container(
      width: 280,
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border(
          right: BorderSide(color: AppColors.borderLight, width: 1),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Branding Header
          Padding(
            padding: const EdgeInsets.all(20.0),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: isDark ? AppColors.surface2 : Colors.deepPurple.shade50,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: isDark ? AppColors.borderLight : Colors.deepPurple.shade100,
                      width: 1.5,
                    ),
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(10),
                    child: schoolLogo != null && schoolLogo.isNotEmpty
                        ? Image.network(
                            schoolLogo,
                            fit: BoxFit.contain,
                            errorBuilder: (_, _, _) => Padding(
                              padding: const EdgeInsets.all(4),
                              child: Image.asset('lib/Alogo.png', fit: BoxFit.contain),
                            ),
                          )
                        : Padding(
                            padding: const EdgeInsets.all(6),
                            child: Image.asset('lib/Alogo.png', fit: BoxFit.contain),
                          ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        schoolName,
                        style: GoogleFonts.inter(
                          fontSize: 14,
                          fontWeight: FontWeight.w900,
                          color: AppColors.textPrimary,
                          letterSpacing: -0.3,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        title,
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: accentColor,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Menu Section Label
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 8.0),
            child: Text(
              'PORTAL MENU',
              style: GoogleFonts.inter(
                fontSize: 10,
                fontWeight: FontWeight.w900,
                color: AppColors.textMuted,
                letterSpacing: 1.5,
              ),
            ),
          ),

          // Navigation Links
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              itemCount: navItems.length,
              itemBuilder: (context, i) {
                final item = navItems[i];
                final isSelected = selectedIndex == i;
                final itemAccent = item.iconColor ?? accentColor;
                final itemBg = item.iconBg ?? itemAccent.withValues(alpha: 0.12);

                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4.0),
                  child: InkWell(
                    onTap: () => onTabSelected(i),
                    borderRadius: BorderRadius.circular(16),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: isSelected ? accentColor : Colors.transparent,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: isSelected
                            ? [
                                BoxShadow(
                                  color: accentColor.withValues(alpha: 0.35),
                                  blurRadius: 12,
                                  offset: const Offset(0, 4),
                                )
                              ]
                            : null,
                      ),
                      child: Row(
                        children: [
                          // Icon Container
                          Container(
                            width: 36,
                            height: 36,
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? Colors.white.withValues(alpha: 0.2)
                                  : itemBg,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Icon(
                              isSelected ? item.activeIcon : item.icon,
                              color: isSelected ? Colors.white : itemAccent,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 14),
                          // Label
                          Expanded(
                            child: Text(
                              item.label,
                              style: GoogleFonts.inter(
                                fontSize: 13,
                                fontWeight: isSelected ? FontWeight.w900 : FontWeight.bold,
                                color: isSelected ? Colors.white : AppColors.textPrimary,
                              ),
                            ),
                          ),
                          // Chevron Arrow if selected
                          if (isSelected)
                            Container(
                              width: 20,
                              height: 20,
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.2),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(
                                Icons.chevron_right_rounded,
                                color: Colors.white,
                                size: 14,
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),

          // User Profile Block at bottom
          _SidebarProfileBlock(auth: auth, user: user, userInitial: userInitial, accentColor: accentColor, roleLabel: roleLabel),
        ],
      ),
    );
  }
}

// ─── Sidebar Profile Block ────────────────────────────────────────────────────
class _SidebarProfileBlock extends StatelessWidget {
  final AuthProvider auth;
  final dynamic user;
  final String userInitial;
  final Color accentColor;
  final String roleLabel;

  const _SidebarProfileBlock({
    required this.auth,
    required this.user,
    required this.userInitial,
    required this.accentColor,
    required this.roleLabel,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      margin: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface2,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderLight, width: 1),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 20,
                backgroundColor: accentColor.withValues(alpha: 0.15),
                backgroundImage: auth.getReachableUrl(user?.profilePhotoUrl) != null
                    ? NetworkImage(auth.getReachableUrl(user?.profilePhotoUrl)!)
                    : null,
                child: auth.getReachableUrl(user?.profilePhotoUrl) != null
                    ? null
                    : Text(
                        userInitial,
                        style: TextStyle(
                          color: accentColor,
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      user?.name ?? 'User',
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: accentColor.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        roleLabel,
                        style: GoogleFonts.inter(
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                          color: accentColor,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          // Logout Button
          InkWell(
            onTap: () => _showLogout(context),
            borderRadius: BorderRadius.circular(10),
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
              decoration: BoxDecoration(
                border: Border.all(color: AppColors.error.withValues(alpha: 0.3)),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.logout_rounded, color: AppColors.error, size: 14),
                  const SizedBox(width: 6),
                  Text(
                    'Sign Out',
                    style: GoogleFonts.inter(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: AppColors.error,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showLogout(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('Confirm Logout',
            style: GoogleFonts.inter(
                color: AppColors.textPrimary, fontWeight: FontWeight.bold)),
        content: Text('Are you sure you want to logout?',
            style: GoogleFonts.inter(color: AppColors.textSecondary)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text('Cancel',
                style: GoogleFonts.inter(color: AppColors.textSecondary)),
          ),
          ElevatedButton(
            onPressed: () async {
              final router = GoRouter.of(ctx);
              Navigator.pop(ctx);
              await auth.logout();
              router.go('/login');
            },
            style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.error,
                foregroundColor: Colors.white),
            child: Text('Logout',
                style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }
}

// ─── Desktop Top Header ───────────────────────────────────────────────────────
class _DesktopHeader extends StatelessWidget {
  final String title;
  final Color accentColor;

  const _DesktopHeader({
    required this.title,
    required this.accentColor,
  });

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final userInitial = (user != null && user.name.trim().isNotEmpty)
        ? user.name.trim().substring(0, 1).toUpperCase()
        : 'U';
    final auth = context.watch<AuthProvider>();

    return Container(
      height: 70,
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border(
          bottom: BorderSide(color: AppColors.borderLight, width: 1),
        ),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Row(
        children: [
          // Screen Title
          Text(
            title,
            style: GoogleFonts.inter(
              fontSize: 18,
              fontWeight: FontWeight.w900,
              color: AppColors.textPrimary,
              letterSpacing: -0.5,
            ),
          ),
          const Spacer(),
          // Connection Status
          const SyncDot(),
          const SizedBox(width: 14),
          // Theme Switcher
          _IconBtn(
            icon: auth.themeMode == ThemeMode.dark
                ? Icons.light_mode_outlined
                : Icons.dark_mode_outlined,
            onTap: () => auth.toggleTheme(),
          ),
          const SizedBox(width: 10),
          // Notifications
          _IconBtn(
            icon: Icons.notifications_outlined,
            onTap: () => context.push('/notifications'),
          ),
          if (user?.role != 'student') ...[
            const SizedBox(width: 10),
            // Chat
            _IconBtn(
              icon: Icons.chat_bubble_outline_rounded,
              onTap: () => context.push('/chat'),
            ),
          ],
          const SizedBox(width: 14),
          // Profile Avatar Triggering Sheet
          GestureDetector(
            onTap: () {
              showModalBottomSheet(
                context: context,
                backgroundColor: AppColors.surface,
                shape: const RoundedRectangleBorder(
                  borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
                ),
                builder: (ctx) => _ProfileSheet(auth: auth),
              );
            },
            child: CircleAvatar(
              radius: 18,
              backgroundColor: accentColor.withValues(alpha: 0.15),
              backgroundImage: auth.getReachableUrl(user?.profilePhotoUrl) != null
                  ? NetworkImage(auth.getReachableUrl(user?.profilePhotoUrl)!)
                  : null,
              child: auth.getReachableUrl(user?.profilePhotoUrl) != null
                  ? null
                  : Text(
                      userInitial,
                      style: TextStyle(
                        color: accentColor,
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
            ),
          ),
        ],
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
              final itemAccent = item.iconColor ?? accentColor;
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
                          ? itemAccent.withValues(alpha: 0.12)
                          : Colors.transparent,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          selected ? item.activeIcon : item.icon,
                          color: selected ? itemAccent : AppColors.textSecondary,
                          size: 22,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          item.label,
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            fontWeight: selected ? FontWeight.bold : FontWeight.w500,
                            color: selected ? itemAccent : AppColors.textSecondary,
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
  final Color? iconBg;
  final Color? iconColor;

  const AHNavItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
    this.iconBg,
    this.iconColor,
  });
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
              style: GoogleFonts.inter(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: GoogleFonts.inter(
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
          style: GoogleFonts.inter(
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
              style: GoogleFonts.inter(
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
                      style: GoogleFonts.inter(
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                          color: AppColors.textPrimary)),
                  const SizedBox(height: 2),
                  Text(subtitle,
                      style: GoogleFonts.inter(
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
                    style: GoogleFonts.inter(
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

// ─── Mobile Drawer ──────────────────────────────────────────────────────────
class _MobileDrawer extends StatelessWidget {
  final List<AHNavItem> navItems;
  final int selectedIndex;
  final ValueChanged<int> onTabSelected;
  final Color accentColor;

  const _MobileDrawer({
    required this.navItems,
    required this.selectedIndex,
    required this.onTabSelected,
    required this.accentColor,
  });

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final isDark = auth.themeMode == ThemeMode.dark;
    final schoolLogo = auth.tenantLogoUrl;
    final schoolName = auth.tenantName ?? 'AcademyHub';

    return Drawer(
      backgroundColor: AppColors.background,
      elevation: 0,
      width: 290,
      child: SafeArea(
        child: Column(
          children: [
            // Top close button
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
              child: Align(
                alignment: Alignment.centerRight,
                child: GestureDetector(
                  onTap: () => Navigator.of(context).pop(),
                  child: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.borderLight),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.03),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        )
                      ],
                    ),
                    child: Icon(
                      Icons.close_rounded,
                      color: AppColors.textSecondary,
                      size: 20,
                    ),
                  ),
                ),
              ),
            ),

            // Branding Info Card
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 4.0),
              padding: const EdgeInsets.all(16.0),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.borderLight),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  )
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: isDark ? AppColors.surface2 : Colors.deepPurple.shade50,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: isDark ? AppColors.borderLight : Colors.deepPurple.shade100,
                        width: 1.5,
                      ),
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: schoolLogo != null && schoolLogo.isNotEmpty
                          ? Image.network(
                              schoolLogo,
                              fit: BoxFit.contain,
                              errorBuilder: (_, _, _) => Padding(
                                padding: const EdgeInsets.all(4),
                                child: Image.asset('lib/Alogo.png', fit: BoxFit.contain),
                              ),
                            )
                          : Padding(
                              padding: const EdgeInsets.all(6),
                              child: Image.asset('lib/Alogo.png', fit: BoxFit.contain),
                            ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          schoolName,
                          style: GoogleFonts.inter(
                            fontSize: 14,
                            fontWeight: FontWeight.w900,
                            color: AppColors.textPrimary,
                            letterSpacing: -0.3,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Smart Learning System',
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: accentColor,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Menu Section Label
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 8.0),
              child: Align(
                alignment: Alignment.centerLeft,
                child: Text(
                  'MAIN MENU',
                  style: GoogleFonts.inter(
                    fontSize: 10,
                    fontWeight: FontWeight.w900,
                    color: AppColors.textMuted,
                    letterSpacing: 1.5,
                  ),
                ),
              ),
            ),

            // Navigation items list
            Expanded(
              child: ListView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 16.0),
                itemCount: navItems.length,
                itemBuilder: (context, i) {
                  final item = navItems[i];
                  final isSelected = selectedIndex == i;
                  final itemAccent = item.iconColor ?? accentColor;
                  final itemBg = item.iconBg ?? itemAccent.withValues(alpha: 0.12);

                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 4.0),
                    child: InkWell(
                      onTap: () {
                        Navigator.of(context).pop();
                        onTabSelected(i);
                      },
                      borderRadius: BorderRadius.circular(16),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12.0, vertical: 10.0),
                        decoration: BoxDecoration(
                          color: isSelected ? accentColor : Colors.transparent,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: isSelected
                              ? [
                                  BoxShadow(
                                    color: accentColor.withValues(alpha: 0.25),
                                    blurRadius: 12,
                                    offset: const Offset(0, 4),
                                  )
                                ]
                              : null,
                        ),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: isSelected ? Colors.white.withValues(alpha: 0.2) : itemBg,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Icon(
                                item.icon,
                                size: 20,
                                color: isSelected ? Colors.white : itemAccent,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                item.label,
                                style: GoogleFonts.inter(
                                  fontSize: 14,
                                  fontWeight: isSelected ? FontWeight.w900 : FontWeight.bold,
                                  color: isSelected ? Colors.white : AppColors.textPrimary,
                                ),
                              ),
                            ),
                            if (isSelected)
                              Container(
                                padding: const EdgeInsets.all(4),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.2),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(
                                  Icons.chevron_right_rounded,
                                  size: 14,
                                  color: Colors.white,
                                ),
                              ),
                          ],
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),

            // Logout footer
            Container(
              padding: const EdgeInsets.all(16.0),
              decoration: BoxDecoration(
                color: AppColors.surface,
                border: Border(
                  top: BorderSide(color: AppColors.borderLight, width: 0.5),
                ),
              ),
              child: InkWell(
                onTap: () async {
                  Navigator.of(context).pop();
                  await auth.logout();
                  if (context.mounted) {
                    context.go('/login');
                  }
                },
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 12.0),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(
                        Icons.logout_rounded,
                        color: Color(0xFFDC2626),
                        size: 16,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Logout',
                        style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFFDC2626),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}