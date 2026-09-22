import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';

class ScoresEntryGridScreen extends StatefulWidget {
  const ScoresEntryGridScreen({super.key});

  @override
  State<ScoresEntryGridScreen> createState() => _ScoresEntryGridScreenState();
}

class _ScoresEntryGridScreenState extends State<ScoresEntryGridScreen> {
  String _selectedSubject = 'Mathematics';
  String _selectedClass = 'Grade 10-A';

  final List<Map<String, dynamic>> _students = [
    {'id': 'STU001', 'name': 'Alexander Wright', 'ca1': 18, 'ca2': 17, 'exam': 54, 'grade': 'A'},
    {'id': 'STU002', 'name': 'Beatrix Vance', 'ca1': 15, 'ca2': 16, 'exam': 48, 'grade': 'B'},
    {'id': 'STU003', 'name': 'Cedric Diggory', 'ca1': 19, 'ca2': 20, 'exam': 58, 'grade': 'A+'},
    {'id': 'STU004', 'name': 'Diana Prince', 'ca1': 14, 'ca2': 15, 'exam': 42, 'grade': 'B'},
    {'id': 'STU005', 'name': 'Ethan Hunt', 'ca1': 12, 'ca2': 14, 'exam': 38, 'grade': 'C'},
    {'id': 'STU006', 'name': 'Fiona Gallagher', 'ca1': 20, 'ca2': 19, 'exam': 57, 'grade': 'A+'},
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surfaceContainerLow,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Scores Entry Grid',
              style: GoogleFonts.plusJakartaSans(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 18),
            ),
            Text(
              '$_selectedSubject · $_selectedClass',
              style: GoogleFonts.plusJakartaSans(color: AppColors.onSurfaceVariant, fontSize: 12),
            ),
          ],
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: AppColors.onSurface),
          onPressed: () => context.pop(),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.cloud_upload_rounded, color: AppColors.primary),
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  backgroundColor: AppColors.primaryContainer,
                  content: Text('Scores synced successfully!', style: GoogleFonts.plusJakartaSans(color: const Color(0xFF2A1700), fontWeight: FontWeight.bold)),
                ),
              );
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Filter Selectors Banner
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainer,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.4)),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: _dropdownFilter('Subject', _selectedSubject, ['Mathematics', 'English', 'Physics', 'Chemistry'], (val) {
                      if (val != null) setState(() => _selectedSubject = val);
                    }),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _dropdownFilter('Class', _selectedClass, ['Grade 10-A', 'Grade 10-B', 'Grade 11-A'], (val) {
                      if (val != null) setState(() => _selectedClass = val);
                    }),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Spreadsheet Grid Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Student Marks Sheet',
                  style: GoogleFonts.plusJakartaSans(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.onSurface),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '${_students.length} Students',
                    style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.primary),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Data Table Card
            Container(
              decoration: BoxDecoration(
                color: AppColors.surfaceContainer,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.4)),
              ),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: DataTable(
                  columnSpacing: 24,
                  headingRowHeight: 48,
                  dataRowMaxHeight: 56,
                  headingRowColor: WidgetStateProperty.all(AppColors.surfaceContainerHigh),
                  columns: [
                    DataColumn(label: Text('Student Name', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.primary))),
                    DataColumn(label: Text('CA 1 (20)', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.onSurface))),
                    DataColumn(label: Text('CA 2 (20)', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.onSurface))),
                    DataColumn(label: Text('Exam (60)', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.onSurface))),
                    DataColumn(label: Text('Total (100)', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.primary))),
                    DataColumn(label: Text('Grade', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.onSurface))),
                  ],
                  rows: _students.map((student) {
                    final ca1 = student['ca1'] as int;
                    final ca2 = student['ca2'] as int;
                    final exam = student['exam'] as int;
                    final total = ca1 + ca2 + exam;
                    return DataRow(
                      cells: [
                        DataCell(
                          Row(
                            children: [
                              CircleAvatar(
                                radius: 14,
                                backgroundColor: AppColors.primary.withValues(alpha: 0.2),
                                child: Text(student['name'][0], style: GoogleFonts.plusJakartaSans(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.primary)),
                              ),
                              const SizedBox(width: 10),
                              Text(student['name'], style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w600, color: AppColors.onSurface)),
                            ],
                          ),
                        ),
                        DataCell(_scoreCell(ca1)),
                        DataCell(_scoreCell(ca2)),
                        DataCell(_scoreCell(exam)),
                        DataCell(Text('$total', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w800, color: AppColors.primary))),
                        DataCell(
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.successGreen.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Text(student['grade'], style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold, color: AppColors.successGreen, fontSize: 12)),
                          ),
                        ),
                      ],
                    );
                  }).toList(),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _dropdownFilter(String label, String value, List<String> items, ValueChanged<String?> onChanged) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: GoogleFonts.plusJakartaSans(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.onSurfaceVariant)),
        const SizedBox(height: 4),
        DropdownButtonFormField<String>(
          initialValue: value,
          isExpanded: true,
          dropdownColor: AppColors.surfaceContainerHigh,
          style: GoogleFonts.plusJakartaSans(color: AppColors.onSurface, fontSize: 13, fontWeight: FontWeight.w600),
          decoration: InputDecoration(
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            fillColor: AppColors.surfaceContainerLow,
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.outlineVariant)),
          ),
          items: items.map((item) => DropdownMenuItem(value: item, child: Text(item))).toList(),
          onChanged: onChanged,
        ),
      ],
    );
  }

  Widget _scoreCell(int score) {
    return Container(
      width: 44,
      height: 32,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLow,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.3)),
      ),
      child: Text('$score', style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.w700, color: AppColors.onSurface, fontSize: 13)),
    );
  }
}
