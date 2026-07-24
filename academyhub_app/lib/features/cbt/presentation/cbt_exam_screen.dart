import 'dart:async';
import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';

class CbtExamScreen extends StatefulWidget {
  const CbtExamScreen({super.key});

  @override
  State<CbtExamScreen> createState() => _CbtExamScreenState();
}

class _CbtExamScreenState extends State<CbtExamScreen> {
  bool _isLoading = false;
  List<dynamic> _availableExams = [];
  
  // Active exam session state
  bool _examStarted = false;
  Map<String, dynamic>? _activeExam;
  String? _attemptUuid;
  List<dynamic> _questions = [];
  int _currentQuestionIndex = 0;
  
  // Answers tracking: question_id -> option_id
  final Map<int, int> _selectedAnswers = {};
  
  // Timer state
  Timer? _timer;
  int _secondsRemaining = 0;

  @override
  void initState() {
    super.initState();
    _fetchExams();
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _fetchExams() async {
    setState(() {
      _isLoading = true;
    });
    try {
      final response = await apiClient.dio.get('/student/exams');
      if (response.statusCode == 200 && response.data != null) {
        setState(() {
          _availableExams = List<dynamic>.from(response.data);
        });
      }
    } catch (e) {
      debugPrint('Error fetching exams: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _startExam(int examId) async {
    setState(() {
      _isLoading = true;
    });
    try {
      final response = await apiClient.dio.post('/student/exams/$examId/start');
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        final rawQuestions = List<dynamic>.from(data['questions'] ?? []);
        final durationMinutes = int.tryParse(data['duration_minutes']?.toString() ?? '45') ?? 45;
        
        setState(() {
          _attemptUuid = data['attempt_uuid'];
          _questions = rawQuestions;
          _currentQuestionIndex = 0;
          _selectedAnswers.clear();
          
          // Load existing answers if reconnection occurs
          final existing = List<dynamic>.from(data['existing_answers'] ?? []);
          for (var answer in existing) {
            final qId = int.tryParse(answer['question_id']?.toString() ?? '');
            final oId = int.tryParse(answer['option_id']?.toString() ?? '');
            if (qId != null && oId != null) {
              _selectedAnswers[qId] = oId;
            }
          }

          _secondsRemaining = durationMinutes * 60;
          _examStarted = true;
          _activeExam = _availableExams.firstWhere((e) => e['id'] == examId);
        });

        _startTimer();
      }
    } catch (e) {
      if (!mounted) return;
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to start exam: $e'), backgroundColor: AppColors.dangerRed),
        );
      }
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _startTimer() {
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_secondsRemaining <= 0) {
        timer.cancel();
        _autoSubmit();
      } else {
        setState(() {
          _secondsRemaining--;
        });
      }
    });
  }

  String _formatTime(int totalSeconds) {
    final minutes = totalSeconds ~/ 60;
    final seconds = totalSeconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  Future<void> _autoSubmit() async {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Time expired! Submitting your exam automatically.'), backgroundColor: AppColors.warningOrange),
    );
    await _submitExam();
  }

  Future<void> _submitExam() async {
    if (_attemptUuid == null) return;
    
    _timer?.cancel();
    setState(() {
      _isLoading = true;
    });

    // Format answers map as expected question_id => option_id
    final Map<String, int> answersPayload = {};
    _selectedAnswers.forEach((qId, oId) {
      answersPayload[qId.toString()] = oId;
    });

    try {
      final response = await apiClient.dio.post(
        '/student/exams/$_attemptUuid/submit',
        data: {'answers': answersPayload},
      );
      if (!mounted) return;
      if (response.statusCode == 200 && response.data != null) {
        final score = response.data['score'];
        final percent = response.data['percent'];
        
        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (context) => AlertDialog(
              title: const Text('Exam Completed!'),
              content: Text(score != null 
                  ? 'Your exam was successfully submitted.\nScore: $score% ($percent%)' 
                  : 'Your exam was successfully submitted to school servers.'),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.pop(context); // Close dialog
                    setState(() {
                      _examStarted = false;
                      _activeExam = null;
                      _attemptUuid = null;
                      _questions.clear();
                    });
                    _fetchExams();
                  },
                  child: const Text('Back to Exams'),
                )
              ],
            ),
          );
        }
      }
    } catch (e) {
      if (!mounted) return;
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to submit exam: $e'), backgroundColor: AppColors.dangerRed),
        );
      }
      // Resume timer
      _startTimer();
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _confirmExit() {
    if (!_examStarted) {
      Navigator.pop(context);
      return;
    }
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.exit_to_app_rounded, color: Color(0xFFF97316), size: 24),
            SizedBox(width: 8),
            Text('Exit Exam?', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
          ],
        ),
        content: const Text(
          'Are you sure you want to leave the exam arena? Your current progress and answers are saved.',
          style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Stay', style: TextStyle(color: Color(0xFF64748B))),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFF97316),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              elevation: 0,
            ),
            onPressed: () {
              Navigator.pop(context);
              setState(() {
                _examStarted = false;
                _timer?.cancel();
              });
            },
            child: const Text('Exit Exam', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _confirmSubmit() {
    final unanswered = _questions.length - _selectedAnswers.length;
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Color(0xFFF43F5E), size: 24),
            SizedBox(width: 8),
            Text('Submit Exam?', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Text(
          unanswered > 0
              ? 'You have $unanswered unanswered question(s). Are you sure you want to submit?'
              : 'You have answered all questions. Ready to submit?',
          style: const TextStyle(fontSize: 13, color: Color(0xFF64748B)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel', style: TextStyle(color: Color(0xFF64748B))),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFF43F5E),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              elevation: 0,
            ),
            onPressed: () {
              Navigator.pop(context);
              _submitExam();
            },
            child: const Text('Submit', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Widget _buildExamList() {
    if (_availableExams.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 80, height: 80,
                decoration: BoxDecoration(
                  color: const Color(0xFFF59E0B).withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.quiz_rounded, size: 40, color: Color(0xFFF59E0B)),
              ),
              const SizedBox(height: 20),
              const Text('No CBT Exams Available',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
              const SizedBox(height: 8),
              const Text(
                'There are no live computer-based tests scheduled for your class at the moment.',
                style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      );
    }

    final subjectColors = [
      const Color(0xFF3B82F6), const Color(0xFF7C3AED), const Color(0xFF10B981),
      const Color(0xFFF59E0B), const Color(0xFFF43F5E), const Color(0xFF0F766E),
    ];

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 16),
      itemCount: _availableExams.length,
      itemBuilder: (context, idx) {
              final exam = _availableExams[idx];
              final title = exam['title'] ?? 'Exam';
              final subject = exam['subject'] ?? 'General';
              final duration = exam['duration_minutes'] ?? 0;
              final state = exam['state'] ?? 'not_started';
              final isFinished = state == 'submitted' || state == 'terminated';
              final color = subjectColors[idx % subjectColors.length];

              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: color.withValues(alpha: 0.2)),
                  boxShadow: [BoxShadow(color: color.withValues(alpha: 0.08), blurRadius: 10, offset: const Offset(0, 4))],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      Container(
                        width: 50, height: 50,
                        decoration: BoxDecoration(
                          color: color.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Icon(Icons.quiz_rounded, color: color, size: 26),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(title,
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
                                maxLines: 1, overflow: TextOverflow.ellipsis),
                            const SizedBox(height: 6),
                            Row(
                              children: [
                                _examPill(subject, color),
                                const SizedBox(width: 6),
                                _examPill('$duration mins', const Color(0xFF64748B)),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                      isFinished
                          ? Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              decoration: BoxDecoration(
                                color: const Color(0xFF10B981).withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: const Text('DONE',
                                  style: TextStyle(color: Color(0xFF10B981), fontSize: 11, fontWeight: FontWeight.bold)),
                            )
                          : GestureDetector(
                              onTap: () => _startExam(exam['id']),
                              child: Container(
                                width: 44, height: 44,
                                decoration: BoxDecoration(
                                  color: color,
                                  borderRadius: BorderRadius.circular(12),
                                  boxShadow: [BoxShadow(color: color.withValues(alpha: 0.35), blurRadius: 8, offset: const Offset(0, 3))],
                                ),
                                child: const Icon(Icons.play_arrow_rounded, color: Colors.white, size: 24),
                              ),
                            ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _examPill(String label, Color color) {
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

  Widget _buildOptionRow(int questionId, dynamic option, String label) {
    final optionId = option['id'];
    final optionText = option['option_text'] ?? '';
    final isSelected = _selectedAnswers[questionId] == optionId;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: OutlinedButton(
        style: OutlinedButton.styleFrom(
          backgroundColor: isSelected ? const Color(0xFFFEF3C7) : Colors.white,
          side: BorderSide(
            color: isSelected ? AppColors.amberPrimary : const Color(0xFFE2E8F0),
            width: isSelected ? 1.5 : 1.0,
          ),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        ),
        onPressed: () {
          setState(() {
            _selectedAnswers[questionId] = optionId;
          });
        },
        child: Row(
          children: [
            CircleAvatar(
              radius: 12,
              backgroundColor: isSelected ? AppColors.amberPrimary : const Color(0xFFF1F5F9),
              child: Text(
                label,
                style: TextStyle(
                  color: isSelected ? Colors.white : AppColors.textSecondary,
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                optionText,
                style: TextStyle(
                  color: AppColors.textPrimary,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                  fontSize: 14,
                ),
              ),
            ),
            if (isSelected)
              const Icon(Icons.check, color: AppColors.amberPrimary, size: 18),
          ],
        ),
      ),
    );
  }

  Widget _buildExamArena() {
    if (_questions.isEmpty) {
      return const Center(child: Text('No questions found in this exam.'));
    }

    final question = _questions[_currentQuestionIndex];
    final qId = question['id'] as int;
    final text = question['question_text'] ?? '';
    final options = List<dynamic>.from(question['options'] ?? []);
    final totalQuestions = _questions.length;
    final questionNumber = _currentQuestionIndex + 1;
    final isLastQuestion = _currentQuestionIndex == totalQuestions - 1;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Lockdown Banner
        SafeArea(
          bottom: false,
          child: Container(
            color: const Color(0xFFFEE2E2),
            padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.lock_outline, size: 16, color: Colors.red.shade700),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Secure Exam Mode Active. App switching will invalidate your test.',
                    style: TextStyle(color: Colors.red.shade700, fontSize: 11, fontWeight: FontWeight.bold),
                    textAlign: TextAlign.center,
                  ),
                ),
              ],
            ),
          ),
        ),

        // Sticky Top Bar
        Container(
          color: Colors.white,
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
          decoration: const BoxDecoration(
            border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0))),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _activeExam?['title'] ?? 'CBT Exam',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
                          maxLines: 1, overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Question $questionNumber of $totalQuestions · ${_selectedAnswers.length} answered',
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                  // Timer pill
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 300),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                    decoration: BoxDecoration(
                      color: _secondsRemaining < 120
                          ? const Color(0xFFF43F5E)
                          : _secondsRemaining < 300
                              ? const Color(0xFFF97316)
                              : const Color(0xFF0F172A),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.alarm_rounded, size: 14, color: Colors.white),
                        const SizedBox(width: 5),
                        Text(
                          _formatTime(_secondsRemaining),
                          style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              // Progress bar
              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: LinearProgressIndicator(
                  value: totalQuestions > 0 ? questionNumber / totalQuestions : 0,
                  minHeight: 5,
                  backgroundColor: const Color(0xFFE2E8F0),
                  color: const Color(0xFFF59E0B),
                ),
              ),
              const SizedBox(height: 10),
            ],
          ),
        ),

        // Main Question Card Area
        Expanded(
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Question number badge + text
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                    boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 3))],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF59E0B).withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              'Q$questionNumber',
                              style: const TextStyle(color: Color(0xFFF59E0B), fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            _activeExam?['subject'] ?? '',
                            style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      Text(
                        text,
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: Color(0xFF0F172A), height: 1.5),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),
                // Options
                ...List.generate(options.length, (idx) {
                  final label = String.fromCharCode(65 + idx);
                  return _buildOptionRow(qId, options[idx], label);
                }),
              ],
            ),
          ),
        ),

        // Bottom Navigation Area
        Container(
          color: Colors.white,
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
          decoration: const BoxDecoration(
            border: Border(top: BorderSide(color: Color(0xFFE2E8F0))),
          ),
          child: Row(
            children: [
              // Previous
              GestureDetector(
                onTap: _currentQuestionIndex > 0
                    ? () => setState(() => _currentQuestionIndex--)
                    : null,
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: 48, height: 48,
                  decoration: BoxDecoration(
                    color: _currentQuestionIndex > 0
                        ? const Color(0xFFF1F5F9)
                        : const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Icon(
                    Icons.arrow_back_rounded,
                    color: _currentQuestionIndex > 0
                        ? const Color(0xFF0F172A)
                        : const Color(0xFFCBD5E1),
                    size: 20,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              // Question dots (mini navigator)
              Expanded(
                child: SizedBox(
                  height: 8,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: totalQuestions,
                    itemBuilder: (context, i) {
                      final isCurrent = i == _currentQuestionIndex;
                      final isAnswered = _selectedAnswers.containsKey(
                          (_questions[i]['id'] as int));
                      return GestureDetector(
                        onTap: () => setState(() => _currentQuestionIndex = i),
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 200),
                          margin: const EdgeInsets.only(right: 4),
                          width: isCurrent ? 20 : 8,
                          height: 8,
                          decoration: BoxDecoration(
                            color: isCurrent
                                ? const Color(0xFFF59E0B)
                                : isAnswered
                                    ? const Color(0xFF10B981)
                                    : const Color(0xFFE2E8F0),
                            borderRadius: BorderRadius.circular(4),
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ),
              const SizedBox(width: 12),
              // Next or Submit
              if (!isLastQuestion)
                GestureDetector(
                  onTap: () => setState(() => _currentQuestionIndex++),
                  child: Container(
                    width: 48, height: 48,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF59E0B),
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [BoxShadow(color: const Color(0xFFF59E0B).withValues(alpha: 0.35), blurRadius: 8, offset: const Offset(0, 3))],
                    ),
                    child: const Icon(Icons.arrow_forward_rounded, color: Colors.white, size: 20),
                  ),
                )
              else
                GestureDetector(
                  onTap: _isLoading ? null : _confirmSubmit,
                  child: Container(
                    height: 48,
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF43F5E),
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [BoxShadow(color: const Color(0xFFF43F5E).withValues(alpha: 0.35), blurRadius: 8, offset: const Offset(0, 3))],
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.send_rounded, color: Colors.white, size: 16),
                        SizedBox(width: 6),
                        Text('SUBMIT', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
                      ],
                    ),
                  ),
                ),
            ],
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.appBackground,
      appBar: null,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _examStarted
              ? _buildExamArena()
              : _buildExamList(),
    );
  }
}
