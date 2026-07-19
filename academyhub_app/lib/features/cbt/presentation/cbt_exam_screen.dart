import 'dart:async';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
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
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to start exam: $e'), backgroundColor: AppColors.dangerRed),
      );
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
      if (response.statusCode == 200 && response.data != null) {
        final score = response.data['score'];
        final percent = response.data['percent'];
        
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
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to submit exam: $e'), backgroundColor: AppColors.dangerRed),
      );
      // Resume timer
      _startTimer();
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Widget _buildExamList() {
    if (_availableExams.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.quiz_outlined, size: 64, color: AppColors.slate400),
              const SizedBox(height: 16),
              const Text(
                'No CBT Exams Available',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 8),
              const Text(
                'There are no live computer-based tests scheduled for your class at the moment.',
                style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _availableExams.length,
      itemBuilder: (context, idx) {
        final exam = _availableExams[idx];
        final title = exam['title'] ?? 'Exam';
        final subject = exam['subject'] ?? 'General';
        final duration = exam['duration_minutes'] ?? 0;
        final state = exam['state'] ?? 'not_started';
        final isFinished = state == 'submitted' || state == 'terminated';

        return Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: AppColors.amberPrimary.withOpacity(0.12),
              child: const Icon(Icons.timer_outlined, color: AppColors.amberPrimary),
            ),
            title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text('$subject · $duration mins'),
            trailing: isFinished
                ? Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.successGreen.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text('SUBMITTED', style: TextStyle(color: AppColors.successGreen, fontSize: 11, fontWeight: FontWeight.bold)),
                  )
                : const Icon(Icons.play_circle_outline, color: AppColors.amberPrimary),
            onTap: isFinished ? null : () => _startExam(exam['id']),
          ),
        );
      },
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
        Container(
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

        // Sticky Top Bar
        Container(
          color: Colors.white,
          padding: const EdgeInsets.all(16),
          decoration: const BoxDecoration(
            border: Border(bottom: BorderSide(color: AppColors.divider)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _activeExam?['title'] ?? 'CBT Exam',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textPrimary),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Question $questionNumber of $totalQuestions',
                      style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.red.shade300, width: 1.5),
                ),
                child: Row(
                  children: [
                    Icon(Icons.alarm, size: 16, color: Colors.red.shade600),
                    const SizedBox(width: 4),
                    Text(
                      '${_formatTime(_secondsRemaining)} remaining',
                      style: TextStyle(color: Colors.red.shade600, fontSize: 12, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),

        // Main Question Card Area
        Expanded(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Card(
              color: Colors.white,
              elevation: 2,
              shadowColor: Colors.black.withOpacity(0.03),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              child: Padding(
                padding: const EdgeInsets.all(20.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(
                      text,
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary, height: 1.5),
                    ),
                    const SizedBox(height: 24),
                    ...List.generate(options.length, (idx) {
                      final label = String.fromCharCode(65 + idx); // A, B, C, D
                      return _buildOptionRow(qId, options[idx], label);
                    }),
                  ],
                ),
              ),
            ),
          ),
        ),

        // Bottom Navigation Area
        Container(
          color: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          decoration: const BoxDecoration(
            border: Border(top: BorderSide(color: AppColors.divider)),
          ),
          child: Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: AppColors.slate400),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                  onPressed: _currentQuestionIndex > 0
                      ? () {
                          setState(() {
                            _currentQuestionIndex--;
                          });
                        }
                      : null,
                  child: const Text('Previous', style: TextStyle(color: AppColors.textPrimary)),
                ),
              ),
              const SizedBox(width: 12),
              if (!isLastQuestion)
                Expanded(
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.amberPrimary,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    onPressed: () {
                      setState(() {
                        _currentQuestionIndex++;
                      });
                    },
                    child: const Text('Next', style: TextStyle(color: Colors.white)),
                  ),
                )
              else
                Expanded(
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.dangerRed,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    onPressed: _isLoading ? null : _submitExam,
                    child: const Text('SUBMIT EXAM', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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
      appBar: AppBar(
        title: Text(_examStarted ? 'CBT Exam Arena' : 'My CBT Exams'),
        leading: _examStarted
            ? null
            : IconButton(
                icon: const Icon(Icons.arrow_back),
                onPressed: () => Navigator.pop(context),
              ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _examStarted
              ? _buildExamArena()
              : _buildExamList(),
    );
  }
}
