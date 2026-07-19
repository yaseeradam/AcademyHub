import 'package:flutter/material.dart';
import 'package:academyhub_app/core/theme/app_theme.dart';
import 'package:academyhub_app/core/network/api_client.dart';

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
    setState(() {
      _isLoading = true;
    });
    try {
      // 1. Fetch children
      final studentsRes = await apiClient.dio.get('/students');
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
        setState(() {
          _invoices = List<dynamic>.from(invoicesRes.data['data'] ?? []);
        });
      }

      // 3. Fetch checkout URL and balance for active child
      if (_selectedChildId != null) {
        await _fetchCheckoutUrlForChild(_selectedChildId!);
      }
    } catch (e) {
      debugPrint('Error loading billing: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _fetchCheckoutUrlForChild(int studentId) async {
    try {
      final res = await apiClient.dio.get(
        '/billing/checkout-url',
        queryParameters: {'student_id': studentId},
      );
      if (res.statusCode == 200 && res.data != null) {
        setState(() {
          _outstandingBalance = double.tryParse(res.data['outstanding_balance']?.toString() ?? '') ?? 0.0;
          _checkoutUrl = res.data['checkout_url'];
        });
      }
    } catch (e) {
      debugPrint('Error fetching checkout details: $e');
    }
  }

  Future<void> _onChildChanged(int? childId) async {
    if (childId == null) return;
    final child = _children.firstWhere((c) => c['id'] == childId);
    setState(() {
      _selectedChildId = childId;
      _selectedChildName = '${child['first_name']} ${child['last_name']}';
      _isLoading = true;
    });
    await _fetchCheckoutUrlForChild(childId);
    setState(() {
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    const emeraldGreen = Color(0xFF10B981);
    const tealColor = Color(0xFF0F766E);

    return Scaffold(
      backgroundColor: AppColors.appBackground,
      appBar: AppBar(
        title: const Text('Fee Payments & Invoices'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
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

                  // Top Balance Card (Emerald Green/Teal gradient)
                  Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [emeraldGreen, tealColor],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [
                        BoxShadow(
                          color: emeraldGreen.withOpacity(0.3),
                          blurRadius: 20,
                          offset: const Offset(0, 8),
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
                            foregroundColor: emeraldGreen,
                            elevation: 0,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            minimumSize: const Size.fromHeight(50),
                          ),
                          onPressed: _outstandingBalance <= 0 || _checkoutUrl == null
                              ? null
                              : () {
                                  // In mobile app, we can launch web checkout
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text('Redirecting to Paystack checkout: $_checkoutUrl'),
                                      backgroundColor: emeraldGreen,
                                    ),
                                  );
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
                                  color: (isPaid ? emeraldGreen : AppColors.dangerRed).withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  isPaid ? 'Paid' : 'Unpaid',
                                  style: TextStyle(
                                    color: isPaid ? emeraldGreen : AppColors.dangerRed,
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
