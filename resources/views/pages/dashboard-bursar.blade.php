@php
    use App\Models\AcademicTerm;
    use App\Models\Student;
    use App\Models\Transaction;
    use App\Models\User;
    use Illuminate\Support\Facades\DB;

    $todayLabel = now()->format('l, F j, Y');
    $user = auth()->user();
    $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
    $currentTerm = AcademicTerm::active();

    // Finance-specific metrics
    $feesCollectedToday = (float) Transaction::query()
        ->where('type', 'Income')
        ->where('is_void', false)
        ->whereDate('date', today())
        ->sum('amount_paid');

    $feesCollectedThisWeek = (float) Transaction::query()
        ->where('type', 'Income')
        ->where('is_void', false)
        ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
        ->sum('amount_paid');

    $feesCollectedThisMonth = (float) Transaction::query()
        ->where('type', 'Income')
        ->where('is_void', false)
        ->whereMonth('date', now()->month)
        ->whereYear('date', now()->year)
        ->sum('amount_paid');

    $totalTransactionsToday = Transaction::query()
        ->where('type', 'Income')
        ->where('is_void', false)
        ->whereDate('date', today())
        ->count();

    $financeData = \Illuminate\Support\Facades\Cache::remember('bursar_dashboard_finance', \DateInterval::createFromDateString('15 minutes'), function () {
        $estimatedTuitionDueAllTime = (float) DB::table('students')
            ->leftJoin('fee_structures', function ($join) {
                $join->on('fee_structures.class_id', '=', 'students.class_id')
                    ->where('fee_structures.category', '=', 'Tuition')
                    ->whereNull('fee_structures.term')
                    ->whereNull('fee_structures.session');
            })
            ->sum('fee_structures.amount_due');

        $incomeAllTime = (float) Transaction::query()
            ->where('type', 'Income')
            ->where('is_void', false)
            ->sum('amount_paid');

        $outstandingPaymentsEstimate = max(0.0, $estimatedTuitionDueAllTime - $incomeAllTime);

        $dueByStudent = DB::table('students')
            ->leftJoin('fee_structures', function ($join) {
                $join->on('fee_structures.class_id', '=', 'students.class_id')
                    ->where('fee_structures.category', '=', 'Tuition')
                    ->whereNull('fee_structures.term')
                    ->whereNull('fee_structures.session');
            })
            ->select('students.id', DB::raw('COALESCE(SUM(fee_structures.amount_due), 0) as due'))
            ->groupBy('students.id');

        $paidByStudent = DB::table('transactions')
            ->where('type', 'Income')
            ->where('is_void', false)
            ->whereNotNull('student_id')
            ->select('student_id', DB::raw('SUM(amount_paid) as paid'))
            ->groupBy('student_id');

        $overdueInvoices = DB::query()
            ->fromSub($dueByStudent, 'd')
            ->leftJoinSub($paidByStudent, 'p', 'p.student_id', '=', 'd.id')
            ->whereRaw('d.due > COALESCE(p.paid, 0)')
            ->count();

        return [
            'estimatedTuitionDueAllTime' => $estimatedTuitionDueAllTime,
            'incomeAllTime' => $incomeAllTime,
            'outstandingPaymentsEstimate' => $outstandingPaymentsEstimate,
            'overdueInvoices' => $overdueInvoices,
        ];
    });

    $estimatedTuitionDueAllTime = $financeData['estimatedTuitionDueAllTime'];
    $incomeAllTime = $financeData['incomeAllTime'];
    $outstandingPaymentsEstimate = $financeData['outstandingPaymentsEstimate'];
    $overdueInvoices = $financeData['overdueInvoices'];

    // Recent transactions
    $recentTransactions = Transaction::query()
        ->with(['student'])
        ->where('type', 'Income')
        ->where('is_void', false)
        ->latest('created_at')
        ->limit(8)
        ->get();

    // Payment methods breakdown
    $paymentMethods = Transaction::query()
        ->where('type', 'Income')
        ->where('is_void', false)
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount_paid) as total'))
        ->groupBy('payment_method')
        ->orderByDesc('total')
        ->get();

    // Monthly revenue trend (last 6 months)
    $monthlyRevenue = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $revenue = Transaction::query()
            ->where('type', 'Income')
            ->where('is_void', false)
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->sum('amount_paid');
        
        $monthlyRevenue[] = [
            'month' => $month->format('M Y'),
            'revenue' => (float) $revenue,
        ];
    }

    $studentsTotal = Student::query()->count();
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="space-y-4">
            <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-600 via-green-600 to-teal-700 shadow-2xl transition-all duration-500 hover:shadow-emerald-500/50">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIj48cGF0aCBkPSJNMzYgMzRjMC0yLjIxLTEuNzktNC00LTRzLTQgMS43OS00IDQgMS43OSA0IDQgNCA0LTEuNzkgNC00em0wLTEwYzAtMi4yMS0xLjc5LTQtNC00cy00IDEuNzktNCA0IDEuNzkgNCA0IDQgNC0xLjc5IDQtNHptMC0xMGMwLTIuMjEtMS43OS00LTQtNHMtNCAxLjc5LTQgNCAxLjc5IDQgNCA0IDQtMS43OSA0LTR6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
                <div class="absolute right-0 top-0 h-96 w-96 -translate-y-32 translate-x-32 rounded-full bg-white/10"></div>
                <div class="absolute left-0 bottom-0 h-64 w-64 -translate-x-24 translate-y-24 rounded-full bg-black/10"></div>
                
                <div class="relative h-48 w-full sm:h-56">
                    <div class="absolute inset-0 flex flex-col justify-end p-8">
                        <div class="max-w-3xl">
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 backdrop-blur-md">
                                <div class="h-2 w-2 animate-pulse rounded-full bg-green-400"></div>
                                <span class="text-sm font-bold text-white">Finance Dashboard</span>
                            </div>
                            
                            <div class="mt-4 text-4xl font-black tracking-tight text-white sm:text-5xl">
                                {{ $schoolName }}
                            </div>
                            
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <div class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 backdrop-blur-md">
                                    <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span class="text-sm font-bold text-white">{{ $currentTerm ? $currentTerm->name : 'No Active Term' }}</span>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 backdrop-blur-md">
                                    <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"/>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                    </svg>
                                    <span class="text-sm font-bold text-white">{{ config('myacademy.currency_symbol') }}{{ number_format($feesCollectedToday, 2) }} Today</span>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ route('billing.index') }}" class="group/btn inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 font-bold text-emerald-600 shadow-lg transition-shadow duration-200 hover:shadow-xl">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"/>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                    </svg>
                                    Record Payment
                                </a>
                                <a href="{{ route('billing.export.transactions') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-5 py-3 font-bold text-white backdrop-blur-md transition-all hover:bg-white/30">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    Export Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-900">Financial Overview</div>
                <div class="text-xs text-slate-500">Signed in as {{ $user?->name ?? 'Bursar' }}</div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-6 shadow-sm ring-1 ring-emerald-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-emerald-600">Today's Collection</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ config('myacademy.currency_symbol') }}{{ number_format($feesCollectedToday, 2) }}</div>
                            <div class="mt-1.5 text-xs text-slate-600">{{ $totalTransactionsToday }} transactions</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100/50 p-6 shadow-sm ring-1 ring-blue-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-blue-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-blue-600">This Week</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ config('myacademy.currency_symbol') }}{{ number_format($feesCollectedThisWeek, 2) }}</div>
                            <div class="mt-1.5 text-xs text-slate-600">weekly collection</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100/50 p-6 shadow-sm ring-1 ring-purple-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-purple-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-purple-600">This Month</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ config('myacademy.currency_symbol') }}{{ number_format($feesCollectedThisMonth, 2) }}</div>
                            <div class="mt-1.5 text-xs text-slate-600">monthly total</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 text-white shadow-lg shadow-purple-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-50 to-red-100/50 p-6 shadow-sm ring-1 ring-red-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-red-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-red-600">Outstanding</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ config('myacademy.currency_symbol') }}{{ number_format($outstandingPaymentsEstimate, 2) }}</div>
                            <div class="mt-1.5 text-xs text-slate-600">{{ $overdueInvoices }} overdue</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-red-400 to-red-600 text-white shadow-lg shadow-red-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-3">
            <div class="text-sm font-semibold text-slate-900">Financial Analytics</div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5"></div>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Revenue Trend</div>
                            <div class="mt-1 text-sm text-slate-600">6-month collection overview</div>
                        </div>
                        <x-status-badge variant="success">Active</x-status-badge>
                    </div>
                    <div class="mt-4">
                        <canvas id="revenueTrendChart" height="220"></canvas>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-blue-500/5"></div>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Payment Methods</div>
                            <div class="mt-1 text-sm text-slate-600">This month's breakdown</div>
                        </div>
                        <x-status-badge variant="info">Current</x-status-badge>
                    </div>
                    <div class="mt-4">
                        <canvas id="paymentMethodsChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-3">
            <div class="text-sm font-semibold text-slate-900">Recent Transactions</div>

            <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <div class="absolute -right-16 top-0 h-40 w-40 rounded-full bg-emerald-500/5"></div>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Latest Payments</div>
                        <div class="mt-1 text-sm text-slate-600">Most recent fee collections</div>
                    </div>
                    <a href="{{ route('billing.index') }}" class="btn-outline">View All</a>
                </div>

                <div class="mt-4">
                    <x-table>
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Student</th>
                                <th class="px-5 py-3">Amount</th>
                                <th class="px-5 py-3">Method</th>
                                <th class="px-5 py-3">Reference</th>
                                <th class="px-5 py-3 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentTransactions as $transaction)
                                <tr class="bg-white hover:bg-slate-50">
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-slate-900">{{ $transaction->student?->full_name ?? 'N/A' }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $transaction->student?->admission_number ?? '' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-emerald-600">{{ config('myacademy.currency_symbol') }}{{ number_format($transaction->amount_paid, 2) }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $transaction->category ?? 'Fee' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-700">{{ $transaction->payment_method ?? 'Cash' }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-700">{{ $transaction->reference ?? '—' }}</td>
                                    <td class="px-5 py-4 text-right text-sm text-slate-600">{{ $transaction->created_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">
                                        No transactions recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-table>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        window.bursarDashboardData = {
            monthlyRevenue: @json($monthlyRevenue),
            paymentMethods: @json($paymentMethods->map(fn($method) => [
                'method' => $method->payment_method ?: 'Cash',
                'total' => $method->total,
                'count' => $method->count
            ]))
        };
    </script>
    @vite('resources/js/pages/dashboard-bursar.js')
@endpush