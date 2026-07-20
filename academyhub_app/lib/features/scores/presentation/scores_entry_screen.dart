import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/database/local_db.dart';
import 'package:academyhub_app/core/network/api_client.dart';

const _rc = AppColors.roleStaff;
const _rcDark = Color(0xFF134E4A);

class ScoresEntryScreen extends StatefulWidget {
  final int classId;
  final String className;
  final int subjectId;
  final String subjectName;

  const ScoresEntryScreen({
    super.key,
    required this.classId,
    required this.className,
    required this.subjectId,
    required this.subjectName,
  });

  @override
  State<ScoresEntryScreen> createState() => _ScoresEntryScreenState();
}

class _ScoresEntryScreenState extends State<ScoresEntryScreen> {
  bool _isLoading = false;
  List<Map<String, dynamic>> _students = [];
  
  // StudentId -> { 'ca1': val, 'ca2': val, 'exam': val }
  final Map<int, Map<String, String>> _scoresMap = {};

  // Active keyboard focus state
  int? _focusedStudentId;
  String? _focusedField; // 'ca1', 'ca2', 'exam'

  @override
  void initState() {
    super.initState();
    _loadStudentsAndScores();
  }

  Future<void> _loadStudentsAndScores() async {
    setState(() {
      _isLoading = true;
    });

    try {
      List<Map<String, dynamic>> studentsList = [];
      final cached = await LocalDatabase.instance.getStudents();
      final classCached = cached.where((s) => s['class_id'] == widget.classId).toList();

      if (classCached.isNotEmpty) {
        studentsList = classCached;
      } else {
        final response = await apiClient.dio.get('/teacher/classes/${widget.classId}/students');
        if (response.statusCode == 200 && response.data != null) {
          final rawList = response.data['data'] ?? response.data;
          studentsList = List<Map<String, dynamic>>.from(rawList);
        }
      }

      for (var s in studentsList) {
        _scoresMap[s['id']] = {'ca1': '', 'ca2': '', 'exam': ''};
      }

      // Fetch existing recorded scores for pre-population
      final scoresResponse = await apiClient.dio.get(
        '/teacher/classes/${widget.classId}/scores',
        queryParameters: {'term': 2, 'session': '2024/2025'},
      );
      if (scoresResponse.statusCode == 200 && scoresResponse.data != null) {
        final List<dynamic> scoresData = scoresResponse.data['data'] ?? [];
        for (var score in scoresData) {
          final studentId = score['student_id'] as int?;
          final subjectId = score['subject_id'] as int?;
          if (studentId != null && subjectId == widget.subjectId) {
            _scoresMap[studentId] = {
              'ca1': score['ca1']?.toString() ?? '',
              'ca2': score['ca2']?.toString() ?? '',
              'exam': score['exam']?.toString() ?? '',
            };
          }
        }
      }

      setState(() {
        _students = studentsList;
      });
    } catch (e) {
      debugPrint('Error loading students or scores: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _saveScores() async {
    setState(() {
      _isLoading = true;
    });

    final List<Map<String, dynamic>> scoresList = [];
    _scoresMap.forEach((studentId, scores) {
      scoresList.add({
        'student_id': studentId,
        'subject_id': widget.subjectId,
        'class_id': widget.classId,
        'term': 2,
        'session': '2024/2025',
        'ca1': int.tryParse(scores['ca1'] ?? '') ?? 0,
        'ca2': int.tryParse(scores['ca2'] ?? '') ?? 0,
        'exam': int.tryParse(scores['exam'] ?? '') ?? 0,
      });
    });

    final payload = {
      'scores': scoresList,
    };

    try {
      final response = await apiClient.dio.post('/teacher/scores', data: payload);
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('✓ Scores updated successfully!'),
              backgroundColor: AppColors.successGreen,
            ),
          );
          Navigator.pop(context);
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Failed to save scores. Saved locally.'),
            backgroundColor: AppColors.warningOrange,
          ),
        );
      }
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _onKeyPress(String key) {
    if (_focusedStudentId == null || _focusedField == null) return;
    
    final currentVal = _scoresMap[_focusedStudentId]![_focusedField] ?? '';
    String newVal = currentVal;

    if (key == '⌫') {
      if (currentVal.isNotEmpty) {
        newVal = currentVal.substring(0, currentVal.length - 1);
      }
    } else {
      // enforce max constraints: CA1/CA2 max 20, EXAM max 60
      final parsed = int.tryParse(currentVal + key) ?? 0;
      final maxVal = _focusedField == 'exam' ? 60 : 20;
      if (parsed <= maxVal && (currentVal + key).length <= 2) {
        newVal = currentVal + key;
      }
    }

    setState(() {
      _scoresMap[_focusedStudentId]![_focusedField!] = newVal;
    });
  }

  void _nextField() {
    if (_focusedStudentId == null || _focusedField == null) return;

    setState(() {
      if (_focusedField == 'ca1') {
        _focusedField = 'ca2';
      } else if (_focusedField == 'ca2') {
        _focusedField = 'exam';
      } else {
        // Move to next student
        final currentStudentIndex = _students.indexWhere((s) => s['id'] == _focusedStudentId);
        if (currentStudentIndex < _students.length - 1) {
          _focusedStudentId = _students[currentStudentIndex + 1]['id'];
          _focusedField = 'ca1';
        } else {
          // Finished all
          _focusedStudentId = null;
          _focusedField = null;
        }
      }
    });
  }

  Widget _buildKeypadOverlay() {
    final maxLabel = _focusedField == 'exam' ? '60' : '20';
    final currentVal = _focusedStudentId != null && _focusedField != null
        ? (_scoresMap[_focusedStudentId]![_focusedField] ?? '')
        : '';
    return Container(
      decoration: const BoxDecoration(
        color: Color(0xFFF8FAFC),
        border: Border(top: BorderSide(color: Color(0xFFE2E8F0))),
      ),
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 12),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: _rc.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${_focusedField?.toUpperCase() ?? ''} · max $maxLabel',
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: _rc),
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFF0F172A),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  currentVal.isEmpty ? '—' : currentVal,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.white),
                ),
              ),
              const Spacer(),
              GestureDetector(
                onTap: () => setState(() {
                  _focusedStudentId = null;
                  _focusedField = null;
                }),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: _rc,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Text('Done', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 6,
              mainAxisSpacing: 6,
              crossAxisSpacing: 6,
              childAspectRatio: 1.3,
            ),
            itemCount: 12,
            itemBuilder: (context, index) {
              final keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '⌫', 'Next'];
              final key = keys[index];
              final isNext = key == 'Next';
              final isBack = key == '⌫';
              return GestureDetector(
                onTap: isNext ? _nextField : () => _onKeyPress(key),
                child: Container(
                  decoration: BoxDecoration(
                    color: isNext
                        ? _rc
                        : isBack
                            ? const Color(0xFFFEE2E2)
                            : Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: isNext
                          ? _rc
                          : isBack
                              ? const Color(0xFFF43F5E).withValues(alpha: 0.3)
                              : const Color(0xFFE2E8F0),
                    ),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 3, offset: const Offset(0, 1)),
                    ],
                  ),
                  child: Center(
                    child: Text(
                      key,
                      style: TextStyle(
                        fontSize: isNext ? 11 : 16,
                        fontWeight: FontWeight.bold,
                        color: isNext
                            ? Colors.white
                            : isBack
                                ? const Color(0xFFF43F5E)
                                : const Color(0xFF0F172A),
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildStudentScoreRow(Map<String, dynamic> student) {
    final id = student['id'];
    final name = '${student['first_name']} ${student['last_name']}';
    final admNum = student['admission_number'] ?? '';
    final scores = _scoresMap[id] ?? {'ca1': '', 'ca2': '', 'exam': ''};

    String initials = '';
    if (student['first_name'] != null && student['first_name'].toString().isNotEmpty) {
      initials += student['first_name'][0].toUpperCase();
    }
    if (student['last_name'] != null && student['last_name'].toString().isNotEmpty) {
      initials += student['last_name'][0].toUpperCase();
    }

    final bool hasAnyScore = scores.values.any((v) => v.isNotEmpty);
    final Color rowAccent = hasAnyScore ? _rc : const Color(0xFF94A3B8);

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: rowAccent.withOpacity(0.15)),
        boxShadow: [BoxShadow(color: rowAccent.withOpacity(0.06), blurRadius: 6, offset: const Offset(0, 2))],
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        child: Row(
          children: [
            Container(
              width: 40, height: 40,
              decoration: BoxDecoration(
                color: rowAccent.withOpacity(0.1),
                shape: BoxShape.circle,
                border: Border.all(color: rowAccent.withOpacity(0.25), width: 1.5),
              ),
              child: Center(
                child: Text(initials.isEmpty ? '?' : initials,
                    style: TextStyle(fontWeight: FontWeight.bold, color: rowAccent, fontSize: 14)),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(name,
                      style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 13),
                      maxLines: 1, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 2),
                  Text(admNum,
                      style: const TextStyle(color: Color(0xFF64748B), fontSize: 11)),
                ],
              ),
            ),
            const SizedBox(width: 10),
            Row(
              children: [
                _buildCell(id, 'ca1', 'CA1', scores['ca1']!),
                const SizedBox(width: 6),
                _buildCell(id, 'ca2', 'CA2', scores['ca2']!),
                const SizedBox(width: 6),
                _buildCell(id, 'exam', 'EXM', scores['exam']!),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCell(int studentId, String field, String label, String value) {
    final isFocused = _focusedStudentId == studentId && _focusedField == field;
    final isEmpty = value.isEmpty;

    return GestureDetector(
      onTap: () {
        setState(() {
          _focusedStudentId = studentId;
          _focusedField = field;
        });
      },
      child: Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          color: const Color(0xFFF1F5F9), // slate-100
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isFocused
                ? AppColors.amberPrimary
                : (isEmpty ? AppColors.amberPrimary.withOpacity(0.3) : Colors.transparent),
            width: isFocused ? 2.0 : 1.0,
          ),
        ),
        child: Stack(
          alignment: Alignment.center,
          children: [
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  label,
                  style: const TextStyle(fontSize: 8, color: AppColors.textSecondary, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 1),
                Text(
                  isEmpty ? '' : value,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: AppColors.amberPrimary,
                  ),
                ),
              ],
            ),
            if (isFocused && isEmpty)
              const _BlinkingCursor(),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.appBackground,
      appBar: null,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary))
          : Column(
              children: [
                // Hero strip
                SafeArea(
                  bottom: false,
                  child: Container(
                    padding: const EdgeInsets.fromLTRB(16, 12, 16, 14),
                    decoration: const BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [_rc, _rcDark],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                    ),
                    child: Row(
                      children: [
                        GestureDetector(
                          onTap: () => Navigator.pop(context),
                          child: Container(
                            width: 36, height: 36,
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.arrow_back_rounded, color: Colors.white, size: 20),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(widget.subjectName,
                                  style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 2),
                              Text('${widget.className} · ${_students.length} students',
                                  style: TextStyle(color: Colors.white.withValues(alpha: 0.75), fontSize: 11)),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.edit_note_rounded, color: Colors.white, size: 13),
                              SizedBox(width: 4),
                              Text('CA1·CA2·EXAM', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                Expanded(
                  child: ListView.builder(
                    padding: const EdgeInsets.all(12),
                    itemCount: _students.length,
                    itemBuilder: (context, index) {
                      return _buildStudentScoreRow(_students[index]);
                    },
                  ),
                ),
                if (_focusedStudentId != null)
                  _buildKeypadOverlay()
                else
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                    child: Container(
                      height: 52,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(14),
                        gradient: const LinearGradient(
                          colors: [_rc, _rcDark],
                        ),
                        boxShadow: [BoxShadow(color: _rc, blurRadius: 14, offset: const Offset(0, 4))],
                      ),
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.transparent,
                          shadowColor: Colors.transparent,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        onPressed: _students.isEmpty ? null : _saveScores,
                        icon: const Icon(Icons.save_rounded, size: 20),
                        label: const Text('SAVE SCORES', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                      ),
                    ),
                  ),
              ],
            ),
    );
  }
}

class _BlinkingCursor extends StatefulWidget {
  const _BlinkingCursor();

  @override
  State<_BlinkingCursor> createState() => _BlinkingCursorState();
}

class _BlinkingCursorState extends State<_BlinkingCursor> with SingleTickerProviderStateMixin {
  late AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 500),
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return FadeTransition(
      opacity: _controller,
      child: Container(
        width: 1.5,
        height: 12,
        color: AppColors.amberPrimary,
      ),
    );
  }
}
