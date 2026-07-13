import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';
import '../../core/constants.dart';
import 'announcement_create_dialog.dart';

class AdminHome extends StatefulWidget {
  const AdminHome({super.key});

  @override
  State<AdminHome> createState() => _AdminHomeState();
}

class _AdminHomeState extends State<AdminHome> {
  final _db = DatabaseHelper();
  StreamSubscription? _syncSub;

  static List<Map<String, dynamic>> _cachedStudents = [];
  static List<Map<String, dynamic>> _cachedHomework = [];
  static List<Map<String, dynamic>> _cachedAnnouncements = [];
  static List<dynamic> _cachedBilling = [];
  static bool _wasLoaded = false;
  static String _lastUserKey = '';

  List<Map<String, dynamic>> _students = _cachedStudents;
  List<Map<String, dynamic>> _homework = _cachedHomework;
  List<Map<String, dynamic>> _announcements = _cachedAnnouncements;
  List<dynamic> _billingTransactions = _cachedBilling;
  bool _loading = !_wasLoaded;
  int _selectedTab = 0;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _load();
    final auth = context.read<AuthProvider>();
    _syncSub = auth.syncService.syncStatusStream.listen((status) {
      if (status == SyncStatus.synced && mounted) _load();
    });
  }

  @override
  void dispose() {
    _syncSub?.cancel();
    super.dispose();
  }

  Future<void> _load() async {
    final auth = context.read<AuthProvider>();
    auth.refreshPlugins();

    final currentUserKey = '${auth.user?.id}_${auth.tenantSlug}';
    if (_lastUserKey != currentUserKey) {
      _cachedStudents = [];
      _cachedHomework = [];
      _cachedAnnouncements = [];
      _cachedBilling = [];
      _wasLoaded = false;
      _lastUserKey = currentUserKey;
      _students = [];
      _homework = [];
      _announcements = [];
      _billingTransactions = [];
      _loading = true;
    }

    // Load from local DB immediately — fast, no network wait
    final cachedStudents = await _db.getAllStudents();
    final cachedHomework = await _db.getAllHomework();
    final cachedAnnouncements = await _db.getAnnouncements();
    List<dynamic> cachedBilling = [];
    try {
      final cachedBillingData = await auth.apiService.dbHelper.getCache('/billing');
      if (cachedBillingData != null) {
        final decoded = jsonDecode(cachedBillingData);
        cachedBilling = (decoded['data'] as List?) ?? [];
      }
    } catch (_) {}

    if (mounted) {
      setState(() {
        _students = cachedStudents;
        _homework = cachedHomework;
        _announcements = cachedAnnouncements;
        _billingTransactions = cachedBilling;
        _loading = false;
        _cachedStudents = _students;
        _cachedHomework = _homework;
        _cachedAnnouncements = _announcements;
        _cachedBilling = _billingTransactions;
        _wasLoaded = true;
      });
    }

    // Silently refresh from network in background
    try {
      final r = await auth.apiService.getWithCache('/students');
      _students = ((r['data'] as List?) ?? []).cast<Map<String, dynamic>>();
    } catch (_) {}

    _homework = await _db.getAllHomework();
    _announcements = await _db.getAnnouncements();

    if (auth.user?.role == 'bursar' || auth.user?.role == 'admin') {
      try {
        final r = await auth.apiService.getWithCache('/billing');
        _billingTransactions = (r['data'] as List?) ?? [];
      } catch (_) {}
    }

    _cachedStudents = _students;
    _cachedHomework = _homework;
    _cachedAnnouncements = _announcements;
    _cachedBilling = _billingTransactions;
    _wasLoaded = true;
    if (mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;
    final role = auth.user?.role ?? 'admin';
    final isAdmin = role == 'admin';
    final isBursar = role == 'bursar';
    final hasHomework = auth.isPluginActive('homework');
    final hasCbt = auth.isPluginActive('cbt');

    final activeTabs = <AHNavItem>[
      const AHNavItem(
        icon: Icons.dashboard_outlined,
        activeIcon: Icons.dashboard_rounded,
        label: 'Dashboard',
        iconBg: Color(0xFFE0E7FF),
        iconColor: Color(0xFF4F46E5),
      ),
      const AHNavItem(
        icon: Icons.people_outline,
        activeIcon: Icons.people_rounded,
        label: 'Students',
        iconBg: Color(0xFFFFE4E6),
        iconColor: Color(0xFFE11D48),
      ),
    ];

    final activePages = <Widget>[
      _buildDashboard(context, auth, isAdmin, isBursar, hasHomework, hasCbt, primary),
      _buildStudents(),
    ];

    if (isAdmin) {
      if (hasHomework) {
        activeTabs.add(const AHNavItem(
          icon: Icons.assignment_outlined,
          activeIcon: Icons.assignment_rounded,
          label: 'Homework',
          iconBg: Color(0xFFF3E8FF),
          iconColor: Color(0xFF7C3AED),
        ));
        activePages.add(_buildHomework());
      }
      activeTabs.add(const AHNavItem(
        icon: Icons.campaign_outlined,
        activeIcon: Icons.campaign_rounded,
        label: 'News',
        iconBg: Color(0xFFFEF9C3),
        iconColor: Color(0xFFCA8A04),
      ));
      activePages.add(_buildAnnouncements());
    } else if (isBursar) {
      activeTabs.add(const AHNavItem(
        icon: Icons.receipt_long_outlined,
        activeIcon: Icons.receipt_long_rounded,
        label: 'Billing',
        iconBg: Color(0xFFE0F2FE),
        iconColor: Color(0xFF0284C7),
      ));
      activePages.add(_buildBursarTransactions(primary));
    }

    int currentTab = _selectedTab;
    if (currentTab >= activeTabs.length) currentTab = 0;

    return RoleShell(
      title: isAdmin ? 'Admin Panel' : 'Bursar Panel',
      navItems: activeTabs,
      selectedIndex: currentTab,
      onTabSelected: (i) => setState(() => _selectedTab = i),
      accentColor: AppColors.adminAccent,
      loading: _loading,
      onRefresh: _load,
      body: activePages[currentTab],
    );
  }

  // ─── Dashboard Tab ─────────────────────────────────────────────────────────
  Widget _buildDashboard(BuildContext context, AuthProvider auth, bool isAdmin,
      bool isBursar, bool hasHomework, bool hasCbt, Color primary) {
    final user = auth.user;

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        // ── Welcome Banner ──────────────────────────────────────────────────
        _WelcomeBanner(user: user, auth: auth, primary: primary),
        const SizedBox(height: 20),

        // ── Stats Row ───────────────────────────────────────────────────────
        Row(
          children: [
            Expanded(
              child: _StatCard(
                label: 'Students',
                value: '${_students.length}',
                icon: Icons.people_rounded,
                color: const Color(0xFF6366F1),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _StatCard(
                label: 'Homework',
                value: '${_homework.length}',
                icon: Icons.assignment_rounded,
                color: const Color(0xFF10B981),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _StatCard(
                label: 'News Items',
                value: '${_announcements.length}',
                icon: Icons.campaign_rounded,
                color: const Color(0xFFF59E0B),
              ),
            ),
          ],
        ),
        const SizedBox(height: 24),

        // ── Quick Actions ───────────────────────────────────────────────────
        _SectionHeader(title: 'Quick Actions'),
        const SizedBox(height: 12),
        GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisSpacing: 10,
          mainAxisSpacing: 10,
          childAspectRatio: 2.4,
          children: [
            _QuickAction(
              icon: Icons.people_outline,
              label: 'Students',
              color: const Color(0xFFE11D48),
              onTap: () => context.push('/students'),
            ),
            _QuickAction(
              icon: Icons.analytics_outlined,
              label: 'Analytics',
              color: const Color(0xFF4F46E5),
              onTap: () => context.push('/analytics-dashboard'),
            ),
            _QuickAction(
              icon: Icons.manage_accounts_outlined,
              label: 'Users',
              color: const Color(0xFF0284C7),
              onTap: () => context.push('/admin-users'),
            ),
            _QuickAction(
              icon: Icons.campaign_outlined,
              label: 'Broadcast',
              color: const Color(0xFFF59E0B),
              onTap: () => context.push('/admin-broadcast'),
            ),
            if (isAdmin) ...[
              _QuickAction(
                icon: Icons.settings_outlined,
                label: 'Sessions',
                color: const Color(0xFF7C3AED),
                onTap: () => context.push('/admin-sessions'),
              ),
              _QuickAction(
                icon: Icons.backup_outlined,
                label: 'Backups',
                color: const Color(0xFF10B981),
                onTap: () => context.push('/admin-backups'),
              ),
            ],
            if (hasCbt)
              _QuickAction(
                icon: Icons.computer_outlined,
                label: 'CBT Exams',
                color: const Color(0xFFE11D48),
                onTap: () => context.push('/cbt'),
              ),
            if (isBursar)
              _QuickAction(
                icon: Icons.receipt_long_outlined,
                label: 'Billing',
                color: const Color(0xFF0284C7),
                onTap: () {},
              ),
          ],
        ),
        const SizedBox(height: 24),

        // ── Recent Announcements ────────────────────────────────────────────
        if (_announcements.isNotEmpty) ...[
          _SectionHeader(
            title: 'Recent Announcements',
            action: 'See all',
            onAction: () {},
          ),
          const SizedBox(height: 12),
          ..._announcements.take(3).map((a) => _AnnouncementTile(a: a)),
        ],
      ],
    );
  }

  // ─── Students Tab ──────────────────────────────────────────────────────────
  Widget _buildStudents() {
    final filtered = _searchQuery.isEmpty
        ? _students
        : _students.where((s) {
            final name = '${s['first_name'] ?? ''} ${s['last_name'] ?? ''}'.toLowerCase();
            final adm = (s['admission_number'] ?? '').toString().toLowerCase();
            return name.contains(_searchQuery) || adm.contains(_searchQuery);
          }).toList();

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
          child: _SearchBar(
            hint: 'Search students by name or admission no...',
            onChanged: (v) => setState(() => _searchQuery = v.toLowerCase()),
          ),
        ),
        Expanded(
          child: filtered.isEmpty
              ? _EmptyState(
                  icon: Icons.people_outline,
                  title: _searchQuery.isEmpty ? 'No students yet' : 'No results found',
                  subtitle: _searchQuery.isEmpty
                      ? 'Students will appear here after sync'
                      : 'Try a different search term',
                )
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
                  itemCount: filtered.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (context, i) => _StudentTile(student: filtered[i]),
                ),
        ),
      ],
    );
  }

  // ─── Homework Tab ──────────────────────────────────────────────────────────
  Widget _buildHomework() {
    if (_homework.isEmpty) {
      return _EmptyState(
        icon: Icons.assignment_outlined,
        title: 'No homework recorded',
        subtitle: 'Homework assigned by teachers appears here',
      );
    }
    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
      itemCount: _homework.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (_, i) => _HomeworkTile(hw: _homework[i]),
    );
  }

  // ─── Announcements Tab ────────────────────────────────────────────────────
  Widget _buildAnnouncements() {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
          child: SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () async {
                await showDialog(
                  context: context,
                  builder: (_) => const AnnouncementCreateDialog(),
                );
                _load(); // refresh list after dialog closes
              },
              icon: const Icon(Icons.add_rounded),
              label: Text('Post Announcement',
                  style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                elevation: 0,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ),
        ),
        Expanded(
          child: _announcements.isEmpty
              ? _EmptyState(
                  icon: Icons.campaign_outlined,
                  title: 'No announcements yet',
                  subtitle: 'Post an announcement for students and staff',
                )
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 32),
                  itemCount: _announcements.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (_, i) => _AnnouncementTile(a: _announcements[i]),
                ),
        ),
      ],
    );
  }

  // ─── Bursar Billing Tab ────────────────────────────────────────────────────
  Widget _buildBursarTransactions(Color primary) {
    if (_billingTransactions.isEmpty) {
      return _EmptyState(
        icon: Icons.receipt_long_outlined,
        title: 'No transactions yet',
        subtitle: 'Billing records will appear here',
      );
    }
    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
      itemCount: _billingTransactions.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final tx = _billingTransactions[i] as Map<String, dynamic>? ?? {};
        return _InfoCard(
          title: tx['student_name']?.toString() ?? 'Student',
          subtitle: tx['description']?.toString() ?? 'Payment',
          trailing: '₦${tx['amount'] ?? '0'}',
          trailingColor: AppColors.success,
          icon: Icons.receipt_rounded,
          iconColor: primary,
        );
      },
    );
  }
}

// ─── Reusable Components ──────────────────────────────────────────────────────

class _WelcomeBanner extends StatelessWidget {
  final User? user;
  final AuthProvider auth;
  final Color primary;
  const _WelcomeBanner({required this.user, required this.auth, required this.primary});

  @override
  Widget build(BuildContext context) {
    final logoUrl = auth.getReachableUrl(auth.tenantLogoUrl);
    final hasLogo = logoUrl != null && logoUrl.isNotEmpty;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            const Color(0xFF1E1B4B),
            const Color(0xFF312E81),
          ],
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Welcome back,',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    color: Colors.white60,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  user?.name ?? 'Admin',
                  style: GoogleFonts.inter(
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    (user?.role ?? 'admin').toUpperCase(),
                    style: GoogleFonts.inter(
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      color: Colors.white70,
                      letterSpacing: 1,
                    ),
                  ),
                ),
              ],
            ),
          ),
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(14),
              child: hasLogo
                  ? Image.network(logoUrl, fit: BoxFit.contain,
                      errorBuilder: (_, __, ___) =>
                          const Icon(Icons.school_rounded, color: Colors.white54, size: 28))
                  : const Icon(Icons.school_rounded, color: Colors.white54, size: 28),
            ),
          ),
        ],
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  const _StatCard({required this.label, required this.value, required this.icon, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: color),
          const SizedBox(height: 8),
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 22,
              fontWeight: FontWeight.w800,
              color: AppColors.textPrimary,
              letterSpacing: -0.5,
            ),
          ),
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 11,
              color: AppColors.textSecondary,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }
}

class _QuickAction extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;
  const _QuickAction({required this.icon, required this.label, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.borderLight),
        ),
        child: Row(
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color, size: 16),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                label,
                style: GoogleFonts.inter(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textPrimary,
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;
  final String? action;
  final VoidCallback? onAction;
  const _SectionHeader({required this.title, this.action, this.onAction});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          title,
          style: GoogleFonts.inter(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
        if (action != null)
          GestureDetector(
            onTap: onAction,
            child: Text(
              action!,
              style: GoogleFonts.inter(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: AppColors.primary,
              ),
            ),
          ),
      ],
    );
  }
}

class _AnnouncementTile extends StatelessWidget {
  final Map<String, dynamic> a;
  const _AnnouncementTile({required this.a});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: AppColors.warning.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(Icons.campaign_rounded, color: AppColors.warning, size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  a['title']?.toString() ?? 'Announcement',
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textPrimary,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 3),
                Text(
                  a['body']?.toString() ?? a['content']?.toString() ?? '',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: AppColors.textSecondary,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _StudentTile extends StatelessWidget {
  final Map<String, dynamic> student;
  const _StudentTile({required this.student});

  @override
  Widget build(BuildContext context) {
    final name = '${student['first_name'] ?? ''} ${student['last_name'] ?? ''}'.trim();
    final admission = student['admission_number']?.toString() ?? '';
    final className = student['class_name']?.toString() ?? student['classroom']?.toString() ?? '';
    final initials = name.isNotEmpty ? name[0].toUpperCase() : 'S';

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 20,
            backgroundColor: AppColors.primary.withValues(alpha: 0.1),
            child: Text(
              initials,
              style: GoogleFonts.inter(
                color: AppColors.primary,
                fontWeight: FontWeight.w700,
                fontSize: 14,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name.isEmpty ? 'Unknown Student' : name,
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  [if (admission.isNotEmpty) admission, if (className.isNotEmpty) className]
                      .join(' · '),
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          Icon(Icons.chevron_right_rounded, color: AppColors.textMuted, size: 20),
        ],
      ),
    );
  }
}

class _HomeworkTile extends StatelessWidget {
  final Map<String, dynamic> hw;
  const _HomeworkTile({required this.hw});

  @override
  Widget build(BuildContext context) {
    return _InfoCard(
      title: hw['title']?.toString() ?? 'Homework',
      subtitle: 'Due: ${hw['due_date']?.toString() ?? 'No date'}',
      icon: Icons.assignment_outlined,
      iconColor: const Color(0xFF7C3AED),
    );
  }
}

class _InfoCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final Color iconColor;
  final String? trailing;
  final Color? trailingColor;
  const _InfoCard({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.iconColor,
    this.trailing,
    this.trailingColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: iconColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: iconColor, size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: GoogleFonts.inter(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textPrimary)),
                const SizedBox(height: 2),
                Text(subtitle,
                    style: GoogleFonts.inter(
                        fontSize: 12, color: AppColors.textSecondary)),
              ],
            ),
          ),
          if (trailing != null)
            Text(
              trailing!,
              style: GoogleFonts.inter(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: trailingColor ?? AppColors.textPrimary,
              ),
            ),
        ],
      ),
    );
  }
}

class _SearchBar extends StatelessWidget {
  final String hint;
  final ValueChanged<String> onChanged;
  const _SearchBar({required this.hint, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: TextField(
        onChanged: onChanged,
        style: GoogleFonts.inter(fontSize: 14, color: AppColors.textPrimary),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: GoogleFonts.inter(fontSize: 13, color: AppColors.textMuted),
          prefixIcon: Icon(Icons.search_rounded, color: AppColors.textMuted, size: 20),
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        ),
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  const _EmptyState({required this.icon, required this.title, required this.subtitle});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: AppColors.surface2,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.borderLight),
              ),
              child: Icon(icon, size: 32, color: AppColors.textMuted),
            ),
            const SizedBox(height: 16),
            Text(
              title,
              style: GoogleFonts.inter(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: AppColors.textPrimary,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 6),
            Text(
              subtitle,
              style: GoogleFonts.inter(
                fontSize: 13,
                color: AppColors.textSecondary,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
