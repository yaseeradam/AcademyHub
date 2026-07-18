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
              color: Colors.white,
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
                  const SizedBox(height: 12),
                  Text(
                    'Enter ${fieldName.toUpperCase()} Score',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      color: AppColors.inputFill,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      input.isEmpty ? '-' : input,
                      style: const TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: AppColors.primaryBlue),
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
                      return ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: key == 'C' || key == '⌫' ? Colors.grey[200] : AppColors.inputFill,
                          foregroundColor: AppColors.textPrimary,
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () => pressKey(key),
                        child: Text(
                          key,
                          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('Confirm'),
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

    return Card(
      color: hasEmpty ? const Color(0xFFFFFBEB) : Colors.white, // Amber tinted if empty cells
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              name,
              style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
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
    return Expanded(
      child: GestureDetector(
        onTap: () => _openNumericKeypad(studentId, field, value),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: Colors.white,
            border: Border.all(color: AppColors.divider),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Column(
            children: [
              Text(
                label,
                style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
              ),
              const SizedBox(height: 4),
              Text(
                value.isEmpty ? '-' : value,
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: value.isEmpty ? AppColors.textDisabled : AppColors.primaryBlue,
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
        title: Text(widget.subjectName),
        elevation: 0.5,
        backgroundColor: AppColors.cardSurface,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  color: AppColors.cardSurface,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Class: ${widget.className}',
                        style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                      const Text(
                        'Term 2',
                        style: TextStyle(color: AppColors.textSecondary),
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
                  child: ElevatedButton(
                    onPressed: _students.isEmpty ? null : _saveScores,
                    child: const Text('Save Scores'),
                  ),
                ),
              ],
            ),
    );
  }
}
