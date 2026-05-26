import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:ui';
import 'dart:convert';
import 'constants.dart';
import 'api_service.dart';
import 'sync_service.dart';

class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final bool? isSuperAdmin;

  User({required this.id, required this.name, required this.email, required this.role, this.isSuperAdmin});

  factory User.fromJson(Map<String, dynamic> json) => User(
        id: json['id'],
        name: json['name'],
        email: json['email'] ?? json['admission_number'] ?? '',
        role: json['role'] ?? 'student',
        isSuperAdmin: json['is_super_admin'] == 1 || json['is_super_admin'] == true,
      );

  Map<String, dynamic> toJson() => {
        'id': id, 'name': name, 'email': email,
        'role': role, 'is_super_admin': isSuperAdmin,
      };
}

class AuthProvider extends ChangeNotifier {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: ApiConstants.baseUrl,
    headers: {'Accept': 'application/json'},
  ));

  User?   _user;
  String? _token;
  bool    _isLoading = true;
  bool    _initialSyncDone = false;
  String? _error;

  // School Tenant branding properties
  String? _tenantSlug;
  String? _tenantName;
  String? _tenantLogoUrl;
  String? _tenantPrimaryColorHex;

  User?   get user             => _user;
  String? get token            => _token;
  bool    get isAuthenticated  => _token != null && _user != null;
  bool    get isLoading        => _isLoading;
  bool    get initialSyncDone  => _initialSyncDone;
  String? get error            => _error;
  Dio     get dio              => _dio;

  String? get tenantSlug            => _tenantSlug;
  String? get tenantName            => _tenantName;
  String? get tenantLogoUrl         => _tenantLogoUrl;
  String? get tenantPrimaryColorHex => _tenantPrimaryColorHex;

  Color get tenantPrimaryColor {
    if (_tenantPrimaryColorHex != null) {
      try {
        final hex = _tenantPrimaryColorHex!.replaceFirst('#', '');
        return Color(int.parse('FF$hex', radix: 16));
      } catch (_) {}
    }
    return AppColors.primary;
  }

  late final ApiService  _apiService  = ApiService(_dio);
  late final SyncService _syncService = SyncService(_dio);

  ApiService  get apiService  => _apiService;
  SyncService get syncService => _syncService;

  AuthProvider() { _init(); }

  Future<void> _init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    _tenantSlug = prefs.getString('tenant_slug');
    _tenantName = prefs.getString('tenant_name');
    _tenantLogoUrl = prefs.getString('tenant_logo_url');
    _tenantPrimaryColorHex = prefs.getString('tenant_primary_color');

    if (_tenantSlug != null) {
      _dio.options.headers['X-Tenant-Slug'] = _tenantSlug;
    }

    if (_token != null) {
      _dio.options.headers['Authorization'] = 'Bearer $_token';
      try {
        final response = await _dio.get('/user');
        _user = User.fromJson(response.data['data']);
        _initialSyncDone = prefs.getBool('initial_sync_done') ?? false;

        if (_initialSyncDone && _user != null) {
          _syncService.backgroundRefresh(_user!.role);
        }
      } catch (_) {
        // Offline resilience: Recover user from SharedPreferences cache if network fails
        final cachedUserJson = prefs.getString('cached_user');
        if (cachedUserJson != null) {
          try {
            _user = User.fromJson(jsonDecode(cachedUserJson));
            _initialSyncDone = prefs.getBool('initial_sync_done') ?? false;
          } catch (_) {
            _token = null;
            await prefs.remove('auth_token');
            _dio.options.headers.remove('Authorization');
          }
        } else {
          _token = null;
          await prefs.remove('auth_token');
          _dio.options.headers.remove('Authorization');
        }
      }
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<bool> resolveTenant(String slug) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _dio.get('/tenant/$slug');
      final data = response.data;
      
      _tenantSlug = data['slug'];
      _tenantName = data['name'];
      _tenantLogoUrl = data['logo_url'];
      _tenantPrimaryColorHex = data['primary_color'];

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('tenant_slug', _tenantSlug!);
      await prefs.setString('tenant_name', _tenantName ?? '');
      await prefs.setString('tenant_logo_url', _tenantLogoUrl ?? '');
      await prefs.setString('tenant_primary_color', _tenantPrimaryColorHex ?? '#F59E0B');

      _dio.options.headers['X-Tenant-Slug'] = _tenantSlug;

      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException catch (de) {
      _isLoading = false;
      final msg = de.response?.data?['message'];
      _error = msg ?? 'School domain not found. Please double-check spelling.';
      notifyListeners();
      return false;
    } catch (e) {
      _isLoading = false;
      _error = 'Failed to verify school. Please try again.';
      notifyListeners();
      return false;
    }
  }

  Future<void> clearTenant() async {
    _tenantSlug = null;
    _tenantName = null;
    _tenantLogoUrl = null;
    _tenantPrimaryColorHex = null;
    _token = null;
    _user = null;
    _initialSyncDone = false;

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('tenant_slug');
    await prefs.remove('tenant_name');
    await prefs.remove('tenant_logo_url');
    await prefs.remove('tenant_primary_color');
    await prefs.remove('auth_token');
    await prefs.remove('initial_sync_done');
    await prefs.remove('cached_user');
    await prefs.remove('student_admission_number');
    await prefs.remove('student_id');

    _dio.options.headers.remove('X-Tenant-Slug');
    _dio.options.headers.remove('Authorization');

    notifyListeners();
  }

  Future<bool> login(String username, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final isStudent = !username.contains('@');
      final endpoint = isStudent ? '/student/login' : '/login';

      final payload = isStudent
          ? {
              'admission_number': username,
              'password': password,
              'device_name': 'academyhub',
            }
          : {
              'email': username,
              'password': password,
              'device_name': 'academyhub',
            };

      final response = await _dio.post(endpoint, data: payload);

      _token = response.data['token'];
      final userData = isStudent ? response.data['student'] : response.data['user'];

      _user = User(
        id: userData['id'],
        name: isStudent ? '${userData['first_name']} ${userData['last_name']}' : userData['name'],
        email: isStudent ? (userData['admission_number'] ?? '') : userData['email'],
        role: isStudent ? 'student' : (userData['role'] ?? 'user'),
      );

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', _token!);
      await prefs.setString('cached_user', jsonEncode(_user!.toJson()));
      if (isStudent) {
        await prefs.setString('student_admission_number', userData['admission_number'] ?? '');
        await prefs.setInt('student_id', userData['id']);
      }
      _dio.options.headers['Authorization'] = 'Bearer $_token';

      _initialSyncDone = prefs.getBool('initial_sync_done') ?? false;

      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException catch (de) {
      _isLoading = false;
      final msg = de.response?.data?['message'];
      _error = msg ?? 'Authentication failed. Please verify credentials.';
      notifyListeners();
      return false;
    } catch (e) {
      _isLoading = false;
      _error = 'Login failed. Please try again.';
      notifyListeners();
      return false;
    }
  }

  void markSyncDone() {
    _initialSyncDone = true;
    notifyListeners();
  }

  bool get isReady => !_isLoading;

  Future<bool> isFirstTime() async {
    final prefs = await SharedPreferences.getInstance();
    return !(prefs.getBool('onboarding_completed') ?? false);
  }

  Future<void> logout() async {
    try {
      if (_token != null) {
        final endpoint = _user?.role == 'student' ? '/student/logout' : '/logout';
        await _dio.post(endpoint);
      }
    } catch (_) {}

    _token           = null;
    _user            = null;
    _initialSyncDone = false;

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('initial_sync_done');
    await prefs.remove('cached_user');
    await prefs.remove('student_admission_number');
    await prefs.remove('student_id');
    _dio.options.headers.remove('Authorization');

    notifyListeners();
  }
}
