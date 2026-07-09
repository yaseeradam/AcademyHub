import 'dart:io';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';
import 'notes_comments_sheet.dart';

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
      CustomToast.show(
        context: context,
        message: 'No downloadable file attached to this material.',
        type: 'warning',
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
        CustomToast.show(
          context: context,
          message: 'Material downloaded! Available offline.',
          type: 'success',
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _downloading = false);
        CustomToast.show(
          context: context,
          message: 'Download failed: ${e.toString()}',
          type: 'error',
        );
      }
    }
  }

  Future<void> _openFile() async {
    if (_localPath == null) return;
    try {
      final result = await OpenFilex.open(_localPath!);
      if (result.type != ResultType.done && mounted) {
        CustomToast.show(
          context: context,
          message: 'Could not open file: ${result.message}',
          type: 'error',
        );
      }
    } catch (e) {
      if (mounted) {
        CustomToast.show(
          context: context,
          message: 'Error opening file: $e',
          type: 'error',
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
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: primary.withValues(alpha: 0.12),
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
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: primary,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      widget.note['title'] ?? '',
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
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
              style: GoogleFonts.inter(
                fontSize: 13,
                color: AppColors.textSecondary,
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
                          backgroundColor: AppColors.surface2,
                          valueColor: AlwaysStoppedAnimation<Color>(primary),
                          minHeight: 4,
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      '${(_progress * 100).toStringAsFixed(0)}%',
                      style: GoogleFonts.inter(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: primary,
                      ),
                    ),
                  ],
                )
              : Row(
                  children: [
                    Expanded(
                      child: SizedBox(
                        height: 40,
                        child: OutlinedButton.icon(
                          onPressed: () {
                            NotesCommentsSheet.show(
                              context,
                              widget.note['id'] as int,
                              widget.note['title'] ?? 'Note',
                            );
                          },
                          icon: Icon(Icons.chat_bubble_outline, size: 16, color: primary),
                          label: Text('Discussion',
                              style: GoogleFonts.inter(fontSize: 11, color: primary, fontWeight: FontWeight.bold)),
                          style: OutlinedButton.styleFrom(
                            side: BorderSide(color: primary.withValues(alpha: 0.5)),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: SizedBox(
                        height: 40,
                        child: ElevatedButton.icon(
                          onPressed: isDownloaded ? _openFile : _downloadFile,
                          icon: Icon(
                            isDownloaded
                                ? Icons.folder_open_rounded
                                : Icons.file_download_outlined,
                            size: 16,
                            color: Colors.black,
                          ),
                          label: Text(
                            isDownloaded ? 'Open' : 'Download',
                            style: GoogleFonts.inter(fontWeight: FontWeight.bold, color: Colors.black, fontSize: 11),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: isDownloaded ? AppColors.success : primary,
                            foregroundColor: Colors.black,
                            elevation: 0,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
        ],
      ),
    );
  }
}
