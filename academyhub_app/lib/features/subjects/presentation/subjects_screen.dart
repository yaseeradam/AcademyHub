import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';
import 'package:academyhub_app/core/storage/secure_storage.dart';
import 'package:academyhub_app/features/scores/presentation/scores_entry_screen.dart';

class SubjectsScreen extends StatefulWidget {
  final int? classId;
  final String? className;

  const SubjectsScreen({super.key, this.classId, this.className});

  @override
  State<SubjectsScreen> createState() => _SubjectsScreenState();
}

class _SubjectsScreenState extends State<SubjectsScreen> {
  bool _isLoading = true;
  List<dynamic> _subjects = [];
  String _userRole = 'student';

  @override
  void initState() {
    super.initState();
    _fetchSubjects();
  }

  Future<void> _fetchSubjects() async {
    setState(() => _isLoading = true);
    try {
      final role = await SecureStorage.instance.getRole();
      _userRole = role ?? 'student';

      String? endpoint;
      if (_userRole == 'teacher') {
        if (widget.classId != null) {
          endpoint = '/teacher/classes/${widget.classId}/subjects';
        }
        // No classId provided — no valid endpoint for teacher; leave list empty.
      } else {
        endpoint = '/student/results';
      }

      if (endpoint != null) {
        final response = await apiClient.dio.get(endpoint);
        if (response.statusCode == 200 && response.data != null) {
          final data = response.data;
          List<dynamic> list = [];
          if (data is List) {
            list = data;
          } else if (data is Map && data.containsKey('data')) {
            list = List<dynamic>.from(data['data']);
          }
          if (!mounted) return;
          setState(() {
            _subjects = list;
          });
        }
      }
    } catch (e) {
      debugPrint('Error fetching subjects: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final titleText = widget.className != null ? 'Subjects (${widget.className})' : 'Subjects';

    return Scaffold(
      backgroundColor: AppColors.appBackground,
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          titleText,
          style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 18),
        ),
        elevation: 0,
        backgroundColor: const Color(0xFF1E293B),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary))
          : RefreshIndicator(
              onRefresh: _fetchSubjects,
              color: AppColors.amberPrimary,
              child: _subjects.isEmpty
                  ? SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      child: Container(
                        height: MediaQuery.of(context).size.height * 0.7,
                        alignment: Alignment.center,
                        padding: const EdgeInsets.all(24.0),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                              width: 72, height: 72,
                              decoration: BoxDecoration(
                                color: AppColors.amberPrimary.withValues(alpha: 0.12),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.menu_book_rounded, size: 36, color: AppColors.amberPrimary),
                            ),
                            const SizedBox(height: 16),
                            const Text(
                              'No Subjects Available',
                              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'There are no active subjects found for your profile.',
                              style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
                              textAlign: TextAlign.center,
                            ),
                          ],
                        ),
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: _subjects.length,
                      itemBuilder: (context, idx) {
                        final sub = _subjects[idx];
                        final subjectName = sub['subject_name'] ?? sub['name'] ?? 'Subject';
                        final code = sub['code'] ?? 'SUB${idx + 101}';
                        final score = sub['total']?.toString() ?? sub['score']?.toString();
                        final grade = sub['grade'] ?? 'Active';

                        final subjectColors = [
                          const Color(0xFF3B82F6), const Color(0xFF7C3AED), const Color(0xFF10B981),
                          const Color(0xFFF59E0B), const Color(0xFFF43F5E), const Color(0xFF0F766E),
                        ];
                        final color = subjectColors[idx % subjectColors.length];

                        return Container(
                          margin: const EdgeInsets.only(bottom: 14),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.04),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Row(
                              children: [
                                Container(
                                  width: 48, height: 48,
                                  decoration: BoxDecoration(
                                    color: color.withValues(alpha: 0.12),
                                    borderRadius: BorderRadius.circular(14),
                                  ),
                                  child: Icon(Icons.menu_book_rounded, color: color, size: 24),
                                ),
                                const SizedBox(width: 14),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        subjectName,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 15,
                                          color: Color(0xFF0F172A),
                                        ),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        'Code: $code',
                                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                                      ),
                                    ],
                                  ),
                                ),
                                if (_userRole == 'teacher' && widget.classId != null)
                                  ElevatedButton(
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFF1E293B),
                                      foregroundColor: Colors.white,
                                      elevation: 0,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                    ),
                                    onPressed: () {
                                      Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (context) => ScoresEntryScreen(
                                            classId: widget.classId!,
                                            className: widget.className ?? 'Class',
                                            subjectId: sub['id'] ?? 0,
                                            subjectName: subjectName,
                                          ),
                                        ),
                                      );
                                    },
                                    child: const Text('Scores', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                  )
                                else if (score != null)
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: color.withValues(alpha: 0.12),
                                      borderRadius: BorderRadius.circular(20),
                                    ),
                                    child: Text(
                                      'Grade: $grade ($score%)',
                                      style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.bold),
                                    ),
                                  )
                                else
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF1F5F9),
                                      borderRadius: BorderRadius.circular(20),
                                    ),
                                    child: const Text(
                                      'Enrolled',
                                      style: TextStyle(color: Color(0xFF0F172A), fontSize: 11, fontWeight: FontWeight.bold),
                                    ),
                                  ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
