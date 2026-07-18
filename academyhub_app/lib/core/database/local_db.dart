import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

class LocalDatabase {
  LocalDatabase._privateConstructor();
  static final LocalDatabase instance = LocalDatabase._privateConstructor();

  static Database? _database;

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  Future<Database> _initDatabase() async {
    final documentsDirectory = await getApplicationDocumentsDirectory();
    final path = p.join(documentsDirectory.path, 'academyhub.db');
    return await openDatabase(
      path,
      version: 1,
      onCreate: _onCreate,
    );
  }

  Future<void> _onCreate(Database db, int version) async {
    // Create sync queue table
    await db.execute('''
      CREATE TABLE sync_queue (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        action_type TEXT NOT NULL,
        endpoint TEXT NOT NULL,
        payload TEXT NOT NULL,
        created_at TEXT NOT NULL
      )
    ''');

    // Create cached students table
    await db.execute('''
      CREATE TABLE cached_students (
        id INTEGER PRIMARY KEY,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        admission_number TEXT NOT NULL,
        class_id INTEGER NOT NULL,
        class_name TEXT NOT NULL,
        status TEXT NOT NULL
      )
    ''');
  }

  // --- Cached Students Helpers ---
  Future<void> insertStudent(Map<String, dynamic> student) async {
    final db = await database;
    await db.insert(
      'cached_students',
      student,
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<List<Map<String, dynamic>>> getStudents() async {
    final db = await database;
    return await db.query('cached_students', orderBy: 'last_name ASC');
  }

  Future<void> clearStudents() async {
    final db = await database;
    await db.delete('cached_students');
  }

  // --- Sync Queue Helpers ---
  Future<void> addToQueue(String actionType, String endpoint, String jsonPayload) async {
    final db = await database;
    await db.insert(
      'sync_queue',
      {
        'action_type': actionType,
        'endpoint': endpoint,
        'payload': jsonPayload,
        'created_at': DateTime.now().toIso8601String(),
      },
    );
  }

  Future<List<Map<String, dynamic>>> getQueue() async {
    final db = await database;
    return await db.query('sync_queue', orderBy: 'id ASC');
  }

  Future<void> deleteFromQueue(int id) async {
    final db = await database;
    await db.delete('sync_queue', where: 'id = ?', whereArgs: [id]);
  }

  Future<int> getQueueCount() async {
    final db = await database;
    final result = await db.rawQuery('SELECT COUNT(*) FROM sync_queue');
    return Sqflite.firstIntValue(result) ?? 0;
  }
}
