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
        switch (e.type) {
          case DioExceptionType.connectionTimeout:
          case DioExceptionType.receiveTimeout:
            msg = 'Connection timed out. Check your internet.';
          case DioExceptionType.connectionError:
            msg = 'Cannot reach the server. Check your internet.';
          default:
            msg = 'Network error. Please try again.';
        }
      }
      setState(() => _errorMessage = msg);
    } catch (_) {
      setState(() => _errorMessage = 'An unexpected error occurred.');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final isStudent = _selectedRole == 'student';

    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.light,
      child: Scaffold(
        resizeToAvoidBottomInset: true,
        body: Stack(
          children: [
            // ── Animated role color background ────────────
            AnimatedContainer(
              duration: const Duration(milliseconds: 500),
              curve: Curves.easeInOut,
              width: size.width,
              height: size.height,
              color: AppColors.rolePrimary(_selectedRole),
            ),

            // ── Soft decorative orbs ───────────────────────────────
            Positioned(
              top: -80, right: -60,
              child: Container(
                width: 280, height: 280,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Colors.white.withValues(alpha: 0.05),
                ),
              ),
            ),
            Positioned(
              top: size.height * 0.12, left: -60,
              child: Container(
                width: 200, height: 200,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Colors.white.withValues(alpha: 0.04),
                ),
              ),
            ),
            Positioned(
              bottom: 120, right: -40,
              child: Container(
                width: 160, height: 160,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Colors.white.withValues(alpha: 0.03),
                ),
              ),
            ),

            // ── Main scrollable content ───────────────────────
            SafeArea(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const SizedBox(height: 16),

                    // ── Top bar ───────────────────────────────
                    FadeTransition(
                      opacity: _headerFade,
                      child: Row(
                        children: [
                          _circleButton(
                            icon: Icons.arrow_back_rounded,
                            onTap: () {
                              final nav = GoRouter.of(context);
                              SecureStorage.instance.deleteSchoolSlug().then((_) {
                                if (mounted) nav.go('/');
                              });
                            },
                          ),
                          const Spacer(),
                          if (_schoolName != null)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.school_rounded, color: Colors.white, size: 13),
                                  const SizedBox(width: 6),
                                  Text(
                                    _schoolName!,
                                    style: GoogleFonts.inter(
                                      color: Colors.white,
                                      fontWeight: FontWeight.w700,
                                      fontSize: 12,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),

                    // ── Hero illustration area ────────────────
                    FadeTransition(
                      opacity: _headerFade,
                      child: Column(
                        children: [
                          // Large role icon
                          AnimatedSwitcher(
                            duration: const Duration(milliseconds: 350),
                            transitionBuilder: (child, anim) => ScaleTransition(
                              scale: anim,
                              child: FadeTransition(opacity: anim, child: child),
                            ),
                            child: Container(
                              key: ValueKey(_selectedRole),
                              width: 80,
                              height: 80,
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.13),
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: Colors.white.withValues(alpha: 0.22),
                                  width: 2,
                                ),
                              ),
                              child: Icon(
                                _roleIllustrationIcon,
                                size: 38,
                                color: Colors.white,
                              ),
                            ),
                          ),
                          const SizedBox(height: 14),
                          AnimatedSwitcher(
                            duration: const Duration(milliseconds: 300),
                            child: Text(
                              _roleTitle,
                              key: ValueKey('title_$_selectedRole'),
                              style: GoogleFonts.inter(
                                color: Colors.white,
                                fontSize: 24,
                                fontWeight: FontWeight.w800,
                                letterSpacing: -0.5,
                              ),
                            ),
                          ),
                          const SizedBox(height: 6),
                          AnimatedSwitcher(
                            duration: const Duration(milliseconds: 300),
                            child: Text(
                              _roleQuote,
                              key: ValueKey('quote_$_selectedRole'),
                              textAlign: TextAlign.center,
                              style: GoogleFonts.inter(
                                color: Colors.white.withValues(alpha: 0.70),
                                fontSize: 13,
                                fontWeight: FontWeight.w500,
                                height: 1.5,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 28),

                    // ── Role selector tabs ────────────────────
                    Container(
                      padding: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: Colors.white.withValues(alpha: 0.18)),
                      ),
                      child: Row(
                        children: [
                          _roleTab('student', 'Student', Icons.school_rounded),
                          _roleTab('parent', 'Parent', Icons.family_restroom_rounded),
                          _roleTab('staff', 'Staff', Icons.badge_rounded),
                        ],
                      ),
                    ),

                    const SizedBox(height: 16),

                    // ── Login card ────────────────────────────
                    SlideTransition(
                      position: _cardAnim,
                      child: Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(24),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.10),
                              blurRadius: 40,
                              offset: const Offset(0, 16),
                            ),
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.04),
                              blurRadius: 6,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        padding: const EdgeInsets.all(24),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            // Card header
                            Row(
                              children: [
                                AnimatedContainer(
                                  duration: const Duration(milliseconds: 300),
                                  width: 40,
                                  height: 40,
                                  decoration: BoxDecoration(
                                    color: _roleColor.withValues(alpha: 0.08),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Icon(_roleIllustrationIcon, color: _roleColor, size: 20),
                                ),
                                const SizedBox(width: 12),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Sign in as ${_selectedRole == 'staff' ? 'Staff' : _selectedRole[0].toUpperCase() + _selectedRole.substring(1)}',
                                      style: GoogleFonts.inter(
                                        fontSize: 17,
                                        fontWeight: FontWeight.w800,
                                        color: AppColors.textPrimary,
                                      ),
                                    ),
                                    Text(
                                      'Access your $_rolePortalLabel',
                                      style: GoogleFonts.inter(
                                        fontSize: 12,
                                        color: AppColors.textSecondary,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),

                            const SizedBox(height: 22),

                            // Username / Admission field
                            _fieldLabel(isStudent ? 'ADMISSION NUMBER' : 'EMAIL ADDRESS'),
                            const SizedBox(height: 8),
                            _inputField(
                              controller: _usernameCtrl,
                              hint: isStudent ? 'e.g. STU20240001' : 'e.g. you@school.com',
                              icon: isStudent ? Icons.badge_outlined : Icons.email_outlined,
                              keyboard: isStudent ? TextInputType.text : TextInputType.emailAddress,
                            ),

                            const SizedBox(height: 16),

                            // Password field
                            _fieldLabel('PASSWORD'),
                            const SizedBox(height: 8),
                            _inputField(
                              controller: _passwordCtrl,
                              hint: 'Enter your password',
                              icon: Icons.lock_outline_rounded,
                              obscure: _obscurePassword,
                              suffix: GestureDetector(
                                onTap: () => setState(() => _obscurePassword = !_obscurePassword),
                                child: Icon(
                                  _obscurePassword
                                      ? Icons.visibility_off_rounded
                                      : Icons.visibility_rounded,
                                  color: AppColors.textSecondary,
                                  size: 20,
                                ),
                              ),
                            ),

                            // Error banner
                            AnimatedSize(
                              duration: const Duration(milliseconds: 250),
                              child: _errorMessage != null
                                  ? Padding(
                                      padding: const EdgeInsets.only(top: 14),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 14,
                                          vertical: 10,
                                        ),
                                        decoration: BoxDecoration(
                                          color: AppColors.dangerRed.withValues(alpha: 0.06),
                                          borderRadius: BorderRadius.circular(10),
                                          border: Border.all(
                                            color: AppColors.dangerRed.withValues(alpha: 0.2),
                                          ),
                                        ),
                                        child: Row(
                                          children: [
                                            const Icon(
                                              Icons.error_outline_rounded,
                                              color: AppColors.dangerRed,
                                              size: 16,
                                            ),
                                            const SizedBox(width: 8),
                                            Expanded(
                                              child: Text(
                                                _errorMessage!,
                                                style: GoogleFonts.inter(
                                                  color: AppColors.dangerRed,
                                                  fontSize: 12,
                                                  fontWeight: FontWeight.w500,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    )
                                  : const SizedBox.shrink(),
                            ),

                            const SizedBox(height: 22),

                            // Sign in button
                            AnimatedContainer(
                              duration: const Duration(milliseconds: 300),
                              height: 54,
                              decoration: BoxDecoration(
                                color: _roleColor,
                                borderRadius: BorderRadius.circular(14),
                                boxShadow: [
                                  BoxShadow(
                                    color: _roleColor.withValues(alpha: 0.22),
                                    blurRadius: 20,
                                    offset: const Offset(0, 8),
                                  ),
                                ],
                              ),
                              child: Material(
                                color: Colors.transparent,
                                child: InkWell(
                                  onTap: _isLoading ? null : _handleLogin,
                                  borderRadius: BorderRadius.circular(14),
                                  child: Center(
                                    child: _isLoading
                                        ? const SizedBox(
                                            width: 22,
                                            height: 22,
                                            child: CircularProgressIndicator(
                                              strokeWidth: 2.5,
                                              color: Colors.white,
                                            ),
                                          )
                                        : Row(
                                            mainAxisAlignment: MainAxisAlignment.center,
                                            children: [
                                              Text(
                                                'Sign in to ${_selectedRole == 'staff' ? 'Staff' : _selectedRole[0].toUpperCase() + _selectedRole.substring(1)} Portal',
                                                style: GoogleFonts.inter(
                                                  color: Colors.white,
                                                  fontWeight: FontWeight.w800,
                                                  fontSize: 15,
                                                  letterSpacing: 0.2,
                                                ),
                                              ),
                                              const SizedBox(width: 8),
                                              const Icon(
                                                Icons.arrow_forward_rounded,
                                                color: Colors.white,
                                                size: 18,
                                              ),
                                            ],
                                          ),
                                  ),
                                ),
                              ),
                            ),

                            const SizedBox(height: 14),
                            Center(
                              child: Text(
                                'Need help? Contact your school portal admin.',
                                style: GoogleFonts.inter(
                                  fontSize: 11,
                                  color: AppColors.textSecondary,
                                ),
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
