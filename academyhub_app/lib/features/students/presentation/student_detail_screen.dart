import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';
import 'package:url_launcher/url_launcher.dart';

class StudentDetailScreen extends StatefulWidget {
  final int studentId;
  final String studentName;

  const StudentDetailScreen({
    super.key,
    required this.studentId,
    required this.studentName,
  });

  static void show(BuildContext context, {required int studentId, required String studentName}) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => FractionallySizedBox(
        heightFactor: 0.92,
        child: StudentDetailScreen(
          studentId: studentId,
          studentName: studentName,
        ),
      ),
    );
  }

  @override
  State<StudentDetailScreen> createState() => _StudentDetailScreenState();
}

class _StudentDetailScreenState extends State<StudentDetailScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  String? _errorMessage;

  dynamic _student;
  Map<String, dynamic> _reportCard = {};
  Map<String, dynamic> _attendance = {};
  Map<String, dynamic> _financials = {};

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadStudentDetails();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadStudentDetails() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await apiClient.dio.get('/students/${widget.studentId}/details');
      if (response.statusCode == 200 && response.data != null && response.data['data'] != null) {
        final data = response.data['data'];
        setState(() {
          _student = data['student'];
          _reportCard = Map<String, dynamic>.from(data['report_card'] ?? {});
          _attendance = Map<String, dynamic>.from(data['attendance'] ?? {});
          _financials = Map<String, dynamic>.from(data['financials'] ?? {});
        });
      } else {
        setState(() {
          _errorMessage = 'Failed to load student records.';
        });
      }
    } catch (e) {
      debugPrint('Error loading student details: $e');
      setState(() {
        _errorMessage = 'Unable to fetch student details. Please try again.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  void _copyToClipboard(String text, String label) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('$label ($text) copied to clipboard!'),
        backgroundColor: AppColors.successGreen,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: AppColors.appBackground,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: Column(
        children: [
          // Bottom sheet handle bar
          const SizedBox(height: 12),
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

          // Main Header Area
          _buildHeader(),

          // Tab Bar Navigation
          Container(
            color: Colors.white,
            child: TabBar(
              controller: _tabController,
              isScrollable: true,
              labelColor: AppColors.amberPrimary,
              unselectedLabelColor: AppColors.textSecondary,
              indicatorColor: AppColors.amberPrimary,
              indicatorWeight: 3,
              labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
              tabs: const [
                Tab(icon: Icon(Icons.person_outline, size: 18), text: 'Profile'),
                Tab(icon: Icon(Icons.analytics_outlined, size: 18), text: 'Report Card'),
                Tab(icon: Icon(Icons.calendar_today_outlined, size: 18), text: 'Attendance'),
                Tab(icon: Icon(Icons.account_balance_wallet_outlined, size: 18), text: 'Financials'),
              ],
            ),
          ),

          // Tab Views
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.amberPrimary))
                : _errorMessage != null
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.error_outline, size: 48, color: AppColors.dangerRed),
                            const SizedBox(height: 12),
                            Text(_errorMessage!, style: const TextStyle(color: AppColors.textSecondary)),
                            const SizedBox(height: 12),
                            ElevatedButton(
                              onPressed: _loadStudentDetails,
                              style: ElevatedButton.styleFrom(backgroundColor: AppColors.amberPrimary),
                              child: const Text('Retry'),
                            ),
                          ],
                        ),
                      )
                    : TabBarView(
                        controller: _tabController,
                        children: [
                          _buildProfileTab(),
                          _buildReportCardTab(),
                          _buildAttendanceTab(),
                          _buildFinancialsTab(),
                        ],
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    final fullName = _student != null
        ? '${_student['first_name'] ?? ''} ${_student['last_name'] ?? ''}'.trim()
        : widget.studentName;
    final admNum = _student != null ? (_student['admission_number'] ?? 'N/A') : '...';
    final className = _student != null ? (_student['school_class']?['name'] ?? 'Unassigned') : '';
    final sectionName = _student != null ? (_student['section']?['name'] ?? '') : '';
    final phone = _student != null ? (_student['guardian_phone'] ?? '') : '';
    final email = _student != null ? (_student['guardian_email'] ?? '') : '';

    String initials = '';
    final parts = fullName.split(' ');
    if (parts.isNotEmpty && parts[0].isNotEmpty) initials += parts[0][0].toUpperCase();
    if (parts.length > 1 && parts[1].isNotEmpty) initials += parts[1][0].toUpperCase();
    if (initials.isEmpty) initials = '?';

    return Container(
      padding: const EdgeInsets.fromLTRB(20, 4, 20, 16),
      color: Colors.white,
      child: Column(
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 28,
                backgroundColor: AppColors.amberPrimary.withValues(alpha: 0.12),
                child: Text(
                  initials,
                  style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.amberPrimary),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      fullName,
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Adm No: $admNum · $className ($sectionName)',
                      style: const TextStyle(fontSize: 12, color: AppColors.textSecondary, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
              ),
              IconButton(
                icon: const Icon(Icons.close_rounded, color: AppColors.textSecondary),
                onPressed: () => Navigator.pop(context),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Quick Action Contacts Row
          Row(
            children: [
              Expanded(
                child: _buildContactChip(
                  icon: Icons.phone_outlined,
                  label: 'Call Guardian',
                  color: const Color(0xFF10B981),
                  onTap: () async {
                    if (phone.isNotEmpty) {
                      final uri = Uri.parse('tel:$phone');
                      if (await canLaunchUrl(uri)) {
                        await launchUrl(uri);
                      } else {
                        _copyToClipboard(phone, 'Guardian Phone');
                      }
                    } else {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('No phone number recorded.')),
                      );
                    }
                  },
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildContactChip(
                  icon: Icons.chat_bubble_outline,
                  label: 'WhatsApp',
                  color: const Color(0xFF25D366),
                  onTap: () async {
                    if (phone.isNotEmpty) {
                      final cleaned = phone.replaceAll(RegExp(r'[^0-9]'), '');
                      final uri = Uri.parse('https://wa.me/$cleaned');
                      if (await canLaunchUrl(uri)) {
                        await launchUrl(uri, mode: LaunchMode.externalApplication);
                      } else {
                        _copyToClipboard(phone, 'WhatsApp Number');
                      }
                    } else {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('No WhatsApp number recorded.')),
                      );
                    }
                  },
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildContactChip(
                  icon: Icons.email_outlined,
                  label: 'Email',
                  color: const Color(0xFF3B82F6),
                  onTap: () async {
                    if (email.isNotEmpty) {
                      final uri = Uri.parse('mailto:$email');
                      if (await canLaunchUrl(uri)) {
                        await launchUrl(uri);
                      } else {
                        _copyToClipboard(email, 'Guardian Email');
                      }
                    } else {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('No email recorded.')),
                      );
                    }
                  },
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildContactChip({
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 14, color: color),
            const SizedBox(width: 4),
            Flexible(
              child: Text(
                label,
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: color),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // 1. Profile Tab
  Widget _buildProfileTab() {
    if (_student == null) return const SizedBox.shrink();

    final dob = _student['date_of_birth'] ?? 'N/A';
    final gender = (_student['gender'] ?? 'N/A').toString().toUpperCase();
    final address = _student['address'] ?? 'No address recorded';
    final guardianName = _student['guardian_name'] ?? 'N/A';
    final guardianPhone = _student['guardian_phone'] ?? 'N/A';
    final guardianEmail = _student['guardian_email'] ?? 'N/A';
    final status = _student['status'] ?? 'Active';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _buildSectionCard(
            title: 'PERSONAL INFORMATION',
            children: [
              _buildDetailRow('Full Name', '${_student['first_name'] ?? ''} ${_student['last_name'] ?? ''}'),
              _buildDetailRow('Admission Number', _student['admission_number'] ?? 'N/A'),
              _buildDetailRow('Date of Birth', dob),
              _buildDetailRow('Gender', gender),
              _buildDetailRow('Status', status, isBadge: true),
            ],
          ),
          const SizedBox(height: 16),
          _buildSectionCard(
            title: 'GUARDIAN & EMERGENCY CONTACT',
            children: [
              _buildDetailRow('Guardian Name', guardianName),
              _buildDetailRow('Phone Number', guardianPhone),
              _buildDetailRow('Email Address', guardianEmail),
              _buildDetailRow('Home Address', address),
            ],
          ),
        ],
      ),
    );
  }

  // 2. Report Card Tab
  Widget _buildReportCardTab() {
    final subjects = List<dynamic>.from(_reportCard['subjects'] ?? []);
    final session = _reportCard['session'] ?? '2024/2025';
    final term = _reportCard['term'] ?? 1;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Term info card
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.divider),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Academic Session: $session', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    const SizedBox(height: 2),
                    Text('Term $term Assessment Results', style: const TextStyle(color: AppColors.textSecondary, fontSize: 11)),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.amberPrimary.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text('${subjects.length} Subjects', style: const TextStyle(color: AppColors.amberPrimary, fontSize: 11, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          if (subjects.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 40),
              child: Center(
                child: Text('No score records published for this term yet.', style: TextStyle(color: AppColors.textSecondary)),
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: subjects.length,
              separatorBuilder: (context, idx) => const SizedBox(height: 10),
              itemBuilder: (context, idx) {
                final sub = subjects[idx];
                final name = sub['subject'] ?? 'Subject';
                final ca1 = sub['ca1'] ?? '-';
                final ca2 = sub['ca2'] ?? '-';
                final exam = sub['exam'] ?? '-';
                final total = sub['total'] ?? '-';
                final grade = sub['grade'] ?? 'N/A';

                Color gradeColor = AppColors.amberPrimary;
                if (grade == 'A') gradeColor = AppColors.successGreen;
                if (grade == 'B') gradeColor = const Color(0xFF3B82F6);
                if (grade == 'F') gradeColor = AppColors.dangerRed;

                return Card(
                  child: Padding(
                    padding: const EdgeInsets.all(14.0),
                    child: Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                              const SizedBox(height: 6),
                              Text('CA 1: $ca1   ·   CA 2: $ca2   ·   Exam: $exam', style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                            ],
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text('$total pts', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.textPrimary)),
                            const SizedBox(height: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: gradeColor.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text('Grade $grade', style: TextStyle(color: gradeColor, fontSize: 10, fontWeight: FontWeight.bold)),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
        ],
      ),
    );
  }

  // 3. Attendance Tab
  Widget _buildAttendanceTab() {
    final rate = _attendance['rate'] ?? 100;
    final present = _attendance['present_count'] ?? 0;
    final absent = _attendance['absent_count'] ?? 0;
    final late = _attendance['late_count'] ?? 0;
    final logs = List<dynamic>.from(_attendance['recent_logs'] ?? []);

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Summary stat cards
          Row(
            children: [
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.divider),
                  ),
                  child: Column(
                    children: [
                      const Text('Rate', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
                      const SizedBox(height: 4),
                      Text('$rate%', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    children: [
                      const Text('Present', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.successGreen)),
                      const SizedBox(height: 4),
                      Text('$present d', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.successGreen)),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    children: [
                      const Text('Absent', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.dangerRed)),
                      const SizedBox(height: 4),
                      Text('$absent d', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.dangerRed)),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFFBEB),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    children: [
                      const Text('Late', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.amberPrimary)),
                      const SizedBox(height: 4),
                      Text('$late d', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.amberPrimary)),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),

          const Text('RECENT ROLL-CALL LOGS', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 1.0)),
          const SizedBox(height: 8),

          if (logs.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 30),
              child: Center(child: Text('No attendance logs found.', style: TextStyle(color: AppColors.textSecondary))),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: logs.length,
              separatorBuilder: (context, idx) => const Divider(height: 1),
              itemBuilder: (context, idx) {
                final item = logs[idx];
                final date = item['date'] ?? 'N/A';
                final status = item['status'] ?? 'Present';
                final note = item['note'] ?? '';

                Color statusColor = AppColors.successGreen;
                if (status == 'Absent') statusColor = AppColors.dangerRed;
                if (status == 'Late') statusColor = AppColors.amberPrimary;

                return ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: Text(date, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  subtitle: note.isNotEmpty ? Text(note, style: const TextStyle(fontSize: 11)) : null,
                  trailing: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(status, style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 11)),
                  ),
                );
              },
            ),
        ],
      ),
    );
  }

  // 4. Financials Tab
  Widget _buildFinancialsTab() {
    final totalPaid = (_financials['total_paid'] ?? 0.0).toDouble();
    final balance = (_financials['outstanding_balance'] ?? 0.0).toDouble();
    final transactions = List<dynamic>.from(_financials['transactions'] ?? []);

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Balance Banner
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppColors.rolePrimary('parent'),
              borderRadius: BorderRadius.circular(20),
              border: const Border(
                bottom: BorderSide(color: Color(0xFF5B21B6), width: 4),
              ),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x3D5B21B6),
                  blurRadius: 10,
                  offset: Offset(0, 4),
                ),
              ],
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Outstanding Balance', style: TextStyle(color: Colors.white70, fontSize: 12)),
                      const SizedBox(height: 4),
                      Text(
                        '₦${balance.toStringAsFixed(2)}',
                        style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Total Paid: ₦${totalPaid.toStringAsFixed(2)}',
                        style: const TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    balance == 0 ? 'CLEARED' : 'PENDING',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 11),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          const Text('TRANSACTION HISTORY', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 1.0)),
          const SizedBox(height: 8),

          if (transactions.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 30),
              child: Center(child: Text('No billing transactions recorded.', style: TextStyle(color: AppColors.textSecondary))),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: transactions.length,
              separatorBuilder: (context, idx) => const Divider(height: 1),
              itemBuilder: (context, idx) {
                final tx = transactions[idx];
                final ref = tx['reference'] ?? 'TXN';
                final date = tx['date'] ?? '';
                final amount = (tx['amount_paid'] ?? 0.0).toDouble();

                return ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const CircleAvatar(
                    backgroundColor: Color(0xFFECFDF5),
                    child: Icon(Icons.receipt_long, color: AppColors.successGreen, size: 20),
                  ),
                  title: Text(ref, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  subtitle: Text(date, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                  trailing: Text('₦${amount.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.successGreen)),
                );
              },
            ),
        ],
      ),
    );
  }

  Widget _buildSectionCard({required String title, required List<Widget> children}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.divider),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 1.0)),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value, {bool isBadge = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 12, fontWeight: FontWeight.w600)),
          if (isBadge)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: AppColors.successGreen.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(value, style: const TextStyle(color: AppColors.successGreen, fontWeight: FontWeight.bold, fontSize: 11)),
            )
          else
            Flexible(
              child: Text(
                value,
                style: const TextStyle(color: AppColors.textPrimary, fontSize: 12, fontWeight: FontWeight.bold),
                textAlign: TextAlign.end,
              ),
            ),
        ],
      ),
    );
  }
}
