import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';
import 'package:academyhub_app/core/network/api_client.dart';

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
    _cardAnim = Tween<Offset>(begin: const Offset(0, 0.1), end: Offset.zero)
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
      backgroundColor: const Color(0xFF0F172A),
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          // ── Ambient gradient background ─────────────────────
          Container(
            width: size.width,
            height: size.height,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: _kGrad,
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
          ),

          // ── Decorative background circles ────────────────────
          Positioned(
            top: -60, right: -40,
            child: Container(
              width: 240, height: 240,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.05),
              ),
            ),
          ),
          Positioned(
            top: 120, left: -50,
            child: Container(
              width: 180, height: 180,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.03),
              ),
            ),
          ),

          // ── Content area ─────────────────────────────────────
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Brand Icon & Header
                    Center(
                      child: Column(
                        children: [
                          Container(
                            width: 76, height: 76,
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.15),
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white.withValues(alpha: 0.35), width: 2.5),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.2),
                                  blurRadius: 16,
                                  offset: const Offset(0, 6),
                                ),
                              ],
                            ),
                            child: const Icon(Icons.school_rounded, size: 38, color: Colors.white),
                          ),
                          const SizedBox(height: 12),
                          const Text(
                            'AcademyHub',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 28,
                              fontWeight: FontWeight.w900,
                              letterSpacing: -0.5,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                            ),
                            child: const Text(
                              'Cloud School Management System',
                              style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600),
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),

                    // ── Upper Floating Input Card ─────────────────────
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
                            const Text(
                              'Find Your School',
                              style: TextStyle(
                                fontSize: 19,
                                fontWeight: FontWeight.w800,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Enter the school code given to you by your school.',
                              style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
                            ),
                            const SizedBox(height: 20),

                            // School code input
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
                                  borderRadius: BorderRadius.circular(16),
                                  borderSide: const BorderSide(color: AppColors.divider),
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(16),
                                  borderSide: const BorderSide(color: AppColors.divider),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(16),
                                  borderSide: const BorderSide(color: Color(0xFF1E3A5F), width: 2),
                                ),
                              ),
                            ),

                            // Resolved School Card
                            AnimatedSize(
                              duration: const Duration(milliseconds: 300),
                              curve: Curves.easeOutCubic,
                              child: _schoolName != null
                                  ? Padding(
                                      padding: const EdgeInsets.only(top: 14),
                                      child: Container(
                                        padding: const EdgeInsets.all(14),
                                        decoration: BoxDecoration(
                                          color: AppColors.successGreen.withValues(alpha: 0.08),
                                          borderRadius: BorderRadius.circular(16),
                                          border: Border.all(color: AppColors.successGreen.withValues(alpha: 0.3)),
                                        ),
                                        child: Row(
                                          children: [
                                            Container(
                                              width: 38, height: 38,
                                              decoration: BoxDecoration(
                                                color: AppColors.successGreen.withValues(alpha: 0.15),
                                                shape: BoxShape.circle,
                                              ),
                                              child: const Icon(Icons.school_rounded, color: AppColors.successGreen, size: 20),
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
                                                    'Portal Active & Verified ✓',
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

                            // Error message display
                            AnimatedSize(
                              duration: const Duration(milliseconds: 250),
                              child: _errorMessage != null
                                  ? Padding(
                                      padding: const EdgeInsets.only(top: 10),
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
                                              child: Text(_errorMessage!,
                                                  style: const TextStyle(color: AppColors.dangerRed, fontSize: 12)),
                                            ),
                                          ],
                                        ),
                                      ),
                                    )
                                  : const SizedBox.shrink(),
                            ),

                            const SizedBox(height: 22),

                            // Action button
                            Container(
                              height: 54,
                              decoration: BoxDecoration(
                                gradient: LinearGradient(
                                  colors: _isValid
                                      ? [const Color(0xFF1E3A5F), const Color(0xFF2d548b)]
                                      : [AppColors.textDisabled, AppColors.textDisabled],
                                ),
                                borderRadius: BorderRadius.circular(16),
                                boxShadow: _isValid
                                    ? [BoxShadow(
                                        color: const Color(0xFF1E3A5F).withValues(alpha: 0.35),
                                        blurRadius: 16,
                                        offset: const Offset(0, 6),
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

                            const SizedBox(height: 14),
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

                    const SizedBox(height: 24),

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
