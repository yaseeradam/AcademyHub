import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';

class DatabaseHelper {
  static final DatabaseHelper _instance = DatabaseHelper._internal();
  factory DatabaseHelper() => _instance;
  DatabaseHelper._internal();

  Database? _database;

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  Future<Database> _initDatabase() async {
    final path = join(await getDatabasesPath(), 'myacademy_offline.db');
    return await openDatabase(path, version: 3, onCreate: _onCreate, onUpgrade: _onUpgrade);
  }

  Future<void> _onCreate(Database db, int version) async {
    await _createAllTables(db);
  }

  Future<void> _onUpgrade(Database db, int oldVersion, int newVersion) async {
    await _createAllTables(db); // CREATE TABLE IF NOT EXISTS is safe to re-run
  }

  Future<void> _createAllTables(Database db) async {
    final statements = [
      '''CREATE TABLE IF NOT EXISTS sync_queue (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entity_type TEXT NOT NULL, action TEXT NOT NULL, payload TEXT NOT NULL,
        created_at TEXT NOT NULL, status TEXT DEFAULT 'pending', retry_count INTEGER DEFAULT 0
      )''',
      '''CREATE TABLE IF NOT EXISTS cache_storage (
        endpoint TEXT PRIMARY KEY, response_data TEXT NOT NULL, updated_at TEXT NOT NULL
      )''',
      '''CREATE TABLE IF NOT EXISTS local_students (
        id INTEGER PRIMARY KEY, first_name TEXT, last_name TEXT,
        admission_number TEXT, class_id INTEGER, section_id INTEGER
      )''',
      '''CREATE TABLE IF NOT EXISTS local_subjects (
        id INTEGER PRIMARY KEY, name TEXT, code TEXT, class_id INTEGER
      )''',
      '''CREATE TABLE IF NOT EXISTS local_scores (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER, subject_id INTEGER, class_id INTEGER,
        term INTEGER, session TEXT,
        ca1 INTEGER DEFAULT 0, ca2 INTEGER DEFAULT 0, exam INTEGER DEFAULT 0,
        total INTEGER DEFAULT 0, grade TEXT, is_dirty INTEGER DEFAULT 0,
        UNIQUE(student_id, subject_id, class_id, term, session)
      )''',
      '''CREATE TABLE IF NOT EXISTS local_attendance (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER, class_id INTEGER, date TEXT,
        term INTEGER, session TEXT, status TEXT DEFAULT 'present', note TEXT,
        is_dirty INTEGER DEFAULT 0,
        UNIQUE(student_id, class_id, date, term, session)
      )''',
      '''CREATE TABLE IF NOT EXISTS local_homework (
        id INTEGER PRIMARY KEY,
        local_id TEXT UNIQUE,
        class_id INTEGER, subject_id INTEGER, teacher_id INTEGER,
        title TEXT, content TEXT, due_date TEXT,
        subject_name TEXT, teacher_name TEXT,
        is_dirty INTEGER DEFAULT 0,
        is_deleted INTEGER DEFAULT 0,
        created_at TEXT
      )''',
      '''CREATE TABLE IF NOT EXISTS local_submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        homework_id INTEGER, student_id INTEGER,
        submission TEXT, submitted_at TEXT,
        grade TEXT, feedback TEXT,
        is_dirty INTEGER DEFAULT 0,
        UNIQUE(homework_id, student_id)
      )''',
      '''CREATE TABLE IF NOT EXISTS local_timetable (
        id INTEGER PRIMARY KEY,
        class_id INTEGER, day_of_week INTEGER,
        starts_at TEXT, ends_at TEXT,
        subject_id INTEGER, subject_name TEXT,
        teacher_id INTEGER, teacher_name TEXT,
        room TEXT
      )''',
      '''CREATE TABLE IF NOT EXISTS local_announcements (
        id INTEGER PRIMARY KEY,
        title TEXT, body TEXT, audience TEXT,
        published_at TEXT, author_name TEXT
      )''',
    ];
    for (final sql in statements) {
      await db.execute(sql);
    }
  }

  // ─── Sync Queue ────────────────────────────────────────────────────────────

  Future<int> insertSyncJob(String entityType, String action, String payload) async {
    final db = await database;
    return db.insert('sync_queue', {
      'entity_type': entityType, 'action': action, 'payload': payload,
      'created_at': DateTime.now().toIso8601String(), 'status': 'pending', 'retry_count': 0,
    });
  }

  Future<List<Map<String, dynamic>>> getPendingSyncJobs() async {
    final db = await database;
    return db.query('sync_queue', where: 'status = ?', whereArgs: ['pending'], orderBy: 'created_at ASC');
  }

  Future<void> markJobCompleted(dynamic id) async {
    final db = await database;
    await db.update('sync_queue', {'status': 'completed'}, where: 'id = ?', whereArgs: [id]);
  }

  Future<void> incrementRetry(int id, int currentCount) async {
    final db = await database;
    await db.update('sync_queue', {'retry_count': currentCount + 1}, where: 'id = ?', whereArgs: [id]);
  }

  // ─── Cache ─────────────────────────────────────────────────────────────────

  Future<void> saveCache(String endpoint, String jsonResponse) async {
    final db = await database;
    await db.insert('cache_storage', {
      'endpoint': endpoint, 'response_data': jsonResponse,
      'updated_at': DateTime.now().toIso8601String(),
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<String?> getCache(String endpoint) async {
    final db = await database;
    final res = await db.query('cache_storage', where: 'endpoint = ?', whereArgs: [endpoint]);
    return res.isNotEmpty ? res.first['response_data'] as String : null;
  }

  // ─── Students ──────────────────────────────────────────────────────────────

  Future<void> upsertStudents(List<Map<String, dynamic>> students) async {
    if (students.isEmpty) return;
    final db = await database;
    final batch = db.batch();
    for (final s in students) {
      batch.insert('local_students', {
        'id': s['id'], 'first_name': s['first_name'], 'last_name': s['last_name'],
        'admission_number': s['admission_number'], 'class_id': s['class_id'], 'section_id': s['section_id'],
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getStudentsByClass(int classId) async {
    final db = await database;
    return db.query('local_students', where: 'class_id = ?', whereArgs: [classId], orderBy: 'first_name');
  }

  Future<List<Map<String, dynamic>>> getAllStudents() async {
    final db = await database;
    return db.query('local_students', orderBy: 'first_name');
  }

  // ─── Subjects ──────────────────────────────────────────────────────────────

  Future<void> upsertSubjects(int classId, List<Map<String, dynamic>> subjects) async {
    final db = await database;
    final batch = db.batch();
    for (final s in subjects) {
      batch.insert('local_subjects', {
        'id': s['id'], 'name': s['name'], 'code': s['code'], 'class_id': classId,
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getSubjectsByClass(int classId) async {
    final db = await database;
    return db.query('local_subjects', where: 'class_id = ?', whereArgs: [classId]);
  }

  // ─── Scores ────────────────────────────────────────────────────────────────

  Future<void> upsertScores(List<Map<String, dynamic>> scores) async {
    final db = await database;
    final batch = db.batch();
    for (final s in scores) {
      batch.insert('local_scores', {
        'student_id': s['student_id'], 'subject_id': s['subject_id'], 'class_id': s['class_id'],
        'term': s['term'], 'session': s['session'],
        'ca1': s['ca1'] ?? 0, 'ca2': s['ca2'] ?? 0, 'exam': s['exam'] ?? 0,
        'total': s['total'] ?? 0, 'grade': s['grade'] ?? '', 'is_dirty': 0,
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  Future<void> saveScoreLocally(int studentId, int subjectId, int classId, int term, String session, int ca1, int ca2, int exam) async {
    final db = await database;
    await db.insert('local_scores', {
      'student_id': studentId, 'subject_id': subjectId, 'class_id': classId,
      'term': term, 'session': session,
      'ca1': ca1, 'ca2': ca2, 'exam': exam, 'total': ca1 + ca2 + exam, 'is_dirty': 1,
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<List<Map<String, dynamic>>> getScores(int classId, int term, String session) async {
    final db = await database;
    return db.query('local_scores', where: 'class_id = ? AND term = ? AND session = ?', whereArgs: [classId, term, session]);
  }

  Future<List<Map<String, dynamic>>> getDirtyScores() async {
    final db = await database;
    return db.query('local_scores', where: 'is_dirty = 1');
  }

  Future<void> markScoresSynced(int classId, int term, String session) async {
    final db = await database;
    await db.update('local_scores', {'is_dirty': 0},
        where: 'class_id = ? AND term = ? AND session = ?', whereArgs: [classId, term, session]);
  }

  // ─── Attendance ────────────────────────────────────────────────────────────

  Future<void> upsertAttendance(int classId, String date, int term, String session, List<Map<String, dynamic>> marks) async {
    final db = await database;
    final batch = db.batch();
    for (final m in marks) {
      batch.insert('local_attendance', {
        'student_id': m['student_id'], 'class_id': classId, 'date': date,
        'term': term, 'session': session, 'status': m['status'] ?? 'present',
        'note': m['note'], 'is_dirty': 0,
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  Future<void> saveAttendanceLocally(int studentId, int classId, String date, int term, String session, String status) async {
    final db = await database;
    await db.insert('local_attendance', {
      'student_id': studentId, 'class_id': classId, 'date': date,
      'term': term, 'session': session, 'status': status, 'is_dirty': 1,
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<List<Map<String, dynamic>>> getAttendance(int classId, String date, int term, String session) async {
    final db = await database;
    return db.query('local_attendance',
        where: 'class_id = ? AND date = ? AND term = ? AND session = ?',
        whereArgs: [classId, date, term, session]);
  }

  Future<List<Map<String, dynamic>>> getDirtyAttendance() async {
    final db = await database;
    return db.query('local_attendance', where: 'is_dirty = 1');
  }

  Future<void> markAttendanceSynced(int classId, String date) async {
    final db = await database;
    await db.update('local_attendance', {'is_dirty': 0},
        where: 'class_id = ? AND date = ?', whereArgs: [classId, date]);
  }

  // ─── Homework ──────────────────────────────────────────────────────────────

  Future<void> upsertHomework(List<Map<String, dynamic>> list) async {
    final db = await database;
    final batch = db.batch();
    for (final h in list) {
      batch.insert('local_homework', {
        'id': h['id'], 'local_id': h['local_id'] ?? 'srv_${h['id']}',
        'class_id': h['class_id'], 'subject_id': h['subject_id'], 'teacher_id': h['teacher_id'],
        'title': h['title'], 'content': h['content'], 'due_date': h['due_date'],
        'subject_name': h['subject']?['name'] ?? h['subject_name'] ?? '',
        'teacher_name': h['teacher']?['name'] ?? h['teacher_name'] ?? '',
        'is_dirty': 0, 'is_deleted': 0,
        'created_at': h['created_at'] ?? DateTime.now().toIso8601String(),
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  /// Save a new homework created offline — uses a local_id (UUID-like) until synced
  Future<String> saveHomeworkLocally({
    required int classId, required int subjectId, required int teacherId,
    required String title, required String content, required String dueDate,
    required String subjectName,
  }) async {
    final db      = await database;
    final localId = 'local_${DateTime.now().millisecondsSinceEpoch}';
    await db.insert('local_homework', {
      'id': null, 'local_id': localId,
      'class_id': classId, 'subject_id': subjectId, 'teacher_id': teacherId,
      'title': title, 'content': content, 'due_date': dueDate,
      'subject_name': subjectName, 'teacher_name': '',
      'is_dirty': 1, 'is_deleted': 0,
      'created_at': DateTime.now().toIso8601String(),
    });
    return localId;
  }

  Future<void> markHomeworkSynced(String localId, int serverId) async {
    final db = await database;
    await db.update('local_homework', {'id': serverId, 'is_dirty': 0},
        where: 'local_id = ?', whereArgs: [localId]);
  }

  Future<List<Map<String, dynamic>>> getHomeworkByClass(int classId) async {
    final db = await database;
    return db.query('local_homework',
        where: 'class_id = ? AND is_deleted = 0', whereArgs: [classId], orderBy: 'due_date DESC');
  }

  Future<List<Map<String, dynamic>>> getAllHomework() async {
    final db = await database;
    return db.query('local_homework', where: 'is_deleted = 0', orderBy: 'due_date DESC');
  }

  Future<List<Map<String, dynamic>>> getDirtyHomework() async {
    final db = await database;
    return db.query('local_homework', where: 'is_dirty = 1 AND is_deleted = 0');
  }

  Future<void> deleteHomeworkLocally(int id) async {
    final db = await database;
    await db.update('local_homework', {'is_deleted': 1, 'is_dirty': 1},
        where: 'id = ?', whereArgs: [id]);
  }

  // ─── Homework Submissions ──────────────────────────────────────────────────

  Future<void> upsertSubmissions(List<Map<String, dynamic>> list) async {
    final db = await database;
    final batch = db.batch();
    for (final s in list) {
      batch.insert('local_submissions', {
        'homework_id': s['homework_id'], 'student_id': s['student_id'],
        'submission': s['submission'], 'submitted_at': s['submitted_at'],
        'grade': s['grade'], 'feedback': s['feedback'], 'is_dirty': 0,
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  Future<void> saveSubmissionLocally(int homeworkId, int studentId, String submission) async {
    final db = await database;
    await db.insert('local_submissions', {
      'homework_id': homeworkId, 'student_id': studentId,
      'submission': submission, 'submitted_at': DateTime.now().toIso8601String(),
      'is_dirty': 1,
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<List<Map<String, dynamic>>> getDirtySubmissions() async {
    final db = await database;
    return db.query('local_submissions', where: 'is_dirty = 1');
  }

  Future<void> markSubmissionSynced(int homeworkId, int studentId) async {
    final db = await database;
    await db.update('local_submissions', {'is_dirty': 0},
        where: 'homework_id = ? AND student_id = ?', whereArgs: [homeworkId, studentId]);
  }

  Future<Map<String, dynamic>?> getSubmission(int homeworkId, int studentId) async {
    final db = await database;
    final res = await db.query('local_submissions',
        where: 'homework_id = ? AND student_id = ?', whereArgs: [homeworkId, studentId]);
    return res.isNotEmpty ? res.first : null;
  }

  // ─── Timetable ─────────────────────────────────────────────────────────────

  Future<void> upsertTimetable(List<Map<String, dynamic>> entries) async {
    final db = await database;
    final batch = db.batch();
    for (final e in entries) {
      batch.insert('local_timetable', {
        'id': e['id'], 'class_id': e['class_id'], 'day_of_week': e['day_of_week'],
        'starts_at': e['starts_at'], 'ends_at': e['ends_at'],
        'subject_id': e['subject_id'],
        'subject_name': e['subject']?['name'] ?? e['subject_name'] ?? '',
        'teacher_id': e['teacher_id'],
        'teacher_name': e['teacher']?['name'] ?? e['teacher_name'] ?? '',
        'room': e['room'] ?? '',
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getTimetable({int? dayOfWeek}) async {
    final db = await database;
    if (dayOfWeek != null) {
      return db.query('local_timetable',
          where: 'day_of_week = ?', whereArgs: [dayOfWeek], orderBy: 'starts_at');
    }
    return db.query('local_timetable', orderBy: 'day_of_week, starts_at');
  }

  // ─── Announcements ─────────────────────────────────────────────────────────

  Future<void> upsertAnnouncements(List<Map<String, dynamic>> list) async {
    final db = await database;
    final batch = db.batch();
    for (final a in list) {
      batch.insert('local_announcements', {
        'id': a['id'], 'title': a['title'], 'body': a['body'],
        'audience': a['audience'], 'published_at': a['published_at'],
        'author_name': a['author']?['name'] ?? a['author_name'] ?? '',
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getAnnouncements() async {
    final db = await database;
    return db.query('local_announcements', orderBy: 'published_at DESC');
  }

  // ─── Pending count (for sync dot) ──────────────────────────────────────────

  Future<int> getTotalDirtyCount() async {
    final a = await getDirtyAttendance();
    final s = await getDirtyScores();
    final h = await getDirtyHomework();
    final sub = await getDirtySubmissions();
    return a.length + s.length + h.length + sub.length;
  }
}
