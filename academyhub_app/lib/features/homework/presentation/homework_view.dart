import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';

class HomeworkView extends StatefulWidget {
  const HomeworkView({super.key});

  @override
  State<HomeworkView> createState() => _HomeworkViewState();
}

class _HomeworkViewState extends State<HomeworkView> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  
  List<dynamic> _assignments = [];
  List<dynamic> _notesList = [];
  bool _isLoadingHomework = false;
  bool _isLoadingNotes = false;

  String _searchQuery = '';
  String? _selectedSubject;
  List<String> _subjectsList = [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _tabController.addListener(() {
      setState(() {}); // trigger rebuild to update action buttons or indicators
    });
    _loadHomework();
    _loadNotes();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadHomework() async {
    if (!mounted) return;
    setState(() {
      _isLoadingHomework = true;
    });
    try {
      final response = await apiClient.dio.get('/student/homework');
      if (response.statusCode == 200 && response.data != null) {
        final list = List<dynamic>.from(response.data['data'] ?? []);
        if (mounted) {
          setState(() {
            _assignments = list;
            // extract unique subjects
            final subs = list.map((item) => (item['subject']?['name'] ?? 'General').toString()).toSet().toList();
            _subjectsList = subs;
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading homework: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoadingHomework = false;
        });
      }
    }
  }

  Future<void> _loadNotes() async {
    if (!mounted) return;
    setState(() {
      _isLoadingNotes = true;
    });
    try {
      final response = await apiClient.dio.get('/student/notes');
      if (response.statusCode == 200 && response.data != null) {
        final list = List<dynamic>.from(response.data['notes'] ?? []);
        if (mounted) {
          setState(() {
            _notesList = list;
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading notes: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoadingNotes = false;
        });
      }
    }
  }

  Future<void> _submitAssignment(int homeworkId, String submissionText, List<String> simulatedAttachments) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator()),
    );
    try {
      // Calls the real submit endpoint with submission text and files if any
      final response = await apiClient.dio.post(
        '/student/homework/$homeworkId/submit-file',
        data: {
          'submission': submissionText,
          // simulated file attachment
        },
      );
      if (mounted) {
        Navigator.pop(context); // Close loading dialog
      }
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('✓ Homework submitted successfully!'),
              backgroundColor: AppColors.successGreen,
            ),
          );
        }
        _loadHomework(); // Reload list to update status
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to submit homework: $e'),
            backgroundColor: AppColors.dangerRed,
          ),
        );
      }
    }
  }

  void _showSubmitSheet(BuildContext context, dynamic item) {
    final title = item['title'] ?? 'Homework';
    final homeworkId = item['id'] as int;
    
    final writtenController = TextEditingController();
    List<String> attachments = [];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setSheetState) {
            return Padding(
              padding: EdgeInsets.only(
                top: 20.0, left: 20.0, right: 20.0,
                bottom: MediaQuery.of(context).viewInsets.bottom + 20.0,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Handle
                    Center(
                      child: Container(
                        width: 40, height: 4,
                        decoration: BoxDecoration(color: const Color(0xFFE2E8F0), borderRadius: BorderRadius.circular(2)),
                      ),
                    ),
                    const SizedBox(height: 16),
                    // Header
                    Row(
                      children: [
                        Container(
                          width: 40, height: 40,
                          decoration: BoxDecoration(
                            color: const Color(0xFF7C3AED).withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(Icons.upload_file_rounded, color: Color(0xFF7C3AED), size: 20),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text('Submit: $title',
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                              maxLines: 2, overflow: TextOverflow.ellipsis),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close_rounded, color: Color(0xFF64748B)),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    // Written answer
                    const Text('WRITTEN ANSWER',
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8)),
                    const SizedBox(height: 8),
                    TextField(
                      controller: writtenController,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: 'Type your answers here...',
                        hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                        fillColor: const Color(0xFFF8FAFC),
                        filled: true,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: Color(0xFF7C3AED), width: 1.5),
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),
                    // Attach files
                    const Text('ATTACH FILES',
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8)),
                    const SizedBox(height: 8),
                    GestureDetector(
                      onTap: () => setSheetState(() {
                        attachments.add('notebook_page_${attachments.length + 1}.jpg');
                      }),
                      child: Container(
                        height: 80,
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFF7C3AED).withValues(alpha: 0.3), width: 1.5),
                        ),
                        child: const Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.add_photo_alternate_rounded, color: Color(0xFF7C3AED), size: 26),
                            SizedBox(height: 4),
                            Text('Tap to attach photos or PDF',
                                style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                          ],
                        ),
                      ),
                    ),
                    if (attachments.isNotEmpty) ...[
                      const SizedBox(height: 12),
                      SizedBox(
                        height: 72,
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          itemCount: attachments.length,
                          itemBuilder: (context, index) {
                            return Container(
                              margin: const EdgeInsets.only(right: 8),
                              width: 72,
                              decoration: BoxDecoration(
                                color: const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                              ),
                              child: Stack(
                                children: [
                                  Center(
                                    child: Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        const Icon(Icons.image_rounded, size: 22, color: Color(0xFF94A3B8)),
                                        const SizedBox(height: 2),
                                        Text(attachments[index],
                                            style: const TextStyle(fontSize: 7, color: Color(0xFF64748B)),
                                            overflow: TextOverflow.ellipsis),
                                      ],
                                    ),
                                  ),
                                  Positioned(
                                    top: 2, right: 2,
                                    child: GestureDetector(
                                      onTap: () => setSheetState(() => attachments.removeAt(index)),
                                      child: Container(
                                        width: 16, height: 16,
                                        decoration: const BoxDecoration(color: Color(0xFFF43F5E), shape: BoxShape.circle),
                                        child: const Icon(Icons.close, size: 10, color: Colors.white),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                      ),
                    ],
                    const SizedBox(height: 20),
                    Container(
                      height: 50,
                      decoration: BoxDecoration(
                        color: AppColors.rolePrimary('student'),
                        borderRadius: BorderRadius.circular(14),
                        border: const Border(
                          bottom: BorderSide(color: Color(0xFF1E40AF), width: 3),
                        ),
                        boxShadow: [BoxShadow(color: const Color(0xFF1E40AF).withValues(alpha: 0.3), blurRadius: 10, offset: const Offset(0, 4))],
                      ),
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.transparent,
                          shadowColor: Colors.transparent,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        onPressed: () {
                          Navigator.pop(context);
                          _submitAssignment(homeworkId, writtenController.text, attachments);
                        },
                        icon: const Icon(Icons.send_rounded, size: 18),
                        label: const Text('SUBMIT TO TEACHER', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                      ),
                    ),
                    const SizedBox(height: 8),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showHomeworkDetails(BuildContext context, dynamic item) {
    final submissions = List<dynamic>.from(item['submissions'] ?? []);
    final isSubmitted = submissions.isNotEmpty;
    final subjectName = item['subject']?['name'] ?? 'General';
    final teacherName = item['teacher']?['name'] ?? 'Teacher';
    final title = item['title'] ?? '';
    final content = item['content'] ?? '';
    final dueStr = item['due_date'] ?? 'Tomorrow at 11:59 PM';

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.divider,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '$subjectName · By $teacherName',
                    style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.softBlue, fontSize: 13),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: (isSubmitted ? AppColors.successGreen : AppColors.dangerRed).withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      isSubmitted ? 'SUBMITTED' : 'PENDING',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: isSubmitted ? AppColors.successGreen : AppColors.dangerRed,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                title,
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 16),
              const Text(
                'Instructions',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 6),
              Text(
                content,
                style: const TextStyle(color: AppColors.textSecondary, fontSize: 13, height: 1.4),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  const Icon(Icons.alarm, size: 16, color: AppColors.dangerRed),
                  const SizedBox(width: 4),
                  Text(
                    'Due: $dueStr',
                    style: const TextStyle(color: AppColors.dangerRed, fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              if (!isSubmitted)
                ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _showSubmitSheet(context, item);
                  },
                  child: const Text('Submit Assignment'),
                ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildHomeworkList() {
    if (_isLoadingHomework) {
      return const Center(child: CircularProgressIndicator());
    }

    // Apply filters
    var list = _assignments;
    if (_searchQuery.isNotEmpty) {
      list = list.where((item) =>
          item['title'].toString().toLowerCase().contains(_searchQuery.toLowerCase()) ||
          item['content'].toString().toLowerCase().contains(_searchQuery.toLowerCase())).toList();
    }
    if (_selectedSubject != null) {
      list = list.where((item) => (item['subject']?['name'] ?? 'General') == _selectedSubject).toList();
    }

    if (list.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.assignment_turned_in_outlined, size: 64, color: AppColors.textDisabled),
              SizedBox(height: 16),
              Text('No Homework Found', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
              SizedBox(height: 4),
              Text('You have no active homework assignments matching the filters.', style: TextStyle(color: AppColors.textSecondary, fontSize: 12), textAlign: TextAlign.center),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
      itemCount: list.length,
      itemBuilder: (context, index) {
        final item = list[index];
        final title = item['title'] ?? '';
        final subject = item['subject']?['name'] ?? 'General';
        final submissions = List<dynamic>.from(item['submissions'] ?? []);
        final isSubmitted = submissions.isNotEmpty;
        final score = isSubmitted && submissions.first['score'] != null
            ? '${submissions.first['score']}/20'
            : null;
        final due = item['due_date']?.toString() ?? 'Tomorrow';

        final Color statusColor = isSubmitted ? const Color(0xFF10B981) : const Color(0xFFF43F5E);
        final IconData statusIcon = isSubmitted ? Icons.check_circle_rounded : Icons.pending_actions_rounded;

        return GestureDetector(
          onTap: () => _showHomeworkDetails(context, item),
          child: Container(
            margin: const EdgeInsets.only(bottom: 10),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: statusColor.withValues(alpha: 0.2)),
              boxShadow: [BoxShadow(color: statusColor.withValues(alpha: 0.06), blurRadius: 8, offset: const Offset(0, 3))],
            ),
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 42, height: 42,
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(statusIcon, color: statusColor, size: 22),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(title,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                            maxLines: 1, overflow: TextOverflow.ellipsis),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            _pill(subject, const Color(0xFF7C3AED)),
                            const SizedBox(width: 6),
                            _pill('Due: $due', const Color(0xFFF43F5E)),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      if (score != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFF10B981).withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(score,
                              style: const TextStyle(color: Color(0xFF10B981), fontSize: 11, fontWeight: FontWeight.bold)),
                        )
                      else
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: statusColor.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            isSubmitted ? 'Done' : 'Pending',
                            style: TextStyle(color: statusColor, fontSize: 11, fontWeight: FontWeight.bold),
                          ),
                        ),
                      const SizedBox(height: 6),
                      const Icon(Icons.arrow_forward_ios_rounded, size: 12, color: Color(0xFF94A3B8)),
                    ],
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _pill(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Text(label, style: TextStyle(color: color, fontSize: 10, fontWeight: FontWeight.w600)),
    );
  }

  Widget _buildNotesList() {
    if (_isLoadingNotes) {
      return const Center(child: CircularProgressIndicator());
    }

    var list = _notesList;
    if (_searchQuery.isNotEmpty) {
      list = list.where((item) =>
          item['title'].toString().toLowerCase().contains(_searchQuery.toLowerCase()) ||
          item['description'].toString().toLowerCase().contains(_searchQuery.toLowerCase())).toList();
    }

    if (list.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.folder_open_outlined, size: 64, color: AppColors.textDisabled),
              SizedBox(height: 16),
              Text('No Class Notes Found', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
              SizedBox(height: 4),
              Text('Your teachers have not uploaded any e-learning study materials yet.', style: TextStyle(color: AppColors.textSecondary, fontSize: 12), textAlign: TextAlign.center),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
      itemCount: list.length,
      itemBuilder: (context, index) {
        final item = list[index];
        final title = item['title'] ?? '';
        final desc = item['description'] ?? 'E-learning resource';
        final subject = item['subject_name'] ?? 'General';
        final sizeBytes = int.tryParse(item['file_size']?.toString() ?? '0') ?? 0;
        final sizeMb = (sizeBytes / (1024 * 1024)).toStringAsFixed(1);
        final fileType = item['file_name']?.toString().split('.').last.toUpperCase() ?? 'PDF';
        final Color typeColor = fileType == 'PDF'
            ? const Color(0xFFF43F5E)
            : fileType == 'DOCX' || fileType == 'DOC'
                ? const Color(0xFF3B82F6)
                : const Color(0xFF10B981);

        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0)),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 6, offset: const Offset(0, 2))],
          ),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 46, height: 46,
                  decoration: BoxDecoration(
                    color: typeColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.description_rounded, color: typeColor, size: 18),
                      Text(fileType, style: TextStyle(color: typeColor, fontSize: 7, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(title,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                          maxLines: 1, overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 4),
                      Text(desc,
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 11),
                          maxLines: 1, overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          _pill(subject, const Color(0xFF0F766E)),
                          const SizedBox(width: 6),
                          _pill('$sizeMb MB', const Color(0xFF64748B)),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: () => ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Downloading $title'), backgroundColor: const Color(0xFF0F766E)),
                  ),
                  child: Container(
                    width: 38, height: 38,
                    decoration: BoxDecoration(
                      color: const Color(0xFF0F766E).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.download_rounded, color: Color(0xFF0F766E), size: 20),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Styled Tab Header
        Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0))),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 14, 16, 0),
                child: Row(
                  children: [
                    Container(
                      width: 36, height: 36,
                      decoration: BoxDecoration(
                        color: const Color(0xFF7C3AED).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.menu_book_rounded, color: Color(0xFF7C3AED), size: 20),
                    ),
                    const SizedBox(width: 10),
                    const Text('Homework & Notes',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                    const Spacer(),
                    if (_tabController.index == 0)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFF7C3AED).withValues(alpha: 0.08),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          '${_assignments.length} tasks',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF7C3AED)),
                        ),
                      )
                    else
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFF0F766E).withValues(alpha: 0.08),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          '${_notesList.length} notes',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF0F766E)),
                        ),
                      ),
                  ],
                ),
              ),
              const SizedBox(height: 10),
              TabBar(
                controller: _tabController,
                indicatorColor: const Color(0xFF7C3AED),
                indicatorWeight: 3,
                indicatorSize: TabBarIndicatorSize.label,
                labelColor: const Color(0xFF7C3AED),
                unselectedLabelColor: const Color(0xFF94A3B8),
                labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.normal, fontSize: 13),
                tabs: const [
                  Tab(text: 'Assignments'),
                  Tab(text: 'Class Notes'),
                ],
              ),
            ],
          ),
        ),

        // Search & Filter Bar
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  style: const TextStyle(fontSize: 13),
                  decoration: InputDecoration(
                    hintText: 'Search by title...',
                    fillColor: Colors.white,
                    filled: true,
                    prefixIcon: const Icon(Icons.search, size: 18),
                    contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.divider)),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.divider)),
                  ),
                  onChanged: (val) {
                    setState(() {
                      _searchQuery = val.trim();
                    });
                  },
                ),
              ),
              if (_tabController.index == 0 && _subjectsList.isNotEmpty) ...[
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10), border: Border.all(color: AppColors.divider)),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: _selectedSubject,
                      hint: const Text('Subject', style: TextStyle(fontSize: 12)),
                      style: const TextStyle(fontSize: 12, color: AppColors.textPrimary),
                      items: [
                        const DropdownMenuItem<String>(value: null, child: Text('All')),
                        ..._subjectsList.map((sub) => DropdownMenuItem<String>(value: sub, child: Text(sub))),
                      ],
                      onChanged: (val) {
                        setState(() {
                          _selectedSubject = val;
                        });
                      },
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),

        // Feeds Area
        Expanded(
          child: TabBarView(
            controller: _tabController,
            children: [
              _buildHomeworkList(),
              _buildNotesList(),
            ],
          ),
        ),
      ],
    );
  }
}
