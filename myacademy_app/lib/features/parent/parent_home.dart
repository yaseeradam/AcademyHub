import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/auth_provider.dart';
import '../../core/constants.dart';
import '../../core/sync_status_widget.dart';

class ParentHome extends StatefulWidget {
  const ParentHome({super.key});

  @override
  State<ParentHome> createState() => _ParentHomeState();
}

class _ParentHomeState extends State<ParentHome> {
  List<dynamic> _billing = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetchBilling();
  }

  Future<void> _fetchBilling() async {
    final auth = context.read<AuthProvider>();
    try {
      final responseData = await auth.apiService.getWithCache('/billing');
      if (mounted) {
        setState(() {
          // Laravel paginated response
          _billing = responseData['data'] ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'Failed to load billing records.';
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
        appBar: AppBar(title: const Text('Parent Portal')),
        body: Center(child: Text(_error!)),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Parent Portal'),
        actions: [
          const SyncStatusWidget(),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () => context.read<AuthProvider>().logout(),
          ),
        ],
      ),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
             padding: const EdgeInsets.all(24),
             color: AppColors.background,
             child: Text('Recent Fee Transactions', style: Theme.of(context).textTheme.titleLarge),
          ),
          Expanded(
            child: _billing.isEmpty
                ? const Center(child: Text('No fee transactions found.'))
                : ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: _billing.length,
                    itemBuilder: (context, index) {
                      final tx = _billing[index];
                      final isIncome = tx['type'] == 'Income';
                      
                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: isIncome ? AppColors.success.withOpacity(0.1) : AppColors.error.withOpacity(0.1),
                            child: Icon(
                              isIncome ? Icons.arrow_downward : Icons.arrow_upward,
                              color: isIncome ? AppColors.success : AppColors.error,
                            ),
                          ),
                          title: Text(tx['category'] ?? 'Fee Payment', style: const TextStyle(fontWeight: FontWeight.bold)),
                          subtitle: Text('Date: ${tx['date'].split('T')[0]}'),
                          trailing: Text(
                            '₦${tx['amount_paid']}',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: isIncome ? AppColors.success : AppColors.error,
                            ),
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
