import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'database_helper.dart';

class ApiService {
  final Dio dio;
  final DatabaseHelper dbHelper = DatabaseHelper();

  ApiService(this.dio);

  /// Helper to check internet connectivity
  Future<bool> get isOnline async {
    final List<ConnectivityResult> connectivityResult = await (Connectivity().checkConnectivity());
    if (connectivityResult.contains(ConnectivityResult.none)) {
      return false;
    }
    return true;
  }

  /// GET request with Caching strategy (Network-first, then Cache)
  Future<dynamic> getWithCache(String endpoint) async {
    if (await isOnline) {
      try {
        final response = await dio.get(endpoint);
        // Save to cache
        await dbHelper.saveCache(endpoint, jsonEncode(response.data));
        return response.data;
      } catch (e) {
        // Fallback to cache on exception
        return await _readFromCache(endpoint);
      }
    } else {
      // Offline: Get from cache
      return await _readFromCache(endpoint);
    }
  }

  Future<dynamic> _readFromCache(String endpoint) async {
    String? cachedData = await dbHelper.getCache(endpoint);
    if (cachedData != null) {
      return jsonDecode(cachedData);
    }
    throw Exception('No network and no cached data available.');
  }

  /// POST/PUT request with Queueing strategy (Online-first, then Queue)
  Future<bool> queueableMutation(String endpoint, String action, Map<String, dynamic> data) async {
    if (await isOnline) {
      try {
        if (action == 'POST') {
          await dio.post(endpoint, data: data);
        } else if (action == 'PUT' || action == 'PATCH') {
          await dio.put(endpoint, data: data);
        } else if (action == 'DELETE') {
          await dio.delete(endpoint, data: data);
        }
        return true;
      } catch (e) {
        // If network fails unexpectedly, push to sync queue
        await dbHelper.insertSyncJob(endpoint, action, jsonEncode(data));
        return false; // Indicating it was queued
      }
    } else {
      // Offline: Queue it
      await dbHelper.insertSyncJob(endpoint, action, jsonEncode(data));
      return false; // Indicating it was queued
    }
  }
}
