import 'dart:async';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';
import 'package:academyhub_app/core/network/api_client.dart';

class SchoolFinderScreen extends StatefulWidget {
  const SchoolFinderScreen({super.key});

  @override
  State<SchoolFinderScreen> createState() => _SchoolFinderScreenState();
}

class _SchoolFinderScreenState extends State<SchoolFinderScreen>
    with SingleTickerProviderStateMixin {
  final _slugCtrl = TextEditingController();
  Timer? _debounceTimer;
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

    _slugCtrl.addListener(() {
      if (mounted) setState(() {});
    });
  }

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _cardCtrl.dispose();
    _slugCtrl.dispose();
    super.dispose();
  }

  void _onSlugChanged(String val) {
    _debounceTimer?.cancel();
    final trimmed = val.trim().toLowerCase();

    // Clear old validation states while actively typing
    if (_errorMessage != null || _isValid) {
      setState(() {
        _errorMessage = null;
        _isValid = false;
        _schoolName = null;
      });
    }

    if (trimmed.length >= 3) {
      _debounceTimer = Timer(const Duration(milliseconds: 500), () {
        _validate(trimmed, isExplicitSubmit: false);
      });
    }
  }

  Future<bool> _validate(String slug, {bool isExplicitSubmit = false}) async {
    if (slug.isEmpty) return false;
    setState(() { _isLoading = true; _errorMessage = null; });
    try {
      final response = await apiClient.dio.get('/tenant/$slug');
      if (response.statusCode == 200 && response.data != null) {
        if (mounted) {
          setState(() {
            _isValid = true;
            _schoolName = response.data['name'] ?? 'School Found';
            _errorMessage = null;
          });
        }
        return true;
      } else {
        if (mounted && isExplicitSubmit) {
          setState(() { _isValid = false; _schoolName = null; _errorMessage = 'School not found. Check the code.'; });
        }
        return false;
      }
    } on DioException catch (e) {
      if (mounted) {
        String msg = e.response?.statusCode == 404
            ? (e.response?.data?['message'] ?? 'School not found. Check the code.')
            : 'Connection error. Try again.';
        setState(() { _isValid = false; _schoolName = null; _errorMessage = msg; });
      }
      return false;
    } catch (_) {
      if (mounted) {
        setState(() { _isValid = false; _schoolName = null; _errorMessage = 'Unexpected error. Try again.'; });
      }
      return false;
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _handleSubmit() async {
    final slug = _slugCtrl.text.trim().toLowerCase();
    if (slug.isEmpty || _isLoading) return;

    final router = GoRouter.of(context);
    bool ok = _isValid;
    if (!ok) {
      ok = await _validate(slug, isExplicitSubmit: true);
    }

    if (ok && mounted) {
      await SecureStorage.instance.setSchoolSlug(slug);
      await SecureStorage.instance.setSchoolName(_schoolName ?? 'School');
      if (mounted) router.go('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final hasText = _slugCtrl.text.trim().isNotEmpty;

    return Scaffold(
      backgroundColor: const Color(0xFF1E3A5F),
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          // ── Solid background ────────────────────────────────
          Container(
            width: size.width,
            height: size.height,
            color: const Color(0xFF1E3A5F),
          ),

          // ── Soft decorative orbs ─────────────────────────────
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
            top: 160, left: -70,
            child: Container(
              width: 200, height: 200,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withValues(alpha: 0.04),
              ),
            ),
          ),
          Positioned(
            bottom: 100, right: -50,
            child: Container(
              width: 160, height: 160,
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
                            width: 80, height: 80,
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.14),
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white.withValues(alpha: 0.30), width: 2),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.10),
                                  blurRadius: 24,
                                  offset: const Offset(0, 8),
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
                          border: Border.all(color: const Color(0xFFEDF0F7)),
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
                              onChanged: _onSlugChanged,
                              onSubmitted: (_) => _handleSubmit(),
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
                            AnimatedContainer(
                              duration: const Duration(milliseconds: 250),
                              height: 54,
                              decoration: BoxDecoration(
                                color: (hasText && !_isLoading) ? const Color(0xFF1E3A5F) : AppColors.textDisabled,
                                borderRadius: BorderRadius.circular(16),
                                boxShadow: (hasText && !_isLoading)
                                    ? [BoxShadow(
                                        color: const Color(0xFF1E3A5F).withValues(alpha: 0.22),
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
                                onPressed: (hasText && !_isLoading) ? _handleSubmit : null,
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Flexible(
                                      child: Text(
                                        _isLoading
                                            ? 'Finding school...'
                                            : _isValid
                                                ? 'Continue to ${_schoolName ?? 'School'}'
                                                : 'Find School & Continue',
                                        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    if (!_isLoading) ...[
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
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
