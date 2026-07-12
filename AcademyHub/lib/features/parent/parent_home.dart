import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/mobile_layout.dart';
import '../../core/constants.dart';

class ParentHome extends StatefulWidget {
  const ParentHome({super.key});

  @override
  State<ParentHome> createState() => _ParentHomeState();
}

class _ParentHomeState extends State<ParentHome> {
  final _db = DatabaseHelper();
  StreamSubscription? _syncSub;

  // Static cache to preserve data across page reconstructions
  static List<Map<String, dynamic>> _cachedChildren = [];
  static List<Map<String, dynamic>> _cachedHomework = [];
  static List<Map<String, dynamic>> _cachedAnnouncements = [];
  static List<dynamic>              _cachedBilling = [];
  static bool                       _wasLoaded = false;
  static String                     _lastUserKey = '';

  List<Map<String, dynamic>> _children      = _cachedChildren;
  List<Map<String, dynamic>> _homework      = _cachedHomework;
  List<Map<String, dynamic>> _announcements = _cachedAnnouncements;
  List<dynamic>              _billing       = _cachedBilling;
  late bool _loading = !_wasLoaded;
  bool _isOnline = false;
  int? _checkoutLoadingStudentId;
  int _selectedTab = 0;

  static const _tabs = [
    AHNavItem(
      icon: Icons.dashboard_outlined,
      activeIcon: Icons.dashboard_rounded,
      label: 'Overview',
      iconBg: Color(0xFFE0E7FF),
      iconColor: Color(0xFF4F46E5),
    ),
    AHNavItem(
      icon: Icons.people_outline,
      activeIcon: Icons.people_rounded,
      label: 'Children',
      iconBg: Color(0xFFFFE4E6),
      iconColor: Color(0xFFE11D48),
    ),
    AHNavItem(
      icon: Icons.receipt_long_outlined,
      activeIcon: Icons.receipt_long_rounded,
      label: 'Fees',
      iconBg: Color(0xFFE0F2FE),
      iconColor: Color(0xFF0284C7),
    ),
    AHNavItem(
      icon: Icons.campaign_outlined,
      activeIcon: Icons.campaign_rounded,
      label: 'Updates',
      iconBg: Color(0xFFFEF9C3),
      iconColor: Color(0xFFCA8A04),
    ),
  ];

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
    _isOnline = await auth.apiService.isOnline;
    auth.refreshPlugins();

    final currentUserKey = '${auth.user?.id}_${auth.tenantSlug}';
    if (_lastUserKey != currentUserKey) {
      _cachedChildren = [];
      _cachedHomework = [];
      _cachedAnnouncements = [];
      _cachedBilling = [];
      _wasLoaded = false;
      _lastUserKey = currentUserKey;
      _children = [];
      _homework = [];
      _announcements = [];
      _billing = [];
      _loading = true;
    }

    // Always load from local DB first and show UI immediately
    final cachedChildren = await _db.getAllStudents();
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
        _children = cachedChildren;
        _homework = cachedHomework;
        _announcements = cachedAnnouncements;
        _billing = cachedBilling;
        _loading = false; // Always show UI immediately
        _cachedChildren = _children;
        _cachedHomework = _homework;
        _cachedAnnouncements = _announcements;
        _cachedBilling = _billing;
        _wasLoaded = true;
      });
    }

    try {
      final r = await auth.apiService.getWithCache('/students');
      final list = ((r['data'] as List?) ?? []).cast<Map<String, dynamic>>();
      await _db.upsertStudents(list);
      _children = list;
    } catch (_) {
      _children = await _db.getAllStudents();
    }

    try {
      final r = await auth.apiService.getWithCache('/billing');
      _billing = (r['data'] as List?) ?? [];
    } catch (_) {}

    _homework      = await _db.getAllHomework();
    _announcements = await _db.getAnnouncements();

    _cachedChildren = _children;
    _cachedHomework = _homework;
    _cachedAnnouncements = _announcements;
    _cachedBilling = _billing;
    _wasLoaded = true;

    if (mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;
    const accent  = AppColors.parentAccent;

    final pages = [
      _buildOverview(primary),
      _buildChildren(primary),
      _buildBilling(primary),
      _buildAnnouncements(),
    ];

    return RoleShell(
      title: 'Parent Portal',
      navItems: _tabs,
      selectedIndex: _selectedTab,
      onTabSelected: (i) => setState(() => _selectedTab = i),
      accentColor: accent,
      loading: _loading,
      onRefresh: _load,
      body: pages[_selectedTab],
    );
  }

  // ─── Overview Tab ──────────────────────────────────────────────────────────
  Widget _buildSkeleton(Color primary) {
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
              Container(width: 100, height: 14, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4))),
              const SizedBox(height: 10),
              Container(width: 160, height: 22, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(6))),
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
            Container(width: 120, height: 18, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4))),
            Container(width: 60, height: 14, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4))),
          ],
        ),
        const SizedBox(height: 12),

        // Children list items skeleton
        Column(
          children: List.generate(2, (_) => Container(
            margin: const EdgeInsets.only(bottom: 12),
            height: 80,
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.borderLight),
            ),
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(width: 44, height: 44, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), shape: BoxShape.circle)),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(width: 130, height: 14, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(4))),
                      const SizedBox(height: 6),
                      Container(width: 90, height: 10, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(3))),
                    ],
                  ),
                ),
                Container(width: 24, height: 24, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), shape: BoxShape.circle)),
              ],
            ),
          )),
        ),
        const SizedBox(height: 24),

        // Announcement / News card skeleton
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Container(width: 140, height: 18, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4))),
          ],
        ),
        const SizedBox(height: 12),

        Container(
          height: 100,
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.borderLight),
          ),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(width: 200, height: 14, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(4))),
              const SizedBox(height: 8),
              Container(width: double.infinity, height: 10, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(3))),
              const SizedBox(height: 6),
              Container(width: 150, height: 10, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(3))),
            ],
          ),
        ),
      ],
    )
    .animate(onPlay: (controller) => controller.repeat())
    .shimmer(duration: 1500.ms, color: Colors.white.withValues(alpha: 0.05));
  }

  Widget _buildOverview(Color primary) {
    if (_loading) {
      return _buildSkeleton(primary);
    }
    final user = context.watch<AuthProvider>().user;
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      children: [
        // Hero
        GlassHeroCard(
          gradientColors: [
            AppColors.parentAccent.withValues(alpha: 0.85),
            const Color(0xFF6B21A8),
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
                          style: GoogleFonts.inter(
                              fontSize: 12, color: Colors.white60)),
                      Text(user?.name ?? 'Parent',
                          style: GoogleFonts.inter(
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
              Row(
                children: [
                  _heroPill('${_children.length}', 'Children',
                      Icons.people_rounded),
                  const SizedBox(width: 10),
                  _heroPill('${_homework.length}', 'Homework',
                      Icons.assignment_rounded),
                  const SizedBox(width: 10),
                  _heroPill(
                      _isOnline ? 'Online' : 'Offline',
                      'Status',
                      _isOnline
                          ? Icons.wifi_rounded
                          : Icons.wifi_off_rounded),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _overviewCard(
                'Homework',
                '${_homework.length} assigned',
                Icons.assignment_rounded,
                AppColors.parentAccent,
                () => setState(() => _selectedTab = 3),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _overviewCard(
                'Updates',
                '${_announcements.length} news',
                Icons.campaign_rounded,
                AppColors.warning,
                () => setState(() => _selectedTab = 3),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _overviewCard(
                'Chats',
                'Message teachers',
                Icons.chat_bubble_outline,
                AppColors.primary,
                () => context.push('/chat'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _overviewCard(
                'Attendance Logs',
                'Detailed record',
                Icons.calendar_month,
                AppColors.info,
                () => context.push('/parent-attendance'),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        // WhatsApp Alert Switch Card
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
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
                  color: AppColors.success.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.notifications_active_outlined, color: AppColors.success, size: 18),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('WhatsApp Alerts',
                        style: GoogleFonts.inter(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary)),
                    Text('Subscribe to automated student updates.',
                        style: GoogleFonts.inter(
                            fontSize: 9,
                            color: AppColors.textSecondary)),
                  ],
                ),
              ),
              Switch(
                value: user?.whatsappSubscribed == true,
                activeThumbColor: AppColors.success,
                onChanged: (val) async {
                  final auth = context.read<AuthProvider>();
                  try {
                    final response = await auth.apiService.dio.post('/parent/whatsapp/toggle');
                    auth.user?.whatsappSubscribed = response.data['whatsapp_subscribed'] == true;
                    setState(() {});
                  } catch (_) {}
                },
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Guide
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.borderLight),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Quick Guidelines',
                  style: GoogleFonts.inter(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary)),
              const SizedBox(height: 10),
              _guideline(
                  "• Tap \"Performance\" on any child's card to view academic records and analytics."),
              _guideline(
                  '• Switch to "Children" tab to see all linked child profiles.'),
              _guideline(
                  '• To pay school fees online, switch to the "Fees" tab.'),
            ],
          ),
        ),
      ],
    );
  }

  Widget _heroPill(String value, String label, IconData icon) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.12),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: Colors.white70, size: 14),
            const SizedBox(height: 4),
            Text(value,
                style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: Colors.white)),
            Text(label,
                style: GoogleFonts.inter(
                    fontSize: 9, color: Colors.white60)),
          ],
        ),
      ),
    );
  }

  Widget _overviewCard(String title, String subtitle, IconData icon,
      Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
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
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title,
                      style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary)),
                  Text(subtitle,
                      style: GoogleFonts.inter(
                          fontSize: 10,
                          color: AppColors.textSecondary)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _guideline(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Text(text,
          style: GoogleFonts.inter(
              fontSize: 12,
              color: AppColors.textSecondary,
              height: 1.4)),
    );
  }

  // ─── Children Tab ──────────────────────────────────────────────────────────
  Widget _buildChildren(Color primary) {
    if (_children.isEmpty) {
      return ListView(physics: const AlwaysScrollableScrollPhysics(), children: [
        const SizedBox(height: 80),
        _empty('No children linked to this account.'),
      ]);
    }

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      children: [
        const SectionHeader(title: 'Associated Child Profiles'),
        const SizedBox(height: 14),
        // Horizontal scroll
        SizedBox(
          height: 200,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: _children.length,
            separatorBuilder: (_, _) => const SizedBox(width: 12),
            itemBuilder: (ctx, i) {
              final c    = _children[i];
              final name = '${c['first_name'] ?? ''} ${c['last_name'] ?? ''}'.trim();
              final cls  = c['school_class']?['name'] ??
                  c['section']?['name'] ??
                  'General';
              return Container(
                width: 200,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [
                      AppColors.parentAccent.withValues(alpha: 0.12),
                      AppColors.surface,
                    ],
                  ),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                      color: AppColors.parentAccent.withValues(alpha: 0.25)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 20,
                          backgroundColor:
                              AppColors.parentAccent.withValues(alpha: 0.15),
                          child: Text(
                            name.isNotEmpty ? name[0].toUpperCase() : '?',
                            style: const TextStyle(
                                color: AppColors.parentAccent,
                                fontWeight: FontWeight.bold,
                                fontSize: 16),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(name.isEmpty ? 'Unknown' : name,
                                  style: GoogleFonts.inter(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                      color: AppColors.textPrimary),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis),
                              Text(cls,
                                  style: GoogleFonts.inter(
                                      fontSize: 11,
                                      color: AppColors.textSecondary),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const Spacer(),
                    Text('Adm: ${c['admission_number'] ?? 'N/A'}',
                        style: GoogleFonts.inter(
                            fontSize: 10,
                            color: AppColors.textMuted,
                            fontWeight: FontWeight.w500)),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Expanded(
                          child: SizedBox(
                            height: 36,
                            child: ElevatedButton(
                              onPressed: () {
                                context.push('/performance', extra: {
                                  'studentId': c['id'],
                                  'studentName': name,
                                  'admissionNumber': c['admission_number'],
                                });
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.parentAccent,
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(8)),
                              ),
                              child: Text('Performance',
                                  style: GoogleFonts.inter(
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: SizedBox(
                            height: 36,
                            child: OutlinedButton(
                              onPressed: () {
                                context.push('/parent-attendance');
                              },
                              style: OutlinedButton.styleFrom(
                                foregroundColor: AppColors.parentAccent,
                                side: const BorderSide(color: AppColors.parentAccent),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(8)),
                              ),
                              child: Text('Attendance',
                                  style: GoogleFonts.inter(
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  // ─── Fees Tab ──────────────────────────────────────────────────────────────
  Future<void> _downloadReceipt(int transactionId) async {
    final auth = context.read<AuthProvider>();
    final urlString = '${auth.tenantApiUrl.replaceAll('/api', '')}/api/parent/billing/receipts/$transactionId';
    try {
      final uri = Uri.parse(urlString);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        throw Exception('Could not launch receipt URL.');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Failed to download receipt: $e'),
                backgroundColor: AppColors.error));
      }
    }
  }

  Future<void> _launchCheckout(int studentId, String studentName) async {
    final auth = context.read<AuthProvider>();
    if (!(await auth.apiService.isOnline)) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Offline: Check connection to pay fees online.'),
        backgroundColor: AppColors.error,
      ));
      return;
    }

    setState(() => _checkoutLoadingStudentId = studentId);
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
    } finally {
      if (mounted) setState(() => _checkoutLoadingStudentId = null);
    }
  }

  Widget _buildBilling(Color primary) {
    final auth             = context.watch<AuthProvider>();
    final isGatewayActive  = auth.isPluginActive('payment-gateway');

    if (_billing.isEmpty && !isGatewayActive) {
      return ListView(children: [
        const SizedBox(height: 80),
        _empty('No fee transactions found.'),
      ]);
    }

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        if (isGatewayActive && _children.isNotEmpty) ...[
          const SectionHeader(title: 'Online Fee Checkout'),
          const SizedBox(height: 12),
          ..._children.map((c) {
            final name            = '${c['first_name'] ?? ''} ${c['last_name'] ?? ''}'.trim();
            final cls             = c['school_class']?['name'] ?? c['section']?['name'] ?? 'General';
            final isCurrentLoading = _checkoutLoadingStudentId == c['id'];
            return Container(
              margin: const EdgeInsets.only(bottom: 10),
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
                      color: AppColors.success.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.payment_rounded,
                        color: AppColors.success, size: 18),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(name.isEmpty ? 'Unknown' : name,
                            style: GoogleFonts.inter(
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                                color: AppColors.textPrimary)),
                        Text('Class: $cls',
                            style: GoogleFonts.inter(
                                fontSize: 11,
                                color: AppColors.textSecondary)),
                        if (!_isOnline)
                          Text('No connection',
                              style: GoogleFonts.inter(
                                  fontSize: 10,
                                  color: AppColors.error)),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: isCurrentLoading
                        ? null
                        : () => _launchCheckout(c['id'] as int, name),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _isOnline
                          ? AppColors.success
                          : AppColors.surface2,
                      foregroundColor:
                          _isOnline ? Colors.white : AppColors.textSecondary,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 8),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8)),
                    ),
                    child: isCurrentLoading
                        ? const SizedBox(
                            width: 14,
                            height: 14,
                            child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white))
                        : Text('Pay Fees',
                            style: GoogleFonts.inter(
                                fontSize: 11,
                                fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            );
          }),
          const SizedBox(height: 16),
        ],
        if (_billing.isNotEmpty) ...[
          const SectionHeader(title: 'Transaction History'),
          const SizedBox(height: 12),
          Container(
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.borderLight),
            ),
            child: ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _billing.length > 20 ? 20 : _billing.length,
              separatorBuilder: (_, _) =>
                  Divider(height: 1, color: AppColors.borderLight),
              itemBuilder: (ctx, idx) {
                final tx         = _billing[idx];
                final isIn       = tx['type'] == 'Income';
                final badgeColor = isIn ? AppColors.success : AppColors.error;
                final date       = (tx['date'] as String?)?.split('T').first ?? '';
                return Padding(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 16, vertical: 12),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: badgeColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Icon(
                          isIn
                              ? Icons.arrow_downward_rounded
                              : Icons.arrow_upward_rounded,
                          color: badgeColor,
                          size: 16,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(tx['category'] ?? 'Fee Payment',
                                style: GoogleFonts.inter(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13,
                                    color: AppColors.textPrimary)),
                            Text(date,
                                style: GoogleFonts.inter(
                                    fontSize: 11,
                                    color: AppColors.textMuted)),
                          ],
                        ),
                      ),
                      Text('₦${tx['amount_paid']}',
                          style: GoogleFonts.inter(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                              color: badgeColor)),
                      if (isIn && tx['receipt_number'] != null) ...[
                        const SizedBox(width: 8),
                        GestureDetector(
                          onTap: () => _downloadReceipt(tx['id'] as int),
                          child: Icon(Icons.download_rounded, color: primary, size: 18),
                        ),
                      ],
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ],
    );
  }

  // ─── Announcements Tab ─────────────────────────────────────────────────────
  Widget _buildAnnouncements() {
    return _announcements.isEmpty
        ? ListView(physics: const AlwaysScrollableScrollPhysics(), children: [
            const SizedBox(height: 80),
            _empty('No announcements.'),
          ])
        : ListView.separated(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
            itemCount: _announcements.length,
            separatorBuilder: (_, _) => const SizedBox(height: 8),
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
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Expanded(
                      child: Text(a['title'] ?? '',
                          style: GoogleFonts.inter(
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                              color: AppColors.textPrimary)),
                    ),
                    Text(date,
                        style: GoogleFonts.inter(
                            fontSize: 11, color: AppColors.textMuted)),
                  ]),
                  const SizedBox(height: 6),
                  Text(a['body'] ?? '',
                      style: GoogleFonts.inter(
                          fontSize: 12,
                          color: AppColors.textSecondary,
                          height: 1.4)),
                  if ((a['author_name'] as String?)?.isNotEmpty == true) ...[
                    const SizedBox(height: 6),
                    Text('— ${a['author_name']}',
                        style: GoogleFonts.inter(
                            fontSize: 11, color: AppColors.textMuted)),
                  ],
                ]),
              );
            },
          );
  }

  Widget _empty(String msg) => Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.inbox_outlined, size: 40, color: AppColors.textMuted),
            const SizedBox(height: 12),
            Text(msg,
                style: GoogleFonts.inter(
                    color: AppColors.textSecondary, fontSize: 13)),
          ],
        ),
      );
}
