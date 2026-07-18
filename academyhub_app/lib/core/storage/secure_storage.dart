import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorage {
  SecureStorage._privateConstructor();
  static final SecureStorage instance = SecureStorage._privateConstructor();

  final _storage = const FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  static const String _keyToken = 'auth_token';
  static const String _keyRole = 'user_role';
  static const String _keySchoolSlug = 'school_slug';
  static const String _keySchoolName = 'school_name';
  static const String _keyStudentId = 'student_id';

  Future<void> write(String key, String value) async {
    await _storage.write(key: key, value: value);
  }

  Future<String?> read(String key) async {
    return await _storage.read(key: key);
  }

  Future<void> delete(String key) async {
    await _storage.delete(key: key);
  }

  // Token
  Future<void> setToken(String token) => write(_keyToken, token);
  Future<String?> getToken() => read(_keyToken);
  Future<void> deleteToken() => delete(_keyToken);

  // Role
  Future<void> setRole(String role) => write(_keyRole, role);
  Future<String?> getRole() => read(_keyRole);
  Future<void> deleteRole() => delete(_keyRole);

  // School Slug
  Future<void> setSchoolSlug(String slug) => write(_keySchoolSlug, slug);
  Future<String?> getSchoolSlug() => read(_keySchoolSlug);
  Future<void> deleteSchoolSlug() => delete(_keySchoolSlug);

  // School Name
  Future<void> setSchoolName(String name) => write(_keySchoolName, name);
  Future<String?> getSchoolName() => read(_keySchoolName);
  Future<void> deleteSchoolName() => delete(_keySchoolName);

  // Student ID
  Future<void> setStudentId(String id) => write(_keyStudentId, id);
  Future<String?> getStudentId() => read(_keyStudentId);
  Future<void> deleteStudentId() => delete(_keyStudentId);

  // User Name
  static const String _keyUserName = 'user_name';
  Future<void> setUserName(String name) => write(_keyUserName, name);
  Future<String?> getUserName() => read(_keyUserName);
  Future<void> deleteUserName() => delete(_keyUserName);

  // Global Clear
  Future<void> clearAll() async {
    await _storage.deleteAll();
  }
}
