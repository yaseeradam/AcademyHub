import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';
import 'package:academyhub_app/core/network/api_client.dart';

// AcademyHub brand gradient — used before any role is known
const _kGrad = [Color(0xFF1E3A5F), Color(0xFF0F2744)];

class SchoolFinderScreen extends StatefulWidget {
  const SchoolFinderScreen({super.key});

  @override
  State<SchoolFinderScreen> createState() => _SchoolFinderScreenState();
}

class _SchoolFinderScreenState extends State<SchoolFinderScreen>
    with SingleTickerProviderStateMixin {
  final _slugCtrl = TextEditingController();
  bool _isLoading = false;
  bool _isValid = false;
  String? _schoolName;
  String? _errorMessage;

  late AnimationController _cardCtrl;
  late Animation<Offset> _cardAnim;

  @override
  void initState() {
    super.initState();
    _cardCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 500));
    _cardAnim = Tween<Offset>(begin: const Offset(0, 1), end: Offset.zero)
        .animate(CurvedAnimation(parent: _cardCtrl, curve: Curves.easeOutCubic));
    _cardCtrl.forward();
  }

  @override
  void dispose() {
    _cardCtrl.dispose();
    _slugCtrl.dispose();
    super.dispose();
  }

  Future<void> _validate(String slug) async {
    if (slug.isEmpty) return;
    setState(() { _isLoading = true; _errorMessage = null; });
    try {
      final response = await apiClient.dio.get('/tenant/$slug');
      if (response.statusCode == 200 && response.data != null) {
        setState(() {
          _isValid = true;
          _schoolName = response.data['name'] ?? 'School Found';
        });
      } else {
        setState(() { _isValid = false; _schoolName = null; _errorMessage = 'School not found. Check the code.'; });
      }
    } on DioException catch (e) {
      String msg = e.response?.statusCode == 404
          ? (e.response?.data?['message'] ?? 'School not found. Check the code.')
          : 'Connection error. Try again.';
      setState(() { _isValid = false; _schoolName = null; _errorMessage = msg; });
    } catch (_) {
      setState(() { _isValid = false; _schoolName = null; _errorMessage = 'Unexpected error. Try again.'; });
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          // ── Full gradient background ──────────────────────────
          Container(
            width: size.width,
            height: size.height,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: _kGrad,
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
          ),

          // ── Decorative circles ────────────────────────────────
          Positioned(
            top: -80, left: -60,
            child: Container(
              width: 260, height: 260,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.05),
              ),
            ),
          ),
          Positioned(
            top: 60, right: -40,
            child: Container(
              width: 160, height: 160,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.04),
              ),
            ),
          ),
          Positioned(
            top: 180, left: 40,
            child: Container(
              width: 80, height: 80,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.06),
              ),
            ),
          ),

          // ── Content ───────────────────────────────────────────
          SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // App Icon & Header
                  Center(
                    child: Column(
                      children: [
                        Container(
                          width: 72, height: 72,
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.15),
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white.withValues(alpha: 0.3), width: 2),
                          ),
                          child: const Icon(Icons.school_rounded, size: 36, color: Colors.white),
                        ),
                        const SizedBox(height: 10),
                        const Text(
                          'AcademyHub',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 26,
                            fontWeight: FontWeight.w800,
                            letterSpacing: -0.5,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                          ),
                          child: const Text(
                            'Cloud School Management System',
                            style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w500),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 20),

                  // ── Main Input Card AT THE TOP ───────────────────
                  SlideTransition(
                    position: _cardAnim,
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
                          // Title
                          const Text(
                            'Find Your School',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w800,
                              color: AppColors.textPrimary,
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Enter the school code given to you by your school.',
                            style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
                          ),
                          const SizedBox(height: 18),

                          // School code field
                          TextField(
                            controller: _slugCtrl,
                            style: const TextStyle(
                              color: AppColors.textPrimary,
                              fontWeight: FontWeight.w700,
                              fontSize: 15,
                              letterSpacing: 0.5,
                            ),
                            textInputAction: TextInputAction.search,
                            onChanged: (val) {
                              if (val.trim().length > 2) {
                                _validate(val.trim().toLowerCase());
                              } else {
                                setState(() {
                                  _isValid = false;
                                  _schoolName = null;
                                });
                              }
                            },
                            decoration: InputDecoration(
                              hintText: 'e.g. greenwood',
                              hintStyle: const TextStyle(
                                color: AppColors.textDisabled,
                                fontSize: 14,
                                fontWeight: FontWeight.normal,
                              ),
                              prefixIcon: const Icon(Icons.domain_rounded, color: AppColors.textSecondary, size: 20),
                              suffixIcon: _isLoading
                                  ? const Padding(
                                      padding: EdgeInsets.all(14),
                                      child: SizedBox(
                                        width: 18, height: 18,
                                        child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.textSecondary),
                                      ),
                                    )
                                  : _isValid
                                      ? const Icon(Icons.check_circle_rounded, color: AppColors.successGreen, size: 22)
                                      : null,
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
                                borderSide: const BorderSide(color: Color(0xFF1E3A5F), width: 2),
                              ),
                            ),
                          ),

                          // School resolved card
                          AnimatedSize(
                            duration: const Duration(milliseconds: 300),
                            curve: Curves.easeOutCubic,
                            child: _schoolName != null
                                ? Padding(
                                    padding: const EdgeInsets.only(top: 12),
                                    child: Container(
                                      padding: const EdgeInsets.all(14),
                                      decoration: BoxDecoration(
                                        color: AppColors.successGreen.withValues(alpha: 0.07),
                                        borderRadius: BorderRadius.circular(14),
                                        border: Border.all(color: AppColors.successGreen.withValues(alpha: 0.3)),
                                      ),
                                      child: Row(
                                        children: [
                                          Container(
                                            width: 36, height: 36,
                                            decoration: BoxDecoration(
                                              color: AppColors.successGreen.withValues(alpha: 0.12),
                                              shape: BoxShape.circle,
                                            ),
                                            child: const Icon(Icons.school_rounded, color: AppColors.successGreen, size: 18),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  _schoolName!,
                                                  style: const TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    color: AppColors.textPrimary,
                                                    fontSize: 14,
                                                  ),
                                                ),
                                                const Text(
                                                  'Portal Active & Ready',
                                                  style: TextStyle(
                                                    fontSize: 11,
                                                    color: AppColors.successGreen,
                                                    fontWeight: FontWeight.w600,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  )
                                : const SizedBox.shrink(),
                          ),

                          const SizedBox(height: 20),

                          // Submit button
                          Container(
                            height: 52,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: _isValid
                                    ? [const Color(0xFF1E3A5F), const Color(0xFF2d548b)]
                                    : [AppColors.textDisabled, AppColors.textDisabled],
                              ),
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: _isValid
                                  ? [BoxShadow(
                                      color: const Color(0xFF1E3A5F).withValues(alpha: 0.4),
                                      blurRadius: 20,
                                      offset: const Offset(0, 8),
                                    )]
                                  : [],
                            ),
                            child: ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.transparent,
                                shadowColor: Colors.transparent,
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              ),
                              onPressed: _isValid
                                  ? () async {
                                      final slug = _slugCtrl.text.trim().toLowerCase();
                                      await SecureStorage.instance.setSchoolSlug(slug);
                                      await SecureStorage.instance.setSchoolName(_schoolName ?? 'School');
                                      if (mounted) context.go('/login');
                                    }
                                  : null,
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    _isValid ? 'Continue to ${_schoolName ?? 'School'}' : 'Enter school code above',
                                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                                  ),
                                  if (_isValid) ...[
                                    const SizedBox(width: 8),
                                    const Icon(Icons.arrow_forward_rounded, size: 18),
                                  ],
                                ],
                              ),
                            ),
                          ),

                          const SizedBox(height: 12),
                          Center(
                            child: Text(
                              'Don\'t have a code? Contact your school admin.',
                              style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 20),

                  // Feature pills row
                  Wrap(
                    spacing: 8, runSpacing: 8,
                    alignment: WrapAlignment.center,
                    children: [
                      _featurePill(Icons.people_rounded, 'Students'),
                      _featurePill(Icons.assignment_rounded, 'Results'),
                      _featurePill(Icons.fact_check_rounded, 'Attendance'),
                      _featurePill(Icons.payment_rounded, 'Fees'),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _featurePill(IconData icon, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: Colors.white.withValues(alpha: 0.85)),
          const SizedBox(width: 5),
          Text(label, style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 11, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
