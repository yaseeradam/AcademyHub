import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';

class ChatScreen extends StatefulWidget {
  const ChatScreen({super.key});

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  int? _activeConversationId;
  String? _activeConversationTitle;
  List<dynamic> _conversations = [];
  List<dynamic> _messages = [];
  List<dynamic> _contacts = [];
  bool _loadingConversations = true;
  bool _loadingMessages = false;
  bool _loadingContacts = false;
  bool _showNewChatDialog = false;
  final TextEditingController _msgController = TextEditingController();
  final TextEditingController _searchController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _loadConversations();
  }

  @override
  void dispose() {
    _msgController.dispose();
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadConversations() async {
    final auth = context.read<AuthProvider>();
    setState(() => _loadingConversations = true);
    try {
      final data = await auth.apiService.getWithCache('/conversations');
      if (mounted) {
        setState(() {
          _conversations = data as List;
          _loadingConversations = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingConversations = false);
    }
  }

  Future<void> _loadMessages(int conversationId, String title) async {
    final auth = context.read<AuthProvider>();
    setState(() {
      _activeConversationId = conversationId;
      _activeConversationTitle = title;
      _loadingMessages = true;
      _messages = [];
    });

    try {
      final data = await auth.apiService.getWithCache('/conversations/$conversationId/messages');
      if (mounted && _activeConversationId == conversationId) {
        setState(() {
          _messages = data as List;
          _loadingMessages = false;
        });
        _scrollToBottom();
      }
    } catch (_) {
      if (mounted) setState(() => _loadingMessages = false);
    }
  }

  Future<void> _sendMessage() async {
    final body = _msgController.text.trim();
    if (body.isEmpty || _activeConversationId == null) return;

    final auth = context.read<AuthProvider>();
    _msgController.clear();

    // Optimistic insert
    final tempMsg = {
      'id': DateTime.now().millisecondsSinceEpoch,
      'sender_id': auth.user?.id,
      'body': body,
      'created_at': DateTime.now().toIso8601String(),
      'sender': {
        'id': auth.user?.id,
        'name': auth.user?.name ?? 'Me',
        'role': auth.user?.role,
      }
    };

    setState(() {
      _messages.add(tempMsg);
    });
    _scrollToBottom();

    try {
      final success = await auth.apiService.queueableMutation(
        '/conversations/$_activeConversationId/messages',
        'POST',
        {'body': body},
      );
      if (success) {
        // Reload messages to get official ID/timestamps
        final data = await auth.apiService.dio.get('/conversations/$_activeConversationId/messages');
        if (mounted) {
          setState(() {
            _messages = data.data as List;
          });
          _scrollToBottom();
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to send message: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  Future<void> _loadContacts() async {
    final auth = context.read<AuthProvider>();
    setState(() {
      _loadingContacts = true;
      _contacts = [];
    });

    try {
      // Admins and teachers can list users, parents can list teachers/admins
      // Let's call the admin/users list or a public list. If parent, list staff.
      // We can query a simple users list from backend or retrieve from caching/local DB.
      // Let's fetch from `/admin/users` if admin, or query `/announcements` and fetch authors,
      // or check the user role. Let's do a simple get with fallback.
      final endpoint = auth.user?.role == 'admin' ? '/admin/users' : '/students'; 
      final data = await auth.apiService.getWithCache(endpoint);
      
      if (mounted) {
        setState(() {
          if (auth.user?.role == 'admin') {
            _contacts = data as List;
          } else {
            // For parents/students, contact options can be teachers/staff
            // Let's default to a list of users or simulate/mock a few support channels
            _contacts = [
              {'id': 1, 'name': 'School Principal (Admin)', 'role': 'admin'},
              {'id': 2, 'name': 'Tuition Desk (Bursar)', 'role': 'bursar'},
              {'id': 3, 'name': 'Class Academic Head', 'role': 'teacher'},
            ];
          }
          _loadingContacts = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _contacts = [
            {'id': 1, 'name': 'School Principal (Admin)', 'role': 'admin'},
            {'id': 2, 'name': 'Tuition Desk (Bursar)', 'role': 'bursar'},
            {'id': 3, 'name': 'Class Academic Head', 'role': 'teacher'},
          ];
          _loadingContacts = false;
        });
      }
    }
  }

  Future<void> _startNewChat(int recipientId, String name) async {
    final auth = context.read<AuthProvider>();
    setState(() => _showNewChatDialog = false);

    try {
      final response = await auth.apiService.dio.post('/conversations', data: {
        'recipient_id': recipientId,
      });
      final convId = response.data['id'] as int;
      await _loadConversations();
      await _loadMessages(convId, name);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not start chat: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(
          _activeConversationId != null ? (_activeConversationTitle ?? 'Chat') : 'Inbox & Chats',
          style: GoogleFonts.inter(
              fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        ),
        backgroundColor: AppColors.surface,
        elevation: 0,
        iconTheme: IconThemeData(color: AppColors.textPrimary),
        leading: _activeConversationId != null
            ? IconButton(
                icon: const Icon(Icons.arrow_back),
                onPressed: () {
                  setState(() {
                    _activeConversationId = null;
                    _activeConversationTitle = null;
                  });
                  _loadConversations();
                },
              )
            : null,
        actions: [
          if (_activeConversationId == null)
            IconButton(
              icon: Icon(Icons.add_comment_outlined, color: primary),
              onPressed: () {
                _loadContacts();
                setState(() => _showNewChatDialog = true);
              },
            ),
        ],
      ),
      body: _activeConversationId != null
          ? _buildChatThread(primary)
          : _buildConversationsList(primary),
    );
  }

  Widget _buildConversationsList(Color primary) {
    if (_loadingConversations) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_conversations.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.chat_bubble_outline, size: 48, color: AppColors.textMuted),
            const SizedBox(height: 12),
            Text('No active conversations.',
                style: GoogleFonts.inter(fontSize: 14, color: AppColors.textSecondary)),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: () {
                _loadContacts();
                setState(() => _showNewChatDialog = true);
              },
              style: ElevatedButton.styleFrom(backgroundColor: primary),
              icon: const Icon(Icons.edit, size: 16, color: Colors.white),
              label: Text('New Chat', style: GoogleFonts.inter(color: Colors.white, fontSize: 12)),
            ),
          ],
        ),
      );
    }

    return Stack(
      children: [
        ListView.separated(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 30),
          itemCount: _conversations.length,
          separatorBuilder: (_, _) => Divider(height: 1, color: AppColors.borderLight),
          itemBuilder: (ctx, index) {
            final c = _conversations[index];
            final title = c['title'] ?? 'Chat';
            final lastMsg = c['last_message'] ?? '(No message)';
            final lastAt = c['last_message_at'] != null
                ? DateTime.tryParse(c['last_message_at'])?.toLocal()
                : null;
            final isUnread = c['unread'] == true;

            return InkWell(
              onTap: () => _loadMessages(c['id'] as int, title),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
                child: Row(
                  children: [
                    CircleAvatar(
                      backgroundColor: primary.withValues(alpha: 0.1),
                      child: Text(title[0].toUpperCase(),
                          style: TextStyle(color: primary, fontWeight: FontWeight.bold)),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                title,
                                style: GoogleFonts.inter(
                                    fontSize: 14,
                                    fontWeight: isUnread ? FontWeight.bold : FontWeight.w500,
                                    color: AppColors.textPrimary),
                              ),
                              if (lastAt != null)
                                Text(
                                  '${lastAt.hour}:${lastAt.minute.toString().padLeft(2, '0')}',
                                  style: GoogleFonts.inter(fontSize: 10, color: AppColors.textMuted),
                                ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            lastMsg,
                            style: GoogleFonts.inter(
                                fontSize: 12,
                                fontWeight: isUnread ? FontWeight.bold : FontWeight.normal,
                                color: isUnread ? AppColors.textPrimary : AppColors.textSecondary),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
        if (_showNewChatDialog) _buildNewChatOverlay(primary),
      ],
    );
  }

  Widget _buildChatThread(Color primary) {
    final auth = context.read<AuthProvider>();

    return Column(
      children: [
        Expanded(
          child: _loadingMessages
              ? const Center(child: CircularProgressIndicator())
              : _messages.isEmpty
                  ? Center(child: Text('Start of thread.', style: GoogleFonts.inter(color: AppColors.textMuted)))
                  : ListView.builder(
                      controller: _scrollController,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                      itemCount: _messages.length,
                      itemBuilder: (ctx, index) {
                        final msg = _messages[index];
                        final isMe = msg['sender_id'] == auth.user?.id;
                        final body = msg['body'] ?? '';
                        final senderName = msg['sender']?['name'] ?? 'User';

                        return Align(
                          alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                          child: Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            padding: const EdgeInsets.all(12),
                            constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.75),
                            decoration: BoxDecoration(
                              color: isMe ? primary : AppColors.surface,
                              borderRadius: BorderRadius.only(
                                topLeft: const Radius.circular(12),
                                topRight: const Radius.circular(12),
                                bottomLeft: isMe ? const Radius.circular(12) : const Radius.circular(0),
                                bottomRight: isMe ? const Radius.circular(0) : const Radius.circular(12),
                              ),
                              border: isMe ? null : Border.all(color: AppColors.borderLight),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                if (!isMe)
                                  Text(senderName,
                                      style: GoogleFonts.inter(
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                          color: AppColors.textSecondary)),
                                if (!isMe) const SizedBox(height: 4),
                                Text(
                                  body,
                                  style: GoogleFonts.inter(
                                      fontSize: 13,
                                      color: isMe ? Colors.white : AppColors.textPrimary),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
        ),
        // Send bar
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          color: AppColors.surface,
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _msgController,
                  style: GoogleFonts.inter(fontSize: 13, color: AppColors.textPrimary),
                  decoration: InputDecoration(
                    hintText: 'Type a message...',
                    hintStyle: GoogleFonts.inter(color: AppColors.textMuted),
                    filled: true,
                    fillColor: AppColors.surface2,
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              GestureDetector(
                onTap: _sendMessage,
                child: CircleAvatar(
                  radius: 20,
                  backgroundColor: primary,
                  child: const Icon(Icons.send, color: Colors.white, size: 16),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildNewChatOverlay(Color primary) {
    return Positioned.fill(
      child: Container(
        color: Colors.black54,
        child: Center(
          child: Container(
            width: MediaQuery.of(context).size.width * 0.85,
            height: MediaQuery.of(context).size.height * 0.6,
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
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('Select Contact',
                        style: GoogleFonts.inter(
                            fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                    IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () => setState(() => _showNewChatDialog = false),
                    ),
                  ],
                ),
                const Divider(),
                Expanded(
                  child: _loadingContacts
                      ? const Center(child: CircularProgressIndicator())
                      : _contacts.isEmpty
                          ? Center(
                              child: Text('No contacts found.',
                                  style: GoogleFonts.inter(color: AppColors.textMuted)))
                          : ListView.separated(
                              itemCount: _contacts.length,
                              separatorBuilder: (_, _) => Divider(height: 1, color: AppColors.borderLight),
                              itemBuilder: (ctx, i) {
                                final contact = _contacts[i];
                                final name = contact['name'] ?? '';
                                final role = contact['role'] ?? 'Staff';

                                return ListTile(
                                  title: Text(name,
                                      style: GoogleFonts.inter(
                                          fontSize: 13,
                                          fontWeight: FontWeight.bold,
                                          color: AppColors.textPrimary)),
                                  subtitle: Text(role.toString().toUpperCase(),
                                      style: GoogleFonts.inter(
                                          fontSize: 10, color: AppColors.textSecondary)),
                                  trailing: Icon(Icons.chat_bubble_outline, color: primary, size: 18),
                                  onTap: () => _startNewChat(contact['id'] as int, name),
                                );
                              },
                            ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
