import 'dart:async';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';
import 'package:academyhub_app/core/database/local_db.dart';
import 'package:academyhub_app/core/network/sync_processor.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  String _userName = 'User';
  String _userRole = 'student';
  String? _schoolName;
  
  // Real Local Database counts
  int _cachedStudentsCount = 0;
  int _pendingSyncCount = 0;
  bool _isSyncing = false;
  String _lastSyncText = 'Synced just now';

  // Settings states
  bool _pushNotifications = true;

  @override
  void initState() {
    super.initState();
    _loadUserData();
    _loadDatabaseStats();
  }

  Future<void> _loadUserData() async {
    final name = await SecureStorage.instance.getUserName();
    final role = await SecureStorage.instance.getRole();
    final school = await SecureStorage.instance.getSchoolName();
    if (mounted) {
      setState(() {
        _userName = name ?? 'User';
        _userRole = role ?? 'Student';
        _schoolName = school ?? 'My School';
      });
    }
  }

  Future<void> _loadDatabaseStats() async {
    final students = await LocalDatabase.instance.getStudents();
    final pending = await LocalDatabase.instance.getQueueCount();
    setState(() {
      _cachedStudentsCount = students.length;
      _pendingSyncCount = pending;
    });
  }

  Future<void> _runSync() async {
    setState(() {
      _isSyncing = true;
    });
    
    // Call the real SyncQueueProcessor to upload pending scores/attendance
    await SyncQueueProcessor.instance.processQueue();
    await _loadDatabaseStats();
    
    setState(() {
      _isSyncing = false;
      _lastSyncText = 'Synced just now';
    });

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('✓ Synchronization process completed.'),
          backgroundColor: AppColors.successGreen,
        ),
      );
    }
  }

  Widget _buildCustomListItem({
    required IconData icon,
    required Color iconColor,
    required Color iconBg,
    required String title,
    Widget? trailing,
    VoidCallback? onTap,
  }) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 14.0),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: iconBg,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: iconColor, size: 20),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Text(
                title,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textPrimary,
                ),
              ),
            ),
            trailing ?? const Icon(Icons.arrow_forward_ios, size: 12, color: AppColors.textDisabled),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    const violetAccent = Color(0xFF7C3AED);

    return Scaffold(
      backgroundColor: AppColors.appBackground,
      appBar: null, // Removed standard generic small AppBar
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 10.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Custom Large Premium Header
              Row(
                children: [
                  GestureDetector(
                    onTap: () => Navigator.pop(context),
                    child: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.04),
                            blurRadius: 10,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textPrimary, size: 18),
                    ),
                  ),
                  const SizedBox(width: 16),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Settings & Sync',
                          style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                        ),
                        SizedBox(height: 2),
                        Text(
                          'Configure preferences and offline database',
                          style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Premium Profile Card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: AppColors.rolePrimary(_userRole.toLowerCase()),
                  borderRadius: BorderRadius.circular(24),
                  border: Border(
                    bottom: BorderSide(
                      color: AppColors.role3DShadowColor(_userRole.toLowerCase()),
                      width: 4,
                    ),
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.role3DShadowColor(_userRole.toLowerCase()).withValues(alpha: 0.35),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(3),
                      decoration: const BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                      ),
                      child: CircleAvatar(
                        radius: 28,
                        backgroundColor: const Color(0xFFF5F3FF),
                        child: Text(
                          _userName.isNotEmpty ? _userName.substring(0, 1).toUpperCase() : 'U',
                          style: const TextStyle(color: violetAccent, fontSize: 22, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            _userName,
                            style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Colors.white),
                          ),
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              '${_userRole.toUpperCase()} · ${_schoolName ?? "AcademyHub"}',
                              style: const TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Settings Items Container
              const Text(
                'ACCOUNT SETTINGS',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 1.0),
              ),
              const SizedBox(height: 8),
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: AppColors.divider),
                ),
                child: Column(
                  children: [
                    _buildCustomListItem(
                      icon: Icons.person_outline,
                      iconColor: const Color(0xFF3B82F6),
                      iconBg: const Color(0xFFEFF6FF),
                      title: 'Profile Info',
                      onTap: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Profile editing coming soon.')),
                        );
                      },
                    ),
                    const Divider(height: 1, indent: 50),
                    _buildCustomListItem(
                      icon: Icons.lock_outline,
                      iconColor: const Color(0xFF10B981),
                      iconBg: const Color(0xFFECFDF5),
                      title: 'Change Password',
                      onTap: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Password change coming soon.')),
                        );
                      },
                    ),
                    const Divider(height: 1, indent: 50),
                    _buildCustomListItem(
                      icon: Icons.notifications_none,
                      iconColor: violetAccent,
                      iconBg: const Color(0xFFF5F3FF),
                      title: 'Push Notifications',
                      trailing: Switch(
                        value: _pushNotifications,
                        activeThumbColor: violetAccent,
                        onChanged: (val) {
                          setState(() {
                            _pushNotifications = val;
                          });
                        },
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Offline Sync Status Highlight Card
              const Text(
                'OFFLINE SYNC STATUS',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 1.0),
              ),
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: violetAccent.withValues(alpha: 0.15)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF5F3FF),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(Icons.cloud_done_outlined, color: violetAccent, size: 24),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _pendingSyncCount == 0 ? 'All Data Synced' : 'Sync Pending',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'Local Database: $_lastSyncText',
                                style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    
                    // Counter stats row
                    Row(
                      children: [
                        Expanded(
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Column(
                              children: [
                                Text(
                                  '$_cachedStudentsCount',
                                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                ),
                                const SizedBox(height: 2),
                                const Text('Students', style: TextStyle(fontSize: 10, color: AppColors.textSecondary)),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Column(
                              children: [
                                const Text(
                                  '12',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                ),
                                const SizedBox(height: 2),
                                const Text('Classes', style: TextStyle(fontSize: 10, color: AppColors.textSecondary)),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Column(
                              children: [
                                Text(
                                  '$_pendingSyncCount',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    color: _pendingSyncCount > 0 ? AppColors.dangerRed : AppColors.textPrimary,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                const Text('Pending', style: TextStyle(fontSize: 10, color: AppColors.textSecondary)),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    
                    // Sync Now Button
                    Container(
                      height: 48,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: violetAccent.withValues(alpha: 0.2),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: violetAccent,
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: _isSyncing ? null : _runSync,
                        icon: _isSyncing
                            ? const SizedBox(
                                width: 16,
                                height: 16,
                                child: CircularProgressIndicator(strokeWidth: 2.0, color: Colors.white),
                              )
                            : const Icon(Icons.sync, size: 18),
                        label: const Text('SYNC DATABASE NOW', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Logout Group
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: AppColors.divider),
                ),
                child: _buildCustomListItem(
                  icon: Icons.logout,
                  iconColor: AppColors.dangerRed,
                  iconBg: const Color(0xFFFEF2F2),
                  title: 'Log Out Account',
                  trailing: const SizedBox.shrink(),
                  onTap: () async {
                    await SecureStorage.instance.clearAll();
                    if (context.mounted) {
                      context.go('/');
                    }
                  },
                ),
              ),
              
              const SizedBox(height: 32),
              const Center(
                child: Text(
                  'App Version 2.4.0',
                  style: TextStyle(color: AppColors.textDisabled, fontSize: 11),
                ),
              ),
              Center(
                child: TextButton(
                  onPressed: () {
                    showDialog(
                      context: context,
                      builder: (_) => AlertDialog(
                        title: const Text('Terms of Service'),
                        content: const Text(
                          'By using AcademyHub, you agree to use the platform responsibly and in accordance with your school\'s policies. All data is handled securely and in compliance with applicable privacy laws.',
                        ),
                        actions: [
                          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Close')),
                        ],
                      ),
                    );
                  },
                  child: const Text(
                    'Terms of Service',
                    style: TextStyle(color: violetAccent, fontSize: 12, decoration: TextDecoration.underline),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
