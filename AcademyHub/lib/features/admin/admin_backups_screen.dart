import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

class AdminBackupsScreen extends StatefulWidget {
  const AdminBackupsScreen({super.key});

  @override
  State<AdminBackupsScreen> createState() => _AdminBackupsScreenState();
}

class _AdminBackupsScreenState extends State<AdminBackupsScreen> {
  List<dynamic> _backups = [];
  bool _loading = true;
  bool _triggering = false;

  @override
  void initState() {
    super.initState();
    _loadBackups();
  }

  Future<void> _loadBackups() async {
    final auth = context.read<AuthProvider>();
    setState(() => _loading = true);

    try {
      final data = await auth.apiService.getWithCache('/admin/backups');
      if (mounted) {
        setState(() {
          _backups = data as List;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        CustomToast.show(context: context, message: 'Failed to retrieve database backups: $e', type: 'error');
      }
    }
  }

  Future<void> _triggerBackup() async {
    final auth = context.read<AuthProvider>();
    setState(() => _triggering = true);

    try {
      await auth.apiService.dio.post('/admin/backups');
      if (mounted) {
        setState(() => _triggering = false);
        CustomToast.show(context: context, message: 'Backup SQL dump triggered successfully!', type: 'success');
        _loadBackups();
      }
    } catch (e) {
      if (mounted) {
        setState(() => _triggering = false);
        CustomToast.show(context: context, message: 'Backup failed: $e', type: 'error');
      }
    }
  }

  Future<void> _downloadBackup(String filename) async {
    final auth = context.read<AuthProvider>();
    final urlString = '${auth.tenantApiUrl.replaceAll('/api', '')}/api/admin/backups/download/$filename';

    try {
      final uri = Uri.parse(urlString);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        throw 'Could not launch download URL.';
      }
    } catch (e) {
      if (mounted) {
        CustomToast.show(context: context, message: 'Error initiating download: $e', type: 'error');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Database Backups',
            style: GoogleFonts.spaceGrotesk(
                fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
        backgroundColor: AppColors.surface,
        elevation: 0,
        iconTheme: IconThemeData(color: AppColors.textPrimary),
        actions: [
          IconButton(icon: Icon(Icons.refresh, color: AppColors.textPrimary), onPressed: _loadBackups),
        ],
      ),
      body: Column(
        children: [
          if (_triggering) const LinearProgressIndicator(color: AppColors.success),
          Container(
            padding: const EdgeInsets.all(16),
            color: AppColors.surface,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('System Backups', style: GoogleFonts.spaceGrotesk(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                      Text('Create snapshot SQL databases archives.', style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary)),
                    ],
                  ),
                ),
                ElevatedButton.icon(
                  onPressed: _triggering ? null : _triggerBackup,
                  style: ElevatedButton.styleFrom(backgroundColor: AppColors.success, foregroundColor: Colors.white),
                  icon: const Icon(Icons.backup_outlined, size: 16),
                  label: Text('Trigger Backup', style: GoogleFonts.spaceGrotesk(fontSize: 12, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _backups.isEmpty
                    ? Center(child: Text('No backup archives recorded.', style: GoogleFonts.spaceGrotesk(color: AppColors.textMuted)))
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _backups.length,
                        itemBuilder: (ctx, i) {
                          final b = _backups[i];
                          final filename = b['filename'] ?? '';
                          final sizeKB = ((b['size'] ?? 0) / 1024).toStringAsFixed(1);
                          final created = b['created'] ?? '';

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
                                const Icon(Icons.storage, color: AppColors.info),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(filename, style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary), maxLines: 1, overflow: TextOverflow.ellipsis),
                                      Text('Size: $sizeKB KB • $created', style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary)),
                                    ],
                                  ),
                                ),
                                IconButton(
                                  icon: Icon(Icons.file_download, color: primary),
                                  onPressed: () => _downloadBackup(filename),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }
}
