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

class _LoginScreenState extends State<LoginScreen> {
  String _selectedRole = 'student'; // 'student', 'parent', 'staff'
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _obscurePassword = true;
  bool _isLoading = false;
  String? _schoolName;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadSchoolName();
  }

  Future<void> _loadSchoolName() async {
    final name = await SecureStorage.instance.getSchoolName();
    setState(() {
      _schoolName = name ?? 'My School';
    });
  }

  Future<void> _handleLogin() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text;

    if (username.isEmpty || password.isEmpty) {
      setState(() {
        _errorMessage = 'All fields are required.';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final endpoint = _selectedRole == 'student' ? '/student/login' : '/login';
      final payload = _selectedRole == 'student'
          ? {
              'admission_number': username,
              'password': password,
              'device_name': 'mobile_companion',
            }
          : {
              'email': username,
              'password': password,
              'device_name': 'mobile_companion',
            };

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
            if (studentId != null) {
              await SecureStorage.instance.setStudentId(studentId);
            }
            final name = response.data['student']['full_name']?.toString();
            if (name != null) {
              await SecureStorage.instance.setUserName(name);
            }
          } else if (response.data['user'] != null) {
            final name = response.data['user']['name']?.toString();
            if (name != null) {
              await SecureStorage.instance.setUserName(name);
            }
          }

          if (mounted) {
            context.go('/dashboard');
          }
        }
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Invalid credentials or login failed.';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Widget _buildRoleCard(String role, String label, IconData icon) {
    final isSelected = _selectedRole == role;
    return Expanded(
      child: InkWell(
        onTap: () {
          setState(() {
            _selectedRole = role;
            _usernameController.clear();
            _errorMessage = null;
          });
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: isSelected ? AppColors.amberPrimary : Colors.transparent,
                width: 3,
              ),
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                color: isSelected ? AppColors.amberPrimary : const Color(0xFF64748B),
                size: 22,
              ),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                  color: isSelected ? AppColors.amberPrimary : const Color(0xFF64748B),
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
    final isStudent = _selectedRole == 'student';
    return Scaffold(
      backgroundColor: AppColors.appBackground,
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF0F172A)),
          onPressed: () async {
            await SecureStorage.instance.deleteSchoolSlug();
            if (mounted) {
              context.go('/');
            }
          },
        ),
        title: Text(
          _schoolName ?? 'Greenwood High School',
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
        ),
        elevation: 0,
        backgroundColor: Colors.transparent,
      ),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Crest Logo
              Center(
                child: Column(
                  children: [
                    const Icon(
                      Icons.school_outlined,
                      size: 64,
                      color: AppColors.amberPrimary,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _schoolName ?? 'AcademyHub',
                      style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Your School Portal',
                      style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 32),

              // Role Selector Label
              const Text(
                'LOG IN AS',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: AppColors.amberPrimary,
                  letterSpacing: 1.5,
                ),
              ),
              const SizedBox(height: 12),
              
              // Role Selectors (Tabs)
              Row(
                children: [
                  _buildRoleCard('student', 'Student', Icons.school),
                  _buildRoleCard('parent', 'Parent', Icons.people_alt),
                  _buildRoleCard('staff', 'Staff', Icons.assignment_ind),
                ],
              ),
              const SizedBox(height: 32),

              // Inputs Card
              Card(
                color: Colors.white,
                elevation: 2,
                shadowColor: Colors.black.withOpacity(0.04),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                child: Padding(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(
                        (isStudent ? 'Admission Number' : 'Email Address').toUpperCase(),
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: AppColors.amberPrimary,
                          letterSpacing: 1.2,
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextField(
                        controller: _usernameController,
                        keyboardType: isStudent ? TextInputType.text : TextInputType.emailAddress,
                        style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.w600),
                        decoration: InputDecoration(
                          fillColor: const Color(0xFFF1F5F9),
                          filled: true,
                          hintText: isStudent ? 'e.g. ADM-2026-0092' : 'e.g. parent1@academyhub.local',
                          hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: AppColors.amberPrimary, width: 1.5),
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),
                      const Text(
                        'PASSWORD',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: AppColors.amberPrimary,
                          letterSpacing: 1.2,
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextField(
                        controller: _passwordController,
                        obscureText: _obscurePassword,
                        style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.w600),
                        decoration: InputDecoration(
                          fillColor: const Color(0xFFF1F5F9),
                          filled: true,
                          hintText: 'Enter your password',
                          hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(color: AppColors.amberPrimary, width: 1.5),
                          ),
                          suffixIcon: IconButton(
                            icon: Icon(
                              _obscurePassword ? Icons.visibility_off : Icons.visibility,
                              color: const Color(0xFF64748B),
                            ),
                            onPressed: () {
                              setState(() {
                                _obscurePassword = !_obscurePassword;
                              });
                            },
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),

              if (_errorMessage != null) ...[
                Text(
                  _errorMessage!,
                  style: const TextStyle(color: AppColors.dangerRed, fontSize: 12),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
              ],

              // Log In Button
              Container(
                height: 52,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.amberPrimary.withOpacity(0.35),
                      blurRadius: 16,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.amberPrimary,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: _isLoading ? null : _handleLogin,
                  child: _isLoading
                      ? const SizedBox(
                          width: 24,
                          height: 24,
                          child: CircularProgressIndicator(
                            strokeWidth: 2.5,
                            color: Colors.white,
                          ),
                        )
                      : const Text(
                          'LOG IN',
                          style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
                        ),
                ),
              ),
              const SizedBox(height: 24),
              
              // Support Links
              Center(
                child: TextButton(
                  onPressed: () {},
                  child: const Text(
                    'Forgot Password?',
                    style: TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
              Center(
                child: TextButton(
                  onPressed: () {},
                  child: const Text(
                    'Need help? Contact Admin',
                    style: TextStyle(color: AppColors.amberPrimary, fontSize: 13, fontWeight: FontWeight.bold),
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
