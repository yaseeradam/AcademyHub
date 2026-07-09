import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';

class NotesCommentsSheet extends StatefulWidget {
  final int noteId;
  final String noteTitle;

  const NotesCommentsSheet({super.key, required this.noteId, required this.noteTitle});

  static void show(BuildContext context, int noteId, String noteTitle) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.75,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        builder: (_, controller) => NotesCommentsSheet(noteId: noteId, noteTitle: noteTitle),
      ),
    );
  }

  @override
  State<NotesCommentsSheet> createState() => _NotesCommentsSheetState();
}

class _NotesCommentsSheetState extends State<NotesCommentsSheet> {
  List<dynamic> _comments = [];
  bool _loading = true;
  final TextEditingController _commentController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadComments();
  }

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }

  Future<void> _loadComments() async {
    final auth = context.read<AuthProvider>();
    setState(() => _loading = true);

    try {
      // Students request via /student/notes/{id}/comments
      // Teachers/admins can request via /student/notes/{id}/comments too or a shared path
      // Let's use the generic endpoint we created:
      final data = await auth.apiService.getWithCache('/student/notes/${widget.noteId}/comments');
      if (mounted) {
        setState(() {
          _comments = data as List;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _postComment() async {
    final text = _commentController.text.trim();
    if (text.isEmpty) return;

    final auth = context.read<AuthProvider>();
    _commentController.clear();

    // Optimistic insert
    final newCommentTemp = {
      'id': DateTime.now().millisecondsSinceEpoch,
      'comment': text,
      'created_at': DateTime.now().toIso8601String(),
      'commenter_name': auth.user?.name ?? 'Me',
      'user': {
        'id': auth.user?.id,
        'name': auth.user?.name ?? 'Me',
        'role': auth.user?.role,
      }
    };

    setState(() {
      _comments.add(newCommentTemp);
    });

    try {
      final success = await auth.apiService.queueableMutation(
        '/student/notes/${widget.noteId}/comments',
        'POST',
        {'comment': text},
      );
      if (success) {
        _loadComments();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to post comment: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
        boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 10, spreadRadius: 1)],
      ),
      child: Column(
        children: [
          // Drag handle
          Container(
            width: 40,
            height: 4,
            margin: const EdgeInsets.symmetric(vertical: 10),
            decoration: BoxDecoration(color: AppColors.borderLight, borderRadius: BorderRadius.circular(2)),
          ),
          // Title
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text('Discussion: ${widget.noteTitle}',
                      style: GoogleFonts.inter(
                          fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis),
                ),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                )
              ],
            ),
          ),
          const Divider(),
          // Comments list
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _comments.isEmpty
                    ? Center(
                        child: Text('No comments yet. Start the discussion!',
                            style: GoogleFonts.inter(color: AppColors.textMuted)))
                    : ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        itemCount: _comments.length,
                        itemBuilder: (ctx, index) {
                          final c = _comments[index];
                          final author = c['commenter_name'] ?? c['user']?['name'] ?? c['student']?['first_name'] ?? 'User';
                          final commentText = c['comment'] ?? '';
                          final isMe = c['user']?['id'] == context.read<AuthProvider>().user?.id;

                          return Align(
                            alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                            child: Container(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(12),
                              constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.75),
                              decoration: BoxDecoration(
                                color: isMe ? primary.withValues(alpha: 0.15) : AppColors.surface2,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: AppColors.borderLight),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    author,
                                    style: GoogleFonts.inter(
                                        fontSize: 11, fontWeight: FontWeight.bold, color: primary),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    commentText,
                                    style: GoogleFonts.inter(fontSize: 13, color: AppColors.textPrimary),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),
          // Input bar
          Padding(
            padding: EdgeInsets.only(
              left: 16,
              right: 16,
              bottom: MediaQuery.of(context).viewInsets.bottom + 16,
              top: 8,
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _commentController,
                    style: GoogleFonts.inter(fontSize: 13, color: AppColors.textPrimary),
                    decoration: InputDecoration(
                      hintText: 'Add a comment...',
                      hintStyle: GoogleFonts.inter(color: AppColors.textMuted),
                      filled: true,
                      fillColor: AppColors.surface2,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                        borderSide: BorderSide.none,
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  icon: Icon(Icons.send_rounded, color: primary),
                  onPressed: _postComment,
                ),
              ],
            ),
          )
        ],
      ),
    );
  }
}
