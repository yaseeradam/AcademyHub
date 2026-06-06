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
        <x-slot:after>
            <div class="flex flex-wrap gap-2">
                @if ($canTransactions)
                    <button wire:click="$set('tab', 'transactions')" 
                            class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $tab === 'transactions' ? 'bg-emerald-500 text-white shadow-md' : 'bg-transparent text-slate-600 hover:bg-slate-100' }}">
                        Transactions
                    </button>
                @endif
                <button wire:click="$set('tab', 'debtors')" 
                        class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $tab === 'debtors' ? 'bg-emerald-500 text-white shadow-md' : 'bg-transparent text-slate-600 hover:bg-slate-100' }}">
                    Outstanding Balances
                </button>
                @if ($canFees)
                    <button wire:click="$set('tab', 'fees')" 
                            class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $tab === 'fees' ? 'bg-emerald-500 text-white shadow-md' : 'bg-transparent text-slate-600 hover:bg-slate-100' }}">
                        Fee Structures
                    </button>
                @endif
                <button wire:click="$set('tab', 'plugin-bills')" 
                        class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $tab === 'plugin-bills' ? 'bg-emerald-500 text-white shadow-md' : 'bg-transparent text-slate-600 hover:bg-slate-100' }}">
                    Plugin Invoices
                </button>
            </div>
        </x-slot:after>
    </x-page-header>

    {{-- Financial Overview Cards --}}
    @if ($user?->role === 'admin' || $user?->role === 'bursar')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Total Income</p>
                    <h3 class="text-2xl font-black text-emerald-600">
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($this->totalIncome, 2) }}
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
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($this->totalExpenses, 2) }}
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
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($this->netBalance, 2) }}
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
                
                @if ($type === 'Income')
                    <select wire:model.live="selectedClassId" class="select">
                        <option value="">All Classes</option>
                        @foreach ($this->classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    
                    <select wire:model.live="studentId" class="select">
                        <option value="">Select Student</option>
                        @foreach ($this->students as $student)
                            <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                        @endforeach
                    </select>
                @else
                    <div class="sm:col-span-2 text-sm text-gray-500 flex items-center">Student selection not required for expenses</div>
                @endif
                
                <input wire:model.live="category" type="text" placeholder="Category" class="input-compact" />
                <input wire:model.live="amountPaid" type="number" step="0.01" placeholder="Amount" class="input-compact" />
                
                @if ($type === 'Income')
                    <select wire:model.live="paymentMethod" class="select">
                        <option value="Cash">Cash</option>
                        <option value="Transfer">Transfer</option>
                        <option value="POS">POS</option>
                    </select>
                    <input wire:model.live="session" type="text" placeholder="Session" class="input-compact" />
                    <select wire:model.live="term" class="select">
                        <option value="">Term</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                @else
                    <div class="sm:col-span-2"></div>
                @endif
                
                <div class="flex gap-2">
                    <input wire:model.live="date" type="date" class="input-compact flex-1" />
                    <button type="submit" class="btn-primary px-6">Save</button>
                </div>
            </form>
        </div>
    @endif

    @if ($tab === 'transactions' && $canTransactions)
        <!-- Transaction Filters -->
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
            <div class="mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <h4 class="font-medium text-gray-900">Filter Transactions</h4>
            </div>
            <div class="grid gap-3 sm:grid-cols-6">
                <select wire:model.live="filterType" class="select">
                    <option value="">All Types</option>
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>
                <input wire:model.live.debounce.300ms="filterCategory" type="text" placeholder="Category" class="input-compact" />
                <select wire:model.live="selectedClassId" class="select">
                    <option value="">All Classes</option>
                    @foreach ($this->classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStudentId" class="select">
                    <option value="">All Students</option>
                    @foreach ($this->students as $student)
                        <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                    @endforeach
                </select>
                <input wire:model.live="filterFrom" type="date" class="input-compact" />
                <input wire:model.live="filterTo" type="date" class="input-compact" />
            </div>
        </div>

    @elseif ($tab === 'debtors')
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-orange-600/10"></div>
            <div class="relative p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Outstanding Balances</h3>
                        <p class="text-sm text-gray-600">Students with pending payments</p>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
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
                        <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">{{ config('academyhub.currency_symbol') }}{{ number_format($row['due'], 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">{{ config('academyhub.currency_symbol') }}{{ number_format($row['paid'], 2) }}</td>
                        <td class="px-5 py-4 text-right">
                            <x-status-badge variant="warning">{{ config('academyhub.currency_symbol') }}{{ number_format($row['balance'], 2) }}</x-status-badge>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No debtors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
    @elseif ($tab === 'fees' && $canFees)
        <!-- Quick Fee Setup -->
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-blue-600/10"></div>
            <div class="relative p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Fee Structure</h3>
                        <p class="text-sm text-gray-600">Configure class fee amounts</p>
                    </div>
                </div>
                <form wire:submit="saveFeeStructure" class="grid gap-4 sm:grid-cols-5">
                <select wire:model.live="feeClassId" class="select">
                    <option value="">Select Class</option>
                    @foreach ($this->classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <input wire:model.live="feeCategory" type="text" placeholder="Category" class="input-compact" />
                <input wire:model.live="feeAmountDue" type="number" step="0.01" placeholder="Amount" class="input-compact" />
                <div class="flex gap-2">
                    <select wire:model.live="feeTerm" class="select flex-1">
                        <option value="">All Terms</option>
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                    </select>
                    <button type="submit" class="btn-primary px-6">
                        {{ $editingFeeId ? 'Update' : 'Save' }}
                    </button>
                </div>
                @if ($editingFeeId)
                    <button type="button" wire:click="cancelEditFee" class="btn-outline">Cancel</button>
                @endif
            </form>
            </div>
        </div>
        
        <!-- Fee Structure Filters -->
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
            <div class="mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <h4 class="font-medium text-gray-900">Filter Fee Structures</h4>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <select wire:model.live="feeFilterClassId" class="select">
                    <option value="">All Classes</option>
                    @foreach ($this->classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <input wire:model.live.debounce.300ms="feeFilterCategory" type="text" placeholder="Category" class="input-compact" />
                <select wire:model.live="feeFilterTerm" class="select">
                    <option value="">All Terms</option>
                    <option value="1">Term 1</option>
                    <option value="2">Term 2</option>
                    <option value="3">Term 3</option>
                </select>
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
                            {{ config('academyhub.currency_symbol') }}{{ number_format((float) $fee->amount_due, 2) }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-1">
                                <button type="button" wire:click="startEditFee({{ $fee->id }})" class="btn-outline btn-sm">Edit</button>
                                <button type="button" wire:click="deleteFeeStructure({{ $fee->id }})" onclick="return confirm('Delete?')" class="btn-ghost btn-sm text-red-600">Delete</button>
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
                            <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">{{ config('academyhub.currency_symbol') }}{{ number_format((float) $t->amount_paid, 2) }}</td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-700">
                                <div class="flex items-center gap-2">
                                    <span>{{ $t->receipt_number ?: '-' }}</span>
                                    @if ($t->is_void)
                                        <x-status-badge variant="warning">VOID</x-status-badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-1">
                                    @if ($t->receipt_number)
                                        <a href="{{ route('billing.receipt', $t) }}" class="btn-outline btn-sm">Receipt</a>
                                    @endif
                                    @if ($canVoid && !$t->is_void)
                                        <button type="button" wire:click="startVoid({{ $t->id }})" class="btn-ghost btn-sm text-red-600">Void</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if ($canVoid && $voidingTransactionId === $t->id)
                            <tr class="bg-red-50">
                                <td colspan="7" class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <input wire:model.live="voidReason" type="text" class="input-compact flex-1" placeholder="Void reason (optional)" />
                                        <button type="button" wire:click="cancelVoid" class="btn-outline btn-sm">Cancel</button>
                                        <button type="button" wire:click="confirmVoid({{ $t->id }})" class="btn-primary btn-sm">Confirm</button>
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
    @elseif ($tab === 'plugin-bills')
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-emerald-600/10"></div>
            <div class="relative p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Plugin Invoices</h3>
                        <p class="text-sm text-gray-600">Pending setup and usage billing for your installed marketplace components</p>
                    </div>
                </div>
            </div>
        </div>

        <x-table>
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-3">Plugin / Component</th>
                    <th class="px-5 py-3">Bill Type</th>
                    <th class="px-5 py-3">Billing Period</th>
                    <th class="px-5 py-3 text-right">Student Count</th>
                    <th class="px-5 py-3 text-right">Pricing Details</th>
                    <th class="px-5 py-3 text-right">Total Due</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($this->pluginBills as $bill)
                    <tr class="bg-white hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="rounded-xl bg-slate-100 p-2 text-slate-700 font-bold">
                                    @if(!empty($bill->marketplaceComponent->icon) && str_contains($bill->marketplaceComponent->icon, '<svg'))
                                        <div class="h-5 w-5 [&>svg]:w-5 [&>svg]:h-5 [&>svg]:stroke-current">{!! $bill->marketplaceComponent->icon !!}</div>
                                    @else
                                        <span class="text-lg">🧩</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $bill->marketplaceComponent->name ?? 'Plugin' }}</div>
                                    <div class="text-xs text-gray-500">{{ $bill->marketplaceComponent->short_description ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700 capitalize">
                            @if ($bill->bill_type === 'setup')
                                <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-xs font-semibold text-purple-700 ring-1 ring-inset ring-purple-600/10">Setup Fee</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/10">Usage Fee</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700">
                            @if ($bill->bill_type === 'usage')
                                <div>{{ $bill->term_name }}</div>
                                <div class="text-xs text-gray-500">{{ $bill->session_name }}</div>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right text-sm text-gray-700">
                            @if ($bill->bill_type === 'usage')
                                {{ number_format($bill->student_count) }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right text-sm text-gray-700">
                            @if ($bill->bill_type === 'setup')
                                <div>Setup: {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($bill->setup_fee, 2) }}</div>
                            @else
                                <div>Rate: {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($bill->usage_fee_per_student, 2) }}/std</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">
                            {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($bill->total_due, 2) }}
                        </td>
                        <td class="px-5 py-4">
                            @if ($bill->status === 'paid')
                                <x-status-badge variant="success">Paid</x-status-badge>
                                @if ($bill->paid_at)
                                    <div class="mt-1 text-[10px] text-gray-400">on {{ $bill->paid_at->format('M j, Y') }}</div>
                                @endif
                            @elseif ($bill->status === 'void')
                                <x-status-badge>Voided</x-status-badge>
                            @else
                                <x-status-badge variant="warning">Unpaid</x-status-badge>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if ($bill->status === 'unpaid')
                                <button type="button" 
                                        wire:click="payPluginBill({{ $bill->id }})" 
                                        wire:loading.attr="disabled"
                                        class="btn-primary btn-sm bg-emerald-600 hover:bg-emerald-700 border-none px-4 text-white">
                                    <span wire:loading.remove wire:target="payPluginBill({{ $bill->id }})">Pay Now</span>
                                    <span wire:loading wire:target="payPluginBill({{ $bill->id }})">...</span>
                                </button>
                            @else
                                <span class="text-gray-400 text-xs font-medium">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500">No plugin bills or invoices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
    @endif
</div>

@assets
<script src="https://js.paystack.co/v1/inline.js" defer></script>
@endassets

@script
    $wire.on('initialize-plugin-paystack', (eventData) => {
        let data = Array.isArray(eventData) ? eventData[0] : eventData;
        let handler = PaystackPop.setup({
            key: '{{ env('PAYSTACK_PUBLIC_KEY', 'pk_test_') }}',
            email: data.email,
            amount: data.amount,
            ref: data.ref,
            currency: 'NGN',
            callback: function(response) {
                $wire.verifyPluginBillPayment(response.reference);
            },
            onClose: function() {
                // Optional
            }
        });
        handler.openIframe();
    });
@endscript
