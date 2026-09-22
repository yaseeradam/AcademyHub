@php
    use App\Models\Transaction;
    use App\Support\ReportCardService;

    // We can use the existing settings json logic to store opening balance
    $settingsPath = storage_path('app/academyhub/settings.json');
    $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
    $openingBalance = (float) ($settings['bursar_opening_balance'] ?? 0);

    $incomeTotal = (float) Transaction::query()->where('type', 'Income')->where('is_void', false)->sum('amount_paid');
    $expenseTotal = (float) Transaction::query()->where('type', 'Expense')->where('is_void', false)->sum('amount_paid');
    
    // Net is calculated from opening balance + income - expenses
    $net = $openingBalance + $incomeTotal - $expenseTotal;

    $recent = Transaction::query()
        ->with('student')
        ->where('is_void', false)
        ->latest('date')
        ->limit(8)
        ->get();

    $topCategories = Transaction::query()
        ->selectRaw('category, SUM(amount_paid) as total')
        ->whereNotNull('category')
        ->where('is_void', false)
        ->groupBy('category')
        ->orderByDesc('total')
        ->limit(6)
        ->get();
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-page-header
            title="Accounts"
            subtitle="Financial overview (income vs expenses) and recent activity."
            accent="accounts"
        >
            <x-slot:actions>
                <a href="{{ route('billing.export.transactions') }}" class="btn-outline">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                    </svg>
                    Export All
                </a>
                <a href="{{ route('billing.index') }}" class="btn-primary">Open Billing</a>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Opening Balance" :value="config('academyhub.currency_symbol').' '.number_format($openingBalance, 2)" iconBg="bg-blue-50" iconColor="text-blue-600" />
            <x-stat-card label="Total Income" :value="config('academyhub.currency_symbol').' '.number_format($incomeTotal, 2)" iconBg="bg-green-50" iconColor="text-green-600" />
            <x-stat-card label="Total Expenses" :value="config('academyhub.currency_symbol').' '.number_format($expenseTotal, 2)" iconBg="bg-orange-50" iconColor="text-orange-600" />
            <x-stat-card label="Net Balance" :value="config('academyhub.currency_symbol').' '.number_format($net, 2)" iconBg="bg-indigo-50" iconColor="text-indigo-600" />
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="card-padded lg:col-span-2">
                <div class="text-sm font-semibold text-gray-900">Recent Transactions</div>
                <div class="mt-4">
                    <x-table>
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Date</th>
                                <th class="px-5 py-3">Student</th>
                                <th class="px-5 py-3">Type</th>
                                <th class="px-5 py-3">Category</th>
                                <th class="px-5 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($recent as $t)
                                <tr class="bg-white hover:bg-gray-50">
                                    <td class="px-5 py-4 text-sm text-gray-700">{{ $t->date?->format('M j, Y') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $t->student?->full_name ?: '-' }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $t->student?->admission_number ?: '' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <x-status-badge variant="{{ $t->type === 'Income' ? 'success' : 'warning' }}">{{ $t->type }}</x-status-badge>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700">{{ $t->category }}</td>
                                    <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">{{ config('academyhub.currency_symbol') }}{{ number_format((float) $t->amount_paid, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No transactions yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-table>
                </div>
            </div>

            <div class="card-padded">
                <div class="text-sm font-semibold text-gray-900">Top Categories</div>
                <div class="mt-4 space-y-3">
                    @forelse ($topCategories as $row)
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-gray-50 p-4 ring-1 ring-inset ring-gray-100">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-gray-900">{{ $row->category }}</div>
                                <div class="mt-1 text-xs text-gray-500">All time</div>
                            </div>
                            <div class="text-sm font-semibold text-gray-900">{{ config('academyhub.currency_symbol') }}{{ number_format((float) $row->total, 2) }}</div>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-gray-50 p-5 text-sm text-gray-600 ring-1 ring-inset ring-gray-100">
                            No categories yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
