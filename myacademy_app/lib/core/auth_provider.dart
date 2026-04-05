import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'constants.dart';
import 'api_service.dart';
import 'sync_service.dart';

class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final bool? isSuperAdmin;

  User({
    required this.id, 
    required this.name, 
    required this.email, 
    required this.role,
    this.isSuperAdmin,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      role: json['role'] ?? 'user',
      isSuperAdmin: json['is_super_admin'] == 1 || json['is_super_admin'] == true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'is_super_admin': isSuperAdmin,
    };
  }
}

class AuthProvider extends ChangeNotifier {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: ApiConstants.baseUrl,
    headers: {
      'Accept': 'application/json',
    },
  ));

  User? _user;
  String? _token;
  bool _isLoading = true;
  String? _error;

  User? get user => _user;
  String? get token => _token;
  bool get isAuthenticated => _token != null && _user != null;
  bool get isLoading => _isLoading;
  String? get error => _error;
  Dio get dio => _dio;

  late final ApiService _apiService = ApiService(_dio);
  late final SyncService _syncService = SyncService(_dio);

  ApiService get apiService => _apiService;
  SyncService get syncService => _syncService;

  AuthProvider() {
    _init();
  }

  Future<void> _init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    
    if (_token != null) {
      _dio.options.headers['Authorization'] = 'Bearer $_token';
      try {
        final response = await _dio.get('/user');
        _user = User.fromJson(response.data['data']);
      } catch (e) {
        _token = null;
        await prefs.remove('auth_token');
        _dio.options.headers.remove('Authorization');
      }
    }
    
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _dio.post('/login', data: {
        'email': email,
        'password': password,
        'device_name': 'flutter_web_app',
      });

      print('Raw response data: ${response.data}');
      print('Token: ${response.data['token']}');
      print('User data: ${response.data['user']}');

      // Laravel API returns token and user directly
      _token = response.data['token'];
      
      print('About to parse user...');
      try {
        _user = User.fromJson(response.data['user']);
        print('User parsed successfully: ${_user?.name}');
      } catch (userError) {
        print('Error parsing user: $userError');
        throw userError;
      }

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', _token!);
      
      _dio.options.headers['Authorization'] = 'Bearer $_token';
      
      print('Login completed successfully');
      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException catch (e) {
      _isLoading = false;
      print('DioException: $e');
      _error = 'Network error. Please try again.';
      notifyListeners();
      return false;
    } catch (e) {
      _isLoading = false;
      print('General Exception during login: $e');
      print('Exception type: ${e.runtimeType}');
      _error = 'Login failed: ${e.toString()}';
      notifyListeners();
      return false;
    }
  }

  Future<bool> checkAuthStatus() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    return token != null && _user != null;
  }

  Future<bool> isFirstTime() async {
    final prefs = await SharedPreferences.getInstance();
    return !(prefs.getBool('onboarding_completed') ?? false);
  }

  Future<void> logout() async {
    try {
      if (_token != null) {
        await _dio.post('/logout');
      }
    } catch (e) {
      // Ignore
    }

    _token = null;
    _user = null;
    
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    _dio.options.headers.remove('Authorization');
    
    notifyListeners();
  }
}
