import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
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
  String? _schoolName;
  String? _errorMessage;

  late AnimationController _gradientCtrl;
  late AnimationController _cardCtrl;
  late Animation<double> _cardSlide;

  @override
  void initState() {
    super.initState();
    _loadSchoolName();
    _gradientCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 500));
    _cardCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 350));
    _cardSlide = CurvedAnimation(parent: _cardCtrl, curve: Curves.easeOutCubic);
    _gradientCtrl.forward();
    _cardCtrl.forward();
  }

  @override
  void dispose() {
    _gradientCtrl.dispose();
    _cardCtrl.dispose();
    _usernameCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadSchoolName() async {
    final name = await SecureStorage.instance.getSchoolName();
    if (mounted) setState(() => _schoolName = name ?? 'AcademyHub');
  }

  void _selectRole(String role) {
    if (_selectedRole == role) return;
    setState(() {
      _selectedRole = role;
      _usernameCtrl.clear();
      _errorMessage = null;
    });
    _gradientCtrl.forward(from: 0);
    _cardCtrl.forward(from: 0);
  }

  List<Color> get _gradient => AppColors.roleGradient(_selectedRole);
  Color get _roleColor => AppColors.rolePrimary(_selectedRole);

  Future<void> _handleLogin() async {
    final username = _usernameCtrl.text.trim();
    final password = _passwordCtrl.text;
    if (username.isEmpty || password.isEmpty) {
      setState(() => _errorMessage = 'Please fill in all fields.');
      return;
    }
    setState(() { _isLoading = true; _errorMessage = null; });
    try {
      final endpoint = _selectedRole == 'student' ? '/student/login' : '/login';
      final payload = _selectedRole == 'student'
          ? {'admission_number': username, 'password': password, 'device_name': 'mobile_companion'}
          : {'email': username, 'password': password, 'device_name': 'mobile_companion'};
      final response = await apiClient.dio.post(endpoint, data: payload);
      if (response.statusCode == 200 && response.data != null) {
        final token = response.data['token'];
        if (token != null) {
          await SecureStorage.instance.setToken(token);
          String storedRole = _selectedRole;
          if (_selectedRole == 'staff' && response.data['user'] != null) {
            storedRole = response.data['user']['role'] ?? 'teacher';
          }
          await SecureStorage.instance.setRole(storedRole);
          if (storedRole == 'student' && response.data['student'] != null) {
            final sid = response.data['student']['id']?.toString();
            if (sid != null) await SecureStorage.instance.setStudentId(sid);
            final name = response.data['student']['full_name']?.toString();
            if (name != null) await SecureStorage.instance.setUserName(name);
          } else if (response.data['user'] != null) {
            final name = response.data['user']['name']?.toString();
            if (name != null) await SecureStorage.instance.setUserName(name);
          }
          if (mounted) context.go('/dashboard');
        }
      }
    } catch (_) {
      setState(() => _errorMessage = 'Invalid credentials. Please try again.');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isStudent = _selectedRole == 'student';
    final size = MediaQuery.of(context).size;

    return Scaffold(
      body: Stack(
        children: [
          // ── Full-screen animated gradient background ──────────
          AnimatedContainer(
            duration: const Duration(milliseconds: 500),
            curve: Curves.easeInOut,
            width: size.width,
            height: size.height,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [_gradient[0], _gradient[1], _gradient[1].withValues(alpha: 0.6)],
                begin: Alignment.topLeft,
                end: Alignment.bottomCenter,
                stops: const [0.0, 0.45, 1.0],
              ),
            ),
          ),

          // ── Decorative circles ────────────────────────────────
          Positioned(
            top: -60, right: -60,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 500),
              width: 220, height: 220,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.07),
              ),
            ),
          ),
          Positioned(
            top: 80, right: -30,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 500),
              width: 120, height: 120,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.05),
              ),
            ),
          ),

          // ── Content ───────────────────────────────────────────
          SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Back button & header row
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
                          ),
                          child: const Icon(Icons.arrow_back_rounded, color: Colors.white, size: 20),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                        ),
                        child: Text(
                          _schoolName ?? 'AcademyHub',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 16),

                  // ── Role Selector Tabs AT THE TOP ─────────────────
                  Row(
                    children: [
                      _roleCard('student', 'Student', Icons.school_rounded, 'Admission No.'),
                      const SizedBox(width: 8),
                      _roleCard('parent', 'Parent', Icons.family_restroom_rounded, 'Email'),
                      const SizedBox(width: 8),
                      _roleCard('staff', 'Staff', Icons.badge_rounded, 'Email'),
                    ],
                  ),

                  const SizedBox(height: 16),

                  // ── Login Credentials Card AT THE TOP ─────────────
                  SlideTransition(
                    position: Tween<Offset>(begin: const Offset(0, 0.2), end: Offset.zero)
                        .animate(_cardSlide),
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(24),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.15),
                            blurRadius: 20,
                            offset: const Offset(0, 8),
                          ),
                        ],
                      ),
                      padding: const EdgeInsets.fromLTRB(22, 24, 22, 22),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Text(
                            'Sign In to Your Account',
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w800,
                              color: AppColors.textPrimary,
                            ),
                          ),
                          const SizedBox(height: 4),
                          AnimatedSwitcher(
                            duration: const Duration(milliseconds: 300),
                            child: Text(
                              _rolePortalLabel,
                              key: ValueKey(_selectedRole),
                              style: const TextStyle(
                                color: AppColors.textSecondary,
                                fontSize: 12,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ),
                          const SizedBox(height: 20),

                          // Username / Email field
                          _label(isStudent ? 'ADMISSION NUMBER' : 'EMAIL ADDRESS'),
                          const SizedBox(height: 8),
                          _field(
                            controller: _usernameCtrl,
                            hint: isStudent ? 'e.g. STU20240001' : 'e.g. you@school.com',
                            keyboard: isStudent ? TextInputType.text : TextInputType.emailAddress,
                            icon: isStudent ? Icons.badge_outlined : Icons.email_outlined,
                          ),
                          const SizedBox(height: 16),

                          // Password field
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

                          // Error message
                          if (_errorMessage != null) ...[
                            const SizedBox(height: 12),
                            Container(
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
                          ],

                          const SizedBox(height: 22),

                          // Login button
                          AnimatedContainer(
                            duration: const Duration(milliseconds: 400),
                            height: 52,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: _gradient,
                                begin: Alignment.centerLeft,
                                end: Alignment.centerRight,
                              ),
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(
                                  color: _roleColor.withValues(alpha: 0.4),
                                  blurRadius: 20,
                                  offset: const Offset(0, 8),
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
                                        Text('Sign in as $_roleShortLabel', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                                        const SizedBox(width: 8),
                                        const Icon(Icons.arrow_forward_rounded, size: 18),
                                      ],
                                    ),
                            ),
                          ),

                          const SizedBox(height: 12),
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
        ],
      ),
    );
  }

  String get _rolePortalLabel {
    switch (_selectedRole) {
      case 'parent': return 'Parent Portal — View your children\'s progress';
      case 'staff':  return 'Staff Portal — Manage classes & records';
      default:       return 'Student Portal — Access your academics';
    }
  }

  String get _roleShortLabel {
    switch (_selectedRole) {
      case 'parent': return 'Parent';
      case 'staff':  return 'Staff';
      default:       return 'Student';
    }
  }

  Widget _roleCard(String role, String label, IconData icon, String hint) {
    final isSelected = _selectedRole == role;
    final color = AppColors.rolePrimary(role);
    return Expanded(
      child: GestureDetector(
        onTap: () => _selectRole(role),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 280),
          curve: Curves.easeOutCubic,
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            color: isSelected ? Colors.white : Colors.white.withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(18),
            border: Border.all(
              color: isSelected ? Colors.white : Colors.white.withValues(alpha: 0.25),
              width: isSelected ? 2 : 1,
            ),
            boxShadow: isSelected
                ? [BoxShadow(color: Colors.black.withValues(alpha: 0.15), blurRadius: 16, offset: const Offset(0, 6))]
                : [],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              AnimatedContainer(
                duration: const Duration(milliseconds: 280),
                width: 40, height: 40,
                decoration: BoxDecoration(
                  color: isSelected ? color.withValues(alpha: 0.12) : Colors.white.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, size: 22, color: isSelected ? color : Colors.white),
              ),
              const SizedBox(height: 8),
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: isSelected ? color : Colors.white,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                hint,
                style: TextStyle(
                  fontSize: 9,
                  color: isSelected ? color.withValues(alpha: 0.6) : Colors.white.withValues(alpha: 0.6),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _label(String text) => Text(
    text,
    style: TextStyle(
      fontSize: 11,
      fontWeight: FontWeight.w700,
      color: _roleColor,
      letterSpacing: 1.1,
    ),
  );

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
      style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w600, fontSize: 14),
      decoration: InputDecoration(
        fillColor: Colors.white,
        filled: true,
        hintText: hint,
        hintStyle: const TextStyle(color: AppColors.textDisabled, fontSize: 13, fontWeight: FontWeight.normal),
        prefixIcon: Icon(icon, color: _roleColor, size: 20),
        suffixIcon: suffix != null ? Padding(padding: const EdgeInsets.only(right: 12), child: suffix) : null,
        suffixIconConstraints: const BoxConstraints(minWidth: 0, minHeight: 0),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: AppColors.divider),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: AppColors.divider),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: _roleColor, width: 2),
        ),
      ),
    );
  }
}
