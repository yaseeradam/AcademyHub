import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/database_helper.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

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

    if (mounted) setState(() => _loading = false);
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
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('Submit Exam', style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontWeight: FontWeight.bold)),
        content: Text('Are you sure you want to finish and submit your exam answers?', style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Cancel', style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.success, foregroundColor: Colors.black),
            child: Text('Submit', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirm == true) {
      await _executeSubmission();
    }
  }

  Future<void> _autoSubmit() async {
    if (mounted) {
      CustomToast.show(
        context: context,
        message: 'Time expired! Submitting your answers...',
        type: 'error',
      );
    }
    await _executeSubmission();
  }

  Future<void> _executeSubmission() async {
    final auth = context.read<AuthProvider>();
    _countdownTimer?.cancel();
    if (mounted) setState(() => _loading = true);

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
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(passed ? 'Congratulations!' : 'Exam Completed', style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              passed ? Icons.emoji_events_rounded : Icons.info_outline_rounded,
              size: 72,
              color: passed ? AppColors.success : AppColors.warning,
            ),
            const SizedBox(height: 16),
            Text(
              'Your Score: ${score.toStringAsFixed(1)}%',
              style: GoogleFonts.spaceGrotesk(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 8),
            Text(
              passed ? 'You passed the exam!' : 'You did not satisfy the pass percentage of $passingScore%.',
              textAlign: TextAlign.center,
              style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary, fontSize: 13),
            ),
          ],
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            style: ElevatedButton.styleFrom(backgroundColor: passed ? AppColors.success : AppColors.studentAccent),
            child: Text('Close', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold, color: Colors.black)),
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
      return Scaffold(
        backgroundColor: AppColors.background,
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_questions.isEmpty) {
      return Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          title: Text(widget.exam['title'] ?? 'CBT Exam'),
          backgroundColor: AppColors.surface,
        ),
        body: Center(
          child: Text(
            'No questions available in this exam.',
            style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary),
          ),
        ),
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
            backgroundColor: AppColors.surface,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: Text('Pause Exam?', style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontWeight: FontWeight.bold)),
            content: Text(
              'You can pause the exam. Your selected answers are saved locally. However, the exam duration timer will continue running.',
              style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: Text('Keep Working', style: GoogleFonts.spaceGrotesk(color: AppColors.textSecondary)),
              ),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                style: ElevatedButton.styleFrom(backgroundColor: AppColors.error, foregroundColor: Colors.white),
                child: Text('Pause & Exit', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        );
        if (leave == true && context.mounted) {
          Navigator.pop(context);
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          title: Text(widget.exam['title'] ?? 'CBT Exam', style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 16)),
          backgroundColor: AppColors.surface,
          foregroundColor: AppColors.textPrimary,
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back_rounded),
            onPressed: () => Navigator.maybePop(context),
          ),
          automaticallyImplyLeading: false,
          actions: [
            Container(
              margin: const EdgeInsets.only(right: 16),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: isTimeCritical 
                    ? AppColors.error.withValues(alpha: 0.12) 
                    : AppColors.surface2,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: isTimeCritical 
                      ? AppColors.error.withValues(alpha: 0.25) 
                      : AppColors.borderLight,
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.timer_outlined,
                    size: 16,
                    color: isTimeCritical ? AppColors.error : AppColors.textSecondary,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    _formatDuration(_secondsRemaining),
                    style: GoogleFonts.spaceGrotesk(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: isTimeCritical ? AppColors.error : AppColors.textPrimary,
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
              backgroundColor: AppColors.surface2,
              valueColor: AlwaysStoppedAnimation<Color>(primary),
              minHeight: 3,
            ),
            // Question Content Card
            Expanded(
              child: ListView(
                padding: const EdgeInsets.all(20),
                children: [
                  Text(
                    'Question ${_currentQuestionIndex + 1} of ${_questions.length}',
                    style: GoogleFonts.spaceGrotesk(
                      color: AppColors.textSecondary,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    activeQuestion['question_text'] ?? '',
                    style: GoogleFonts.spaceGrotesk(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                      height: 1.5,
                    ),
                  ),
                  const SizedBox(height: 24),
                  // MCQ choices
                  ...options.map((option) {
                    final selected = currentChoice == option;
                    return Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      decoration: BoxDecoration(
                        color: selected 
                            ? primary.withValues(alpha: 0.12) 
                            : AppColors.surface,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: selected ? primary : AppColors.borderLight,
                          width: selected ? 1.5 : 1,
                        ),
                      ),
                      child: ListTile(
                        leading: Container(
                          width: 24,
                          height: 24,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: selected ? primary : AppColors.textSecondary,
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
                          style: GoogleFonts.spaceGrotesk(
                            fontSize: 14,
                            fontWeight: selected ? FontWeight.bold : FontWeight.normal,
                            color: selected ? AppColors.textPrimary : AppColors.textSecondary,
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
              decoration: BoxDecoration(
                color: AppColors.surface,
                border: Border(top: BorderSide(color: AppColors.borderLight)),
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
                      side: BorderSide(color: AppColors.borderLight),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    child: Text('Previous', style: GoogleFonts.spaceGrotesk(color: AppColors.textPrimary)),
                  ),
                  // Next / Submit
                  _currentQuestionIndex < _questions.length - 1
                      ? ElevatedButton(
                          onPressed: () => setState(() => _currentQuestionIndex++),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: primary,
                            foregroundColor: Colors.black,
                            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          child: Text('Next', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold)),
                        )
                      : ElevatedButton(
                          onPressed: _submitAttempt,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.success,
                            foregroundColor: Colors.black,
                            padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          child: Text('Finish Exam', style: GoogleFonts.spaceGrotesk(fontWeight: FontWeight.bold)),
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
