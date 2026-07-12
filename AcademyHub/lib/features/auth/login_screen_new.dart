import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/toast_utility.dart';
import '../../core/constants.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with TickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  String _selectedRole = 'staff'; // 'staff', 'parent', 'student'
  bool _rememberMe = false;
  bool _isLoading = false;
  bool _obscurePassword = true;

  late final AnimationController _blobController;

  @override
  void initState() {
    super.initState();
    _emailController.text = 'admin@academyhub.local';
    _passwordController.text = 'password';
    _blobController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 8),
    )..repeat();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _blobController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    try {
      final success = await context.read<AuthProvider>().login(
            _emailController.text.trim(),
            _passwordController.text,
          );
      if (success && mounted) {
        CustomToast.show(
          context: context,
          message: 'Welcome back! Sign in successful.',
          type: 'success',
        );
      } else if (!success && mounted) {
        CustomToast.show(
          context: context,
          message: context.read<AuthProvider>().error ??
              'Login failed. Please verify credentials.',
          type: 'error',
        );
      }
    } catch (e) {
      if (mounted) {
        CustomToast.show(
          context: context,
          message: 'Network error or connection timed out.',
          type: 'error',
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Color get _currentRoleColor {
    return AppColors.primary;
  }

  String get _currentRoleTitle {
    switch (_selectedRole) {
      case 'parent':
        return 'Parent Login';
      case 'student':
        return 'Student Login';
      case 'staff':
      default:
        return 'Staff Login';
    }
  }

  Widget _buildRoleTabs() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface2,
        borderRadius: BorderRadius.circular(12),
      ),
      padding: const EdgeInsets.all(4),
      margin: const EdgeInsets.only(bottom: 24),
      child: Row(
        children: [
          _buildRoleTab('staff', 'Staff', Icons.domain_rounded),
          _buildRoleTab('parent', 'Parent', Icons.people_alt_rounded),
          _buildRoleTab('student', 'Student', Icons.school_rounded),
        ],
      ),
    );
  }

  Widget _buildRoleTab(String role, String label, IconData icon) {
    final isSelected = _selectedRole == role;
    return Expanded(
      child: GestureDetector(
        onTap: () {
          setState(() {
            _selectedRole = role;
            if (role == 'staff') {
              _emailController.text = 'admin@academyhub.local';
            } else if (role == 'parent') {
              _emailController.text = 'parent@academyhub.local';
            } else {
              _emailController.text = 'student@academyhub.local';
            }
            _passwordController.text = 'password';
          });
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.surface : Colors.transparent,
            borderRadius: BorderRadius.circular(9),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.05),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    )
                  ]
                : null,
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                icon,
                size: 14,
                color: isSelected ? AppColors.textPrimary : AppColors.textSecondary,
              ),
              const SizedBox(width: 5),
              Text(
                label,
                style: GoogleFonts.inter(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: isSelected ? AppColors.textPrimary : AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = _currentRoleColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: Stack(
        children: [
          // Animated ambient gradient blobs
          _AmbientBlobs(controller: _blobController, primary: primary),
          // Main content
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 400),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      _buildHeader(auth, primary),
                      const SizedBox(height: 32),
                      _buildFormCard(auth, primary),
                      const SizedBox(height: 24),
                      _buildFooter(),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader(AuthProvider auth, Color primary) {
    return Column(
      children: [
        // School logo with glow
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
                color: primary.withValues(alpha: 0.4), width: 1.5),
            boxShadow: [
              BoxShadow(
                color: primary.withValues(alpha: 0.2),
                blurRadius: 20,
                spreadRadius: 0,
              ),
            ],
          ),
          child: auth.tenantLogoUrl != null && auth.tenantLogoUrl!.isNotEmpty
              ? ClipRRect(
                  borderRadius: BorderRadius.circular(20),
                  child: Image.network(
                    auth.tenantLogoUrl!,
                    fit: BoxFit.contain,
                    errorBuilder: (_, _, _) => ClipRRect(
                      borderRadius: BorderRadius.circular(20),
                      child: Padding(
                        padding: const EdgeInsets.all(8),
                        child: Image.asset('lib/Alogo.png',
                            fit: BoxFit.contain),
                      ),
                    ),
                  ),
                )
              : Padding(
                  padding: const EdgeInsets.all(12),
                  child: Image.asset('lib/Alogo.png', fit: BoxFit.contain),
                ),
        ),
        const SizedBox(height: 20),
        Text(
          auth.tenantName ?? 'AcademyHub',
          textAlign: TextAlign.center,
          style: GoogleFonts.inter(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
            letterSpacing: -0.5,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          _currentRoleTitle,
          style: GoogleFonts.inter(
            fontSize: 14,
            color: primary,
            fontWeight: FontWeight.bold,
          ),
        ),
      ],
    );
  }

  Widget _buildFormCard(AuthProvider auth, Color primary) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: AppColors.borderLight),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 24,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      padding: const EdgeInsets.all(24),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _buildRoleTabs(),
            _fieldLabel(_selectedRole == 'student' ? 'Admission Number' : 'Email Address'),
            const SizedBox(height: 8),
            TextFormField(
              controller: _emailController,
              keyboardType: TextInputType.text,
              style: GoogleFonts.inter(
                color: AppColors.textPrimary,
                fontSize: 14,
                fontWeight: FontWeight.w600,
              ),
              decoration: InputDecoration(
                hintText: _selectedRole == 'student' ? 'e.g. 2026/001' : 'e.g. parent@school.com',
                prefixIcon: Icon(Icons.person_outline_rounded,
                    color: AppColors.textSecondary, size: 20),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide(color: primary, width: 2),
                ),
              ),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Username is required' : null,
            ),
            const SizedBox(height: 20),
            _fieldLabel('Password'),
            const SizedBox(height: 8),
            TextFormField(
              controller: _passwordController,
              obscureText: _obscurePassword,
              style: GoogleFonts.inter(
                color: AppColors.textPrimary,
                fontSize: 14,
                fontWeight: FontWeight.w600,
              ),
              decoration: InputDecoration(
                hintText: '••••••••',
                prefixIcon: Icon(Icons.lock_outline_rounded,
                    color: AppColors.textSecondary, size: 20),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide(color: primary, width: 2),
                ),
                suffixIcon: IconButton(
                  onPressed: () =>
                      setState(() => _obscurePassword = !_obscurePassword),
                  icon: Icon(
                    _obscurePassword
                        ? Icons.visibility_outlined
                        : Icons.visibility_off_outlined,
                    color: AppColors.textSecondary,
                    size: 20,
                  ),
                ),
              ),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Password is required' : null,
            ),
            const SizedBox(height: 16),
            // Remember me
            Row(
              children: [
                SizedBox(
                  width: 20,
                  height: 20,
                  child: Checkbox(
                    value: _rememberMe,
                    onChanged: (v) =>
                        setState(() => _rememberMe = v ?? false),
                    fillColor: WidgetStateProperty.resolveWith((states) {
                      if (states.contains(WidgetState.selected)) {
                        return primary;
                      }
                      return Colors.transparent;
                    }),
                    checkColor: Colors.white,
                    side: BorderSide(
                        color: AppColors.borderLight, width: 1.5),
                  ),
                ),
                const SizedBox(width: 10),
                Text(
                  'Keep me signed in',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    color: AppColors.textSecondary,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),
            // Sign In button
            SizedBox(
              height: 52,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _handleLogin,
                style: ElevatedButton.styleFrom(
                  backgroundColor: primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                  elevation: 0,
                ),
                child: _isLoading
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor:
                              AlwaysStoppedAnimation<Color>(Colors.white),
                        ),
                      )
                    : Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            'Login',
                            style: GoogleFonts.inter(
                              fontSize: 15,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(width: 8),
                          const Icon(Icons.arrow_forward_rounded, color: Colors.white, size: 18),
                        ],
                      ),
              ),
            ),
            const SizedBox(height: 12),
            TextButton(
              onPressed: () => auth.clearTenant(),
              child: Text(
                'Change School',
                style: GoogleFonts.inter(
                  fontWeight: FontWeight.bold,
                  fontSize: 13,
                  color: AppColors.textSecondary,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _fieldLabel(String label) {
    return Text(
      label,
      style: GoogleFonts.inter(
        fontSize: 12,
        fontWeight: FontWeight.bold,
        color: AppColors.textSecondary,
        letterSpacing: 0.3,
      ),
    );
  }

  Widget _buildFooter() {
    return Text(
      'AcademyHub Gateway Client',
      style: GoogleFonts.inter(
        fontSize: 12,
        fontWeight: FontWeight.w500,
        color: AppColors.textMuted,
      ),
      textAlign: TextAlign.center,
    );
  }
}

// ─── Animated ambient blobs ───────────────────────────────────────────────────
class _AmbientBlobs extends StatelessWidget {
  final AnimationController controller;
  final Color primary;

  const _AmbientBlobs({required this.controller, required this.primary});

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: controller,
      builder: (_, _) {
        final t = controller.value;
        return CustomPaint(
          size: MediaQuery.of(context).size,
          painter: _BlobPainter(t: t, primary: primary),
        );
      },
    );
  }
}

class _BlobPainter extends CustomPainter {
  final double t;
  final Color primary;

  _BlobPainter({required this.t, required this.primary});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..blendMode = BlendMode.screen;

    // Blob 1 — top-left amber glow
    final x1 = size.width * 0.1 + math.sin(t * math.pi * 2) * 40;
    final y1 = size.height * 0.15 + math.cos(t * math.pi * 2) * 30;
    paint.shader = RadialGradient(
      colors: [primary.withValues(alpha: 0.25), Colors.transparent],
    ).createShader(Rect.fromCircle(
        center: Offset(x1, y1), radius: size.width * 0.45));
    canvas.drawCircle(Offset(x1, y1), size.width * 0.45, paint);

    // Blob 2 — bottom-right indigo glow
    final x2 = size.width * 0.85 + math.cos(t * math.pi * 2 + 1) * 50;
    final y2 = size.height * 0.75 + math.sin(t * math.pi * 2 + 1) * 40;
    paint.shader = RadialGradient(
      colors: [AppColors.studentAccent.withValues(alpha: 0.18), Colors.transparent],
    ).createShader(Rect.fromCircle(
        center: Offset(x2, y2), radius: size.width * 0.55));
    canvas.drawCircle(Offset(x2, y2), size.width * 0.55, paint);
  }

  @override
  bool shouldRepaint(_BlobPainter old) => old.t != t;
}