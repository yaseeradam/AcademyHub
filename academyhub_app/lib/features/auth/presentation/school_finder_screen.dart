import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import 'package:google_fonts/google_fonts.dart';
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

  Future<void> _enterDemoMode() async {
    HapticFeedback.lightImpact();
    await SecureStorage.instance.setSchoolSlug('demo-academy');
    await SecureStorage.instance.setSchoolName('Stitch Academy (Demo)');
    if (mounted) GoRouter.of(context).go('/login');
  }

  Future<bool> _validate(String slug, {bool isExplicitSubmit = false}) async {
    if (slug.isEmpty) return false;
    if (slug == 'demo' || slug == 'test' || slug == 'stitch') {
      if (mounted) {
        setState(() {
          _isValid = true;
          _schoolName = 'Stitch Academy (Demo)';
          _errorMessage = null;
          _isLoading = false;
        });
      }
      return true;
    }
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
          setState(() { _isValid = false; _schoolName = null; _errorMessage = 'School not found. Check code or try Demo Mode.'; });
        }
        return false;
      }
    } on DioException catch (e) {
      if (mounted) {
        String msg = e.response?.statusCode == 404
            ? (e.response?.data?['message'] ?? 'School not found.')
            : 'Backend offline. Tap Demo Mode below to preview!';
        setState(() { _isValid = false; _schoolName = null; _errorMessage = msg; });
      }
      return false;
    } catch (_) {
      if (mounted) {
        setState(() { _isValid = false; _schoolName = null; _errorMessage = 'Unexpected error. Try Demo Mode.'; });
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
    final hasText = _slugCtrl.text.trim().isNotEmpty;

    return Scaffold(
      backgroundColor: AppColors.background,
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          // ── Background Ambient Glow ────────────────────────────────
          Positioned(
            top: -100,
            left: MediaQuery.of(context).size.width / 2 - 120,
            child: Container(
              width: 240,
              height: 240,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: AppColors.primary.withValues(alpha: 0.12),
              ),
            ),
          ),

          // ── Main Content Container ─────────────────────────────────
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    // Brand Logo & Glow Header
                    Stack(
                      alignment: Alignment.center,
                      children: [
                        Container(
                          width: 72,
                          height: 72,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: AppColors.primary.withValues(alpha: 0.2),
                          ),
                        ),
                        Container(
                          width: 64,
                          height: 64,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: AppColors.surfaceContainerHigh,
                            border: Border.all(color: AppColors.outline.withValues(alpha: 0.4), width: 1.5),
                          ),
                          child: const Icon(Icons.school_rounded, size: 32, color: AppColors.primary),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'AcademyHub',
                      style: GoogleFonts.plusJakartaSans(
                        color: AppColors.primary,
                        fontSize: 32,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Your School Management Suite',
                      style: GoogleFonts.plusJakartaSans(
                        color: AppColors.onSurfaceVariant,
                        fontSize: 14,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 32),

                    // ── Obsidian Floating Input Card ──────────────────────
                    SlideTransition(
                      position: _cardAnim,
                      child: Container(
                        constraints: const BoxConstraints(maxWidth: 480),
                        decoration: AppColors.soft3DCardDecoration(
                          borderRadius: 24,
                        ),
                        padding: const EdgeInsets.all(24),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Text(
                              'Find Your School Portal',
                              style: GoogleFonts.plusJakartaSans(
                                fontSize: 20,
                                fontWeight: FontWeight.w700,
                                color: AppColors.onSurface,
                              ),
                            ),
                            const SizedBox(height: 6),
                            Text(
                              'Enter the unique school code provided by your administration.',
                              style: GoogleFonts.plusJakartaSans(
                                fontSize: 13,
                                color: AppColors.onSurfaceVariant,
                              ),
                            ),
                            const SizedBox(height: 22),

                            // Code Search Field
                            TextField(
                              controller: _slugCtrl,
                              style: GoogleFonts.plusJakartaSans(
                                color: AppColors.onSurface,
                                fontWeight: FontWeight.w700,
                                fontSize: 15,
                                letterSpacing: 0.5,
                              ),
                              textInputAction: TextInputAction.search,
                              onChanged: _onSlugChanged,
                              onSubmitted: (_) => _handleSubmit(),
                              decoration: InputDecoration(
                                hintText: 'e.g. greenwood or demo',
                                hintStyle: GoogleFonts.plusJakartaSans(
                                  color: AppColors.outline,
                                  fontSize: 14,
                                  fontWeight: FontWeight.w400,
                                ),
                                prefixIcon: const Icon(Icons.search_rounded, color: AppColors.onSurfaceVariant, size: 22),
                                suffixIcon: _isLoading
                                    ? const Padding(
                                        padding: EdgeInsets.all(14),
                                        child: SizedBox(
                                          width: 18, height: 18,
                                          child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary),
                                        ),
                                      )
                                    : _isValid
                                        ? const Icon(Icons.check_circle_rounded, color: AppColors.successGreen, size: 22)
                                        : null,
                                contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                              ),
                            ),

                            // Resolved School Card Banner
                            AnimatedSize(
                              duration: const Duration(milliseconds: 300),
                              curve: Curves.easeOutCubic,
                              child: _schoolName != null
                                  ? Padding(
                                      padding: const EdgeInsets.only(top: 16),
                                      child: Container(
                                        padding: const EdgeInsets.all(14),
                                        decoration: BoxDecoration(
                                          color: AppColors.successGreen.withValues(alpha: 0.12),
                                          borderRadius: BorderRadius.circular(16),
                                          border: Border.all(color: AppColors.successGreen.withValues(alpha: 0.3)),
                                        ),
                                        child: Row(
                                          children: [
                                            Container(
                                              width: 38,
                                              height: 38,
                                              decoration: BoxDecoration(
                                                color: AppColors.successGreen.withValues(alpha: 0.2),
                                                shape: BoxShape.circle,
                                              ),
                                              child: const Icon(Icons.verified_user_rounded, color: AppColors.successGreen, size: 20),
                                            ),
                                            const SizedBox(width: 12),
                                            Expanded(
                                              child: Column(
                                                crossAxisAlignment: CrossAxisAlignment.start,
                                                children: [
                                                  Text(
                                                    _schoolName!,
                                                    style: GoogleFonts.plusJakartaSans(
                                                      fontWeight: FontWeight.bold,
                                                      color: AppColors.onSurface,
                                                      fontSize: 14,
                                                    ),
                                                  ),
                                                  Text(
                                                    'Portal Active & Ready ✓',
                                                    style: GoogleFonts.plusJakartaSans(
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

                            // Error Message Banner
                            AnimatedSize(
                              duration: const Duration(milliseconds: 250),
                              child: _errorMessage != null
                                  ? Padding(
                                      padding: const EdgeInsets.only(top: 12),
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

                            // Continue Action Button
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: (hasText && !_isLoading) ? AppColors.primaryContainer : AppColors.outlineVariant,
                                foregroundColor: (hasText && !_isLoading) ? const Color(0xFF2A1700) : AppColors.onSurfaceVariant,
                                elevation: 0,
                                minimumSize: const Size.fromHeight(54),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              ),
                              onPressed: (hasText && !_isLoading) ? _handleSubmit : null,
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    _isLoading
                                        ? 'Validating school...'
                                        : _isValid
                                            ? 'Continue to ${_schoolName ?? 'School'}'
                                            : 'Find School & Continue',
                                    style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w700, fontSize: 15),
                                  ),
                                  if (!_isLoading) ...[
                                    const SizedBox(width: 8),
                                    const Icon(Icons.arrow_forward_rounded, size: 18),
                                  ],
                                ],
                              ),
                            ),

                            const SizedBox(height: 12),

                            // Demo Mode Button
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
