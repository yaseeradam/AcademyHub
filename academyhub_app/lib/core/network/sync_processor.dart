import 'dart:async';
import 'dart:convert';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:academyhub_app/core/database/local_db.dart';
import 'package:academyhub_app/core/network/api_client.dart';

class SyncQueueProcessor {
  SyncQueueProcessor._privateConstructor();
  static final SyncQueueProcessor instance = SyncQueueProcessor._privateConstructor();

  bool _isProcessing = false;
  StreamSubscription<List<ConnectivityResult>>? _subscription;

  // Callback to notify UI shell of sync status changes
  Function(String status)? onStatusChanged; // 'synced', 'syncing', 'pending'

  void startListening() {
    _subscription?.cancel();
    _subscription = Connectivity().onConnectivityChanged.listen((results) {
      final isOnline = !results.contains(ConnectivityResult.none);
      if (isOnline) {
        processQueue();
      } else {
        onStatusChanged?.call('pending');
      }
    });
  }

  void stopListening() {
    _subscription?.cancel();
    _subscription = null;
  }

  Future<void> processQueue() async {
    if (_isProcessing) return;
    
    final count = await LocalDatabase.instance.getQueueCount();
    if (count == 0) {
      onStatusChanged?.call('synced');
      return;
    }

    _isProcessing = true;
    onStatusChanged?.call('syncing');

    try {
      final queue = await LocalDatabase.instance.getQueue();
      for (var action in queue) {
        final int id = action['id'];
        final String endpoint = action['endpoint'];
        final String payloadStr = action['payload'];

        final Map<String, dynamic> payload = jsonDecode(payloadStr);

        // Attempt HTTP sync
        final response = await apiClient.dio.post(endpoint, data: payload);
        if (response.statusCode == 200 || response.statusCode == 201) {
          // Success: Remove from queue
          await LocalDatabase.instance.deleteFromQueue(id);
        } else {
          // Non-success status code, stop processing further items in case of server side issues
          onStatusChanged?.call('pending');
          _isProcessing = false;
          return;
        }
      }

      final remainingCount = await LocalDatabase.instance.getQueueCount();
      if (remainingCount == 0) {
        onStatusChanged?.call('synced');
      } else {
        onStatusChanged?.call('pending');
      }
    } catch (e) {
      // Network timeout or exception: stop processing and keep in queue
      onStatusChanged?.call('pending');
    } finally {
      _isProcessing = false;
    }
  }
}
