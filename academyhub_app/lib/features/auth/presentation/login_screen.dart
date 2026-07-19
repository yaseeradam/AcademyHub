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

class _LoginScreenState extends State<LoginScreen> with SingleTickerProviderStateMixin {
  String _selectedRole = 'student';
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _obscurePassword = true;
  bool _isLoading = false;
  String? _schoolName;
  String? _errorMessage;

  late AnimationController _animController;
  late Animation<double> _fadeAnim;

  @override
  void initState() {
    super.initState();
    _loadSchoolName();
    _animController = AnimationController(vsync: this, duration: const Duration(milliseconds: 400));
    _fadeAnim = CurvedAnimation(parent: _animController, curve: Curves.easeOut);
    _animController.forward();
  }

  @override
  void dispose() {
    _animController.dispose();
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _loadSchoolName() async {
    final name = await SecureStorage.instance.getSchoolName();
    if (mounted) setState(() => _schoolName = name ?? 'My School');
  }

  void _selectRole(String role) {
    if (_selectedRole == role) return;
    setState(() {
      _selectedRole = role;
      _usernameController.clear();
      _errorMessage = null;
    });
    _animController.forward(from: 0);
  }

  List<Color> get _gradient => AppColors.roleGradient(_selectedRole);
  Color get _roleColor => AppColors.rolePrimary(_selectedRole);

  Future<void> _handleLogin() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text;
    if (username.isEmpty || password.isEmpty) {
      setState(() => _errorMessage = 'All fields are required.');
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
            final studentId = response.data['student']['id']?.toString();
            if (studentId != null) await SecureStorage.instance.setStudentId(studentId);
            final name = response.data['student']['full_name']?.toString();
            if (name != null) await SecureStorage.instance.setUserName(name);
          } else if (response.data['user'] != null) {
            final name = response.data['user']['name']?.toString();
            if (name != null) await SecureStorage.instance.setUserName(name);
          }
          if (mounted) context.go('/dashboard');
        }
      }
    } catch (e) {
      setState(() => _errorMessage = 'Invalid credentials or login failed.');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isStudent = _selectedRole == 'student';
    return Scaffold(
      backgroundColor: AppColors.appBackground,
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // ── Liquid Hero ──────────────────────────────────────
            AnimatedContainer(
              duration: const Duration(milliseconds: 400),
              curve: Curves.easeInOut,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: _gradient,
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
              child: ClipPath(
                clipper: _LiquidBottomClipper(),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 400),
                  curve: Curves.easeInOut,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: _gradient,
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                  ),
                  child: SafeArea(
                    bottom: false,
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(24, 12, 24, 56),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Back button
                          GestureDetector(
                            onTap: () async {
                              await SecureStorage.instance.deleteSchoolSlug();
                              if (mounted) context.go('/');
                            },
                            child: Container(
                              width: 38, height: 38,
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.18),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.arrow_back_rounded, color: Colors.white, size: 20),
                            ),
                          ),
                          const SizedBox(height: 24),
                          // School icon + name
                          FadeTransition(
                            opacity: _fadeAnim,
                            child: Row(
                              children: [
                                Container(
                                  width: 52, height: 52,
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.2),
                                    borderRadius: BorderRadius.circular(16),
                                    border: Border.all(color: Colors.white.withValues(alpha: 0.35), width: 1.5),
                                  ),
                                  child: const Icon(Icons.school_rounded, color: Colors.white, size: 28),
                                ),
                                const SizedBox(width: 14),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        _schoolName ?? 'AcademyHub',
                                        style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                                        maxLines: 1, overflow: TextOverflow.ellipsis,
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        _roleLabel,
                                        style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 12),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 28),
                          // Role selector pills
                          Row(
                            children: [
                              _rolePill('student', 'Student', Icons.school_rounded),
                              const SizedBox(width: 8),
                              _rolePill('parent', 'Parent', Icons.people_alt_rounded),
                              const SizedBox(width: 8),
                              _rolePill('staff', 'Staff', Icons.badge_rounded),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),

            // ── Form ─────────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 0, 20, 32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Form card lifted over the wave
                  Transform.translate(
                    offset: const Offset(0, -28),
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(24),
                        boxShadow: [
                          BoxShadow(
                            color: _roleColor.withValues(alpha: 0.12),
                            blurRadius: 24,
                            offset: const Offset(0, 8),
                          ),
                        ],
                      ),
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Field label
                          _fieldLabel(isStudent ? 'ADMISSION NUMBER' : 'EMAIL ADDRESS'),
                          const SizedBox(height: 8),
                          _inputField(
                            controller: _usernameController,
                            hint: isStudent ? 'e.g. STU20240001' : 'e.g. teacher@school.com',
                            keyboardType: isStudent ? TextInputType.text : TextInputType.emailAddress,
                            roleColor: _roleColor,
                          ),
                          const SizedBox(height: 20),
                          _fieldLabel('PASSWORD'),
                          const SizedBox(height: 8),
                          _inputField(
                            controller: _passwordController,
                            hint: 'Enter your password',
                            obscure: _obscurePassword,
                            roleColor: _roleColor,
                            suffix: IconButton(
                              icon: Icon(
                                _obscurePassword ? Icons.visibility_off_rounded : Icons.visibility_rounded,
                                color: AppColors.textSecondary, size: 20,
                              ),
                              onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                            ),
                          ),
                          if (_errorMessage != null) ...[
                            const SizedBox(height: 14),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              decoration: BoxDecoration(
                                color: AppColors.dangerRed.withValues(alpha: 0.08),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: AppColors.dangerRed.withValues(alpha: 0.25)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.error_outline_rounded, color: AppColors.dangerRed, size: 16),
                                  const SizedBox(width: 8),
                                  Expanded(child: Text(_errorMessage!, style: const TextStyle(color: AppColors.dangerRed, fontSize: 12))),
                                ],
                              ),
                            ),
                          ],
                          const SizedBox(height: 24),
                          // Login button
                          AnimatedContainer(
                            duration: const Duration(milliseconds: 300),
                            height: 52,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(colors: _gradient, begin: Alignment.centerLeft, end: Alignment.centerRight),
                              borderRadius: BorderRadius.circular(14),
                              boxShadow: [
                                BoxShadow(color: _roleColor.withValues(alpha: 0.35), blurRadius: 16, offset: const Offset(0, 6)),
                              ],
                            ),
                            child: ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.transparent,
                                shadowColor: Colors.transparent,
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              ),
                              onPressed: _isLoading ? null : _handleLogin,
                              child: _isLoading
                                  ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                                  : const Text('LOG IN', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15, letterSpacing: 1)),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  // Support links (outside card, after transform offset)
                  Transform.translate(
                    offset: const Offset(0, -20),
                    child: Column(
                      children: [
                        TextButton(
                          onPressed: () {},
                          child: Text('Forgot Password?',
                              style: TextStyle(color: _roleColor, fontSize: 13, fontWeight: FontWeight.bold)),
                        ),
                        TextButton(
                          onPressed: () {},
                          child: const Text('Need help? Contact Admin',
                              style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String get _roleLabel {
    switch (_selectedRole) {
      case 'parent': return 'Parent Portal';
      case 'staff':  return 'Staff Portal';
      default:       return 'Student Portal';
    }
  }

  Widget _rolePill(String role, String label, IconData icon) {
    final isSelected = _selectedRole == role;
    return Expanded(
      child: GestureDetector(
        onTap: () => _selectRole(role),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 250),
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: isSelected ? Colors.white : Colors.white.withValues(alpha: 0.15),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isSelected ? Colors.white : Colors.white.withValues(alpha: 0.3),
              width: 1.5,
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 20, color: isSelected ? _roleColor : Colors.white),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: isSelected ? _roleColor : Colors.white,
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
      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: _roleColor, letterSpacing: 1.2),
    );
  }

  Widget _inputField({
    required TextEditingController controller,
    required String hint,
    required Color roleColor,
    TextInputType keyboardType = TextInputType.text,
    bool obscure = false,
    Widget? suffix,
  }) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      obscureText: obscure,
      style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w600, fontSize: 14),
      decoration: InputDecoration(
        fillColor: AppColors.inputFill,
        filled: true,
        hintText: hint,
        hintStyle: const TextStyle(color: AppColors.textDisabled, fontSize: 13, fontWeight: FontWeight.normal),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.divider)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.divider)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: roleColor, width: 1.8)),
        suffixIcon: suffix,
      ),
    );
  }
}

class _LiquidBottomClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    final path = Path();
    path.lineTo(0, size.height - 30);
    path.quadraticBezierTo(size.width * 0.25, size.height, size.width * 0.5, size.height - 20);
    path.quadraticBezierTo(size.width * 0.75, size.height - 40, size.width, size.height - 10);
    path.lineTo(size.width, 0);
    path.close();
    return path;
  }

  @override
  bool shouldReclip(_LiquidBottomClipper old) => false;
}
