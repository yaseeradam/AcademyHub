import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';
import '../../core/sync_status_widget.dart';

class StudentHome extends StatefulWidget {
  const StudentHome({super.key});

  @override
  State<StudentHome> createState() => _StudentHomeState();
}

class _StudentHomeState extends State<StudentHome> {
  Map<String, dynamic>? _reportCardData;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    final auth = context.read<AuthProvider>();
    
    try {
      // In a robust implementation, the user object or a /me endpoint would 
      // provide the student_id linked to the current user. 
      // For this prototype, we query student ID 1 to demonstrate the API connection.
      final responseData = await auth.apiService.getWithCache('/students/1/report-card');
      if (mounted) {
        setState(() {
          _reportCardData = responseData['data'];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'Failed to load report card. Please try again.';
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    if (_error != null) {
      return Scaffold(
        appBar: AppBar(title: const Text('My Results')),
        body: Center(child: Text(_error!)),
      );
    }

    final subjects = _reportCardData?['subjects'] as List<dynamic>? ?? [];
    final sessionInfo = '${_reportCardData?['session']} - Term ${_reportCardData?['term']}';
    final attendance = _reportCardData?['attendance'] ?? 0;
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Dashboard'),
        actions: [
          const SyncStatusWidget(),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () => context.read<AuthProvider>().logout(),
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              color: AppColors.background,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Academic Results', style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 4),
                      Text(sessionInfo, style: Theme.of(context).textTheme.bodyMedium),
                    ],
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    decoration: BoxDecoration(
                      color: AppColors.success.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text('Att: $attendance%', style: const TextStyle(color: AppColors.success, fontWeight: FontWeight.bold)),
                  )
                ],
              ),
            ),
            Expanded(
              child: ListView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                itemCount: subjects.length,
                itemBuilder: (context, index) {
                  final subj = subjects[index];
                  final String grade = subj['grade'];
                  final Color gradeColor = grade.startsWith('A') ? AppColors.success : (grade.startsWith('B') ? AppColors.info : AppColors.primary);
                  
                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: ListTile(
                      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                      title: Text(subj['subject'], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      subtitle: Padding(
                        padding: const EdgeInsets.top(4.0),
                        child: Text('CA: ${subj['ca1'] + subj['ca2']} • Exam: ${subj['exam']} • Total: ${subj['total']}'),
                      ),
                      trailing: Container(
                        height: 48,
                        width: 48,
                        decoration: BoxDecoration(
                          color: gradeColor.withOpacity(0.1),
                          shape: BoxShape.circle,
                        ),
                        child: Center(
                          child: Text(
                            grade,
                            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: gradeColor)
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}
