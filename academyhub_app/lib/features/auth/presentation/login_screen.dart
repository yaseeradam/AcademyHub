import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';
import 'package:academyhub_app/core/network/api_client.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with SingleTickerProviderStateMixin {
  String _selectedRole = 'student'; // 'student', 'parent', 'staff'
  final _usernameCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();

  bool _obscurePassword = true;
  bool _isLoading = false;
  String? _errorMessage;
  String? _schoolName;

  late AnimationController _cardCtrl;
  late Animation<Offset> _cardAnim;

  @override
  void initState() {
    super.initState();
    _cardCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 500));
    _cardAnim = Tween<Offset>(begin: const Offset(0, 0.1), end: Offset.zero)
        .animate(CurvedAnimation(parent: _cardCtrl, curve: Curves.easeOutCubic));
    _cardCtrl.forward();

    _loadSchoolInfo();
  }

  Future<void> _loadSchoolInfo() async {
    final name = await SecureStorage.instance.getSchoolName();
    if (name != null && mounted) {
      setState(() {
        _schoolName = name;
      });
    }
  }

  @override
  void dispose() {
    _cardCtrl.dispose();
    _usernameCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  Color get _roleColor => AppColors.rolePrimary(_selectedRole);

  List<Color> get _gradient => AppColors.roleGradient(_selectedRole);

  String get _rolePortalLabel {
    switch (_selectedRole) {
      case 'student': return 'Student Portal';
      case 'parent':  return 'Parent Portal';
      case 'staff':   return 'Staff & Faculty Portal';
      default:        return 'School Portal';
    }
  }

  String get _roleShortLabel {
    switch (_selectedRole) {
      case 'student': return 'Student';
      case 'parent':  return 'Parent';
      case 'staff':   return 'Staff';
      default:        return 'User';
    }
  }

  void _switchRole(String role) {
    if (_selectedRole == role) return;
    setState(() {
      _selectedRole = role;
      _errorMessage = null;
      _usernameCtrl.clear();
      _passwordCtrl.clear();
    });
  }

  Future<void> _handleLogin() async {
    final loginInput = _usernameCtrl.text.trim();
    final password = _passwordCtrl.text;

    if (loginInput.isEmpty || password.isEmpty) {
      setState(() {
        _errorMessage = 'Please enter your login details.';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final payload = <String, dynamic>{
      'role': _selectedRole,
      'password': password,
    };

    if (_selectedRole == 'student') {
      payload['admission_number'] = loginInput;
    } else {
      payload['email'] = loginInput;
    }

    try {
      final response = await apiClient.dio.post('/login', data: payload);

      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        final token = data['token'] ?? data['access_token'];

        if (token != null) {
          await SecureStorage.instance.setToken(token.toString());

          final user = data['user'] ?? data['student'];
          if (user != null) {
            final userRole = user['role'] ?? _selectedRole;
            final userId = user['id']?.toString() ?? '';
            final userName = '${user['first_name'] ?? ''} ${user['last_name'] ?? user['name'] ?? ''}'.trim();

            await SecureStorage.instance.setRole(userRole.toString());
            await SecureStorage.instance.setStudentId(userId);
            if (userName.isNotEmpty) {
              await SecureStorage.instance.setUserName(userName);
            }
          } else {
            await SecureStorage.instance.setRole(_selectedRole);
          }

          if (mounted) context.go('/dashboard');
          return;
        }
      }

      setState(() {
        _errorMessage = 'Invalid login credentials.';
      });
    } on DioException catch (e) {
      String msg = 'Login failed. Check your credentials.';
      if (e.response != null && e.response?.data != null) {
        msg = e.response?.data['message'] ?? e.response?.data['error'] ?? msg;
      }
      setState(() {
        _errorMessage = msg;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'An unexpected error occurred. Try again.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final isStudent = _selectedRole == 'student';

    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          // ── Ambient background gradient ─────────────────────
          AnimatedContainer(
            duration: const Duration(milliseconds: 500),
            width: size.width,
            height: size.height,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [_gradient[0], _gradient[1].withValues(alpha: 0.8), const Color(0xFF0F172A)],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
          ),

          // ── Decorative background circles ────────────────────
          Positioned(
            top: -60, right: -40,
            child: Container(
              width: 220, height: 220,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.05),
              ),
            ),
          ),

          // ── Main Content Area ────────────────────────────────
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Top Bar (Back button + School Name Badge)
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        GestureDetector(
                          onTap: () async {
                            await SecureStorage.instance.deleteSchoolSlug();
                            if (mounted) context.go('/');
                          },
                          child: Container(
                            width: 40, height: 40,
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.15),
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                            ),
                            child: const Icon(Icons.arrow_back_rounded, color: Colors.white, size: 20),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.school_rounded, color: Colors.white, size: 14),
                              const SizedBox(width: 6),
                              Text(
                                _schoolName ?? 'AcademyHub',
                                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 20),

                    // Role Selector Segmented Controls
                    Container(
                      padding: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                      ),
                      child: Row(
                        children: [
                          _roleTab('student', 'Student', Icons.school_rounded),
                          _roleTab('parent', 'Parent', Icons.family_restroom_rounded),
                          _roleTab('staff', 'Staff', Icons.badge_rounded),
                        ],
                      ),
                    ),

                    const SizedBox(height: 20),

                    // ── Upper Floating Login Credentials Card ────────
                    SlideTransition(
                      position: _cardAnim,
                      child: Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(28),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.18),
                              blurRadius: 30,
                              offset: const Offset(0, 12),
                            ),
                          ],
                        ),
                        padding: const EdgeInsets.fromLTRB(24, 26, 24, 24),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Text(
                              'Sign In as $_roleShortLabel',
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.w800,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Access your $_rolePortalLabel account',
                              style: const TextStyle(
                                color: AppColors.textSecondary,
                                fontSize: 13,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 22),

                            // Username / Admission Number / Email
                            _label(isStudent ? 'ADMISSION NUMBER' : 'EMAIL ADDRESS'),
                            const SizedBox(height: 8),
                            _field(
                              controller: _usernameCtrl,
                              hint: isStudent ? 'e.g. STU20240001' : 'e.g. you@school.com',
                              keyboard: isStudent ? TextInputType.text : TextInputType.emailAddress,
                              icon: isStudent ? Icons.badge_outlined : Icons.email_outlined,
                            ),
                            const SizedBox(height: 18),

                            // Password Field
                            _label('PASSWORD'),
                            const SizedBox(height: 8),
                            _field(
                              controller: _passwordCtrl,
                              hint: 'Enter your password',
                              icon: Icons.lock_outline_rounded,
                              obscure: _obscurePassword,
                              suffix: GestureDetector(
                                onTap: () => setState(() => _obscurePassword = !_obscurePassword),
                                child: Icon(
                                  _obscurePassword ? Icons.visibility_off_rounded : Icons.visibility_rounded,
                                  color: AppColors.textSecondary, size: 20,
                                ),
                              ),
                            ),

                            // Error display
                            AnimatedSize(
                              duration: const Duration(milliseconds: 250),
                              child: _errorMessage != null
                                  ? Padding(
                                      padding: const EdgeInsets.only(top: 14),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                        decoration: BoxDecoration(
                                          color: AppColors.dangerRed.withValues(alpha: 0.07),
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(color: AppColors.dangerRed.withValues(alpha: 0.2)),
                                        ),
                                        child: Row(
                                          children: [
                                            const Icon(Icons.error_outline_rounded, color: AppColors.dangerRed, size: 16),
                                            const SizedBox(width: 8),
                                            Expanded(
                                              child: Text(
                                                _errorMessage!,
                                                style: const TextStyle(color: AppColors.dangerRed, fontSize: 12),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    )
                                  : const SizedBox.shrink(),
                            ),

                            const SizedBox(height: 24),

                            // Submit Action Button
                            AnimatedContainer(
                              duration: const Duration(milliseconds: 400),
                              height: 54,
                              decoration: BoxDecoration(
                                gradient: LinearGradient(
                                  colors: _gradient,
                                  begin: Alignment.centerLeft,
                                  end: Alignment.centerRight,
                                ),
                                borderRadius: BorderRadius.circular(16),
                                boxShadow: [
                                  BoxShadow(
                                    color: _roleColor.withValues(alpha: 0.35),
                                    blurRadius: 16,
                                    offset: const Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: ElevatedButton(
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.transparent,
                                  shadowColor: Colors.transparent,
                                  foregroundColor: Colors.white,
                                  elevation: 0,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                ),
                                onPressed: _isLoading ? null : _handleLogin,
                                child: _isLoading
                                    ? const SizedBox(
                                        width: 22, height: 22,
                                        child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                                      )
                                    : Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Text(
                                            'Sign in to $_roleShortLabel Portal',
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w800,
                                              fontSize: 15,
                                              letterSpacing: 0.3,
                                            ),
                                          ),
                                          const SizedBox(width: 8),
                                          const Icon(Icons.arrow_forward_rounded, size: 18),
                                        ],
                                      ),
                              ),
                            ),

                            const SizedBox(height: 14),
                            Center(
                              child: Text(
                                'Need account help? Contact your school portal admin.',
                                style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
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
            borderRadius: BorderRadius.circular(16),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.1),
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
                color: isSelected ? _roleColor : Colors.white.withValues(alpha: 0.7),
              ),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(
                  color: isSelected ? AppColors.textPrimary : Colors.white.withValues(alpha: 0.85),
                  fontSize: 12,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _label(String text) {
    return Text(
      text,
      style: const TextStyle(
        fontSize: 11,
        fontWeight: FontWeight.bold,
        color: AppColors.textSecondary,
        letterSpacing: 1.0,
      ),
    );
  }

  Widget _field({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    TextInputType keyboard = TextInputType.text,
    bool obscure = false,
    Widget? suffix,
  }) {
    return TextField(
      controller: controller,
      keyboardType: keyboard,
      obscureText: obscure,
      style: const TextStyle(
        color: AppColors.textPrimary,
        fontWeight: FontWeight.w600,
        fontSize: 15,
      ),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: AppColors.textDisabled, fontSize: 14),
        prefixIcon: Icon(icon, color: AppColors.textSecondary, size: 20),
        suffixIcon: suffix,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.divider),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.divider),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: _roleColor, width: 2),
        ),
      ),
    );
  }
}
