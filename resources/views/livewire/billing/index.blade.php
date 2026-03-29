@php
    $user = auth()->user();
    $canTransactions = $user?->hasPermission('billing.transactions');
    $canFees = $user?->hasPermission('fees.manage');
    $canExport = $user?->hasPermission('billing.export');
    $canVoid = $user?->hasPermission('billing.void');
@endphp

<div class="space-y-6">
    <x-page-header title="Billing" subtitle="Record fees and expenses" accent="billing">
        <x-slot:actions>
            @if ($user?->role === 'bursar')
                <a href="{{ route('accounts') }}" class="btn-outline">Accounts</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Financial Overview Cards --}}
    @if ($user?->role === 'admin' || $user?->role === 'bursar')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Total Income</p>
                    <h3 class="text-2xl font-black text-emerald-600">
                        {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($this->totalIncome, 2) }}
                    </h3>
                </div>
                <div class="h-12 w-12 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Total Expenses</p>
                    <h3 class="text-2xl font-black text-rose-500">
                        {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($this->totalExpenses, 2) }}
                    </h3>
                </div>
                <div class="h-12 w-12 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Net Balance</p>
                    <h3 class="text-2xl font-black {{ $this->netBalance >= 0 ? 'text-slate-900' : 'text-rose-600' }}">
                        {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($this->netBalance, 2) }}
                    </h3>
                </div>
                <div class="h-12 w-12 rounded-full bg-slate-50 text-slate-500 flex items-center justify-center">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
            </div>
        </div>
    @endif

    @if ($canTransactions)
        <div class="rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-6 shadow-sm ring-1 ring-emerald-200/50">
            <div class="text-sm font-semibold text-emerald-900">New Transaction</div>
            <div class="mt-1 text-xs text-emerald-700">Record income (fees) or expenses</div>

            <form wire:submit="saveTransaction" class="mt-4 grid gap-3 sm:grid-cols-6">
                <select wire:model.live="type" class="sm:col-span-1 select">
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>

                <select wire:model.live="studentId" @disabled($type !== 'Income') class="sm:col-span-2 select">
                    <option value="">Student (Income only)</option>
                    @foreach ($this->students as $student)
                        <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->admission_number }})</option>
                    @endforeach
                </select>

                <input wire:model.live="category" type="text" placeholder="Category (e.g. Tuition)" class="sm:col-span-1 input-compact" />
                <input wire:model.live="amountPaid" type="number" step="0.01" placeholder="Amount" class="sm:col-span-1 input-compact" />
                <button type="submit" class="sm:col-span-1 btn-primary">Save</button>

                <select wire:model.live="paymentMethod" @disabled($type !== 'Income') class="sm:col-span-1 select">
                    <option value="Cash">Cash</option>
                    <option value="Transfer">Transfer</option>
                    <option value="POS">POS</option>
                </select>

                <input wire:model.live="session" @disabled($type !== 'Income') type="text" placeholder="2025/2026" class="sm:col-span-2 input-compact" />

                <select wire:model.live="term" @disabled($type !== 'Income') class="sm:col-span-1 select">
                    <option value="">-</option>
                    <option value="1">Term 1</option>
                    <option value="2">Term 2</option>
                    <option value="3">Term 3</option>
                </select>

                <input wire:model.live="date" type="date" class="sm:col-span-2 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
            </form>
        </div>
    @endif

    @if ($tab === 'transactions' && $canTransactions)
        <div class="card-padded">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Filters</div>
                    <div class="mt-1 text-sm text-gray-600">Narrow the transaction list (up to 100 rows).</div>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                    @if ($canExport)
                        <a
                            href="{{ route('billing.export.transactions', [
                                'type' => $filterType,
                                'category' => $filterCategory,
                                'student_id' => $filterStudentId,
                                'session' => $filterSession,
                                'term' => $filterTerm,
                                'from' => $filterFrom,
                                'to' => $filterTo,
                                'include_voided' => $includeVoided ? 1 : 0,
                            ]) }}"
                            class="inline-flex items-center gap-1 rounded-lg bg-green-50 px-3 py-2 font-semibold text-green-700 ring-1 ring-green-200 hover:bg-green-100"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                            </svg>
                            Export CSV
                        </a>
                    @endif
                    <a href="{{ route('billing.index') }}" class="underline">Reset Filters</a>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <div class="lg:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Category</label>
                    <input wire:model.live.debounce.300ms="filterCategory" type="text" placeholder="e.g. Tuition" class="mt-2 input-compact" />
                </div>

                <div class="lg:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Student</label>
                    <select wire:model.live="filterStudentId" class="mt-2 select">
                        <option value="">All</option>
                        @foreach ($this->students as $student)
                            <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->admission_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Type</label>
                    <select wire:model.live="filterType" class="mt-2 select">
                        <option value="">All</option>
                        <option value="Income">Income</option>
                        <option value="Expense">Expense</option>
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Voided</label>
                    <select wire:model.live="includeVoided" class="mt-2 select">
                        <option value="0">Hide</option>
                        <option value="1">Show</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Session</label>
                    <input wire:model.live="filterSession" type="text" placeholder="2025/2026" class="mt-2 input-compact" />
                </div>

                <div class="lg:col-span-1">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term</label>
                    <select wire:model.live="filterTerm" class="mt-2 select">
                        <option value="">All</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">From</label>
                    <input wire:model.live="filterFrom" type="date" class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                </div>

                <div class="lg:col-span-1">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">To</label>
                    <input wire:model.live="filterTo" type="date" class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                </div>
            </div>
        </div>
    @endif

    @if ($tab === 'debtors')
        <div>
            <div class="card-padded">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-gray-900">
                            Debtors ({{ trim($debtorsCategory) !== '' ? trim($debtorsCategory) : 'Tuition' }})
                        </div>
                        <div class="mt-1 text-sm text-gray-600">Uses the configured fee structure per class and sums recorded payments.</div>
                    </div>

                    <div class="grid w-full gap-3 sm:grid-cols-3 lg:max-w-4xl">
                        <input wire:model.live.debounce.300ms="debtorsCategory" type="text" placeholder="Category (e.g. Tuition)" class="input-compact" />
                        <input wire:model.live.debounce.300ms="debtorsSession" type="text" placeholder="Session (e.g. 2025/2026)" class="input-compact" />
                        <select wire:model.live="debtorsTerm" class="select">
                            <option value="">All terms</option>
                            <option value="1">Term 1</option>
                            <option value="2">Term 2</option>
                            <option value="3">Term 3</option>
                        </select>
                    </div>
                </div>
            </div>

            <x-table>
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Student</th>
                        <th class="px-5 py-3">Class</th>
                        <th class="px-5 py-3 text-right">Due</th>
                        <th class="px-5 py-3 text-right">Paid</th>
                        <th class="px-5 py-3 text-right">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->debtors as $row)
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $row['student']->full_name }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $row['student']->admission_number }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ $row['student']->schoolClass?->name }} / {{ $row['student']->section?->name }}
                            </td>
                            <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">{{ config('myacademy.currency_symbol') }}{{ number_format($row['due'], 2) }}</td>
                            <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">{{ config('myacademy.currency_symbol') }}{{ number_format($row['paid'], 2) }}</td>
                            <td class="px-5 py-4 text-right">
                                <x-status-badge variant="warning">{{ config('myacademy.currency_symbol') }}{{ number_format($row['balance'], 2) }}</x-status-badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No debtors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>
    @elseif ($tab === 'fees')
        @if (! $canFees)
            <div class="card-padded text-sm text-gray-600">You do not have permission to manage fee structures.</div>
        @else
            <div class="card-padded">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-gray-900">Fee Structures</div>
                        <div class="mt-1 text-sm text-gray-600">Set amount due per class/category. Term/session rows override defaults.</div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($editingFeeId)
                            <x-status-badge variant="info">Editing</x-status-badge>
                        @endif
                        <button type="button" wire:click="cancelEditFee" class="btn-outline">
                            {{ $editingFeeId ? 'Cancel edit' : 'Reset form' }}
                        </button>
                    </div>
                </div>

                <form wire:submit="saveFeeStructure" class="mt-4 grid w-full gap-3 sm:grid-cols-6">
                    <select wire:model.live="feeClassId" class="sm:col-span-2 select">
                        <option value="">Class</option>
                        @foreach ($this->classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>

                    <input wire:model.live="feeCategory" type="text" placeholder="Category (e.g. Tuition)" class="sm:col-span-2 input-compact" />

                    <input wire:model.live="feeAmountDue" type="number" step="0.01" placeholder="Amount due" class="sm:col-span-1 input-compact" />

                    <button type="submit" class="sm:col-span-1 btn-primary">
                        {{ $editingFeeId ? 'Update' : 'Save' }}
                    </button>

                    <input wire:model.live="feeSession" type="text" placeholder="Session (optional)" class="sm:col-span-2 input-compact" />

                    <select wire:model.live="feeTerm" class="sm:col-span-1 select">
                        <option value="">All terms</option>
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                    </select>

                    <div class="sm:col-span-3 flex items-center justify-end text-xs text-gray-500">
                        <span>Leave session/term blank for defaults.</span>
                    </div>
                </form>
            </div>

            <div class="card-padded">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Fee Structure List</div>
                        <div class="mt-1 text-sm text-gray-600">Filter and manage saved fee structures (up to 100 rows).</div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div class="lg:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Class</label>
                        <select wire:model.live="feeFilterClassId" class="mt-2 select">
                            <option value="">All</option>
                            @foreach ($this->classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Category</label>
                        <input wire:model.live.debounce.300ms="feeFilterCategory" type="text" placeholder="e.g. Tuition" class="mt-2 input-compact" />
                    </div>

                    <div class="lg:col-span-1">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term</label>
                        <select wire:model.live="feeFilterTerm" class="mt-2 select">
                            <option value="">All</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>

                    <div class="lg:col-span-1">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Session</label>
                        <input wire:model.live.debounce.300ms="feeFilterSession" type="text" placeholder="2025/2026" class="mt-2 input-compact" />
                    </div>
                </div>
            </div>

            <x-table>
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Class</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Session</th>
                        <th class="px-5 py-3">Term</th>
                        <th class="px-5 py-3 text-right">Amount Due</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->feeStructures as $fee)
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm font-semibold text-gray-900">
                                {{ $fee->schoolClass?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $fee->category }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $fee->session ?: 'Default' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $fee->term ?: 'Default' }}</td>
                            <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">
                                {{ config('myacademy.currency_symbol') }}{{ number_format((float) $fee->amount_due, 2) }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button" wire:click="startEditFee({{ $fee->id }})" class="btn-outline">Edit</button>
                                    <button
                                        type="button"
                                        wire:click="deleteFeeStructure({{ $fee->id }})"
                                        onclick="return confirm('Delete fee structure?')"
                                        class="btn-ghost text-rose-700 hover:bg-rose-50"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No fee structures found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        @endif
    @elseif ($tab === 'transactions')
        @if (! $canTransactions)
            <div class="card-padded text-sm text-gray-600">Transactions are disabled for your account.</div>
        @else
            <x-table>
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Student</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3">Receipt</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->transactions as $t)
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $t->date?->format('M j, Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $t->student?->full_name ?: '-' }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $t->student?->admission_number ?: '' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if ($t->is_void)
                                    <x-status-badge variant="warning">Voided</x-status-badge>
                                @elseif ($t->type === 'Income')
                                    <x-status-badge variant="success">Income</x-status-badge>
                                @else
                                    <x-status-badge variant="warning">Expense</x-status-badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700">
                                <div class="font-medium text-gray-900">{{ $t->category }}</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    @if ($t->session)
                                        {{ $t->session }}
                                    @endif
                                    @if ($t->term)
                                        &middot; Term {{ $t->term }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">{{ config('myacademy.currency_symbol') }}{{ number_format((float) $t->amount_paid, 2) }}</td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-700">
                                <div class="flex items-center gap-2">
                                    <span>{{ $t->receipt_number ?: '-' }}</span>
                                    @if ($t->is_void)
                                        <x-status-badge variant="warning">VOID</x-status-badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    @if ($t->receipt_number)
                                        <a
                                            href="{{ route('billing.receipt', $t) }}"
                                            class="{{ $t->is_void ? 'opacity-60' : '' }} inline-flex items-center justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-brand-700 ring-1 ring-inset ring-brand-100 hover:bg-brand-50"
                                        >
                                            Receipt
                                        </a>

                                        @if ($canExport)
                                            <a
                                                href="{{ route('billing.export.transactions', ['student_id' => $t->student_id, 'from' => $t->date?->format('Y-m-d'), 'to' => $t->date?->format('Y-m-d')]) }}"
                                                class="inline-flex items-center justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-200 hover:bg-gray-50"
                                                title="Export this transaction"
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                                                </svg>
                                            </a>
                                        @endif
                                    @endif

                                    @if ($canVoid)
                                        @if (! $t->is_void)
                                            <button type="button" wire:click="startVoid({{ $t->id }})" class="btn-ghost">
                                                Void
                                            </button>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if ($canVoid && $voidingTransactionId === $t->id)
                            <tr class="bg-slate-50">
                                <td colspan="7" class="px-5 py-4">
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-6">
                                        <div class="sm:col-span-4">
                                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Void reason (optional)</label>
                                            <input wire:model.live="voidReason" type="text" class="mt-2 input-compact" placeholder="e.g. Duplicate entry" />
                                        </div>
                                        <div class="sm:col-span-2 flex items-end justify-end gap-2">
                                            <button type="button" wire:click="cancelVoid" class="btn-outline">Cancel</button>
                                            <button type="button" wire:click="confirmVoid({{ $t->id }})" class="btn-primary">Confirm Void</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        @endif
    @endif
</div>
