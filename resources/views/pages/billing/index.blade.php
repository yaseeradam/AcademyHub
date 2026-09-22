@php
    $tab = request('tab', 'fees');

    $transactions = [
        [
            'id' => 'TXN-10021',
            'date' => '2026-02-04',
            'student' => 'Amina Yusuf',
            'class' => 'JSS2',
            'category' => ['label' => 'Fees Received', 'variant' => 'success'],
            'method' => 'Cash',
            'amount' => '₦30,000',
            'status' => ['label' => 'Completed', 'variant' => 'success'],
        ],
        [
            'id' => 'TXN-10019',
            'date' => '2026-02-03',
            'student' => 'David Okoye',
            'class' => 'SSS1',
            'category' => ['label' => 'Fees Received', 'variant' => 'success'],
            'method' => 'Bank',
            'amount' => '₦55,000',
            'status' => ['label' => 'Completed', 'variant' => 'success'],
        ],
        [
            'id' => 'TXN-10012',
            'date' => '2026-02-01',
            'student' => 'Fatima Bello',
            'class' => 'JSS1',
            'category' => ['label' => 'Fees Received', 'variant' => 'success'],
            'method' => 'Bank',
            'amount' => '₦25,000',
            'status' => ['label' => 'Pending', 'variant' => 'warning'],
        ],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-2xl font-bold tracking-tight text-gray-900">Billing / Transactions</div>
                    <div class="mt-1 text-sm text-gray-600">Track fees received and expenses.</div>
                </div>
                <a
                    href="#"
                    class="btn-primary"
                >
                    Add Transaction
                </a>
            </div>

            <div class="mt-6 flex gap-6 border-b border-gray-100">
                <a
                    href="{{ route('billing.index', ['tab' => 'fees']) }}"
                    class="{{ $tab === 'fees' ? 'border-b-2 border-brand-500 text-brand-700' : 'border-b-2 border-transparent text-gray-600 hover:text-gray-900' }} -mb-px pb-3 text-sm font-semibold"
                >
                    Fees Received
                </a>
                <a
                    href="{{ route('billing.index', ['tab' => 'expenses']) }}"
                    class="{{ $tab === 'expenses' ? 'border-b-2 border-brand-500 text-brand-700' : 'border-b-2 border-transparent text-gray-600 hover:text-gray-900' }} -mb-px pb-3 text-sm font-semibold"
                >
                    Expenses
                </a>
            </div>
        </div>

        @if ($tab === 'expenses')
            <div class="rounded-2xl border border-gray-100 bg-white p-10 text-center shadow-sm">
                <div class="text-lg font-semibold text-gray-900">Expenses</div>
                <div class="mt-2 text-sm text-gray-600">No expense records in this UI demo.</div>
            </div>
        @else
            <x-table>
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Student / Class</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Payment Method</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($transactions as $t)
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ \Illuminate\Support\Carbon::parse($t['date'])->format('M j, Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $t['student'] }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $t['class'] }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <x-status-badge variant="{{ $t['category']['variant'] }}">{{ $t['category']['label'] }}</x-status-badge>
                            </td>
                            <td class="px-5 py-4">
                                <div class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <span class="grid h-8 w-8 place-items-center rounded-full bg-gray-100 text-gray-600">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 7h18v10H3z" />
                                            <path d="M7 7V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </span>
                                    {{ $t['method'] }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">{{ $t['amount'] }}</td>
                            <td class="px-5 py-4">
                                <x-status-badge variant="{{ $t['status']['variant'] }}">{{ $t['status']['label'] }}</x-status-badge>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a
                                    href="{{ route('billing.receipt', $t['id']) }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-brand-700 ring-1 ring-inset ring-brand-100 hover:bg-brand-50"
                                >
                                    Download Receipt
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
        @endif
    </div>
@endsection
