import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';
import '../../core/constants.dart';
import 'announcement_create_dialog.dart';
import 'package:flutter_animate/flutter_animate.dart';

class AdminHome extends StatefulWidget {
  const AdminHome({super.key});

  @override
  State<AdminHome> createState() => _AdminHomeState();
}

class _AdminHomeState extends State<AdminHome> {
  final _db = DatabaseHelper();
  StreamSubscription? _syncSub;

  // Static cache to preserve data across page reconstructions
  static List<Map<String, dynamic>> _cachedStudents = [];
  static List<Map<String, dynamic>> _cachedHomework = [];
  static List<Map<String, dynamic>> _cachedAnnouncements = [];
  static List<dynamic>              _cachedBilling = [];
  static bool                       _wasLoaded = false;
  static String                     _lastUserKey = '';

  List<Map<String, dynamic>> _students      = _cachedStudents;
  List<Map<String, dynamic>> _homework      = _cachedHomework;
  List<Map<String, dynamic>> _announcements = _cachedAnnouncements;
  List<dynamic>              _billingTransactions = _cachedBilling;
  late bool _loading = !_wasLoaded;
  int _selectedTab = 0;

  @override
  void initState() {
    super.initState();
    _load();
    final auth = context.read<AuthProvider>();
    _syncSub = auth.syncService.syncStatusStream.listen((status) {
      if (status == SyncStatus.synced && mounted) {
        _load();
      }
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

    final isFirstLoad = !_wasLoaded;
    if (isFirstLoad) {
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
          _loading = _students.isEmpty && _billingTransactions.isEmpty && _homework.isEmpty && _announcements.isEmpty;
          
          _cachedStudents = _students;
          _cachedHomework = _homework;
          _cachedAnnouncements = _announcements;
          _cachedBilling = _billingTransactions;
          _wasLoaded = !_loading;
        });
      }
    }

    try {
      final r = await auth.apiService.getWithCache('/students');
      _students = ((r['data'] as List?) ?? []).cast<Map<String, dynamic>>();
    } catch (_) {}

    _homework      = await _db.getAllHomework();
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

    if (mounted) {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;
    const accent  = AppColors.adminAccent;
    final role = auth.user?.role ?? 'admin';
    final isAdmin = role == 'admin';
    final isBursar = role == 'bursar';
    final hasHomework = auth.isPluginActive('homework');
    final hasCbt = auth.isPluginActive('cbt');

    final activeTabs = <AHNavItem>[
      const AHNavItem(
          icon: Icons.terminal_outlined,
          activeIcon: Icons.terminal_rounded,
          label: 'Command'),
      const AHNavItem(
          icon: Icons.people_outline,
          activeIcon: Icons.people_rounded,
          label: 'Students'),
    ];

    final activePages = <Widget>[
      _buildCommand(context, isAdmin, isBursar, hasHomework, hasCbt),
      _buildStudents(),
    ];

    if (isAdmin) {
      if (hasHomework) {
        activeTabs.add(const AHNavItem(
            icon: Icons.assignment_outlined,
            activeIcon: Icons.assignment_rounded,
            label: 'Homework'));
        activePages.add(_buildHomework());
      }
      activeTabs.add(const AHNavItem(
          icon: Icons.campaign_outlined,
          activeIcon: Icons.campaign_rounded,
          label: 'News'));
      activePages.add(_buildAnnouncements());
    } else if (isBursar) {
      activeTabs.add(const AHNavItem(
          icon: Icons.receipt_long_outlined,
          activeIcon: Icons.receipt_long_rounded,
          label: 'Transactions'));
      activePages.add(_buildBursarTransactions(primary));
    }

    int currentTab = _selectedTab;
    if (currentTab >= activeTabs.length) {
      currentTab = 0;
    }

    return RoleShell(
      title: isBursar ? 'Bursar Portal' : 'Admin Portal',
      body: Column(
        children: [
          if (_loading) LinearProgressIndicator(color: primary, minHeight: 2),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _load,
              color: primary,
              child: activePages[currentTab],
            ),
          ),
          AHBottomNav(
            items: activeTabs,
            selectedIndex: currentTab,
            onTap: (i) => setState(() => _selectedTab = i),
            accentColor: accent,
          ),
        ],
      ),
    );
  }

  Widget _buildSkeleton(Color primary, bool isBursar) {
    return ListView(
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      children: [
        // Hero skeleton card
        Container(
          height: 140,
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.borderLight),
          ),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(width: 120, height: 16, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4))),
              const SizedBox(height: 10),
              Container(width: 180, height: 24, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(6))),
              const Spacer(),
              Row(
                children: [
                  Container(width: 100, height: 32, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20))),
                  const SizedBox(width: 10),
                  Container(width: 100, height: 32, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20))),
                ],
              )
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Section header skeleton
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Container(width: 150, height: 18, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4))),
            Container(width: 80, height: 14, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4))),
          ],
        ),
        const SizedBox(height: 12),

        // Grid skeleton
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: 1.4,
          children: List.generate(4, (_) => Container(
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.borderLight),
            ),
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(width: 30, height: 30, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), shape: BoxShape.circle)),
                const SizedBox(height: 12),
                Container(width: 80, height: 20, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(4))),
                const SizedBox(height: 6),
                Container(width: 50, height: 12, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(3))),
              ],
            ),
          )),
        ),
        const SizedBox(height: 24),

        // Quick action list skeleton
        Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Column(
            children: List.generate(3, (idx) => Column(
              children: [
                Padding(
                  padding: const EdgeInsets.all(14),
                  child: Row(
                    children: [
                      Container(width: 36, height: 36, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8))),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(width: 140, height: 14, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(4))),
                            const SizedBox(height: 6),
                            Container(width: 200, height: 10, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(3))),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                if (idx < 2) Divider(height: 1, color: AppColors.borderLight),
              ],
            )),
          ),
        ),
      ],
    )
    .animate(onPlay: (controller) => controller.repeat())
    .shimmer(duration: 1500.ms, color: Colors.white.withValues(alpha: 0.05));
  }

  // ─── Command Tab ────────────────────────────────────────────────────────────
  Widget _buildCommand(BuildContext context, bool isAdmin, bool isBursar, bool hasHomework, bool hasCbt) {
    final auth    = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;
    final user    = auth.user;

    if (_loading) {
      return _buildSkeleton(primary, isBursar);
    }

    // Compute Bursar dynamic dashboard stats
    double todayRevenue = 0.0;
    double monthRevenue = 0.0;
    final nowStr = DateTime.now().toIso8601String().substring(0, 10);
    final monthPrefix = DateTime.now().toIso8601String().substring(0, 7);

    for (final tx in _billingTransactions) {
      final rawAmt = tx['amount_paid'];
      double amt = 0.0;
      if (rawAmt != null) {
        if (rawAmt is num) {
          amt = rawAmt.toDouble();
        } else if (rawAmt is String) {
          amt = double.tryParse(rawAmt) ?? 0.0;
        }
      }
      final txDate = tx['date'] as String? ?? '';
      if (txDate.startsWith(nowStr)) {
        todayRevenue += amt;
      }
      if (txDate.startsWith(monthPrefix)) {
        monthRevenue += amt;
      }
    }

    // Dyn KPI Grid Cards
    final kpiCards = <Widget>[];
    if (isAdmin) {
      kpiCards.add(DarkStatCard(
        label: 'Students Enrolled',
        value: '${_students.length}',
        icon: Icons.people_alt_outlined,
        color: AppColors.info,
        onTap: () => context.push('/analytics-dashboard'),
      ));
      if (hasHomework) {
        kpiCards.add(DarkStatCard(
          label: 'Total Homework',
          value: '${_homework.length}',
          icon: Icons.assignment_outlined,
          color: AppColors.parentAccent,
          onTap: () => context.push('/analytics-dashboard'),
        ));
      }
      kpiCards.add(DarkStatCard(
        label: 'Announcements',
        value: '${_announcements.length}',
        icon: Icons.campaign_outlined,
        color: AppColors.success,
        onTap: () => context.push('/analytics-dashboard'),
      ));
      kpiCards.add(StreamBuilder<int>(
        stream: auth.syncService.pendingCountStream,
        builder: (_, snap) => DarkStatCard(
          label: 'Pending Sync',
          value: '${snap.data ?? 0}',
          icon: Icons.cloud_upload_outlined,
          color: AppColors.warning,
          onTap: () => context.push('/analytics-dashboard'),
        ),
      ));
    } else if (isBursar) {
      kpiCards.add(DarkStatCard(
        label: 'Students Enrolled',
        value: '${_students.length}',
        icon: Icons.people_alt_outlined,
        color: AppColors.info,
        onTap: () => setState(() => _selectedTab = 1),
      ));
      kpiCards.add(DarkStatCard(
        label: 'Collected Today',
        value: '₦${todayRevenue.toStringAsFixed(0)}',
        icon: Icons.payments_outlined,
        color: AppColors.success,
        onTap: () => setState(() => _selectedTab = 2),
      ));
      kpiCards.add(DarkStatCard(
        label: 'Collected This Month',
        value: '₦${monthRevenue.toStringAsFixed(0)}',
        icon: Icons.account_balance_wallet_outlined,
        color: AppColors.primary,
        onTap: () => setState(() => _selectedTab = 2),
      ));
      kpiCards.add(StreamBuilder<int>(
        stream: auth.syncService.pendingCountStream,
        builder: (_, snap) => DarkStatCard(
          label: 'Pending Sync',
          value: '${snap.data ?? 0}',
          icon: Icons.cloud_upload_outlined,
          color: AppColors.warning,
        ),
      ));
    }

    // Dyn Quick Actions
    final quickActionRows = <Widget>[];
    if (isAdmin) {
      quickActionRows.add(DarkActionRow(
        title: 'Take Attendance Register',
        subtitle: 'Mark student daily presence logs',
        icon: Icons.how_to_reg_rounded,
        color: AppColors.success,
        onTap: () => context.push('/attendance'),
      ));
      quickActionRows.add(Divider(height: 1, color: AppColors.borderLight));
      quickActionRows.add(DarkActionRow(
        title: 'Report Gradebook Scores',
        subtitle: 'Enter term test and exam scores',
        icon: Icons.edit_note_rounded,
        color: AppColors.info,
        onTap: () => context.push('/scores'),
      ));
      if (hasHomework) {
        quickActionRows.add(Divider(height: 1, color: AppColors.borderLight));
        quickActionRows.add(DarkActionRow(
          title: 'Homework Assignments',
          subtitle: 'Publish classes and home study lessons',
          icon: Icons.assignment_outlined,
          color: AppColors.parentAccent,
          onTap: () => context.push('/homework'),
        ));
      }
      if (hasCbt) {
        quickActionRows.add(Divider(height: 1, color: AppColors.borderLight));
        quickActionRows.add(DarkActionRow(
          title: 'CBT Exam Management',
          subtitle: 'Create and configure student exams',
          icon: Icons.computer_rounded,
          color: AppColors.primary,
          onTap: () => context.push('/cbt'),
        ));
      }
    } else if (isBursar) {
      quickActionRows.add(DarkActionRow(
        title: 'Record Fee Payment',
        subtitle: 'Select a student to pay outstanding fees',
        icon: Icons.payments_outlined,
        color: AppColors.success,
        onTap: () => setState(() => _selectedTab = 1),
      ));
      quickActionRows.add(Divider(height: 1, color: AppColors.borderLight));
      quickActionRows.add(DarkActionRow(
        title: 'Fee Transactions Ledger',
        subtitle: 'View school income payments record history',
        icon: Icons.receipt_long_outlined,
        color: AppColors.info,
        onTap: () => setState(() => _selectedTab = 2),
      ));
    }

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      children: [
        // Hero card
        if (isBursar)
          GlassHeroCard(
            gradientColors: [
              AppColors.success.withValues(alpha: 0.85),
              const Color(0xFF0F766E),
            ],
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Bursar Portal',
                            style: GoogleFonts.spaceGrotesk(
                                fontSize: 12, color: Colors.white60)),
                        Text(user?.name ?? 'Bursar',
                            style: GoogleFonts.spaceGrotesk(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: Colors.white)),
                      ],
                    ),
                    GestureDetector(
                      onTap: _load,
                      child: Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.sync_rounded,
                            color: Colors.white, size: 18),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    _heroPill('₦${todayRevenue.toStringAsFixed(0)}', 'Today', Icons.payments_outlined),
                    const SizedBox(width: 10),
                    _heroPill('₦${monthRevenue.toStringAsFixed(0)}', 'This Month', Icons.account_balance_wallet_outlined),
                  ],
                ),
              ],
            ),
          )
        else
          GlassHeroCard(
            gradientColors: [
              AppColors.adminAccent.withValues(alpha: 0.9),
              const Color(0xFF92400E),
            ],
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Welcome back,',
                            style: GoogleFonts.spaceGrotesk(
                                fontSize: 12, color: Colors.white60)),
                        Text(user?.name ?? 'Admin',
                            style: GoogleFonts.spaceGrotesk(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: Colors.white)),
                      ],
                    ),
                    GestureDetector(
                      onTap: _load,
                      child: Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.sync_rounded,
                            color: Colors.white, size: 18),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                // Status console row
                StreamBuilder<int>(
                  stream: auth.syncService.pendingCountStream,
                  builder: (ctx, snap) {
                    final pending   = snap.data ?? 0;
                    final hasPending = pending > 0;
                    return Row(
                      children: [
                        Container(
                          width: 8,
                          height: 8,
                          decoration: BoxDecoration(
                            color: hasPending ? AppColors.warning : AppColors.success,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            hasPending
                                ? '$pending pending sync transactions'
                                : 'All data synced • Local copy up to date',
                            style: GoogleFonts.spaceGrotesk(
                                fontSize: 12,
                                color: Colors.white70),
                          ),
                        ),
                        if (hasPending)
                          GestureDetector(
                            onTap: () => auth.syncService.syncNow(),
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 10, vertical: 5),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text('Sync Now',
                                  style: GoogleFonts.spaceGrotesk(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.white)),
                            ),
                          ),
                      ],
                    );
                  },
                ),
              ],
            ),
          ),
        const SizedBox(height: 20),

        // KPI Grid
        SectionHeader(
          title: isBursar ? 'Collections Overview' : 'Performance & Resource KPI',
          actionLabel: isBursar ? null : 'Analyze Trends',
          onAction: isBursar ? null : () => context.push('/analytics-dashboard'),
        ),
        const SizedBox(height: 12),
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: 1.4,
          children: kpiCards,
        ).animate().fadeIn(duration: 400.ms).slideY(begin: 0.08, end: 0),
        const SizedBox(height: 24),

        // Quick Actions
        const SectionHeader(title: 'Quick Actions'),
        const SizedBox(height: 12),
        Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Column(
            children: quickActionRows,
          ),
        ),
        const SizedBox(height: 24),

        // Administrative Controls
        if (isAdmin) ...[
          const SectionHeader(title: 'Administrative Controls'),
          const SizedBox(height: 12),
          Container(
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.borderLight),
            ),
            child: Column(
              children: [
                DarkActionRow(
                  title: 'Academic Configurations',
                  subtitle: 'Manage sessions, terms, and periods',
                  icon: Icons.settings_applications_rounded,
                  color: AppColors.adminAccent,
                  onTap: () => context.push('/admin-sessions'),
                ),
                Divider(height: 1, color: AppColors.borderLight),
                DarkActionRow(
                  title: 'User Directory Profiles',
                  subtitle: 'Manage administrative and portal user profiles',
                  icon: Icons.supervised_user_circle_rounded,
                  color: AppColors.info,
                  onTap: () => context.push('/admin-users'),
                ),
                Divider(height: 1, color: AppColors.borderLight),
                DarkActionRow(
                  title: 'Database SQL Archives',
                  subtitle: 'Trigger system database backup archives',
                  icon: Icons.backup_rounded,
                  color: AppColors.success,
                  onTap: () => context.push('/admin-backups'),
                ),
                Divider(height: 1, color: AppColors.borderLight),
                DarkActionRow(
                  title: 'Broadcaster Dispatch',
                  subtitle: 'Bulk notification announcements broadcaster',
                  icon: Icons.settings_input_antenna_rounded,
                  color: AppColors.warning,
                  onTap: () => context.push('/admin-broadcast'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
        ],

        // Recent students preview
        SectionHeader(
          title: 'Recent Student Directory',
          actionLabel: 'View All',
          onAction: () => setState(() => _selectedTab = 1),
        ),
        const SizedBox(height: 12),
        ..._students.take(5).map((s) => _buildStudentRow(s, primary)),
      ],
    );
  }

  Widget _buildStudentRow(Map<String, dynamic> s, Color primary) {
    final name   = '${s['first_name'] ?? ''} ${s['last_name'] ?? ''}'.trim();
    final cls    = s['school_class']?['name'] ?? 'Unassigned';
    final admNo  = s['admission_number'] ?? 'N/A';

    return GestureDetector(
      onTap: () => _onStudentRowTap(s, primary),
      behavior: HitTestBehavior.opaque,
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.borderLight),
        ),
        child: Row(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: primary.withValues(alpha: 0.12),
              child: Text(
                name.isNotEmpty ? name[0].toUpperCase() : '?',
                style: TextStyle(
                    color: primary,
                    fontWeight: FontWeight.bold,
                    fontSize: 13),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(name.isEmpty ? 'Unknown' : name,
                      style: GoogleFonts.spaceGrotesk(
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                          color: AppColors.textPrimary)),
                  Text(cls,
                      style: GoogleFonts.spaceGrotesk(
                          fontSize: 11,
                          color: AppColors.textSecondary)),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(
                  horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.surface2,
                borderRadius: BorderRadius.circular(6),
                border: Border.all(color: AppColors.borderLight),
              ),
              child: Text(admNo,
                  style: GoogleFonts.spaceGrotesk(
                      fontSize: 10,
                      color: AppColors.textSecondary,
                      fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  void _onStudentRowTap(Map<String, dynamic> s, Color primary) {
    final auth = context.read<AuthProvider>();
    final isBursar = auth.user?.role == 'bursar';
    final isAdmin = auth.user?.role == 'admin';
    final isGatewayActive = auth.isPluginActive('payment-gateway');
    final name = '${s['first_name'] ?? ''} ${s['last_name'] ?? ''}'.trim();

    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (ctx) => SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
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
            Text(
              name,
              style: GoogleFonts.spaceGrotesk(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 16),
            if ((isBursar || isAdmin) && isGatewayActive) ...[
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.success.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.payment_rounded, color: AppColors.success),
                ),
                title: Text('Launch Payment Checkout', style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontWeight: FontWeight.bold)),
                subtitle: Text('Initiate payment gateway billing links', style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 11)),
                onTap: () {
                  Navigator.pop(ctx);
                  _launchCheckout(s['id'] as int, name);
                },
              ),
              Divider(height: 1, color: AppColors.borderLight),
            ],
            ListTile(
              leading: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.info.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.insights_rounded, color: AppColors.info),
              ),
              title: Text('View Academic Performance', style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontWeight: FontWeight.bold)),
              subtitle: Text('View attendance and score trends', style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 11)),
              onTap: () {
                Navigator.pop(ctx);
                context.push('/performance', extra: {
                  'studentId': s['id'],
                  'studentName': name,
                  'admissionNumber': s['admission_number'],
                });
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _launchCheckout(int studentId, String studentName) async {
    final auth = context.read<AuthProvider>();
    if (!(await auth.apiService.isOnline)) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Offline: Check connection to launch checkout links.'),
        backgroundColor: AppColors.error,
      ));
      return;
    }

    try {
      final response = await auth.apiService.dio.get('/billing/checkout-url',
          queryParameters: {'student_id': studentId});
      final checkoutUrl = response.data['checkout_url'] as String;
      final uri = Uri.parse(checkoutUrl);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        throw Exception('Could not launch payment gateway URL.');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Failed to initiate checkout: $e'),
                backgroundColor: AppColors.error));
      }
    }
  }

  Widget _heroPill(String value, String label, IconData icon) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.12),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Row(
          children: [
            Icon(icon, color: Colors.white70, size: 14),
            const SizedBox(width: 6),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(value,
                      style: GoogleFonts.spaceGrotesk(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: Colors.white),
                      overflow: TextOverflow.ellipsis),
                  Text(label,
                      style: GoogleFonts.spaceGrotesk(
                          fontSize: 9, color: Colors.white60)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBursarTransactions(Color primary) {
    if (_billingTransactions.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          const SizedBox(height: 80),
          _emptyState('No transaction logs retrieved.'),
        ],
      );
    }

    return ListView.separated(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 30),
      itemCount: _billingTransactions.length,
      separatorBuilder: (context, index) => const SizedBox(height: 8),
      itemBuilder: (ctx, idx) {
        final tx = _billingTransactions[idx];
        final isIn = tx['type'] == 'Income';
        final badgeColor = isIn ? AppColors.success : AppColors.error;
        final date = (tx['date'] as String?)?.split('T').first ?? '';
        final studentName = tx['student']?['first_name'] != null 
            ? '${tx['student']['first_name']} ${tx['student']['last_name'] ?? ''}'.trim()
            : 'N/A';
        final admNo = tx['student']?['admission_number'] ?? '';

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
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: badgeColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(
                  isIn ? Icons.arrow_downward_rounded : Icons.arrow_upward_rounded,
                  color: badgeColor,
                  size: 18,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      studentName,
                      style: GoogleFonts.spaceGrotesk(
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    if (admNo.isNotEmpty)
                      Text(
                        'Adm: $admNo',
                        style: GoogleFonts.spaceGrotesk(
                          fontSize: 11,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    const SizedBox(height: 4),
                    Text(
                      '${tx['category'] ?? "Fee"} • $date',
                      style: GoogleFonts.spaceGrotesk(
                        fontSize: 11,
                        color: AppColors.textMuted,
                      ),
                    ),
                  ],
                ),
              ),
              Text(
                '₦${tx['amount_paid']}',
                style: GoogleFonts.spaceGrotesk(
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                  color: badgeColor,
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  // ─── Students Tab ──────────────────────────────────────────────────────────
  Widget _buildStudents() {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;
    if (_students.isEmpty) {
      return ListView(physics: const AlwaysScrollableScrollPhysics(), children: [
        const SizedBox(height: 80),
        _emptyState('No students enrolled yet.'),
      ]);
    }
    return ListView.separated(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      itemCount: _students.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (_, i) => _buildStudentRow(_students[i], primary),
    );
  }

  // ─── Homework Tab ──────────────────────────────────────────────────────────
  Widget _buildHomework() {
    final now     = DateTime.now().toIso8601String().substring(0, 10);
    return _homework.isEmpty
        ? ListView(physics: const AlwaysScrollableScrollPhysics(), children: [
            const SizedBox(height: 80),
            _emptyState('No homework yet.'),
          ])
        : ListView.separated(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
            itemCount: _homework.length,
            separatorBuilder: (_, _) => const SizedBox(height: 8),
            itemBuilder: (_, i) {
              final h       = _homework[i];
              final due     = h['due_date'] as String? ?? '';
              final overdue = due.isNotEmpty && due.compareTo(now) < 0;
              final isDirty = (h['is_dirty'] as int? ?? 0) == 1;
              return Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                    color: isDirty
                        ? AppColors.warning.withValues(alpha: 0.3)
                        : overdue
                            ? AppColors.error.withValues(alpha: 0.3)
                            : AppColors.borderLight,
                  ),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: AppColors.parentAccent.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Icon(Icons.assignment_outlined,
                          color: AppColors.parentAccent, size: 18),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(children: [
                            Expanded(
                              child: Text(h['title'] ?? '',
                                  style: GoogleFonts.spaceGrotesk(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                      color: AppColors.textPrimary)),
                            ),
                            if (isDirty)
                              const Icon(Icons.cloud_off,
                                  size: 14, color: AppColors.warning),
                          ]),
                          const SizedBox(height: 2),
                          Text(h['subject_name'] ?? '',
                              style: GoogleFonts.spaceGrotesk(
                                  fontSize: 11,
                                  color: AppColors.textSecondary)),
                          const SizedBox(height: 4),
                          Text('Due: $due',
                              style: GoogleFonts.spaceGrotesk(
                                  fontSize: 11,
                                  fontWeight: overdue
                                      ? FontWeight.bold
                                      : FontWeight.normal,
                                  color: overdue
                                      ? AppColors.error
                                      : AppColors.textSecondary)),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            },
          );
  }

  // ─── Announcements Tab ─────────────────────────────────────────────────────
  Widget _buildAnnouncements() {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(16),
          child: SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton.icon(
              onPressed: () {
                showDialog(
                  context: context,
                  builder: (_) => const AnnouncementCreateDialog(),
                ).then((val) { if (val == true) _load(); });
              },
              icon: const Icon(Icons.add, size: 18),
              label: Text('Publish Announcement',
                  style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold)),
              style: ElevatedButton.styleFrom(
                backgroundColor: primary,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                elevation: 0,
              ),
            ),
          ),
        ),
        Expanded(
          child: _announcements.isEmpty
              ? _emptyState('No announcements yet.')
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                  itemCount: _announcements.length,
                  separatorBuilder: (_, _) =>
                      const SizedBox(height: 8),
                  itemBuilder: (_, i) {
                    final a    = _announcements[i];
                    final date = (a['published_at'] as String?)?.substring(0, 10) ?? '';
                    return Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.borderLight),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(children: [
                            Expanded(
                              child: Text(a['title'] ?? '',
                                  style: GoogleFonts.spaceGrotesk(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                      color: AppColors.textPrimary)),
                            ),
                            Text(date,
                                style: GoogleFonts.spaceGrotesk(
                                    fontSize: 11,
                                    color: AppColors.textMuted)),
                          ]),
                          const SizedBox(height: 6),
                          Text(a['body'] ?? '',
                              style: GoogleFonts.spaceGrotesk(
                                  fontSize: 12,
                                  color: AppColors.textSecondary,
                                  height: 1.4)),
                          if ((a['author_name'] as String?)?.isNotEmpty == true) ...[
                            const SizedBox(height: 6),
                            Text('— ${a['author_name']}',
                                style: GoogleFonts.spaceGrotesk(
                                    fontSize: 11,
                                    color: AppColors.textMuted)),
                          ],
                        ],
                      ),
                    );
                  },
                ),
        ),
      ],
    );
  }

  Widget _emptyState(String msg) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.inbox_outlined, size: 40, color: AppColors.textMuted),
          const SizedBox(height: 12),
          Text(msg,
              style: GoogleFonts.spaceGrotesk(
                  color: AppColors.textSecondary, fontSize: 13)),
        ],
      ),
    );
  }
}
