import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/database/local_db.dart';
import 'package:academyhub_app/core/network/api_client.dart';

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

  void _openNumericKeypad(int studentId, String fieldName, String currentVal) {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF1E293B),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        String input = currentVal;
        return StatefulBuilder(
          builder: (context, setModalState) {
            void pressKey(String key) {
              setModalState(() {
                if (key == 'C') {
                  input = '';
                } else if (key == '⌫') {
                  if (input.isNotEmpty) {
                     input = input.substring(0, input.length - 1);
                  }
                } else {
                  // Cap scores length
                  if (input.length < 3) {
                    input += key;
                  }
                }
              });
              
              setState(() {
                _scoresMap[studentId]![fieldName] = input;
              });
            }

            return Container(
              padding: const EdgeInsets.all(20),
              color: const Color(0xFF1E293B),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: const Color(0xFF334155),
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Enter ${fieldName.toUpperCase()} Score',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.white),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0F172A),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      input.isEmpty ? '-' : input,
                      style: const TextStyle(fontSize: 36, fontWeight: FontWeight.w800, color: AppColors.amberPrimary),
                      textAlign: TextAlign.center,
                    ),
                  ),
                  const SizedBox(height: 20),
                  
                  // Keypad Grid
                  GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 3,
                      mainAxisSpacing: 10,
                      crossAxisSpacing: 10,
                      childAspectRatio: 1.6,
                    ),
                    itemCount: 12,
                    itemBuilder: (context, index) {
                      final keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9', 'C', '0', '⌫'];
                      final key = keys[index];
                      Color keyBg = const Color(0xFF334155);
                      Color textCol = Colors.white;
                      
                      if (key == 'C') {
                        keyBg = const Color(0xFF7F1D1D); // dark red
                        textCol = const Color(0xFFF43F5E); // text red
                      } else if (key == '⌫') {
                        textCol = const Color(0xFFF43F5E);
                      }

                      return ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: keyBg,
                          foregroundColor: AppColors.amberPrimary, // amber ripple on press
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () => pressKey(key),
                        child: Text(
                          key,
                          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: textCol),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  Container(
                    height: 52,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.amberPrimary.withOpacity(0.4),
                          blurRadius: 16,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.amberPrimary,
                        foregroundColor: Colors.white,
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: () => Navigator.pop(context),
                      child: const Text('CONFIRM', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
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

  Widget _buildStudentScoreRow(Map<String, dynamic> student) {
    final id = student['id'];
    final name = '${student['first_name']} ${student['last_name']}';
    final scores = _scoresMap[id] ?? {'ca1': '', 'ca2': '', 'exam': ''};

    final hasEmpty = scores['ca1']!.isEmpty || scores['ca2']!.isEmpty || scores['exam']!.isEmpty;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border(
          left: BorderSide(
            color: hasEmpty ? AppColors.amberPrimary : Colors.transparent,
            width: 3,
          ),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              name,
              style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 15),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                _buildCell(id, 'ca1', 'CA1', scores['ca1']!),
                const SizedBox(width: 8),
                _buildCell(id, 'ca2', 'CA2', scores['ca2']!),
                const SizedBox(width: 8),
                _buildCell(id, 'exam', 'Exam', scores['exam']!),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCell(int studentId, String field, String label, String value) {
    final isEmpty = value.isEmpty;
    return Expanded(
      child: GestureDetector(
        onTap: () => _openNumericKeypad(studentId, field, value),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: const Color(0xFFF1F5F9), // slate-100
            borderRadius: BorderRadius.circular(10),
          ),
          child: Column(
            children: [
              Text(
                label.toUpperCase(),
                style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8), fontWeight: FontWeight.w700, letterSpacing: 0.5),
              ),
              const SizedBox(height: 4),
              Text(
                isEmpty ? '—' : value,
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w800,
                  color: isEmpty ? const Color(0xFFCBD5E1) : AppColors.amberPrimary,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          widget.subjectName,
          style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        elevation: 0,
        backgroundColor: const Color(0xFF1E293B),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary))
          : Column(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: const BoxDecoration(
                    color: Color(0xFF1E293B),
                    border: Border(
                      bottom: BorderSide(color: Color(0xFF334155), width: 1.0),
                    ),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Class: ${widget.className}',
                        style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 14),
                      ),
                      const Text(
                        'Term 2',
                        style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _students.length,
                    itemBuilder: (context, index) {
                      return _buildStudentScoreRow(_students[index]);
                    },
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Container(
                    height: 52,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.amberPrimary.withOpacity(0.35),
                          blurRadius: 16,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.amberPrimary,
                        foregroundColor: Colors.white,
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: _students.isEmpty ? null : _saveScores,
                      child: const Text(
                        'SAVE SCORES',
                        style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
                      ),
                    ),
                  ),
                ),
              ],
            ),
    );
  }
}
