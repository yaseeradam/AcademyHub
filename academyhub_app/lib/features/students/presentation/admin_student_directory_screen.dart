import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class AdminStudentDirectoryScreen extends StatefulWidget {
  const AdminStudentDirectoryScreen({super.key});

  @override
  State<AdminStudentDirectoryScreen> createState() => _AdminStudentDirectoryScreenState();
}

class _AdminStudentDirectoryScreenState extends State<AdminStudentDirectoryScreen> {
  final _searchCtrl = TextEditingController();
  String _selectedClassFilter = 'All Classes';

  final List<Map<String, dynamic>> _students = [
    {'name': 'Alexander Wright', 'reg': 'STU2024001', 'class': 'Grade 10-A', 'status': 'Active', 'gpa': '3.84'},
    {'name': 'Beatrix Vance', 'reg': 'STU2024002', 'class': 'Grade 10-A', 'status': 'Active', 'gpa': '3.65'},
    {'name': 'Cedric Diggory', 'reg': 'STU2024003', 'class': 'Grade 11-B', 'status': 'Active', 'gpa': '3.92'},
    {'name': 'Diana Prince', 'reg': 'STU2024004', 'class': 'Grade 9-A', 'status': 'Pending', 'gpa': '3.40'},
    {'name': 'Ethan Hunt', 'reg': 'STU2024005', 'class': 'Grade 11-A', 'status': 'Active', 'gpa': '3.10'},
    {'name': 'Fiona Gallagher', 'reg': 'STU2024006', 'class': 'Grade 10-B', 'status': 'Active', 'gpa': '3.95'},
  ];

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surfaceContainerLow,
        title: Text('Student Directory', style: GoogleFonts.plusJakartaSans(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 18)),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: AppColors.onSurface),
          onPressed: () => context.pop(),
        ),
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Search Input
            TextField(
              controller: _searchCtrl,
              style: GoogleFonts.plusJakartaSans(color: AppColors.onSurface, fontSize: 14),
              decoration: InputDecoration(
                hintText: 'Search by student name or registration ID...',
                prefixIcon: const Icon(Icons.search_rounded, color: AppColors.onSurfaceVariant),
                fillColor: AppColors.surfaceContainer,
              ),
            ),
            const SizedBox(height: 16),

            // Class Filter Chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: ['All Classes', 'Grade 9', 'Grade 10-A', 'Grade 10-B', 'Grade 11'].map((cls) {
                  final isSelected = _selectedClassFilter == cls;
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: ChoiceChip(
                      label: Text(cls),
                      selected: isSelected,
                      selectedColor: AppColors.primaryContainer,
                      backgroundColor: AppColors.surfaceContainerLow,
                      labelStyle: GoogleFonts.plusJakartaSans(
                        color: isSelected ? const Color(0xFF2A1700) : AppColors.onSurfaceVariant,
                        fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                        fontSize: 12,
                      ),
                      onSelected: (val) {
                        if (val) setState(() => _selectedClassFilter = cls);
                      },
                    ),
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 16),

            // Student List
            Expanded(
              child: ListView.builder(
                itemCount: _students.length,
                itemBuilder: (context, index) {
                  final student = _students[index];
                  return Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppColors.surfaceContainer,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.3)),
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 22,
                          backgroundColor: AppColors.primary.withValues(alpha: 0.15),
                          child: Text(student['name'][0], style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.primary, fontSize: 16)),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(student['name'], style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.onSurface, fontSize: 14)),
                              Text('${student['reg']} · ${student['class']}', style: GoogleFonts.plusJakartaSans(fontSize: 12, color: AppColors.onSurfaceVariant)),
                            ],
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(color: AppColors.successGreen.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(8)),
                              child: Text(student['status'], style: GoogleFonts.plusJakartaSans(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.successGreen)),
                            ),
                            const SizedBox(height: 4),
                            Text('GPA: ${student['gpa']}', style: GoogleFonts.plusJakartaSans(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.primary)),
                          ],
                        ),
                      ],
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
