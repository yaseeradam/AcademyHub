import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

class AdminUsersScreen extends StatefulWidget {
  const AdminUsersScreen({super.key});

  @override
  State<AdminUsersScreen> createState() => _AdminUsersScreenState();
}

class _AdminUsersScreenState extends State<AdminUsersScreen> {
  List<dynamic> _users = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadUsers();
  }

  Future<void> _loadUsers() async {
    final auth = context.read<AuthProvider>();
    setState(() => _loading = true);

    try {
      final data = await auth.apiService.getWithCache('/admin/users');
      if (mounted) {
        setState(() {
          _users = data as List;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        CustomToast.show(context: context, message: 'Failed to load user accounts: $e', type: 'error');
      }
    }
  }

  Future<void> _deleteUser(int id) async {
    final authProvider = context.read<AuthProvider>();
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        title: Text('Delete User Account', style: GoogleFonts.inter(fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
        content: Text('Are you sure you want to permanently delete this user?', style: GoogleFonts.inter(color: AppColors.textSecondary)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Cancel', style: GoogleFonts.inter(color: AppColors.textSecondary)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: Text('Delete', style: GoogleFonts.inter(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final auth = authProvider;
      try {
        await auth.apiService.dio.delete('/admin/users/$id');
        _loadUsers();
        if (mounted) {
          CustomToast.show(context: context, message: 'User account deleted.', type: 'success');
        }
      } catch (e) {
        if (mounted) {
          CustomToast.show(context: context, message: 'Failed to delete account: $e', type: 'error');
        }
      }
    }
  }

  Future<void> _showUserForm({Map<String, dynamic>? user}) async {
    final nameController = TextEditingController(text: user?['name'] ?? '');
    final emailController = TextEditingController(text: user?['email'] ?? '');
    final phoneController = TextEditingController(text: user?['whatsapp_phone'] ?? '');
    final passController = TextEditingController();
    String role = user?['role'] ?? 'teacher';
    bool isActive = user?['is_active'] != false;
    final authProvider = context.read<AuthProvider>();

    final result = await showDialog<bool>(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          backgroundColor: AppColors.surface,
          title: Text(user == null ? 'Create User' : 'Edit User',
              style: GoogleFonts.inter(fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameController,
                  style: GoogleFonts.inter(color: AppColors.textPrimary),
                  decoration: InputDecoration(
                    labelText: 'Full Name',
                    labelStyle: GoogleFonts.inter(color: AppColors.textSecondary),
                  ),
                ),
                TextField(
                  controller: emailController,
                  style: GoogleFonts.inter(color: AppColors.textPrimary),
                  decoration: InputDecoration(
                    labelText: 'Email Address',
                    labelStyle: GoogleFonts.inter(color: AppColors.textSecondary),
                  ),
                ),
                TextField(
                  controller: phoneController,
                  style: GoogleFonts.inter(color: AppColors.textPrimary),
                  decoration: InputDecoration(
                    labelText: 'WhatsApp Phone',
                    labelStyle: GoogleFonts.inter(color: AppColors.textSecondary),
                  ),
                ),
                TextField(
                  controller: passController,
                  obscureText: true,
                  style: GoogleFonts.inter(color: AppColors.textPrimary),
                  decoration: InputDecoration(
                    labelText: user == null ? 'Password' : 'Password (leave blank to keep current)',
                    labelStyle: GoogleFonts.inter(color: AppColors.textSecondary),
                  ),
                ),
                const SizedBox(height: 14),
                // Role Dropdown
                DropdownButtonFormField<String>(
                  initialValue: role,
                  dropdownColor: AppColors.surface,
                  style: GoogleFonts.inter(color: AppColors.textPrimary),
                  decoration: InputDecoration(
                    labelText: 'System Role',
                    labelStyle: GoogleFonts.inter(color: AppColors.textSecondary),
                  ),
                  items: ['admin', 'teacher', 'parent', 'bursar'].map((r) {
                    return DropdownMenuItem<String>(value: r, child: Text(r.toUpperCase(), style: GoogleFonts.inter(fontSize: 13, color: AppColors.textPrimary)));
                  }).toList(),
                  onChanged: (val) {
                    if (val != null) {
                      setDialogState(() => role = val);
                    }
                  },
                ),
                const SizedBox(height: 12),
                CheckboxListTile(
                  title: Text('Account Active?', style: GoogleFonts.inter(color: AppColors.textPrimary)),
                  value: isActive,
                  onChanged: (val) {
                    if (val != null) {
                      setDialogState(() => isActive = val);
                    }
                  },
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: Text('Cancel', style: GoogleFonts.inter(color: AppColors.textSecondary)),
            ),
            ElevatedButton(
              onPressed: () => Navigator.pop(context, true),
              style: ElevatedButton.styleFrom(backgroundColor: context.read<AuthProvider>().tenantPrimaryColor),
              child: Text('Save', style: GoogleFonts.inter(color: Colors.black)),
            ),
          ],
        ),
      ),
    );

    if (result == true) {
      final auth = authProvider;
      final payload = {
        'name': nameController.text.trim(),
        'email': emailController.text.trim(),
        'role': role,
        'is_active': isActive,
        'whatsapp_phone': phoneController.text.trim(),
        if (passController.text.trim().isNotEmpty) 'password': passController.text.trim(),
      };

      try {
        if (user == null) {
          await auth.apiService.dio.post('/admin/users', data: payload);
        } else {
          await auth.apiService.dio.put('/admin/users/${user['id']}', data: payload);
        }
        _loadUsers();
      } catch (e) {
        if (mounted) {
          CustomToast.show(context: context, message: 'Failed to save user: $e', type: 'error');
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('User Management',
            style: GoogleFonts.inter(
                fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
        backgroundColor: AppColors.surface,
        elevation: 0,
        iconTheme: IconThemeData(color: AppColors.textPrimary),
        actions: [
          IconButton(icon: Icon(Icons.add, color: primary), onPressed: () => _showUserForm()),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _users.isEmpty
              ? Center(child: Text('No accounts directory items found.', style: GoogleFonts.inter(color: AppColors.textMuted)))
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _users.length,
                  itemBuilder: (ctx, i) {
                    final u = _users[i];
                    final name = u['name'] ?? '';
                    final email = u['email'] ?? '';
                    final role = u['role'] ?? 'teacher';
                    final active = u['is_active'] != false;

                    return Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.borderLight),
                      ),
                      child: Row(
                        children: [
                          CircleAvatar(
                            backgroundColor: primary.withValues(alpha: 0.12),
                            child: Text(name.isNotEmpty ? name[0].toUpperCase() : '?', style: TextStyle(color: primary, fontWeight: FontWeight.bold)),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(name, style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary)),
                                Text(email, style: GoogleFonts.inter(fontSize: 11, color: AppColors.textSecondary)),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(color: primary.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(4)),
                                      child: Text(role.toString().toUpperCase(), style: GoogleFonts.inter(fontSize: 9, color: primary, fontWeight: FontWeight.bold)),
                                    ),
                                    const SizedBox(width: 6),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(color: active ? AppColors.success.withValues(alpha: 0.12) : AppColors.error.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(4)),
                                      child: Text(active ? 'ACTIVE' : 'SUSPENDED', style: GoogleFonts.inter(fontSize: 9, color: active ? AppColors.success : AppColors.error, fontWeight: FontWeight.bold)),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.edit, size: 18),
                            onPressed: () => _showUserForm(user: u),
                          ),
                          IconButton(
                            icon: Icon(Icons.delete_outline, size: 18, color: AppColors.error),
                            onPressed: () => _deleteUser(u['id'] as int),
                          ),
                        ],
                      ),
                    );
                  },
                ),
    );
  }
}
