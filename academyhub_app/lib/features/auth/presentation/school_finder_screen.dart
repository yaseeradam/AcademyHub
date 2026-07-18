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
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFF4F6FA), Color(0xFFE8EEF8)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // AcademyHub Logo / Icon with shadow
              Center(
                child: Container(
                  width: 90,
                  height: 90,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.primaryBlue.withOpacity(0.12),
                        blurRadius: 16,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.school_rounded,
                    size: 48,
                    color: AppColors.primaryBlue,
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Center(
                child: Text(
                  'AcademyHub',
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),
              ),
              const Center(
                child: Text(
                  'Your School, In Your Pocket',
                  style: TextStyle(
                    fontSize: 14,
                    color: AppColors.textSecondary,
                  ),
                ),
              ),
              const SizedBox(height: 32),
              
              // Input Field Card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'School Identifier',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextField(
                        controller: _slugController,
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
                          hintText: 'Enter school slug (e.g. greenwood)',
                          prefixIcon: const Icon(Icons.domain, color: AppColors.textSecondary),
                          suffixIcon: _isLoading
                              ? const Padding(
                                  padding: EdgeInsets.all(12.0),
                                  child: SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(strokeWidth: 2),
                                  ),
                                )
                              : _isValidSlug
                                  ? const Icon(Icons.check_circle, color: AppColors.successGreen)
                                  : null,
                        ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'Your school administrator provided you with this code.',
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Resolved Confirmation Chip
              if (_resolvedSchoolName != null)
                AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.softBlue.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.softBlue.withOpacity(0.2)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.check_circle_outline, color: AppColors.softBlue),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _resolvedSchoolName!,
                          style: const TextStyle(
                            fontWeight: FontWeight.w600,
                            color: AppColors.textPrimary,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              
              if (_errorMessage != null)
                Text(
                  _errorMessage!,
                  style: const TextStyle(color: AppColors.dangerRed, fontSize: 12),
                  textAlign: TextAlign.center,
                ),

              const SizedBox(height: 24),
              
              // Continue Button
              ElevatedButton(
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
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('Continue'),
                    SizedBox(width: 8),
                    Icon(Icons.arrow_forward_rounded, size: 20),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
