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
          await SecureStorage.instance.setRole(_selectedRole);

          if (_selectedRole == 'student' && response.data['student'] != null) {
            final studentId = response.data['student']['id']?.toString();
            if (studentId != null) {
              await SecureStorage.instance.setStudentId(studentId);
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
      child: GestureDetector(
        onTap: () {
          setState(() {
            _selectedRole = role;
            _usernameController.clear();
            _errorMessage = null;
          });
        },
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primaryBlue : Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.04),
                blurRadius: 6,
                offset: const Offset(0, 3),
              ),
            ],
            border: Border.all(
              color: isSelected ? AppColors.primaryBlue : AppColors.divider,
            ),
          ),
          child: Column(
            children: [
              Icon(
                icon,
                color: isSelected ? Colors.white : AppColors.textSecondary,
                size: 24,
              ),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: isSelected ? Colors.white : AppColors.textSecondary,
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
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.textPrimary),
          onPressed: () async {
            await SecureStorage.instance.deleteSchoolSlug();
            if (mounted) {
              context.go('/');
            }
          },
        ),
        title: Text(
          _schoolName ?? 'Greenwood High School',
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        ),
        elevation: 0,
        backgroundColor: Colors.transparent,
      ),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Who are you?',
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 16),
              
              // Role Selectors
              Row(
                children: [
                  _buildRoleCard('student', 'Student', Icons.school),
                  const SizedBox(width: 8),
                  _buildRoleCard('parent', 'Parent', Icons.people_alt),
                  const SizedBox(width: 8),
                  _buildRoleCard('staff', 'Staff', Icons.assignment_ind),
                ],
              ),
              const SizedBox(height: 32),

              // Inputs Card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(
                        isStudent ? 'Admission Number' : 'Email Address',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextField(
                        controller: _usernameController,
                        keyboardType: isStudent ? TextInputType.text : TextInputType.emailAddress,
                        decoration: InputDecoration(
                          hintText: isStudent ? 'e.g. STU20240001' : 'e.g. parent@school.com',
                        ),
                      ),
                      const SizedBox(height: 20),
                      const Text(
                        'Password',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextField(
                        controller: _passwordController,
                        obscureText: _obscurePassword,
                        decoration: InputDecoration(
                          hintText: 'Enter your password',
                          suffixIcon: IconButton(
                            icon: Icon(
                              _obscurePassword ? Icons.visibility_off : Icons.visibility,
                              color: AppColors.textSecondary,
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
              const SizedBox(height: 16),

              if (_errorMessage != null)
                Text(
                  _errorMessage!,
                  style: const TextStyle(color: AppColors.dangerRed, fontSize: 12),
                  textAlign: TextAlign.center,
                ),

              const SizedBox(height: 24),

              // Sign In Button with loading animations
              _isLoading
                  ? const Center(
                      child: CircularProgressIndicator(color: AppColors.primaryBlue),
                    )
                  : ElevatedButton(
                      onPressed: _handleLogin,
                      child: const Text('Sign In'),
                    ),
            ],
          ),
        ),
      ),
    );
  }
}
