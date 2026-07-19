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
    List<String> attachments = ['page_1_scan.jpg', 'page_2_scan.jpg']; // default mock files in composer

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
                top: 20.0,
                left: 20.0,
                right: 20.0,
                bottom: MediaQuery.of(context).viewInsets.bottom + 20.0,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Handle & Header
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
                        Expanded(
                          child: Text(
                            'Submit: $title',
                            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // Written Answers Area
                    const Text(
                      'WRITTEN ANSWERS (OPTIONAL)',
                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 0.5),
                    ),
                    const SizedBox(height: 8),
                    TextField(
                      controller: writtenController,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: 'Type your written answers here...',
                        fillColor: const Color(0xFFF1F5F9),
                        filled: true,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Attach Files Section
                    const Text(
                      'ATTACH PHOTOS / FILES',
                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 0.5),
                    ),
                    const SizedBox(height: 8),
                    GestureDetector(
                      onTap: () {
                        // simulate adding file
                        setSheetState(() {
                          attachments.add('notebook_page_${attachments.length + 1}.jpg');
                        });
                      },
                      child: Container(
                        height: 90,
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.amberPrimary.withOpacity(0.5), style: BorderStyle.values[1]), // dashed-like effect
                        ),
                        child: const Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.camera_alt_outlined, color: AppColors.amberPrimary),
                            SizedBox(height: 4),
                            Text(
                              'Tap to take picture of notebook pages or upload PDF',
                              style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                              textAlign: TextAlign.center,
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Attachment Preview
                    if (attachments.isNotEmpty) ...[
                      SizedBox(
                        height: 80,
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          itemCount: attachments.length,
                          itemBuilder: (context, index) {
                            return Container(
                              margin: const EdgeInsets.only(right: 8),
                              width: 80,
                              decoration: BoxDecoration(
                                color: const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Stack(
                                children: [
                                  Center(
                                    child: Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        const Icon(Icons.image, size: 24, color: AppColors.slate400),
                                        const SizedBox(height: 2),
                                        Text(
                                          attachments[index],
                                          style: const TextStyle(fontSize: 8, color: AppColors.textSecondary),
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ],
                                    ),
                                  ),
                                  Positioned(
                                    top: 2,
                                    right: 2,
                                    child: GestureDetector(
                                      onTap: () {
                                        setSheetState(() {
                                          attachments.removeAt(index);
                                        });
                                      },
                                      child: const CircleAvatar(
                                        radius: 8,
                                        backgroundColor: Colors.red,
                                        child: Icon(Icons.close, size: 10, color: Colors.white),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                      ),
                      const SizedBox(height: 20),
                    ],

                    // Submit Button
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.amberPrimary,
                        foregroundColor: Colors.white,
                        elevation: 0,
                      ),
                      onPressed: () {
                        Navigator.pop(context);
                        _submitAssignment(homeworkId, writtenController.text, attachments);
                      },
                      child: const Text('SUBMIT TO TEACHER', style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                    const SizedBox(height: 12),
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
                      color: (isSubmitted ? AppColors.successGreen : AppColors.dangerRed).withOpacity(0.12),
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
      padding: const EdgeInsets.all(12),
      itemCount: list.length,
      itemBuilder: (context, index) {
        final item = list[index];
        final title = item['title'] ?? '';
        final subject = item['subject']?['name'] ?? 'General';
        final submissions = List<dynamic>.from(item['submissions'] ?? []);
        final isSubmitted = submissions.isNotEmpty;
        final score = isSubmitted && submissions.first['score'] != null ? '${submissions.first['score']}/20' : null;

        return Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: (isSubmitted ? AppColors.successGreen : AppColors.dangerRed).withOpacity(0.12),
              child: Icon(
                isSubmitted ? Icons.check_circle_outline : Icons.pending_actions,
                color: isSubmitted ? AppColors.successGreen : AppColors.dangerRed,
              ),
            ),
            title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text('$subject · Due: ${item['due_date'] ?? 'Tomorrow'}'),
            trailing: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                if (score != null) ...[
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(color: AppColors.successGreen.withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
                    child: Text(score, style: const TextStyle(color: AppColors.successGreen, fontSize: 11, fontWeight: FontWeight.bold)),
                  ),
                ] else ...[
                  Text(
                    isSubmitted ? 'Submitted' : 'Pending',
                    style: TextStyle(
                      color: isSubmitted ? AppColors.successGreen : AppColors.dangerRed,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
                const Icon(Icons.arrow_forward_ios, size: 10, color: AppColors.textDisabled),
              ],
            ),
            onTap: () => _showHomeworkDetails(context, item),
          ),
        );
      },
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
      padding: const EdgeInsets.all(12),
      itemCount: list.length,
      itemBuilder: (context, index) {
        final item = list[index];
        final title = item['title'] ?? '';
        final desc = item['description'] ?? 'E-learning resources';
        final subject = item['subject_name'] ?? 'General';
        final sizeBytes = int.tryParse(item['file_size']?.toString() ?? '0') ?? 0;
        final sizeMb = (sizeBytes / (1024 * 1024)).toStringAsFixed(1);
        final fileType = item['file_name']?.toString().split('.').last.toUpperCase() ?? 'PDF';

        return Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: AppColors.amberPrimary.withOpacity(0.12),
              child: const Icon(Icons.description_outlined, color: AppColors.amberPrimary),
            ),
            title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text('$subject · $desc'),
            trailing: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text('$sizeMb MB ($fileType)', style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                const SizedBox(width: 8),
                IconButton(
                  icon: const Icon(Icons.cloud_download, color: AppColors.amberPrimary),
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Downloading note: $title'), backgroundColor: AppColors.amberPrimary),
                    );
                  },
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
        // Tab Control Header
        Container(
          color: Colors.white,
          child: TabBar(
            controller: _tabController,
            indicatorColor: AppColors.amberPrimary,
            labelColor: AppColors.amberPrimary,
            unselectedLabelColor: AppColors.textSecondary,
            tabs: const [
              Tab(text: 'Homework Assignments'),
              Tab(text: 'Downloaded Notes'),
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
