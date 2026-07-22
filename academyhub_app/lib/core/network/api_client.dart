import 'package:dio/dio.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';

class ApiClient {
  late final Dio dio;
  
  // Target production VPS API host endpoint
  static const String defaultBaseUrl = 'https://academyhub.com.ng/api';

  ApiClient() {
    dio = Dio(BaseOptions(
      baseUrl: defaultBaseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 15),
    ));

    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Append X-Tenant-Slug if configured
          final slug = await SecureStorage.instance.getSchoolSlug();
          if (slug != null) {
            options.headers['X-Tenant-Slug'] = slug;
          }

          // Append Authorization token if authenticated
          final token = await SecureStorage.instance.getToken();
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }

          options.headers['Accept'] = 'application/json';
          options.headers['Content-Type'] = 'application/json';
          return handler.next(options);
        },
        onError: (DioException e, handler) async {
          // Force logout on 401 Unauthorized
          if (e.response?.statusCode == 401) {
            await SecureStorage.instance.deleteToken();
            await SecureStorage.instance.deleteRole();
            // In a production app, this would trigger an event to redirect to login
          }
          return handler.next(e);
        },
      ),
    );
  }

  // Update base URL when school domain is resolved (always HTTPS)
  void updateBaseUrl(String domainOrIp) {
    dio.options.baseUrl = 'https://$domainOrIp/api';
  }
}

final apiClient = ApiClient();
