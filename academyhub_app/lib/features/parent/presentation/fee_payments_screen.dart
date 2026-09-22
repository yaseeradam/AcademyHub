import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';
import 'package:url_launcher/url_launcher.dart';

class FeePaymentsScreen extends StatefulWidget {
  const FeePaymentsScreen({super.key});

  @override
  State<FeePaymentsScreen> createState() => _FeePaymentsScreenState();
}

class _FeePaymentsScreenState extends State<FeePaymentsScreen> {
  bool _isLoading = false;
  List<dynamic> _children = [];
  int? _selectedChildId;
  String _selectedChildName = '';
  
  double _outstandingBalance = 45000.0; // default/fallback
  String? _checkoutUrl;

  List<dynamic> _invoices = [];

  @override
  void initState() {
    super.initState();
    _loadChildrenAndBilling();
  }

  Future<void> _loadChildrenAndBilling() async {
    if (mounted) setState(() { _isLoading = true; });
    try {
      // 1. Fetch parent's children
      final studentsRes = await apiClient.dio.get('/parent/children');
      if (studentsRes.statusCode == 200 && studentsRes.data != null) {
        final list = List<dynamic>.from(studentsRes.data['data'] ?? []);
        if (list.isNotEmpty) {
          _children = list;
          final firstChild = list.first;
          _selectedChildId = firstChild['id'];
          _selectedChildName = '${firstChild['first_name']} ${firstChild['last_name']}';
        }
      }

      // 2. Fetch invoices history
      final invoicesRes = await apiClient.dio.get('/billing');
      if (invoicesRes.statusCode == 200 && invoicesRes.data != null) {
        if (mounted) {
          setState(() {
            _invoices = List<dynamic>.from(invoicesRes.data['data'] ?? []);
          });
        }
      }

      // 3. Fetch checkout URL and balance for active child
      if (_selectedChildId != null) {
        await _fetchCheckoutUrlForChild(_selectedChildId!);
      }
    } catch (e) {
      debugPrint('Error loading billing: $e');
    } finally {
      if (mounted) setState(() { _isLoading = false; });
    }
  }

  Future<void> _fetchCheckoutUrlForChild(int studentId) async {
    try {
      final res = await apiClient.dio.get(
        '/billing/checkout-url',
        queryParameters: {'student_id': studentId},
      );
      if (res.statusCode == 200 && res.data != null) {
        if (mounted) {
          setState(() {
            _outstandingBalance = double.tryParse(res.data['outstanding_balance']?.toString() ?? '') ?? 0.0;
            _checkoutUrl = res.data['checkout_url'];
          });
        }
      }
    } catch (e) {
      debugPrint('Error fetching checkout details: $e');
    }
  }

  Future<void> _onChildChanged(int? childId) async {
    if (childId == null) return;
    final child = _children.firstWhere(
      (c) => c['id'] == childId,
      orElse: () => _children.isNotEmpty ? _children.first : {},
    );
    if (child.isEmpty) return;
    if (mounted) {
      setState(() {
        _selectedChildId = childId;
        _selectedChildName = '${child['first_name']} ${child['last_name']}';
        _isLoading = true;
      });
    }
    await _fetchCheckoutUrlForChild(childId);
    if (mounted) setState(() { _isLoading = false; });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.appBackground,
      appBar: AppBar(
        backgroundColor: AppColors.rolePrimary('parent'),
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text('Fee Payments & Invoices', style: TextStyle(fontWeight: FontWeight.bold)),
        leading: Padding(
          padding: const EdgeInsets.all(8.0),
          child: InkWell(
            onTap: () => Navigator.maybePop(context),
            borderRadius: BorderRadius.circular(10),
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.18),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 18),
            ),
          ),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Child selector dropdown
                  if (_children.length > 1) ...[
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<int>(
                            value: _selectedChildId,
                            isExpanded: true,
                            items: _children.map((c) {
                              return DropdownMenuItem<int>(
                                value: c['id'],
                                child: Text('Child: ${c['first_name']} ${c['last_name']}'),
                              );
                            }).toList(),
                            onChanged: _onChildChanged,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],

                  // Top Balance Card (Solid 3D Parent Violet)
                  Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: AppColors.rolePrimary('parent'),
                      borderRadius: BorderRadius.circular(24),
                      border: Border(
                        bottom: BorderSide(
                          color: AppColors.role3DShadowColor('parent'),
                          width: 4,
                        ),
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.role3DShadowColor('parent').withValues(alpha: 0.35),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Column(
                      children: [
                        const Text(
                          'TOTAL OUTSTANDING BALANCE',
                          style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 1.0),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          '₦${_outstandingBalance.toStringAsFixed(2)}',
                          style: const TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Term 2 Tuition & Materials for $_selectedChildName',
                          style: const TextStyle(color: Colors.white70, fontSize: 12),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 20),
                        // Pay Button
                        ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.white,
                            foregroundColor: AppColors.rolePrimary('parent'),
                            elevation: 0,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            minimumSize: const Size.fromHeight(50),
                          ),
                          onPressed: _outstandingBalance <= 0 || _checkoutUrl == null
                              ? null
                              : () async {
                                  final messenger = ScaffoldMessenger.of(context);
                                  final uri = Uri.tryParse(_checkoutUrl!);
                                  if (uri != null && await canLaunchUrl(uri)) {
                                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                                  } else {
                                    messenger.showSnackBar(
                                      const SnackBar(
                                        content: Text('Could not open payment page. Please try again.'),
                                        backgroundColor: AppColors.dangerRed,
                                      ),
                                    );
                                  }
                                },
                          child: Text(
                            _outstandingBalance <= 0 ? 'FEE FULLY PAID' : 'PAY TOTAL NOW via Paystack',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Invoice History Title
                  const Text(
                    'INVOICE HISTORY & TRANSACTIONS',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textSecondary, letterSpacing: 1.0),
                  ),
                  const SizedBox(height: 12),

                  // Invoice List
                  if (_invoices.isEmpty)
                    const Card(
                      child: Padding(
                        padding: EdgeInsets.all(20.0),
                        child: Center(
                          child: Text(
                            'No billing transaction records found.',
                            style: TextStyle(color: AppColors.textSecondary),
                          ),
                        ),
                      ),
                    )
                  else
                    ..._invoices.map((inv) {
                      final title = inv['description'] ?? 'School Fees & Materials';
                      final rawAmount = double.tryParse(inv['amount_paid']?.toString() ?? '0') ?? 0.0;
                      final type = inv['type'] ?? 'Income'; // Income means paid, Expense/Liability means due
                      final date = inv['date'] ?? 'Jan 12, 2026';
                      final isPaid = type == 'Income' && rawAmount > 0;

                      return Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      title,
                                      style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'Date: $date',
                                      style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'Amount: ₦${rawAmount.toStringAsFixed(2)}',
                                      style: const TextStyle(fontWeight: FontWeight.w600, color: AppColors.textPrimary, fontSize: 13),
                                    ),
                                  ],
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                decoration: BoxDecoration(
                                  color: (isPaid ? AppColors.successGreen : AppColors.dangerRed).withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  isPaid ? 'Paid' : 'Unpaid',
                                  style: TextStyle(
                                    color: isPaid ? AppColors.successGreen : AppColors.dangerRed,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 11,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    }),

                  const SizedBox(height: 24),
                  
                  // Fallback Bank Transfer Details
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.divider),
                    ),
                    child: const Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(Icons.info_outline, color: AppColors.textSecondary, size: 18),
                            SizedBox(width: 8),
                            Text(
                              'Manual Bank Transfer Option',
                              style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary, fontSize: 13),
                            ),
                          ],
                        ),
                        SizedBox(height: 8),
                        Text(
                          'Bank Name: Access Bank PLC\nAccount Number: 0112345678\nAccount Name: Greenwood Academy Limited\n\nNote: Please send the transfer receipt to bursar@academyhub.com.ng for manual verification.',
                          style: TextStyle(color: AppColors.textSecondary, fontSize: 12, height: 1.4),
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
