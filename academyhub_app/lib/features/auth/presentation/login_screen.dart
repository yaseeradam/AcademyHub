import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';
import 'package:academyhub_app/core/network/api_client.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> with TickerProviderStateMixin {
  String _selectedRole = 'student';
  final _usernameCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  bool _obscurePassword = true;
  bool _isLoading = false;
  String? _errorMessage;
  String? _schoolName;

  late AnimationController _cardCtrl;
  late AnimationController _headerCtrl;
  late Animation<Offset> _cardAnim;
  late Animation<double> _headerFade;

  @override
  void initState() {
    super.initState();
    _cardCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 500));
    _headerCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 600));

    _cardAnim = Tween<Offset>(begin: const Offset(0, 0.1), end: Offset.zero)
        .animate(CurvedAnimation(parent: _cardCtrl, curve: Curves.easeOutCubic));
    _headerFade = CurvedAnimation(parent: _headerCtrl, curve: Curves.easeOut);

    _headerCtrl.forward();
    Future.delayed(const Duration(milliseconds: 100), () {
      if (mounted) _cardCtrl.forward();
    });

    _loadSchoolInfo();
  }

  Future<void> _loadSchoolInfo() async {
    final name = await SecureStorage.instance.getSchoolName();
    if (name != null && mounted) setState(() => _schoolName = name);
  }

  @override
  void dispose() {
    _cardCtrl.dispose();
    _headerCtrl.dispose();
    _usernameCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  // ── Role helpers ──────────────────────────────────────────
  Color get _roleColor => AppColors.rolePrimary(_selectedRole);

  String get _roleTitle {
    switch (_selectedRole) {
      case 'parent':  return 'Parent Login';
      case 'staff':   return 'Staff Login';
      default:        return 'Student Login';
    }
  }

  String get _roleQuote {
    switch (_selectedRole) {
      case 'parent':  return 'Together, we nurture\ngrowth, every day.';
      case 'staff':   return 'Lead with vision,\nmanage with purpose.';
      default:        return 'Learn today,\nlead tomorrow.';
    }
  }

  IconData get _roleIllustrationIcon {
    switch (_selectedRole) {
      case 'parent':  return Icons.family_restroom_rounded;
      case 'staff':   return Icons.badge_rounded;
      default:        return Icons.school_rounded;
    }
  }

  String get _rolePortalLabel {
    switch (_selectedRole) {
      case 'parent':  return 'Parent Portal';
      case 'staff':   return 'Staff & Faculty Portal';
      default:        return 'Student Portal';
    }
  }

  void _switchRole(String role) {
    if (_selectedRole == role) return;
    HapticFeedback.selectionClick();
    setState(() {
      _selectedRole = role;
      _errorMessage = null;
      _usernameCtrl.clear();
      _passwordCtrl.clear();
    });
    _cardCtrl.reset();
    _cardCtrl.forward();
  }

  Future<void> _enterDemoMode() async {
    HapticFeedback.lightImpact();
    await SecureStorage.instance.setToken('demo-jwt-token-12345');
    await SecureStorage.instance.setRole(_selectedRole);
    if (_selectedRole == 'student') {
      await SecureStorage.instance.setUserName('Alex Morgan');
      await SecureStorage.instance.setStudentId('101');
    } else if (_selectedRole == 'staff') {
      await SecureStorage.instance.setUserName('Dr. Sarah Jenkins');
    } else {
      await SecureStorage.instance.setUserName('Mr. David Adeleke');
    }
    if (mounted) GoRouter.of(context).go('/dashboard');
  }

  Future<void> _handleLogin() async {
    final loginInput = _usernameCtrl.text.trim();
    final password = _passwordCtrl.text;

    if (loginInput.isEmpty || password.isEmpty) {
      setState(() => _errorMessage = 'Please enter your login details.');
      return;
    }

    setState(() { _isLoading = true; _errorMessage = null; });

    final router = GoRouter.of(context);
    final isStudent = _selectedRole == 'student';
    final endpoint = isStudent ? '/student/login' : '/login';
    final payload = <String, dynamic>{
      'password': password,
      'device_name': 'academyhub_mobile_app',
    };
    if (isStudent) {
      payload['admission_number'] = loginInput;
    } else {
      payload['email'] = loginInput;
    }

    try {
      final response = await apiClient.dio.post(endpoint, data: payload);
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data as Map<String, dynamic>;
        final token = (data['token'] ?? data['access_token'])?.toString();
        if (token != null && token.isNotEmpty) {
          await SecureStorage.instance.setToken(token);
          final userMap = (data['student'] ?? data['user']) as Map<String, dynamic>?;
          if (userMap != null) {
            final userRole = (userMap['role'] ?? _selectedRole).toString();
            final userId = userMap['id']?.toString() ?? '';
            final String userName;
            if (isStudent) {
              final first = userMap['first_name']?.toString() ?? '';
              final last = userMap['last_name']?.toString() ?? '';
              userName = '$first $last'.trim();
            } else {
              userName = userMap['name']?.toString() ?? '';
            }
            await SecureStorage.instance.setRole(userRole);
            if (userId.isNotEmpty) await SecureStorage.instance.setStudentId(userId);
            if (userName.isNotEmpty) await SecureStorage.instance.setUserName(userName);
          } else {
            await SecureStorage.instance.setRole(_selectedRole);
          }
          if (!mounted) return;
          router.go('/dashboard');
          return;
        }
      }
      setState(() => _errorMessage = 'Invalid login credentials.');
    } on DioException catch (e) {
      String msg = 'Login failed. Please check your credentials.';
      final responseData = e.response?.data;
      if (responseData is Map<String, dynamic>) {
        final errors = responseData['errors'];
        String? firstError;
        if (errors is Map) {
          final firstList = errors.values.whereType<List>().firstOrNull;
          firstError = firstList?.whereType<String>().firstOrNull;
        }
        msg = firstError ?? responseData['message']?.toString() ?? msg;
      }
      if (e.response == null) {
        msg = 'Backend server offline. Tap Demo Mode below to preview!';
      }
      setState(() => _errorMessage = msg);
    } catch (_) {
      setState(() => _errorMessage = 'An unexpected error occurred. Tap Demo Mode below to preview!');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isStudent = _selectedRole == 'student';

    return Scaffold(
      backgroundColor: AppColors.background,
      body: Stack(
        children: [
          // ── Hero Background Illustration & Ambient Glow ────────────────
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            height: MediaQuery.of(context).size.height * 0.42,
            child: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    _roleColor.withValues(alpha: 0.25),
                    AppColors.background,
                  ],
                ),
              ),
              child: SafeArea(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          _circleButton(
                            icon: Icons.arrow_back_rounded,
                            onTap: () => GoRouter.of(context).go('/'),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppColors.surfaceContainerHigh,
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: AppColors.outline.withValues(alpha: 0.3)),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.school_rounded, size: 14, color: AppColors.primary),
                                const SizedBox(width: 6),
                                Text(
                                  _schoolName ?? 'School Portal',
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: AppColors.onSurface,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const Spacer(),
                      FadeTransition(
                        opacity: _headerFade,
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: _roleColor.withValues(alpha: 0.2),
                                shape: BoxShape.circle,
                                border: Border.all(color: _roleColor.withValues(alpha: 0.4), width: 1.5),
                              ),
                              child: Icon(_roleIllustrationIcon, size: 36, color: _roleColor),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    _roleTitle,
                                    style: GoogleFonts.plusJakartaSans(
                                      color: AppColors.onSurface,
                                      fontSize: 28,
                                      fontWeight: FontWeight.w800,
                                      letterSpacing: -0.5,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    _roleQuote,
                                    style: GoogleFonts.plusJakartaSans(
                                      color: AppColors.onSurfaceVariant,
                                      fontSize: 13,
                                      fontWeight: FontWeight.w500,
                                      height: 1.3,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),
                    ],
                  ),
                ),
              ),
            ),
          ),

          // ── Obsidian Floating Input Card ──────────────────────────────
          SafeArea(
            child: SingleChildScrollView(
              padding: EdgeInsets.only(
                top: MediaQuery.of(context).size.height * 0.32,
                left: 20,
                right: 20,
                bottom: 24,
              ),
              child: Column(
                children: [
                  SlideTransition(
                    position: _cardAnim,
                    child: Container(
                      constraints: const BoxConstraints(maxWidth: 480),
                      decoration: AppColors.soft3DCardDecoration(borderRadius: 24),
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Role Switcher Tabs
                          Container(
                            padding: const EdgeInsets.all(4),
                            decoration: BoxDecoration(
                              color: AppColors.surfaceContainerHigh,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: AppColors.outline.withValues(alpha: 0.2)),
                            ),
                            child: Row(
                              children: [
                                _roleTab('student', 'Student', Icons.school_rounded),
                                _roleTab('staff', 'Staff', Icons.badge_rounded),
                                _roleTab('parent', 'Parent', Icons.family_restroom_rounded),
                              ],
                            ),
                          ),
                          const SizedBox(height: 24),

                          // Form Header
                          Text(
                            _rolePortalLabel,
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 18,
                              fontWeight: FontWeight.w700,
                              color: AppColors.onSurface,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            isStudent
                                ? 'Enter your admission number and portal password.'
                                : 'Enter your registered email and portal password.',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 12.5,
                              color: AppColors.onSurfaceVariant,
                            ),
                          ),
                          const SizedBox(height: 20),

                          // Username / Admission Input
                          _fieldLabel(isStudent ? 'ADMISSION NUMBER' : 'EMAIL ADDRESS'),
                          const SizedBox(height: 6),
                          _inputField(
                            controller: _usernameCtrl,
                            hint: isStudent ? 'e.g. ADM-2024-001' : 'e.g. name@school.edu',
                            icon: isStudent ? Icons.badge_outlined : Icons.email_outlined,
                            keyboard: isStudent ? TextInputType.text : TextInputType.emailAddress,
                          ),
                          const SizedBox(height: 16),

                          // Password Input
                          _fieldLabel('PASSWORD'),
                          const SizedBox(height: 6),
                          _inputField(
                            controller: _passwordCtrl,
                            hint: '••••••••',
                            icon: Icons.lock_outline_rounded,
                            obscure: _obscurePassword,
                            suffix: IconButton(
                              icon: Icon(
                                _obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                color: AppColors.textSecondary,
                                size: 20,
                              ),
                              onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                            ),
                          ),

                          // Error Message Banner
                          AnimatedSize(
                            duration: const Duration(milliseconds: 250),
                            child: _errorMessage != null
                                ? Padding(
                                    padding: const EdgeInsets.only(top: 14),
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                      decoration: BoxDecoration(
                                        color: AppColors.errorContainer.withValues(alpha: 0.5),
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(color: AppColors.error.withValues(alpha: 0.3)),
                                      ),
                                      child: Row(
                                        children: [
                                          const Icon(Icons.error_outline_rounded, color: AppColors.error, size: 18),
                                          const SizedBox(width: 10),
                                          Expanded(
                                            child: Text(
                                              _errorMessage!,
                                              style: GoogleFonts.plusJakartaSans(color: AppColors.error, fontSize: 12),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  )
                                : const SizedBox.shrink(),
                          ),

                          const SizedBox(height: 24),

                          // Sign In Action Button
                          ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primaryContainer,
                              foregroundColor: const Color(0xFF2A1700),
                              elevation: 0,
                              minimumSize: const Size.fromHeight(54),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            ),
                            onPressed: _isLoading ? null : _handleLogin,
                            child: _isLoading
                                ? const SizedBox(
                                    width: 22,
                                    height: 22,
                                    child: CircularProgressIndicator(strokeWidth: 2.5, color: Color(0xFF2A1700)),
                                  )
                                : Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        'Sign in to Portal',
                                        style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w700, fontSize: 15),
                                      ),
                                      const SizedBox(width: 8),
                                      const Icon(Icons.arrow_forward_rounded, size: 18),
                                    ],
                                  ),
                          ),

                          const SizedBox(height: 12),

                          // Offline Demo Mode Button
                          OutlinedButton.icon(
                            style: OutlinedButton.styleFrom(
                              foregroundColor: AppColors.primary,
                              side: BorderSide(color: AppColors.primary.withValues(alpha: 0.4), width: 1.5),
                              minimumSize: const Size.fromHeight(50),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            ),
                            onPressed: _enterDemoMode,
                            icon: const Icon(Icons.bolt_rounded, size: 18, color: AppColors.primary),
                            label: Text(
                              'Explore App (Offline Demo Mode)',
                              style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w700, fontSize: 13.5),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 32),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _circleButton({required IconData icon, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.15),
          shape: BoxShape.circle,
          border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
        ),
        child: Icon(icon, color: Colors.white, size: 20),
      ),
    );
  }

  Widget _roleTab(String role, String label, IconData icon) {
    final isSelected = _selectedRole == role;
    return Expanded(
      child: GestureDetector(
        onTap: () => _switchRole(role),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 250),
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: isSelected ? Colors.white : Colors.transparent,
            borderRadius: BorderRadius.circular(14),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.10),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ]
                : [],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                size: 18,
                color: isSelected ? _roleColor : Colors.white.withValues(alpha: 0.75),
              ),
              const SizedBox(height: 4),
              Text(
                label,
                style: GoogleFonts.inter(
                  color: isSelected ? AppColors.textPrimary : Colors.white.withValues(alpha: 0.85),
                  fontSize: 11,
                  fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _fieldLabel(String text) {
    return Text(
      text,
      style: GoogleFonts.inter(
        fontSize: 10,
        fontWeight: FontWeight.w700,
        color: AppColors.textSecondary,
        letterSpacing: 1.2,
      ),
    );
  }

  Widget _inputField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    TextInputType keyboard = TextInputType.text,
    bool obscure = false,
    Widget? suffix,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.inputFill,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.divider),
      ),
      child: TextField(
        controller: controller,
        keyboardType: keyboard,
        obscureText: obscure,
        style: GoogleFonts.inter(
          color: AppColors.textPrimary,
          fontWeight: FontWeight.w600,
          fontSize: 15,
        ),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: GoogleFonts.inter(color: AppColors.textDisabled, fontSize: 14),
          prefixIcon: Icon(icon, color: AppColors.textSecondary, size: 20),
          suffixIcon: suffix,
          border: InputBorder.none,
          enabledBorder: InputBorder.none,
          focusedBorder: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        ),
      ),
    );
  }
}
