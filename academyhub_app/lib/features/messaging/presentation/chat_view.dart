import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class ChatMessage {
  final String text;
  final bool isSentByMe;
  final String time;

  const ChatMessage({
    required this.text,
    required this.isSentByMe,
    required this.time,
  });
}

class ChatView extends StatefulWidget {
  const ChatView({super.key});

  @override
  State<ChatView> createState() => _ChatViewState();
}

class _ChatViewState extends State<ChatView> {
  final List<ChatMessage> _messages = [
    const ChatMessage(
      text: "Hello! Just wanted to follow up on David's science project grade.",
      isSentByMe: true,
      time: "10:15 AM",
    ),
    const ChatMessage(
      text: "Hello, yes! David did an excellent job on the project presentation. The score will be uploaded by this afternoon.",
      isSentByMe: false,
      time: "10:18 AM",
    ),
    const ChatMessage(
      text: "Perfect, thank you so much for the update!",
      isSentByMe: true,
      time: "10:20 AM",
    ),
  ];

  final TextEditingController _messageController = TextEditingController();

  void _sendMessage() {
    final text = _messageController.text.trim();
    if (text.isEmpty) return;

    setState(() {
      _messages.add(ChatMessage(
        text: text,
        isSentByMe: true,
        time: "Just now",
      ));
      _messageController.clear();
    });

    // Simulate auto response
    Future.delayed(const Duration(seconds: 1), () {
      if (mounted) {
        setState(() {
          _messages.add(const ChatMessage(
            text: "Got it! I will notify you as soon as the scores are finalized.",
            isSentByMe: false,
            time: "Just now",
          ));
        });
      }
    });
  }

  Widget _buildMessageBubble(ChatMessage msg) {
    return Align(
      alignment: msg.isSentByMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 4, horizontal: 8),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: msg.isSentByMe ? AppColors.primaryBlue : Colors.white,
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(16),
            topRight: const Radius.circular(16),
            bottomLeft: msg.isSentByMe ? const Radius.circular(16) : Radius.zero,
            bottomRight: msg.isSentByMe ? Radius.zero : const Radius.circular(16),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.03),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: msg.isSentByMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          children: [
            Text(
              msg.text,
              style: TextStyle(
                color: msg.isSentByMe ? Colors.white : AppColors.textPrimary,
                fontSize: 14,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              msg.time,
              style: TextStyle(
                color: msg.isSentByMe ? Colors.white70 : AppColors.textDisabled,
                fontSize: 10,
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Message list
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.all(12),
            itemCount: _messages.length,
            itemBuilder: (context, idx) {
              return _buildMessageBubble(_messages[idx]);
            },
          ),
        ),
        
        // Input bar
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: const BoxDecoration(
            color: Colors.white,
            border: Border(top: BorderSide(color: AppColors.divider)),
          ),
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _messageController,
                  decoration: const InputDecoration(
                    hintText: "Type a message...",
                    fillColor: Colors.white,
                    filled: true,
                    contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  ),
                  onSubmitted: (_) => _sendMessage(),
                ),
              ),
              const SizedBox(width: 8),
              IconButton(
                icon: const Icon(Icons.send, color: AppColors.primaryBlue),
                onPressed: _sendMessage,
              ),
            ],
          ),
        ),
      ],
    );
  }
}
