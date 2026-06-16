import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';
import '../../core/toast_utility.dart';

class CSVRecord {
  final String admissionNumber;
  final String studentName;
  int ca1;
  int ca2;
  int exam;

  CSVRecord({
    required this.admissionNumber,
    required this.studentName,
    this.ca1 = 0,
    this.ca2 = 0,
    this.exam = 0,
  });
}

class CSVScoresImporter extends StatefulWidget {
  final int classId;
  final int subjectId;
  final String className;
  final String subjectName;

  const CSVScoresImporter({
    super.key,
    required this.classId,
    required this.subjectId,
    required this.className,
    required this.subjectName,
  });

  @override
  State<CSVScoresImporter> createState() => _CSVScoresImporterState();
}

class _CSVScoresImporterState extends State<CSVScoresImporter> {
  List<CSVRecord> _records = [];
  bool _importing = false;
  int _activeTerm = 1;
  String _activeSession = '2026/2027';

  @override
  void initState() {
    super.initState();
    _loadActiveTermAndSession();
  }

  Future<void> _loadActiveTermAndSession() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        _activeTerm = prefs.getInt('active_term') ?? 1;
        _activeSession = prefs.getString('active_session') ?? '2026/2027';
      });
    }
  }

  void _loadSimulatedCSV() {
    setState(() {
      _records = [
        CSVRecord(admissionNumber: 'ADM-001', studentName: 'Abdullahi Bala', ca1: 15, ca2: 12, exam: 54),
        CSVRecord(admissionNumber: 'ADM-002', studentName: 'Chinedu Okafor', ca1: 18, ca2: 15, exam: 62),
        CSVRecord(admissionNumber: 'ADM-003', studentName: 'Fatima Yusuf', ca1: 14, ca2: 13, exam: 48),
        CSVRecord(admissionNumber: 'ADM-004', studentName: 'Emeka Nwosu', ca1: 16, ca2: 14, exam: 58),
      ];
    });
    CustomToast.show(
      context: context,
      message: 'Simulated CSV template loaded successfully!',
      type: 'success',
    );
  }

  Future<void> _submitImport() async {
    if (_records.isEmpty) {
      CustomToast.show(
        context: context,
        message: 'No records to import. Please load a CSV file first.',
        type: 'warning',
      );
      return;
    }

    final auth = context.read<AuthProvider>();
    setState(() => _importing = true);

    final scoresPayload = _records.map((r) => {
      'class_id': widget.classId,
      'subject_id': widget.subjectId,
      'term': _activeTerm,
      'session': _activeSession,
      'admission_number': r.admissionNumber,
      'ca1': r.ca1,
      'ca2': r.ca2,
      'exam': r.exam,
    }).toList();

    try {
      final success = await auth.apiService.queueableMutation(
        '/teacher/scores/import',
        'POST',
        {'scores': scoresPayload},
      );

      if (mounted) {
        setState(() => _importing = false);
        if (success) {
          CustomToast.show(
            context: context,
            message: 'Imported scores uploaded successfully!',
            type: 'success',
          );
          Navigator.pop(context);
        } else {
          CustomToast.show(
            context: context,
            message: 'Offline: Import saved to local sync queue.',
            type: 'info',
          );
          Navigator.pop(context);
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _importing = false);
        CustomToast.show(
          context: context,
          message: 'Import failed: $e',
          type: 'error',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final primary = context.read<AuthProvider>().tenantPrimaryColor;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('CSV Scores Importer',
            style: GoogleFonts.spaceGrotesk(
                fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
        backgroundColor: AppColors.surface,
        elevation: 0,
        iconTheme: IconThemeData(color: AppColors.textPrimary),
      ),
      body: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            color: AppColors.surface,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(widget.className,
                            style: GoogleFonts.spaceGrotesk(
                                fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                        Text(widget.subjectName,
                            style: GoogleFonts.spaceGrotesk(fontSize: 12, color: AppColors.textSecondary)),
                      ],
                    ),
                    ElevatedButton.icon(
                      onPressed: _loadSimulatedCSV,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: primary,
                        foregroundColor: Colors.black,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      icon: const Icon(Icons.upload_file, size: 16),
                      label: Text('Select CSV',
                          style: GoogleFonts.spaceGrotesk(fontSize: 12, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Expanded(
            child: _records.isEmpty
                ? Center(
                    child: Text('No CSV records loaded yet.',
                        style: GoogleFonts.spaceGrotesk(color: AppColors.textMuted)),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _records.length,
                    itemBuilder: (ctx, i) {
                      final record = _records[i];
                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.borderLight),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(record.studentName,
                                style: GoogleFonts.spaceGrotesk(
                                    fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                            Text('Admission: ${record.admissionNumber}',
                                style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary)),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                _buildScoreField('CA1', record.ca1, (val) => record.ca1 = val),
                                const SizedBox(width: 8),
                                _buildScoreField('CA2', record.ca2, (val) => record.ca2 = val),
                                const SizedBox(width: 8),
                                _buildScoreField('Exam', record.exam, (val) => record.exam = val),
                              ],
                            ),
                          ],
                        ),
                      );
                    },
                  ),
          ),
          if (_records.isNotEmpty)
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: _importing ? null : _submitImport,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.success,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: _importing
                      ? const CircularProgressIndicator(color: Colors.white)
                      : Text('Import Scores',
                          style: GoogleFonts.spaceGrotesk(
                              color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildScoreField(String label, int value, Function(int) onChanged) {
    return Expanded(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: GoogleFonts.spaceGrotesk(fontSize: 11, color: AppColors.textSecondary)),
          const SizedBox(height: 4),
          TextFormField(
            initialValue: value.toString(),
            keyboardType: TextInputType.number,
            style: GoogleFonts.spaceGrotesk(fontSize: 13, color: AppColors.textPrimary),
            decoration: InputDecoration(
              filled: true,
              fillColor: AppColors.surface2,
              contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: BorderSide(color: AppColors.borderLight),
              ),
            ),
            onChanged: (val) {
              final parsed = int.tryParse(val) ?? 0;
              onChanged(parsed);
            },
          ),
        ],
      ),
    );
  }
}
