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

class _SchoolFinderScreenState extends State<SchoolFinderScreen> {
  final TextEditingController _slugController = TextEditingController();
  bool _isLoading = false;
  bool _isValidSlug = false;
  String? _resolvedSchoolName;
  String? _errorMessage;

  Future<void> _validateSchoolSlug(String slug) async {
    if (slug.isEmpty) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await apiClient.dio.get('/tenant/$slug');
      if (response.statusCode == 200 && response.data != null) {
        setState(() {
          _isValidSlug = true;
          _resolvedSchoolName = response.data['name'] ?? 'School Resolved';
          _errorMessage = null;
        });
      } else {
        setState(() {
          _isValidSlug = false;
          _resolvedSchoolName = null;
          _errorMessage = 'School not found. Check slug.';
        });
      }
    } on DioException catch (e) {
      String msg = 'Connection error: ${e.message ?? e.toString()}';
      if (e.response != null) {
        if (e.response!.statusCode == 404) {
          msg = e.response!.data?['message'] ?? 'School not found. Check slug.';
        } else {
          msg = 'Server returned error status: ${e.response!.statusCode}';
        }
      }
      setState(() {
        _isValidSlug = false;
        _resolvedSchoolName = null;
        _errorMessage = msg;
      });
    } catch (e) {
      setState(() {
        _isValidSlug = false;
        _resolvedSchoolName = null;
        _errorMessage = 'An unexpected error occurred: ${e.toString()}';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.appBackground,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 48),
              // Logo
              Center(
                child: Container(
                  width: 90,
                  height: 90,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.amberPrimary.withOpacity(0.15),
                        blurRadius: 24,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.school_rounded,
                    size: 48,
                    color: AppColors.amberPrimary,
                  ),
                ),
              ),
              const SizedBox(height: 20),
              const Center(
                child: Text(
                  'AcademyHub',
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF0F172A),
                  ),
                ),
              ),
              const SizedBox(height: 4),
              const Center(
                child: Text(
                  'Your School Portal',
                  style: TextStyle(
                    fontSize: 13,
                    color: Color(0xFF64748B),
                  ),
                ),
              ),
              const SizedBox(height: 48),
              
              // Input Field Card
              Card(
                color: Colors.white,
                elevation: 2,
                shadowColor: Colors.black.withOpacity(0.04),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                child: Padding(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'SCHOOL CODE',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: AppColors.amberPrimary,
                          letterSpacing: 1.2,
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: _slugController,
                        style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.bold),
                        onChanged: (val) {
                          if (val.length > 2) {
                            _validateSchoolSlug(val.trim().toLowerCase());
                          } else {
                            setState(() {
                              _isValidSlug = false;
                              _resolvedSchoolName = null;
                            });
                          }
                        },
                        decoration: InputDecoration(
                          fillColor: const Color(0xFFF1F5F9),
                          filled: true,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          hintText: 'e.g. greenwood',
                          hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                          prefixIcon: const Icon(Icons.domain, color: Color(0xFF94A3B8)),
                          suffixIcon: _isLoading
                              ? const Padding(
                                  padding: EdgeInsets.all(12.0),
                                  child: SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.amberPrimary),
                                  ),
                                )
                              : _isValidSlug
                                  ? Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        const Icon(Icons.check_circle, color: AppColors.successGreen, size: 16),
                                        const SizedBox(width: 4),
                                        const Text(
                                          'Verified',
                                          style: TextStyle(
                                            color: AppColors.successGreen,
                                            fontSize: 12,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                      ],
                                    )
                                  : null,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide.none,
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide.none,
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: AppColors.amberPrimary, width: 1.5),
                          ),
                          errorBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: AppColors.dangerRed, width: 1.5),
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'Your school gave you this.',
                        style: TextStyle(
                          fontSize: 11,
                          color: Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Resolved Confirmation Chip
              if (_resolvedSchoolName != null) ...[
                AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF3C7),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.amberPrimary),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.check_circle, color: AppColors.amberPrimary),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _resolvedSchoolName!,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
              ],
              
              if (_errorMessage != null) ...[
                Text(
                  _errorMessage!,
                  style: const TextStyle(color: AppColors.dangerRed, fontSize: 12),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
              ],

              const SizedBox(height: 24),
              
              // Continue Button
              Opacity(
                opacity: _isValidSlug ? 1.0 : 0.4,
                child: Container(
                  height: 52,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: _isValidSlug
                        ? [
                            BoxShadow(
                              color: AppColors.amberPrimary.withOpacity(0.4),
                              blurRadius: 20,
                              offset: const Offset(0, 4),
                            ),
                          ]
                        : [],
                  ),
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.amberPrimary,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    onPressed: _isValidSlug
                        ? () async {
                            final slug = _slugController.text.trim().toLowerCase();
                            await SecureStorage.instance.setSchoolSlug(slug);
                            await SecureStorage.instance.setSchoolName(_resolvedSchoolName ?? 'School');
                            if (mounted) {
                              context.go('/login');
                            }
                          }
                        : null,
                    child: const Text(
                      'Continue →',
                      style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
