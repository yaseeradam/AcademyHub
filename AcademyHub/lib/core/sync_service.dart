import 'dart:async';
import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'database_helper.dart';

enum SyncStatus { synced, syncing, offline, error }

class SyncProgress {
  final String message;
  final double progress;
  SyncProgress(this.message, this.progress);
}

class SyncService {
  final Dio dio;
  final DatabaseHelper _db = DatabaseHelper();
  bool _isSyncing = false;
  StreamSubscription<List<ConnectivityResult>>? _connectivitySub;

  final _statusController      = StreamController<SyncStatus>.broadcast();
  final _pendingCountController = StreamController<int>.broadcast();
  final _progressController    = StreamController<SyncProgress>.broadcast();

  Stream<SyncStatus>   get syncStatusStream   => _statusController.stream;
  Stream<int>          get pendingCountStream  => _pendingCountController.stream;
  Stream<SyncProgress> get progressStream      => _progressController.stream;

  SyncService(this.dio) {
    _emitPendingCount();
    _connectivitySub = Connectivity().onConnectivityChanged.listen((results) {
      if (!results.contains(ConnectivityResult.none)) {
        syncNow();
      } else {
        _statusController.add(SyncStatus.offline);
      }
    });
  }

  void dispose() {
    _connectivitySub?.cancel();
    _statusController.close();
    _pendingCountController.close();
    _progressController.close();
  }

  // ─── Initial full sync ─────────────────────────────────────────────────────

  Future<void> initialSync(String role) async {
    _emit('Starting sync...', 0.0);
    try {
      _emit('Fetching active term...', 0.05);
      int term = 1;
      String session = '';
      try {
        final termRes = await dio.get('/term');
        term    = termRes.data['term'] ?? 1;
        session = termRes.data['session'] ?? '';
      } catch (_) {}
      final prefs = await SharedPreferences.getInstance();
      await prefs.setInt('active_term', term);
      await prefs.setString('active_session', session);

      _emit('Downloading announcements...', 0.1);
      await _fetchAnnouncements();

      if (role == 'teacher' || role == 'admin') {
        await _syncTeacherData(term, session);
      } else if (role == 'student') {
        await _syncStudentData(term, session);
      } else if (role == 'parent') {
        await _syncParentData(term, session);
      }

      _emit('All done!', 1.0);
      await prefs.setBool('initial_sync_done', true);
    } catch (e) {
      _emit('Sync failed: $e', 1.0);
      rethrow;
    }
  }

  Future<void> _syncTeacherData(int term, String session) async {
    _emit('Downloading your classes...', 0.15);
    final classesRes = await dio.get('/teacher/classes');
    final classes    = (classesRes.data['data'] as List).cast<Map<String, dynamic>>();
    final total      = classes.length;

    for (int i = 0; i < total; i++) {
      final cls      = classes[i];
      final classId  = cls['id'] as int;
      final progress = 0.15 + (0.55 * (i / (total == 0 ? 1 : total)));

      _emit('Syncing ${cls['name']}...', progress);

      await _tryFetch(() async {
        final r = await dio.get('/teacher/classes/$classId/students');
        await _db.upsertStudents((r.data['data'] as List).cast<Map<String, dynamic>>());
      });
      await _tryFetch(() async {
        final r = await dio.get('/teacher/classes/$classId/subjects');
        await _db.upsertSubjects(classId, (r.data['data'] as List).cast<Map<String, dynamic>>());
      });
      await _tryFetch(() async {
        final r = await dio.get('/teacher/classes/$classId/scores?term=$term&session=$session');
        await _db.upsertScores((r.data['data'] as List).cast<Map<String, dynamic>>());
      });
      await _tryFetch(() async {
        final today = DateTime.now().toIso8601String().substring(0, 10);
        final r     = await dio.get('/teacher/classes/$classId/attendance?date=$today&term=$term&session=$session');
        final sheet = r.data['data'];
        if (sheet != null) {
          final marks = (sheet['marks'] as List?)?.cast<Map<String, dynamic>>() ?? [];
          await _db.upsertAttendance(classId, today, term, session, marks);
        }
      });
    }

    _emit('Downloading homework...', 0.72);
    await _fetchHomework();

    _emit('Downloading timetable...', 0.85);
    await _fetchTimetable();
  }

  Future<void> _syncStudentData(int term, String session) async {
    _emit('Downloading student stats...', 0.15);
    await _tryFetch(() async {
      final r = await dio.get('/student/dashboard');
      final data = r.data['data'] ?? r.data;
      final prefs = await SharedPreferences.getInstance();
      final admissionNo = prefs.getString('student_admission_number') ?? '';
      if (admissionNo.isNotEmpty) {
        await _db.saveStudentStats(admissionNo, data);
      }
    });

    _emit('Downloading academic results...', 0.3);
    await _tryFetch(() async {
      final r = await dio.get('/student/results');
      final list = (r.data['data'] as List?)?.cast<Map<String, dynamic>>() ?? [];
      await _db.upsertScores(list);
    });

    _emit('Downloading attendance...', 0.4);
    await _tryFetch(() async {
      final r = await dio.get('/student/attendance');
      final list = (r.data['data'] as List?)?.cast<Map<String, dynamic>>() ?? [];
      await _db.upsertAttendance(0, '', term, session, list);
    });

    _emit('Downloading live exams...', 0.55);
    await _tryFetch(() async {
      final r = await dio.get('/student/cbt');
      final exams = (r.data['data'] as List?)?.cast<Map<String, dynamic>>() ?? [];
      await _db.upsertCbtExams(exams);
      for (final e in exams) {
        final examId = e['id'] as int;
        final detailRes = await dio.get('/student/cbt/$examId');
        final questions = (detailRes.data['questions'] as List?)?.cast<Map<String, dynamic>>() ?? [];
        await _db.upsertCbtQuestions(examId, questions);
      }
    });

    _emit('Downloading learning materials...', 0.7);
    await _tryFetch(() async {
      final r = await dio.get('/student/elearning');
      final list = (r.data['data'] as List?)?.cast<Map<String, dynamic>>() ?? [];
      await _db.upsertELearningNotes(list);
    });

    _emit('Downloading assignments feed...', 0.8);
    await _fetchHomework();

    _emit('Downloading announcements...', 0.85);
    await _fetchAnnouncements();

    _emit('Downloading timetable...', 0.9);
    await _fetchTimetable();

    _emit('Downloading notifications feed...', 0.95);
    await _tryFetch(() async {
      final r = await dio.get('/student/notifications');
      final list = (r.data['data'] as List?)?.cast<Map<String, dynamic>>() ?? [];
      await _db.upsertNotifications(list);
    });
  }

  Future<void> _syncParentData(int term, String session) async {
    _emit('Downloading children data...', 0.2);
    await _tryFetch(() async {
      final r        = await dio.get('/students');
      final students = (r.data['data'] as List?)?.cast<Map<String, dynamic>>() ?? [];
      await _db.upsertStudents(students);
    });

    _emit('Downloading fee records...', 0.4);
    await _tryFetch(() async {
      await dio.get('/billing');
      // Cache is saved by ApiService; billing is read-only so no local table needed
      // The raw response is already cached via cache_storage by getWithCache
    });

    _emit('Downloading homework...', 0.6);
    await _fetchHomework();

    _emit('Downloading timetable...', 0.75);
    await _fetchTimetable();

    _emit('Downloading announcements...', 0.9);
    await _fetchAnnouncements();
  }

  Future<void> _fetchHomework() async {
    await _tryFetch(() async {
      final r    = await dio.get('/homework');
      final list = (r.data['data'] as List).cast<Map<String, dynamic>>();
      await _db.upsertHomework(list);
    });
  }

  Future<void> _fetchTimetable() async {
    await _tryFetch(() async {
      final r    = await dio.get('/timetable');
      final list = (r.data['data'] as List).cast<Map<String, dynamic>>();
      await _db.upsertTimetable(list);
    });
  }

  Future<void> _fetchAnnouncements() async {
    await _tryFetch(() async {
      final r    = await dio.get('/announcements');
      final list = (r.data['data'] as List).cast<Map<String, dynamic>>();
      await _db.upsertAnnouncements(list);
    });
  }

  // ─── Background refresh ────────────────────────────────────────────────────

  Future<void> backgroundRefresh(String role) async {
    try {
      final prefs   = await SharedPreferences.getInstance();
      final term    = prefs.getInt('active_term') ?? 1;
      final session = prefs.getString('active_session') ?? '';
      if (role == 'teacher' || role == 'admin') {
        await _syncTeacherData(term, session);
      } else if (role == 'student') {
        await _syncStudentData(term, session);
      } else if (role == 'parent') {
        await _syncParentData(term, session);
      }
    } catch (_) {}
  }

  // ─── Upload dirty data ─────────────────────────────────────────────────────

  Future<void> syncNow() async {
    if (_isSyncing) return;
    _isSyncing = true;
    _statusController.add(SyncStatus.syncing);
    try {
      await _uploadAttendance();
      await _uploadScores();
      await _uploadHomework();
      await _uploadSubmissions();
      await _uploadCbtAttempts();
      await _uploadNotifications();
      await _emitPendingCount();
      _statusController.add(SyncStatus.synced);
    } catch (_) {
      _statusController.add(SyncStatus.error);
    } finally {
      _isSyncing = false;
    }
  }

  Future<void> _uploadAttendance() async {
    final dirty = await _db.getDirtyAttendance();
    if (dirty.isEmpty) return;
    final Map<String, List<Map<String, dynamic>>> grouped = {};
    for (final row in dirty) {
      final key = '${row['class_id']}_${row['date']}_${row['term']}_${row['session']}';
      grouped.putIfAbsent(key, () => []).add(row);
    }
    for (final rows in grouped.values) {
      final first = rows.first;
      final res   = await dio.post('/teacher/attendance', data: {
        'class_id': first['class_id'], 'date': first['date'],
        'term': first['term'], 'session': first['session'],
        'marks': rows.map((r) => {'student_id': r['student_id'], 'status': r['status'], 'note': r['note']}).toList(),
      });
      if ((res.statusCode ?? 0) < 300) {
        await _db.markAttendanceSynced(first['class_id'] as int, first['date'] as String);
      }
    }
  }

  Future<void> _uploadScores() async {
    final dirty = await _db.getDirtyScores();
    if (dirty.isEmpty) return;
    final res = await dio.post('/teacher/scores', data: {
      'scores': dirty.map((s) => {
        'student_id': s['student_id'], 'subject_id': s['subject_id'],
        'class_id': s['class_id'], 'term': s['term'], 'session': s['session'],
        'ca1': s['ca1'], 'ca2': s['ca2'], 'exam': s['exam'],
      }).toList(),
    });
    if ((res.statusCode ?? 0) < 300) {
      final classIds = dirty.map((s) => s['class_id'] as int).toSet();
      for (final classId in classIds) {
        final row = dirty.firstWhere((s) => s['class_id'] == classId);
        await _db.markScoresSynced(classId, row['term'] as int, row['session'] as String);
      }
    }
  }

  Future<void> _uploadHomework() async {
    final dirty = await _db.getDirtyHomework();
    for (final h in dirty) {
      try {
        if (h['is_deleted'] == 1 && h['id'] != null) {
          await dio.delete('/homework/${h['id']}');
        } else {
          final res = await dio.post('/homework', data: {
            'class_id': h['class_id'], 'subject_id': h['subject_id'],
            'title': h['title'], 'content': h['content'], 'due_date': h['due_date'],
          });
          if ((res.statusCode ?? 0) < 300) {
            final serverId = res.data['data']['id'] as int;
            await _db.markHomeworkSynced(h['local_id'] as String, serverId);
          }
        }
      } catch (_) {}
    }
  }

  Future<void> _uploadSubmissions() async {
    final dirty = await _db.getDirtySubmissions();
    for (final s in dirty) {
      try {
        final res = await dio.post('/homework/${s['homework_id']}/submit', data: {
          'submission': s['submission'],
        });
        if ((res.statusCode ?? 0) < 300) {
          await _db.markSubmissionSynced(s['homework_id'] as int, s['student_id'] as int);
        }
      } catch (_) {}
    }
  }

  Future<void> _uploadCbtAttempts() async {
    final dirty = await _db.getDirtyCbtAttempts();
    for (final attempt in dirty) {
      try {
        final localId = attempt['id'] as int;
        final res = await dio.post('/student/cbt/submit', data: {
          'exam_id': attempt['exam_id'],
          'started_at': attempt['started_at'],
          'submitted_at': attempt['submitted_at'],
          'answers': attempt['answers'],
          'score': attempt['score'],
        });
        if ((res.statusCode ?? 0) < 300) {
          final serverAttemptId = res.data['data']?['id'] ?? res.data['id'] ?? 0;
          await _db.markCbtAttemptSynced(localId, serverAttemptId);
        }
      } catch (_) {}
    }
  }

  Future<void> _uploadNotifications() async {
    final dirty = await _db.getDirtyNotifications();
    for (final n in dirty) {
      try {
        final id = n['id'] as int;
        final res = await dio.post('/student/notifications/$id/read');
        if ((res.statusCode ?? 0) < 300) {
          await _db.markNotificationSynced(id);
        }
      } catch (_) {}
    }
  }

  // ─── Helpers ───────────────────────────────────────────────────────────────

  Future<void> _emitPendingCount() async {
    final count = await _db.getTotalDirtyCount();
    _pendingCountController.add(count);
  }

  Future<void> notifyDirty() async {
    await _emitPendingCount();
    _statusController.add(SyncStatus.offline);
  }

  Future<void> _tryFetch(Future<void> Function() fn) async {
    try { await fn(); } catch (_) {}
  }

  void _emit(String message, double progress) {
    _progressController.add(SyncProgress(message, progress));
  }

  // Legacy batch queue
  Future<void> syncPendingJobs() async {
    final jobs = await _db.getPendingSyncJobs();
    if (jobs.isEmpty) return;
    try {
      final res = await dio.post('/sync', data: {
        'mutations': jobs.map((j) => {
          'id': j['id'], 'endpoint': j['entity_type'],
          'action': j['action'], 'payload': jsonDecode(j['payload'] as String),
        }).toList(),
      });
      if (res.statusCode == 200) {
        for (final id in (res.data['success_ids'] as List? ?? [])) {
          await _db.markJobCompleted(id);
        }
      }
    } catch (_) {}
  }
}
