import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:async';
import 'dart:convert';
import 'constants.dart';
import 'api_service.dart';
import 'sync_service.dart';
export 'sync_service.dart';

class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final bool? isSuperAdmin;
  final String? profilePhotoUrl;
  bool? whatsappSubscribed;
  final bool? isClassTeacher;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.isSuperAdmin,
    this.profilePhotoUrl,
    this.whatsappSubscribed,
    this.isClassTeacher,
  });

  factory User.fromJson(Map<String, dynamic> json) => User(
        id: json['id'],
        name: json['name'],
        email: json['email'] ?? json['admission_number'] ?? '',
        role: json['role'] ?? 'student',
        isSuperAdmin: json['is_super_admin'] == 1 || json['is_super_admin'] == true,
        profilePhotoUrl: json['profile_photo_url'],
        whatsappSubscribed: json['whatsapp_subscribed'] == 1 || json['whatsapp_subscribed'] == true,
        isClassTeacher: json['is_class_teacher'] == 1 || json['is_class_teacher'] == true,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'role': role,
        'is_super_admin': isSuperAdmin,
        'profile_photo_url': profilePhotoUrl,
        'whatsapp_subscribed': whatsappSubscribed,
        'is_class_teacher': isClassTeacher,
      };
}

class AuthProvider extends ChangeNotifier {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: ApiConstants.baseUrl,
    connectTimeout: const Duration(seconds: 5),
    receiveTimeout: const Duration(seconds: 5),
    headers: {'Accept': 'application/json'},
  ));

  User?   _user;
  String? _token;
  bool    _isLoading = true;
  bool    _initialSyncDone = false;
  String? _error;
  List<String> _activePlugins = [];
  List<Map<String, dynamic>> _allPlugins = [];
  ThemeMode _themeMode = ThemeMode.light;

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
  bool    get hasTenant        => _tenantSlug != null;
  String? get error            => _error;
  Dio     get dio              => _dio;
  ThemeMode get themeMode      => _themeMode;
  String get tenantApiUrl      => _dio.options.baseUrl;

  String? getReachableUrl(String? path) {
    if (path == null || path.trim().isEmpty) return null;
    if (!path.startsWith('http://') && !path.startsWith('https://')) {
      final base = tenantApiUrl.replaceAll('/api/', '').replaceAll('/api', '');
      final cleanPath = path.startsWith('/') ? path : '/$path';
      return '$base$cleanPath';
    }
    if (path.contains('localhost') || path.contains('127.0.0.1')) {
      final uri = Uri.tryParse(path);
      final baseUri = Uri.tryParse(tenantApiUrl);
      if (uri != null && baseUri != null) {
        final portString = baseUri.hasPort ? ':${baseUri.port}' : '';
        final newBase = '${baseUri.scheme}://${baseUri.host}$portString';
        final relativePath = uri.path + (uri.hasQuery ? '?${uri.query}' : '');
        return '$newBase$relativePath';
      }
    }
    return path;
  }

  String? get tenantSlug            => _tenantSlug;
  String? get tenantName            => _tenantName;
  String? get tenantLogoUrl         => _tenantLogoUrl;
  String? get tenantPrimaryColorHex => _tenantPrimaryColorHex;
  List<String> get activePlugins    => _activePlugins;
  List<Map<String, dynamic>> get allPlugins => _allPlugins;

  bool isPluginActive(String slug) => _activePlugins.contains(slug);

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
    
    // Restore theme mode (defaults to light)
    final savedTheme = prefs.getString('theme_mode');
    _themeMode = (savedTheme == 'dark') ? ThemeMode.dark : ThemeMode.light;
    AppColors.isDark = _themeMode == ThemeMode.dark;

    _token = prefs.getString('auth_token');
    _tenantSlug = prefs.getString('tenant_slug');
    _tenantName = prefs.getString('tenant_name');
    _tenantLogoUrl = prefs.getString('tenant_logo_url');
    _tenantPrimaryColorHex = prefs.getString('tenant_primary_color');

    if (_tenantSlug != null) {
      _dio.options.headers['X-Tenant-Slug'] = _tenantSlug;
      // Restore tenant-specific API base URL if saved
      final savedUrl = prefs.getString('tenant_api_url');
      if (savedUrl != null && savedUrl.isNotEmpty) {
        _dio.options.baseUrl = savedUrl;
      }
    }

    if (_token != null) {
      _dio.options.headers['Authorization'] = 'Bearer $_token';
      try {
        final response = await _dio.get('/user');
        _user = User.fromJson(response.data['data']);
        _initialSyncDone = prefs.getBool('initial_sync_done') ?? false;

        final cachedAllPlugins = prefs.getString('tenant_all_plugins');
        if (cachedAllPlugins != null) {
          try {
            _allPlugins = List<Map<String, dynamic>>.from(jsonDecode(cachedAllPlugins));
          } catch (_) {}
        }

        try {
          final termRes = await _dio.get('/term');
          final activePluginsList = List<String>.from(termRes.data['active_plugins'] ?? []);
          _activePlugins = activePluginsList;
          await prefs.setStringList('tenant_active_plugins', activePluginsList);

          final allPluginsList = List<Map<String, dynamic>>.from(termRes.data['all_plugins'] ?? []);
          _allPlugins = allPluginsList;
          await prefs.setString('tenant_all_plugins', jsonEncode(allPluginsList));
        } catch (_) {
          _activePlugins = prefs.getStringList('tenant_active_plugins') ?? [];
          final cachedAll = prefs.getString('tenant_all_plugins');
          if (cachedAll != null) {
            try {
              _allPlugins = List<Map<String, dynamic>>.from(jsonDecode(cachedAll));
            } catch (_) {}
          }
        }

        if (_initialSyncDone && _user != null) {
          _syncService.backgroundRefresh(_user!.role);
          _startPeriodicSync();
        }
      } catch (_) {
        _activePlugins = prefs.getStringList('tenant_active_plugins') ?? [];
        final cachedAll = prefs.getString('tenant_all_plugins');
        if (cachedAll != null) {
          try {
            _allPlugins = List<Map<String, dynamic>>.from(jsonDecode(cachedAll));
          } catch (_) {}
        }
        // Offline resilience: Recover user from SharedPreferences cache if network fails
        final cachedUserJson = prefs.getString('cached_user');
        if (cachedUserJson != null) {
          try {
            _user = User.fromJson(jsonDecode(cachedUserJson));
            _initialSyncDone = prefs.getBool('initial_sync_done') ?? false;
            if (_initialSyncDone) {
              _startPeriodicSync();
            }
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

      // If the API response provides a dedicated server URL, switch baseUrl.
      // Otherwise, reconstruct it dynamically using the tenant's resolved domain,
      // but only if we are not in a local development network setup (where the physical 
      // device cannot resolve the laptop's custom subdomain).
      final tenantApiUrl = data['api_url'] as String?;
      if (tenantApiUrl != null && tenantApiUrl.isNotEmpty) {
        _dio.options.baseUrl = tenantApiUrl.endsWith('/') ? tenantApiUrl : '$tenantApiUrl/';
      } else {
        final tenantDomain = data['domain'] as String?;
        if (tenantDomain != null && tenantDomain.isNotEmpty) {
          try {
            final uri = Uri.parse(_dio.options.baseUrl);
            final host = uri.host;
            
            // Check if current host is a local network IP or localhost
            final isLocal = host == 'localhost' || 
                            host == '127.0.0.1' || 
                            host.startsWith('192.168.') || 
                            host.startsWith('10.') || 
                            host.startsWith('172.');
                            
            if (!isLocal) {
              final scheme = uri.scheme.isNotEmpty ? uri.scheme : 'http';
              final portString = uri.hasPort ? ':${uri.port}' : '';
              _dio.options.baseUrl = '$scheme://$tenantDomain$portString/api/';
            } else {
              debugPrint('Local development detected ($host). Keeping base URL as ${_dio.options.baseUrl} and relying on X-Tenant-Slug header.');
            }
          } catch (e) {
            debugPrint('Error parsing baseUrl: $e');
            _dio.options.baseUrl = 'http://$tenantDomain/api/';
          }
        }
      }

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('tenant_slug', _tenantSlug!);
      await prefs.setString('tenant_name', _tenantName ?? '');
      await prefs.setString('tenant_logo_url', _tenantLogoUrl ?? '');
      await prefs.setString('tenant_primary_color', _tenantPrimaryColorHex ?? '#F59E0B');
      await prefs.setString('tenant_api_url', _dio.options.baseUrl);

      _dio.options.headers['X-Tenant-Slug'] = _tenantSlug;

      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException catch (de) {
      _isLoading = false;
      debugPrint('DioException during resolveTenant: ${de.message}, response: ${de.response?.data}');
      final msg = de.response?.data?['message'];
      _error = msg ?? 'School domain not found. Please double-check spelling.';
      notifyListeners();
      return false;
    } catch (e, stack) {
      _isLoading = false;
      debugPrint('Unexpected error during resolveTenant: $e\n$stack');
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
    _activePlugins = [];
    _allPlugins = [];

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('tenant_slug');
    await prefs.remove('tenant_name');
    await prefs.remove('tenant_logo_url');
    await prefs.remove('tenant_primary_color');
    await prefs.remove('tenant_api_url');
    await prefs.remove('auth_token');
    await prefs.remove('initial_sync_done');
    await prefs.remove('cached_user');
    await prefs.remove('student_admission_number');
    await prefs.remove('student_id');
    await prefs.remove('tenant_active_plugins');
    await prefs.remove('tenant_all_plugins');

    // Reset baseUrl back to default
    _dio.options.baseUrl = ApiConstants.baseUrl;

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
        profilePhotoUrl: isStudent ? userData['passport_photo_url'] : userData['profile_photo_url'],
      );

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', _token!);
      await prefs.setString('cached_user', jsonEncode(_user!.toJson()));
      if (isStudent) {
        await prefs.setString('student_admission_number', userData['admission_number'] ?? '');
        await prefs.setInt('student_id', userData['id']);
      }
      _dio.options.headers['Authorization'] = 'Bearer $_token';

      try {
        final termRes = await _dio.get('/term');
        final activePluginsList = List<String>.from(termRes.data['active_plugins'] ?? []);
        _activePlugins = activePluginsList;
        await prefs.setStringList('tenant_active_plugins', activePluginsList);

        final allPluginsList = List<Map<String, dynamic>>.from(termRes.data['all_plugins'] ?? []);
        _allPlugins = allPluginsList;
        await prefs.setString('tenant_all_plugins', jsonEncode(allPluginsList));
      } catch (_) {
        _activePlugins = [];
        _allPlugins = [];
      }

      _initialSyncDone = prefs.getBool('initial_sync_done') ?? false;
      _startPeriodicSync();

      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException catch (de) {
      _isLoading = false;
      debugPrint('DioException during login: ${de.message}, response: ${de.response?.data}');
      final msg = de.response?.data?['message'];
      _error = msg ?? 'Authentication failed. Please verify credentials.';
      notifyListeners();
      return false;
    } catch (e, stack) {
      _isLoading = false;
      debugPrint('Unexpected error during login: $e\n$stack');
      _error = 'Login failed. Please try again.';
      notifyListeners();
      return false;
    }
  }

  Future<void> refreshPlugins() async {
    if (_token == null) return;
    try {
      final prefs = await SharedPreferences.getInstance();
      final termRes = await _dio.get('/term');
      
      final activePluginsList = List<String>.from(termRes.data['active_plugins'] ?? []);
      _activePlugins = activePluginsList;
      await prefs.setStringList('tenant_active_plugins', activePluginsList);

      final allPluginsList = List<Map<String, dynamic>>.from(termRes.data['all_plugins'] ?? []);
      _allPlugins = allPluginsList;
      await prefs.setString('tenant_all_plugins', jsonEncode(allPluginsList));
      
      notifyListeners();
    } catch (_) {}
  }

  void markSyncDone() async {
    _initialSyncDone = true;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('initial_sync_done', true);
    notifyListeners();
  }

  bool get isReady => !_isLoading;

  Future<void> toggleTheme() async {
    _themeMode = _themeMode == ThemeMode.dark ? ThemeMode.light : ThemeMode.dark;
    AppColors.isDark = _themeMode == ThemeMode.dark;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('theme_mode', _themeMode == ThemeMode.dark ? 'dark' : 'light');
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      if (_token != null) {
        final endpoint = _user?.role == 'student' ? '/student/logout' : '/logout';
        await _dio.post(endpoint);
      }
    } catch (_) {}

    _stopPeriodicSync();
    _token           = null;
    _user            = null;
    _initialSyncDone = false;

    _activePlugins = [];
    _allPlugins = [];
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('initial_sync_done');
    await prefs.remove('cached_user');
    await prefs.remove('student_admission_number');
    await prefs.remove('student_id');
    await prefs.remove('tenant_active_plugins');
    await prefs.remove('tenant_all_plugins');
    _dio.options.headers.remove('Authorization');

    notifyListeners();
  }

  Timer? _syncTimer;

  void _startPeriodicSync() {
    _syncTimer?.cancel();
    _syncTimer = Timer.periodic(const Duration(seconds: 15), (timer) {
      if (_token != null && _user != null && _initialSyncDone) {
        _syncService.syncNow();
      }
    });
  }

  void _stopPeriodicSync() {
    _syncTimer?.cancel();
    _syncTimer = null;
  }
}
