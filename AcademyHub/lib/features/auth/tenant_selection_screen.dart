import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:dio/dio.dart';
import '../../core/auth_provider.dart';
import '../../core/toast_utility.dart';
import '../../core/constants.dart';

class TenantSelectionScreen extends StatefulWidget {
  const TenantSelectionScreen({super.key});

  @override
  State<TenantSelectionScreen> createState() => _TenantSelectionScreenState();
}

class _TenantSelectionScreenState extends State<TenantSelectionScreen> {
  final _formKey      = GlobalKey<FormState>();
  final _slugController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;
  bool _isSuccess = false;
  String? _schoolLogoUrl;
  String? _schoolName;

  @override
  void dispose() {
    _slugController.dispose();
    super.dispose();
  }

  Future<void> _handleTenantSelect() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    final auth = context.read<AuthProvider>();
    final slug = _slugController.text.trim().toLowerCase();

    try {
      // First, fetch the tenant details to show the logo/success state
      final response = await auth.dio.get('/tenant/$slug');
      final data = response.data;

      setState(() {
        _isSuccess = true;
        _schoolLogoUrl = data['logo_url'];
        _schoolName = data['name'];
        _isLoading = false;
      });

      if (mounted) {
        CustomToast.show(
          context: context,
          message: 'Connected to ${_schoolName ?? "School"} successfully!',
          type: 'success',
        );
      }

      // Show the success screen with logo for 2 seconds
      await Future.delayed(const Duration(seconds: 2));

      // Complete the tenant resolution in provider (triggers router redirect)
      await auth.resolveTenant(slug);

    } on DioException catch (de) {
      setState(() {
        _isLoading = false;
        final msg = de.response?.data?['message'];
        _errorMessage = msg ?? 'School domain not found. Please double-check spelling.';
      });
      if (mounted) {
        CustomToast.show(
          context: context,
          message: _errorMessage!,
          type: 'error',
        );
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
        _errorMessage = 'Connection failed. Please check server status.';
      });
      if (mounted) {
        CustomToast.show(
          context: context,
          message: _errorMessage!,
          type: 'error',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final primary = auth.tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 400),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _isSuccess
                      ? _buildSuccessCard(auth, primary)
                      : _buildCard(auth, primary),
                  const SizedBox(height: 24),
                  Text(
                    'AcademyHub School Workspace Gateway',
                    style: GoogleFonts.inter(
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                      color: AppColors.textMuted,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSuccessCard(AuthProvider auth, Color primary) {
    final logoUrl = auth.getReachableUrl(_schoolLogoUrl);
    final hasLogo = logoUrl != null && logoUrl.isNotEmpty;

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
      padding: const EdgeInsets.all(32),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // School Logo
          Container(
            width: 90,
            height: 90,
            decoration: BoxDecoration(
              color: AppColors.surface2,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(
                  color: primary.withValues(alpha: 0.3), width: 1.5),
              boxShadow: [
                BoxShadow(
                  color: primary.withValues(alpha: 0.15),
                  blurRadius: 16,
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(22),
              child: hasLogo
                  ? Image.network(
                      logoUrl,
                      fit: BoxFit.contain,
                      errorBuilder: (ctx, err, stack) => Padding(
                        padding: const EdgeInsets.all(12),
                        child: Image.asset('lib/Alogo.png', fit: BoxFit.contain),
                      ),
                    )
                  : Padding(
                      padding: const EdgeInsets.all(16),
                      child: Image.asset('lib/Alogo.png', fit: BoxFit.contain),
                    ),
            ),
          ),
          const SizedBox(height: 24),
          // Checkmark Icon
          const Icon(
            Icons.check_circle_rounded,
            color: Color(0xFF3FB950),
            size: 48,
          ),
          const SizedBox(height: 16),
          Text(
            _schoolName ?? 'Welcome!',
            textAlign: TextAlign.center,
            style: GoogleFonts.inter(
              fontSize: 20,
              fontWeight: FontWeight.w900,
              color: AppColors.textPrimary,
              letterSpacing: -0.5,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'School Workspace Verified',
            style: GoogleFonts.inter(
              fontSize: 13,
              color: AppColors.textSecondary,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 28),
          // Connecting Loader
          SizedBox(
            width: 20,
            height: 20,
            child: CircularProgressIndicator(
              strokeWidth: 2.5,
              valueColor: AlwaysStoppedAnimation<Color>(primary),
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'Connecting to workspace...',
            style: GoogleFonts.inter(
              fontSize: 11,
              color: AppColors.textMuted,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCard(AuthProvider auth, Color primary) {
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
      padding: const EdgeInsets.all(28),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Logo
            Center(
              child: Container(
                width: 72,
                height: 72,
                decoration: BoxDecoration(
                  color: AppColors.surface2,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                      color: primary.withValues(alpha: 0.3), width: 1.5),
                  boxShadow: [
                    BoxShadow(
                      color: primary.withValues(alpha: 0.15),
                      blurRadius: 16,
                    ),
                  ],
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(18),
                  child: Padding(
                    padding: const EdgeInsets.all(10),
                    child: Image.asset('lib/Alogo.png', fit: BoxFit.contain),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'Connect Your School',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: AppColors.textPrimary,
                letterSpacing: -0.5,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'Enter your school workspace domain slug',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                fontSize: 14,
                color: AppColors.textSecondary,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 24),
            // Laravel-style Error Banner
            if (_errorMessage != null) ...[
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF2F2),
                  border: Border.all(color: const Color(0xFFFCA5A5), width: 1.5),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.error_outline_rounded, color: Color(0xFFEF4444), size: 20),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        _errorMessage!,
                        style: GoogleFonts.inter(
                          color: const Color(0xFFB91C1C),
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          height: 1.4,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
            ],
            Text(
              'SCHOOL WORKSPACE DOMAIN',
              style: GoogleFonts.inter(
                fontSize: 10,
                fontWeight: FontWeight.bold,
                color: AppColors.textSecondary,
                letterSpacing: 1.2,
              ),
            ),
            const SizedBox(height: 8),
            TextFormField(
              controller: _slugController,
              keyboardType: TextInputType.text,
              style: GoogleFonts.inter(
                color: AppColors.textPrimary,
                fontSize: 14,
                fontWeight: FontWeight.w600,
              ),
              decoration: InputDecoration(
                hintText: 'e.g., yaseeradam or model.myacademy.com.ng',
                prefixIcon: Icon(Icons.domain_rounded,
                    color: AppColors.textSecondary, size: 20),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide(color: primary, width: 2),
                ),
              ),
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Please enter school domain slug';
                }
                return null;
              },
            ),
            const SizedBox(height: 24),
            // Continue button
            SizedBox(
              height: 52,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _handleTenantSelect,
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
                            'Continue to Login',
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
          ],
        ),
      ),
    );
  }
}
