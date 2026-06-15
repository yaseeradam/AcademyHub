import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:dio/dio.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

class MockPlatformFile {
  final String name;
  final int size;
  final String path;
  MockPlatformFile({required this.name, required this.size, required this.path});
}

class HomeworkSubmitSheet extends StatefulWidget {
  final Map<String, dynamic> homework;

  const HomeworkSubmitSheet({super.key, required this.homework});

  static void show(BuildContext context, Map<String, dynamic> homework) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.75,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        builder: (_, controller) => HomeworkSubmitSheet(homework: homework),
      ),
    );
  }

  @override
  State<HomeworkSubmitSheet> createState() => _HomeworkSubmitSheetState();
}

class _HomeworkSubmitSheetState extends State<HomeworkSubmitSheet> {
  final TextEditingController _submissionController = TextEditingController();
  MockPlatformFile? _selectedFile;
  bool _submitting = false;
  bool _loadingState = true;
  Map<String, dynamic>? _existingSubmission;

  @override
  void initState() {
    super.initState();
    _loadExistingSubmission();
  }

  @override
  void dispose() {
    _submissionController.dispose();
    super.dispose();
  }

  Future<void> _loadExistingSubmission() async {
    final subs = widget.homework['submissions'] as List?;
    if (subs != null && subs.isNotEmpty) {
      setState(() {
        _existingSubmission = subs.first;
        _submissionController.text = _existingSubmission?['submission'] ?? '';
        _loadingState = false;
      });
      return;
    }
    setState(() => _loadingState = false);
  }

  Future<void> _pickFile() async {
    final mockFiles = [
      MockPlatformFile(name: 'assignment_answers_sheet.pdf', size: 1024 * 340, path: '/mock/assignment_answers_sheet.pdf'),
      MockPlatformFile(name: 'cbt_science_screenshot.png', size: 1024 * 510, path: '/mock/cbt_science_screenshot.png'),
      MockPlatformFile(name: 'literature_notes.docx', size: 1024 * 120, path: '/mock/literature_notes.docx'),
    ];

    final chosen = await showDialog<MockPlatformFile>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        title: Text('Select File Attachment', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: mockFiles.map((f) => ListTile(
            leading: Icon(Icons.insert_drive_file, color: context.read<AuthProvider>().tenantPrimaryColor),
            title: Text(f.name, style: GoogleFonts.spaceGrotesk(fontSize: 13, color: AppColors.textPrimary)),
            subtitle: Text('${(f.size / 1024).toStringAsFixed(0)} KB', style: GoogleFonts.spaceGrotesk(fontSize: 10, color: AppColors.textMuted)),
            onTap: () => Navigator.pop(context, f),
          )).toList(),
        ),
      ),
    );

    if (chosen != null) {
      setState(() {
        _selectedFile = chosen;
      });
    }
  }

  Future<void> _submitHomework() async {
    final text = _submissionController.text.trim();
    if (text.isEmpty && _selectedFile == null) {
      CustomToast.show(
        context: context,
        message: 'Please provide either a text response or file attachment.',
        type: 'warning',
      );
      return;
    }

    final auth = context.read<AuthProvider>();
    final isOnline = await auth.apiService.isOnline;
    if (!isOnline) {
      if (mounted) {
        CustomToast.show(
          context: context,
          message: 'Offline: Submitting attachments requires an internet connection.',
          type: 'warning',
        );
      }
      return;
    }

    setState(() => _submitting = true);

    try {
      final id = widget.homework['id'];
      
      MultipartFile? fileUpload;
      if (_selectedFile != null) {
        fileUpload = MultipartFile.fromBytes(
          [1, 2, 3, 4], // mock dummy bytes
          filename: _selectedFile!.name,
        );
      }

      final Map<String, dynamic> uploadFields = {
        'submission': text,
      };
      if (fileUpload != null) {
        uploadFields['file'] = fileUpload;
      }

      final formData = FormData.fromMap(uploadFields);

      final response = await auth.apiService.dio.post(
        '/student/homework/$id/submit-file',
        data: formData,
      );

      if (mounted) {
        setState(() {
          _submitting = false;
          _existingSubmission = response.data['data'];
        });
        CustomToast.show(
          context: context,
          message: 'Homework submitted successfully!',
          type: 'success',
        );
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        setState(() => _submitting = false);
        CustomToast.show(
          context: context,
          message: 'Submission failed: $e',
          type: 'error',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;
    final isSubmitted = _existingSubmission != null;
    final grade = _existingSubmission?['grade'];
    final feedback = _existingSubmission?['feedback'];

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
        boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 10, spreadRadius: 1)],
      ),
      child: Column(
        children: [
          Container(
            width: 40,
            height: 4,
            margin: const EdgeInsets.symmetric(vertical: 10),
            decoration: BoxDecoration(color: AppColors.borderLight, borderRadius: BorderRadius.circular(2)),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    widget.homework['title'] ?? 'Homework Details',
                    style: GoogleFonts.spaceGrotesk(
                        fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                )
              ],
            ),
          ),
          const Divider(),
          Expanded(
            child: _loadingState
                ? const Center(child: CircularProgressIndicator())
                : ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.surface2,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.borderLight),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              widget.homework['subject_name'] ?? 'General',
                              style: GoogleFonts.spaceGrotesk(
                                  fontSize: 11, fontWeight: FontWeight.bold, color: primary),
                            ),
                            const SizedBox(height: 6),
                            Text(
                              widget.homework['content'] ?? widget.homework['description'] ?? '',
                              style: GoogleFonts.spaceGrotesk(fontSize: 13, color: AppColors.textPrimary, height: 1.4),
                            ),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                Icon(Icons.alarm, size: 13, color: AppColors.textMuted),
                                const SizedBox(width: 4),
                                Text(
                                  'Due Date: ${widget.homework['due_date'] ?? ''}',
                                  style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textMuted),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),
                      if (isSubmitted) ...[
                        _buildSubmissionStatusCard(grade, feedback),
                        const SizedBox(height: 20),
                      ],
                      if (!isSubmitted) ...[
                        Text(
                          'Your Submission',
                          style: GoogleFonts.spaceGrotesk(
                              fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                        ),
                        const SizedBox(height: 8),
                        TextField(
                          controller: _submissionController,
                          style: GoogleFonts.spaceGrotesk(fontSize: 13, color: AppColors.textPrimary),
                          maxLines: 4,
                          decoration: InputDecoration(
                            hintText: 'Type your text response here (optional)...',
                            hintStyle: GoogleFonts.spaceGrotesk(color: AppColors.textMuted),
                            filled: true,
                            fillColor: AppColors.surface2,
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(color: AppColors.borderLight)),
                            contentPadding: const EdgeInsets.all(12),
                          ),
                        ),
                        const SizedBox(height: 16),
                        GestureDetector(
                          onTap: _pickFile,
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                            decoration: BoxDecoration(
                              color: AppColors.surface2,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: AppColors.borderLight, style: BorderStyle.solid),
                            ),
                            child: Row(
                              children: [
                                Icon(Icons.attach_file, color: primary, size: 18),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Text(
                                    _selectedFile != null ? _selectedFile!.name : 'Attach a Document/Photo (Simulated)',
                                    style: GoogleFonts.spaceGrotesk(
                                      fontSize: 12,
                                      color: _selectedFile != null ? AppColors.textPrimary : AppColors.textMuted,
                                      fontWeight: _selectedFile != null ? FontWeight.bold : FontWeight.normal,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                if (_selectedFile != null)
                                  GestureDetector(
                                    onTap: () => setState(() => _selectedFile = null),
                                    child: const Icon(Icons.close, size: 18, color: Colors.grey),
                                  ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 24),
                        SizedBox(
                          width: double.infinity,
                          height: 46,
                          child: ElevatedButton(
                            onPressed: _submitting ? null : _submitHomework,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: primary,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                            child: _submitting
                                ? const CircularProgressIndicator(color: Colors.white)
                                : Text(
                                    'Submit Assignment',
                                    style: GoogleFonts.spaceGrotesk(
                                        color: Colors.black, fontWeight: FontWeight.bold, fontSize: 13),
                                  ),
                          ),
                        ),
                      ],
                    ],
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubmissionStatusCard(String? grade, String? feedback) {
    final hasGrade = grade != null && grade.isNotEmpty;
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: hasGrade ? AppColors.success.withValues(alpha: 0.1) : AppColors.info.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: hasGrade ? AppColors.success : AppColors.info),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                hasGrade ? Icons.check_circle_outline : Icons.pending_actions_outlined,
                color: hasGrade ? AppColors.success : AppColors.info,
                size: 20,
              ),
              const SizedBox(width: 8),
              Text(
                hasGrade ? 'Graded' : 'Submitted & Pending Review',
                style: GoogleFonts.spaceGrotesk(
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                  color: hasGrade ? AppColors.success : AppColors.info,
                ),
              ),
            ],
          ),
          if (hasGrade) ...[
            const SizedBox(height: 10),
            Text('Grade: $grade',
                style: GoogleFonts.spaceGrotesk(
                    fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
          ],
          if (feedback != null && feedback.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text('Feedback: $feedback',
                style: GoogleFonts.spaceGrotesk(
                    fontSize: 12, color: AppColors.textSecondary, fontStyle: FontStyle.italic)),
          ],
        ],
      ),
    );
  }
}
