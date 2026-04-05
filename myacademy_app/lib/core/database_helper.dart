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
    String path = join(await getDatabasesPath(), 'myacademy_offline.db');
    return await openDatabase(
      path,
      version: 1,
      onCreate: _onCreate,
    );
  }

  Future<void> _onCreate(Database db, int version) async {
    // Sync Queue Table - Stores pending mutations for Laravel
    await db.execute('''
      CREATE TABLE sync_queue (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entity_type TEXT NOT NULL,
        action TEXT NOT NULL,
        payload TEXT NOT NULL,
        created_at TEXT NOT NULL,
        status TEXT DEFAULT 'pending',
        retry_count INTEGER DEFAULT 0
      )
    ''');

    // Local caches for offline viewing
    // We will cache GET requests (e.g., student lists, report cards, billing)
    await db.execute('''
      CREATE TABLE cache_storage (
        endpoint TEXT PRIMARY KEY,
        response_data TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');
  }

  // --- Sync Queue Methods ---
  
  Future<int> insertSyncJob(String entityType, String action, String payload) async {
    final db = await database;
    return await db.insert('sync_queue', {
      'entity_type': entityType,
      'action': action,
      'payload': payload,
      'created_at': DateTime.now().toIso8601String(),
      'status': 'pending',
      'retry_count': 0,
    });
  }

  Future<List<Map<String, dynamic>>> getPendingSyncJobs() async {
    final db = await database;
    return await db.query(
      'sync_queue',
      where: 'status = ?',
      whereArgs: ['pending'],
      orderBy: 'created_at ASC',
    );
  }

  Future<int> markJobCompleted(int id) async {
    final db = await database;
    return await db.update(
      'sync_queue',
      {'status': 'completed'},
      where: 'id = ?',
      whereArgs: [id],
    );
  }

  Future<int> incrementRetry(int id, int currentCount) async {
    final db = await database;
    return await db.update(
      'sync_queue',
      {'retry_count': currentCount + 1},
      where: 'id = ?',
      whereArgs: [id],
    );
  }

  // --- Cache Storage Methods ---

  Future<void> saveCache(String endpoint, String jsonResponse) async {
    final db = await database;
    await db.insert('cache_storage', {
      'endpoint': endpoint,
      'response_data': jsonResponse,
      'updated_at': DateTime.now().toIso8601String(),
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<String?> getCache(String endpoint) async {
    final db = await database;
    final res = await db.query(
      'cache_storage',
      where: 'endpoint = ?',
      whereArgs: [endpoint],
    );
    if (res.isNotEmpty) {
      return res.first['response_data'] as String;
    }
    return null;
  }
}
