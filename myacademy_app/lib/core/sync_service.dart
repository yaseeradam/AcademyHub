import 'dart:async';
import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'database_helper.dart';

class SyncService {
  final Dio dio;
  final DatabaseHelper dbHelper = DatabaseHelper();
  bool _isSyncing = false;
  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;

  // Stream to notify UI of sync status
  final _syncStatusController = StreamController<SyncStatus>.broadcast();
  Stream<SyncStatus> get syncStatusStream => _syncStatusController.stream;

  SyncService(this.dio) {
    _initTimeMonitor();
  }

  void _initTimeMonitor() {
    _connectivitySubscription = Connectivity().onConnectivityChanged.listen((List<ConnectivityResult> results) {
      if (!results.contains(ConnectivityResult.none)) {
        // Network came back, attempt sync
        syncPendingJobs();
      } else {
        _syncStatusController.add(SyncStatus.offline);
      }
    });
  }

  void dispose() {
    _connectivitySubscription?.cancel();
    _syncStatusController.close();
  }

  Future<void> syncPendingJobs() async {
    if (_isSyncing) return;

    final jobs = await dbHelper.getPendingSyncJobs();
    if (jobs.isEmpty) {
      _syncStatusController.add(SyncStatus.synced);
      return;
    }

    _isSyncing = true;
    _syncStatusController.add(SyncStatus.syncing);

    // Group jobs to send to Laravel batch endpoint if available, 
    // or send them sequentially.
    // We send payload as a single batch to Phase 7 sync endpoint.
    
    // Convert DB rows into a JSON list
    List<Map<String, dynamic>> payloadBatch = jobs.map((job) {
      return {
        'id': job['id'],
        'endpoint': job['entity_type'],
        'action': job['action'],
        'payload': jsonDecode(job['payload'] as String),
      };
    }).toList();

    try {
      final response = await dio.post('/sync', data: {'mutations': payloadBatch});
      
      if (response.statusCode == 200 || response.statusCode == 201) {
        // Determine which were successful, mark them as completed
        final successes = response.data['success_ids'] as List<dynamic>? ?? [];
        for (var id in successes) {
          await dbHelper.markJobCompleted(id);
        }
        _syncStatusController.add(SyncStatus.synced);
      } else {
        _incrementRetries(jobs);
        _syncStatusController.add(SyncStatus.error);
      }
    } catch (e) {
      _incrementRetries(jobs);
      _syncStatusController.add(SyncStatus.offline);
    } finally {
      _isSyncing = false;
    }
  }

  void _incrementRetries(List<Map<String, dynamic>> jobs) async {
    for (var job in jobs) {
      await dbHelper.incrementRetry(job['id'] as int, job['retry_count'] as int);
    }
  }
}

enum SyncStatus {
  synced,
  syncing,
  offline,
  error
}
