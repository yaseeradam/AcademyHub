import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';

class CbtExamScreen extends StatefulWidget {
  final Map<String, dynamic> exam;
  const CbtExamScreen({super.key, required this.exam});

  @override
  State<CbtExamScreen> createState() => _CbtExamScreenState();
}

class _CbtExamScreenState extends State<CbtExamScreen> {
  final _db = DatabaseHelper();
  bool _loading = true;
  List<Map<String, dynamic>> _questions = [];
  Map<int, String> _selectedAnswers = {};
  
  int _currentQuestionIndex = 0;
  Timer? _countdownTimer;
  int _secondsRemaining = 0;
  int _localAttemptId = 0;
  DateTime? _startTime;

  @override
  void initState() {
    super.initState();
    _initializeExam();
  }

  @override
  void dispose() {
    _countdownTimer?.cancel();
    super.dispose();
  }

  Future<void> _initializeExam() async {
    final auth = context.read<AuthProvider>();
    final studentId = auth.user?.id ?? 0;
    final examId = widget.exam['id'] as int;

    // Fetch questions from cache
    _questions = await _db.getCbtQuestions(examId);

    // Look for unfinished attempt
    final activeAttempt = await _db.getActiveCbtAttempt(examId, studentId);
    
    if (activeAttempt != null) {
      _localAttemptId = activeAttempt['id'] as int;
      _startTime = DateTime.parse(activeAttempt['started_at'] as String);
      _selectedAnswers = Map<int, String>.from(activeAttempt['answers'] ?? {});
      
      final elapsed = DateTime.now().difference(_startTime!).inSeconds;
      final totalSeconds = (widget.exam['duration_minutes'] as int) * 60;
      _secondsRemaining = totalSeconds - elapsed;
    } else {
      // Start a brand-new attempt
      _startTime = DateTime.now();
      _secondsRemaining = (widget.exam['duration_minutes'] as int) * 60;
      
      _localAttemptId = await _db.saveCbtAttemptLocally(
        examId: examId,
        studentId: studentId,
        startedAt: _startTime!.toIso8601String(),
        answers: {},
      );
    }

    if (_secondsRemaining <= 0) {
      _secondsRemaining = 0;
      _autoSubmit();
    } else {
      _startTimer();
    }

    setState(() => _loading = false);
  }

  void _startTimer() {
    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      setState(() {
        if (_secondsRemaining > 0) {
          _secondsRemaining--;
          // Save choices to SQLite periodically (every 10 seconds)
          if (_secondsRemaining % 10 == 0) {
            _saveProgress();
          }
        } else {
          timer.cancel();
          _autoSubmit();
        }
      });
    });
  }

  Future<void> _saveProgress() async {
    final auth = context.read<AuthProvider>();
    await _db.saveCbtAttemptLocally(
      examId: widget.exam['id'] as int,
      studentId: auth.user?.id ?? 0,
      startedAt: _startTime!.toIso8601String(),
      answers: _selectedAnswers,
    );
  }

  Future<void> _selectAnswer(String option) async {
    final questionId = _questions[_currentQuestionIndex]['id'] as int;
    setState(() {
      _selectedAnswers[questionId] = option;
    });
    await _saveProgress();
  }

  double _calculateScore() {
    int correctCount = 0;
    for (final q in _questions) {
      final qId = q['id'] as int;
      // We assume correct options or check matching correct answers
      // If correct option is missing, default score is 0
      final correctOption = q['correct_option'] ?? '';
      if (_selectedAnswers[qId] == correctOption && correctOption.isNotEmpty) {
        correctCount++;
      }
    }
    if (_questions.isEmpty) return 0.0;
    return (correctCount / _questions.length) * 100.0;
  }

  Future<void> _submitAttempt() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Submit Exam'),
        content: const Text('Are you sure you want to finish and submit your exam?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Go Back'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Submit'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      await _executeSubmission();
    }
  }

  Future<void> _autoSubmit() async {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Time expired! Submitting your answers automatically...'),
        backgroundColor: Color(0xFFEF4444),
      ),
    );
    await _executeSubmission();
  }

  Future<void> _executeSubmission() async {
    final auth = context.read<AuthProvider>();
    _countdownTimer?.cancel();
    setState(() => _loading = true);

    final score = _calculateScore();
    final submitTime = DateTime.now().toIso8601String();

    // Mark submitted locally
    await _db.submitCbtAttemptLocally(_localAttemptId, submitTime, _selectedAnswers, score);

    // Notify sync service to upload
    await auth.syncService.notifyDirty();
    await auth.syncService.syncNow();

    if (mounted) {
      Navigator.pop(context);
      _showResultDialog(score);
    }
  }

  void _showResultDialog(double score) {
    final passingScore = widget.exam['pass_percentage'] as double? ?? 50.0;
    final passed = score >= passingScore;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(passed ? 'Congratulations!' : 'Exam Completed'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              passed ? Icons.emoji_events_rounded : Icons.info_outline_rounded,
              size: 72,
              color: passed ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
            ),
            const SizedBox(height: 16),
            Text(
              'Your Score: ${score.toStringAsFixed(1)}%',
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(
              passed ? 'You passed the exam!' : 'You did not satisfy the pass percentage of $passingScore%.',
              textAlign: TextAlign.center,
              style: const TextStyle(color: Color(0xFF64748B)),
            ),
          ],
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  String _formatDuration(int totalSeconds) {
    final minutes = totalSeconds ~/ 60;
    final seconds = totalSeconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (_questions.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: Text(widget.exam['title'])),
        body: const Center(child: Text('No questions available in this exam.')),
      );
    }

    final activeQuestion = _questions[_currentQuestionIndex];
    final questionId = activeQuestion['id'] as int;
    final options = (activeQuestion['options'] as List?)?.cast<String>() ?? [];
    final currentChoice = _selectedAnswers[questionId];

    final isTimeCritical = _secondsRemaining < 300; // under 5 mins
    final auth = context.read<AuthProvider>();
    final primary = auth.tenantPrimaryColor;

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (didPop) return;
        final leave = await showDialog<bool>(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Pause Exam?'),
            content: const Text(
              'You can pause the exam. Your selected answers are saved locally. However, the exam duration timer will continue running.',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('Keep Working'),
              ),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                child: const Text('Pause & Exit'),
              ),
            ],
          ),
        );
        if (leave == true && context.mounted) {
          Navigator.pop(context);
        }
      },
      child: Scaffold(
        appBar: AppBar(
          title: Text(widget.exam['title']),
          leading: IconButton(
            icon: const Icon(Icons.arrow_back),
            onPressed: () => Navigator.maybePop(context),
          ),
          automaticallyImplyLeading: false,
          actions: [
            Container(
              margin: const EdgeInsets.only(right: 16),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: isTimeCritical ? const Color(0xFFFEE2E2) : const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: isTimeCritical ? const Color(0xFFFCA5A5) : const Color(0xFFE2E8F0),
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.timer_outlined,
                    size: 16,
                    color: isTimeCritical ? const Color(0xFFEF4444) : const Color(0xFF475569),
                  ),
                  const SizedBox(width: 4),
                  Text(
                    _formatDuration(_secondsRemaining),
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: isTimeCritical ? const Color(0xFFEF4444) : const Color(0xFF475569),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        body: Column(
          children: [
            // Progress Bar
            LinearProgressIndicator(
              value: (_currentQuestionIndex + 1) / _questions.length,
              backgroundColor: const Color(0xFFF1F5F9),
              valueColor: AlwaysStoppedAnimation<Color>(primary),
            ),
            // Question Content Card
            Expanded(
              child: ListView(
                padding: const EdgeInsets.all(20),
                children: [
                  Text(
                    'Question ${_currentQuestionIndex + 1} of ${_questions.length}',
                    style: const TextStyle(
                      color: Color(0xFF64748B),
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    activeQuestion['question_text'] ?? '',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                  const SizedBox(height: 24),
                  // MCQ choices
                  ...options.map((option) {
                    final selected = currentChoice == option;
                    return Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      decoration: BoxDecoration(
                        color: selected ? primary.withValues(alpha: 0.05) : Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: selected ? primary : const Color(0xFFE2E8F0),
                          width: selected ? 2 : 1,
                        ),
                      ),
                      child: ListTile(
                        leading: Container(
                          width: 24,
                          height: 24,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: selected ? primary : const Color(0xFF94A3B8),
                            ),
                          ),
                          alignment: Alignment.center,
                          child: selected
                              ? CircleAvatar(
                                  radius: 6,
                                  backgroundColor: primary,
                                )
                              : null,
                        ),
                        title: Text(
                          option,
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: selected ? FontWeight.bold : FontWeight.normal,
                            color: selected ? const Color(0xFF0F172A) : const Color(0xFF475569),
                          ),
                        ),
                        onTap: () => _selectAnswer(option),
                      ),
                    );
                  }),
                ],
              ),
            ),
            // Bottom Action buttons
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              decoration: const BoxDecoration(
                color: Colors.white,
                border: Border(top: BorderSide(color: Color(0xFFF1F5F9))),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // Prev
                  OutlinedButton(
                    onPressed: _currentQuestionIndex > 0
                        ? () => setState(() => _currentQuestionIndex--)
                        : null,
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    child: const Text('Previous'),
                  ),
                  // Next / Submit
                  _currentQuestionIndex < _questions.length - 1
                      ? ElevatedButton(
                          onPressed: () => setState(() => _currentQuestionIndex++),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: primary,
                            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          child: const Text('Next'),
                        )
                      : ElevatedButton(
                          onPressed: _submitAttempt,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF10B981),
                            padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          child: const Text('Finish Exam'),
                        ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
