import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';

class NotesDownloadTile extends StatefulWidget {
  final Map<String, dynamic> note;
  const NotesDownloadTile({super.key, required this.note});

  @override
  State<NotesDownloadTile> createState() => _NotesDownloadTileState();
}

class _NotesDownloadTileState extends State<NotesDownloadTile> {
  final _db = DatabaseHelper();
  bool _downloading = false;
  double _progress = 0.0;
  String? _localPath;

  @override
  void initState() {
    super.initState();
    _checkLocalFile();
  }

  Future<void> _checkLocalFile() async {
    final path = widget.note['file_path'] as String?;
    if (path != null && path.isNotEmpty) {
      final file = File(path);
      if (await file.exists()) {
        if (mounted) setState(() => _localPath = path);
        return;
      }
    }
    if (mounted) setState(() => _localPath = null);
  }

  Future<void> _downloadFile() async {
    final fileUrl = widget.note['file_url'] as String?;
    if (fileUrl == null || fileUrl.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No downloadable file attached to this note.')),
      );
      return;
    }

    setState(() {
      _downloading = true;
      _progress = 0.0;
    });

    try {
      final auth = context.read<AuthProvider>();
      final appDocDir = await getApplicationDocumentsDirectory();
      final extension = fileUrl.split('.').last.split('?').first;
      final fileName = 'note_${widget.note['id']}.$extension';
      final savePath = '${appDocDir.path}/$fileName';

      await auth.apiService.dio.download(
        fileUrl,
        savePath,
        onReceiveProgress: (received, total) {
          if (total != -1) {
            setState(() {
              _progress = received / total;
            });
          }
        },
      );

      // Persist in DB
      await _db.updateDownloadedNotePath(widget.note['id'] as int, savePath);

      if (mounted) {
        setState(() {
          _downloading = false;
          _localPath = savePath;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Material downloaded successfully! Available offline.'),
            backgroundColor: Color(0xFF10B981),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _downloading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Download failed: ${e.toString()}'),
            backgroundColor: const Color(0xFFEF4444),
          ),
        );
      }
    }
  }

  Future<void> _openFile() async {
    if (_localPath == null) return;
    try {
      final result = await OpenFilex.open(_localPath!);
      if (result.type != ResultType.done && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not open file: ${result.message}')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error opening file: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDownloaded = _localPath != null;
    final auth = context.read<AuthProvider>();
    final primary = auth.tenantPrimaryColor;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x05000000),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: primary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  Icons.menu_book_rounded,
                  color: primary,
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      widget.note['subject_name'] ?? 'General',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: primary,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      widget.note['title'] ?? '',
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          if ((widget.note['content'] as String?)?.isNotEmpty == true) ...[
            const SizedBox(height: 12),
            Text(
              widget.note['content'] as String,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontSize: 13,
                color: Color(0xFF64748B),
                height: 1.4,
              ),
            ),
          ],
          const SizedBox(height: 16),
          // Download progress or action button
          _downloading
              ? Row(
                  children: [
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: LinearProgressIndicator(
                          value: _progress,
                          backgroundColor: const Color(0xFFF1F5F9),
                          valueColor: AlwaysStoppedAnimation<Color>(primary),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      '${(_progress * 100).toStringAsFixed(0)}%',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: primary,
                      ),
                    ),
                  ],
                )
              : SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: isDownloaded ? _openFile : _downloadFile,
                    icon: Icon(
                      isDownloaded
                          ? Icons.folder_open_rounded
                          : Icons.file_download_outlined,
                      size: 18,
                    ),
                    label: Text(isDownloaded ? 'Open Material' : 'Download Note'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: isDownloaded ? const Color(0xFF10B981) : primary,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                ),
        ],
      ),
    );
  }
}
