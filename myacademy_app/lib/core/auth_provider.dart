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

  User({required this.id, required this.name, required this.email, required this.role, this.isSuperAdmin});

  factory User.fromJson(Map<String, dynamic> json) => User(
        id: json['id'],
        name: json['name'],
        email: json['email'],
        role: json['role'] ?? 'user',
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

  User?   get user             => _user;
  String? get token            => _token;
  bool    get isAuthenticated  => _token != null && _user != null;
  bool    get isLoading        => _isLoading;
  bool    get initialSyncDone  => _initialSyncDone;
  String? get error            => _error;
  Dio     get dio              => _dio;

  late final ApiService  _apiService  = ApiService(_dio);
  late final SyncService _syncService = SyncService(_dio);

  ApiService  get apiService  => _apiService;
  SyncService get syncService => _syncService;

  AuthProvider() { _init(); }

  Future<void> _init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');

    if (_token != null) {
      _dio.options.headers['Authorization'] = 'Bearer $_token';
      try {
        final response = await _dio.get('/user');
        _user = User.fromJson(response.data['data']);
        _initialSyncDone = prefs.getBool('initial_sync_done') ?? false;

        // Background refresh silently on every app open after first sync
        if (_initialSyncDone && _user != null) {
          _syncService.backgroundRefresh(_user!.role);
        }
      } catch (_) {
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
        'device_name': 'myacademy_app',
      });

      _token = response.data['token'];
      _user  = User.fromJson(response.data['user']);

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', _token!);
      _dio.options.headers['Authorization'] = 'Bearer $_token';

      _initialSyncDone = prefs.getBool('initial_sync_done') ?? false;

      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException {
      _isLoading = false;
      _error = 'Network error. Please check your connection.';
      notifyListeners();
      return false;
    } catch (e) {
      _isLoading = false;
      _error = 'Login failed. Please try again.';
      notifyListeners();
      return false;
    }
  }

  /// Called by InitialSyncScreen when sync completes
  void markSyncDone() {
    _initialSyncDone = true;
    notifyListeners();
  }

  Future<bool> isFirstTime() async {
    final prefs = await SharedPreferences.getInstance();
    return !(prefs.getBool('onboarding_completed') ?? false);
  }

  Future<void> logout() async {
    try {
      if (_token != null) await _dio.post('/logout');
    } catch (_) {}

    _token           = null;
    _user            = null;
    _initialSyncDone = false;

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('initial_sync_done');
    _dio.options.headers.remove('Authorization');

    notifyListeners();
  }
}
