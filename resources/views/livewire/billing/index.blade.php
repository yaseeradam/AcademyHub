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
                            class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $tab === 'transactions' ? 'bg-emerald-500 text-white shadow-md' : 'bg-transparent text-slate-600 hover:bg-slate-100' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        Transactions
                    </button>
                @endif
                <button wire:click="$set('tab', 'debtors')" 
                        class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $tab === 'debtors' ? 'bg-emerald-500 text-white shadow-md' : 'bg-transparent text-slate-600 hover:bg-slate-100' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    Outstanding Balances
                </button>
                @if ($canFees)
                    <button wire:click="$set('tab', 'fees')" 
                            class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $tab === 'fees' ? 'bg-emerald-500 text-white shadow-md' : 'bg-transparent text-slate-600 hover:bg-slate-100' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        Fee Structures
                    </button>
                @endif
                <button wire:click="$set('tab', 'plugin-bills')" 
                        class="px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $tab === 'plugin-bills' ? 'bg-emerald-500 text-white shadow-md' : 'bg-transparent text-slate-600 hover:bg-slate-100' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    Plugin Invoices
                </button>
            </div>
        </x-slot:after>
    </x-page-header>

    {{-- Financial Overview Cards --}}
    @if ($user?->role === 'admin' || $user?->role === 'bursar' || $user?->role === 'proprietor')
        <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 mb-4 sm:mb-6">
            <div class="bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex items-center justify-between active:scale-[0.98] transition-transform">
                <div class="min-w-0 flex-1 pr-2">
                    <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider mb-0.5 sm:mb-1 truncate">Total Income</p>
                    <h3 class="text-lg sm:text-2xl font-black text-emerald-600 truncate">
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($this->totalIncome, 2) }}
                    </h3>
                </div>
                <div class="h-9 w-9 sm:h-12 sm:w-12 rounded-xl sm:rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex items-center justify-between active:scale-[0.98] transition-transform">
                <div class="min-w-0 flex-1 pr-2">
                    <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider mb-0.5 sm:mb-1 truncate">Total Expenses</p>
                    <h3 class="text-lg sm:text-2xl font-black text-rose-500 truncate">
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($this->totalExpenses, 2) }}
                    </h3>
                </div>
                <div class="h-9 w-9 sm:h-12 sm:w-12 rounded-xl sm:rounded-full bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </div>
            </div>

            <div class="col-span-2 sm:col-span-1 bg-white rounded-2xl p-3.5 sm:p-5 lg:p-6 shadow-sm border border-slate-100 flex items-center justify-between active:scale-[0.98] transition-transform">
                <div class="min-w-0 flex-1 pr-2">
                    <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider mb-0.5 sm:mb-1 truncate">Net Balance</p>
                    <h3 class="text-lg sm:text-2xl font-black truncate {{ $this->netBalance >= 0 ? 'text-slate-900' : 'text-rose-600' }}">
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($this->netBalance, 2) }}
                    </h3>
                </div>
                <div class="h-9 w-9 sm:h-12 sm:w-12 rounded-xl sm:rounded-full bg-slate-50 text-slate-500 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
            </div>
        </div>
    @endif

    @if ($canTransactions)
        <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 shadow-lg sm:shadow-xl transition-all duration-300">
            <!-- Header bar with subtle gradient background and mode toggle -->
            <div class="px-4 sm:px-6 py-3.5 sm:py-5 bg-gradient-to-r from-slate-50 via-slate-50/50 to-white dark:from-slate-800/80 dark:to-slate-800 border-b border-slate-200/70 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3 sm:gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl {{ $type === 'Income' ? 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400' }} flex items-center justify-center shadow-inner shrink-0">
                        @if($type === 'Income')
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        @else
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                            <h2 class="text-sm sm:text-lg font-bold text-slate-900 dark:text-white">
                                {{ $type === 'Income' ? 'Cashier & Fee Payment Terminal' : 'Record School Expense' }}
                            </h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold {{ $type === 'Income' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-800/50' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200/50 dark:border-rose-800/50' }}">
                                {{ $type === 'Income' ? 'Fee Inflow' : 'Operating Outflow' }}
                            </span>
                        </div>
                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $type === 'Income' ? 'Receive cash, bank transfers, or card payments with automatic ledger reconciliation' : 'Record school maintenance, supplies, or staff reimbursements' }}
                        </p>
                    </div>
                </div>

                <!-- Segmented Type Selector (Income / Expense) -->
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-900/60 rounded-xl border border-slate-200/80 dark:border-slate-700 w-full sm:w-auto">
                    <button type="button" 
                            wire:click="$set('type', 'Income')" 
                            class="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all active:scale-95 {{ $type === 'Income' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Fee Inflow (Income)
                    </button>
                    <button type="button" 
                            wire:click="$set('type', 'Expense')" 
                            class="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all active:scale-95 {{ $type === 'Expense' ? 'bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                        Expense (Payout)
                    </button>
                </div>
            </div>

            <form wire:submit="saveTransaction" class="p-4 sm:p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    {{-- Left Section: Student & Category Selection (7 Cols on desktop) --}}
                    <div class="lg:col-span-7 space-y-4">
                        @if ($type === 'Income')
                            {{-- Class & Student Picker --}}
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <div class="sm:col-span-5">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        Filter Class
                                    </label>
                                    <select wire:model.live="selectedClassId" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700/80 text-slate-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">All Classes (All Students)</option>
                                        @foreach ($this->classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-7">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            Select Student *
                                        </span>
                                        <span class="text-[11px] font-normal text-slate-400">({{ $this->students->count() }} students)</span>
                                    </label>
                                    <select wire:model.live="studentId" class="w-full text-sm font-medium rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700/80 text-slate-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">-- Choose Student to Receive Cash --</option>
                                        @foreach ($this->students as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->full_name }} ({{ $student->admission_number ?? 'No Adm #' }}) - {{ $student->schoolClass?->name ?? 'No Class' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('studentId') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Student Financial Ledger Snapshot --}}
                            @if ($this->selectedStudent)
                                @php
                                    $balanceInfo = $this->selectedStudentBalance;
                                    $student = $this->selectedStudent;
                                @endphp
                                <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-500/5 via-teal-500/5 to-slate-50 dark:from-emerald-950/30 dark:to-slate-800/50 border border-emerald-200/60 dark:border-emerald-800/40 transition-all">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300 flex items-center justify-center font-bold text-sm">
                                                {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                                    {{ $student->full_name }}
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono">
                                                        {{ $student->admission_number }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                                    Class: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $student->schoolClass?->name ?? 'N/A' }}</span>
                                                    @if($student->section) &middot; Section: {{ $student->section->name }} @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if ($balanceInfo)
                                            <div class="flex items-center gap-4 text-xs">
                                                <div class="text-right">
                                                    <div class="text-slate-400 text-[11px] uppercase tracking-wider font-semibold">Total Fee Due</div>
                                                    <div class="font-bold text-slate-800 dark:text-slate-200 font-mono">₦{{ number_format($balanceInfo['due'], 2) }}</div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-slate-400 text-[11px] uppercase tracking-wider font-semibold">Total Paid</div>
                                                    <div class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">₦{{ number_format($balanceInfo['paid'], 2) }}</div>
                                                </div>
                                                <div class="text-right pl-3 border-l border-slate-200 dark:border-slate-700">
                                                    <div class="text-rose-500 text-[11px] uppercase tracking-wider font-bold">Outstanding</div>
                                                    <div class="text-sm font-extrabold text-rose-600 dark:text-rose-400 font-mono">₦{{ number_format($balanceInfo['balance'], 2) }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($balanceInfo && $balanceInfo['balance'] > 0)
                                        <div class="mt-3 pt-2.5 border-t border-emerald-100 dark:border-emerald-800/40 flex items-center justify-between">
                                            <span class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Unpaid balance available for auto-fill
                                            </span>
                                            <button type="button" 
                                                    wire:click="applyStudentBalance" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/60 rounded-lg hover:bg-emerald-200 transition-colors shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Apply Outstanding Balance (₦{{ number_format($balanceInfo['balance'], 2) }})
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @else
                            {{-- Expense Description / Purpose --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Expense Purpose / Description *
                                </label>
                                <input wire:model.live="category" type="text" placeholder="e.g. Generator Fuel, Science Lab Chemicals, Classroom Chalk" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700/80 text-slate-900 dark:text-white shadow-sm focus:border-rose-500 focus:ring-rose-500" />
                                @error('category') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        {{-- Fee Category & Academic Timeline --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Payment Category *
                                </label>
                                <input wire:model.live="category" type="text" list="category-suggestions" placeholder="e.g. Tuition" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700/80 text-slate-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                <datalist id="category-suggestions">
                                    <option value="Tuition">
                                    <option value="Uniform">
                                    <option value="Books">
                                    <option value="Registration">
                                    <option value="Transport">
                                    <option value="PTA Levy">
                                    <option value="Graduation">
                                    <option value="Examination">
                                </datalist>
                                @error('category') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            @if ($type === 'Income')
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Academic Session</label>
                                    <input wire:model.live="session" type="text" placeholder="2025/2026" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700/80 text-slate-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Academic Term</label>
                                    <select wire:model.live="term" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700/80 text-slate-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">Full Year / General</option>
                                        <option value="1">Term 1 (First Term)</option>
                                        <option value="2">Term 2 (Second Term)</option>
                                        <option value="3">Term 3 (Third Term)</option>
                                    </select>
                                </div>
                            @else
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Transaction Date *</label>
                                    <input wire:model.live="date" type="date" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700/80 text-slate-900 dark:text-white shadow-sm focus:border-rose-500 focus:ring-rose-500" />
                                </div>
                            @endif
                        </div>

                        @if ($type === 'Income')
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Transaction Date *</label>
                                <input wire:model.live="date" type="date" class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700/80 text-slate-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                @error('date') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    {{-- Right Section: Payment Register & Amount (5 Cols on desktop) --}}
                    <div class="lg:col-span-5 bg-slate-50/70 dark:bg-slate-800/60 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between space-y-4">
                        
                        {{-- Payment Method Pills (Cash, Transfer, POS) --}}
                        @if ($type === 'Income')
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                                    Payment Method
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    <!-- CASH -->
                                    <button type="button" 
                                            wire:click="setPaymentMethod('Cash')" 
                                            class="relative flex flex-col items-center justify-center py-3 px-2 rounded-xl border-2 transition-all {{ $paymentMethod === 'Cash' ? 'border-emerald-500 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 shadow-md ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300' }}">
                                        <svg class="w-5 h-5 mb-1 {{ $paymentMethod === 'Cash' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span class="text-xs font-bold">Cash</span>
                                        @if($paymentMethod === 'Cash')
                                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center text-[10px] text-white">✓</span>
                                        @endif
                                    </button>

                                    <!-- TRANSFER -->
                                    <button type="button" 
                                            wire:click="setPaymentMethod('Transfer')" 
                                            class="relative flex flex-col items-center justify-center py-3 px-2 rounded-xl border-2 transition-all {{ $paymentMethod === 'Transfer' ? 'border-indigo-500 bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 shadow-md ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300' }}">
                                        <svg class="w-5 h-5 mb-1 {{ $paymentMethod === 'Transfer' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                        </svg>
                                        <span class="text-xs font-bold">Transfer</span>
                                        @if($paymentMethod === 'Transfer')
                                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] text-white">✓</span>
                                        @endif
                                    </button>

                                    <!-- POS -->
                                    <button type="button" 
                                            wire:click="setPaymentMethod('POS')" 
                                            class="relative flex flex-col items-center justify-center py-3 px-2 rounded-xl border-2 transition-all {{ $paymentMethod === 'POS' ? 'border-purple-500 bg-purple-500/10 text-purple-700 dark:text-purple-300 shadow-md ring-2 ring-purple-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300' }}">
                                        <svg class="w-5 h-5 mb-1 {{ $paymentMethod === 'POS' ? 'text-purple-600 dark:text-purple-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        <span class="text-xs font-bold">POS / Card</span>
                                        @if($paymentMethod === 'POS')
                                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-purple-500 flex items-center justify-center text-[10px] text-white">✓</span>
                                        @endif
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Prominent Amount Register --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Amount to Record (₦) *
                                </label>
                                @if((float)$amountPaid > 0)
                                    <button type="button" wire:click="clearAmount" class="text-xs font-semibold text-rose-500 hover:text-rose-700">
                                        Clear ✕
                                    </button>
                                @endif
                            </div>

                            <div class="relative rounded-2xl shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="text-2xl font-black text-slate-400 dark:text-slate-500">₦</span>
                                </div>
                                <input wire:model.live="amountPaid" 
                                       type="number" 
                                       step="0.01" 
                                       min="0" 
                                       placeholder="0.00" 
                                       class="w-full pl-11 pr-4 py-3.5 text-2xl sm:text-3xl font-black font-mono tracking-tight text-slate-900 dark:text-white bg-white dark:bg-slate-800 rounded-2xl border-2 {{ $errors->has('amountPaid') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-300 dark:border-slate-600 focus:border-emerald-500 focus:ring-emerald-500' }} transition-all" />
                            </div>
                            @error('amountPaid') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror

                            {{-- Cash Preset Fast-Tap Chips --}}
                            <div class="mt-3">
                                <div class="text-[11px] font-semibold text-slate-400 mb-1.5">Quick Cash Presets:</div>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" wire:click="addQuickAmount(1000)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all shadow-2xs">
                                        +₦1k
                                    </button>
                                    <button type="button" wire:click="addQuickAmount(5000)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all shadow-2xs">
                                        +₦5k
                                    </button>
                                    <button type="button" wire:click="addQuickAmount(10000)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all shadow-2xs">
                                        +₦10k
                                    </button>
                                    <button type="button" wire:click="addQuickAmount(20000)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all shadow-2xs">
                                        +₦20k
                                    </button>
                                    <button type="button" wire:click="addQuickAmount(50000)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all shadow-2xs">
                                        +₦50k
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-2">
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="w-full py-4 px-6 rounded-2xl font-bold text-white text-base shadow-lg transition-all duration-200 flex items-center justify-center gap-2 {{ $type === 'Income' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-500/25' : 'bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 shadow-rose-500/25' }}">
                                <span wire:loading.remove wire:target="saveTransaction" class="flex items-center gap-2">
                                    @if ($type === 'Income')
                                        @if ($paymentMethod === 'Cash')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            <span>Confirm &amp; Record {{ (float)$amountPaid > 0 ? '₦' . number_format((float)$amountPaid, 2) . ' ' : '' }}Cash Payment</span>
                                        @elseif ($paymentMethod === 'Transfer')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                                            <span>Record {{ (float)$amountPaid > 0 ? '₦' . number_format((float)$amountPaid, 2) . ' ' : '' }}Bank Transfer</span>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                            <span>Record {{ (float)$amountPaid > 0 ? '₦' . number_format((float)$amountPaid, 2) . ' ' : '' }}POS Payment</span>
                                        @endif
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                        <span>Record {{ (float)$amountPaid > 0 ? '₦' . number_format((float)$amountPaid, 2) . ' ' : '' }}Expense</span>
                                    @endif
                                </span>
                                <span wire:loading wire:target="saveTransaction" class="flex items-center gap-2">
                                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Processing &amp; Generating Receipt...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endif

    @if ($tab === 'transactions' && $canTransactions)
        <!-- Transaction Filters -->
        <div class="rounded-xl sm:rounded-2xl bg-white p-4 sm:p-5 shadow-sm ring-1 ring-gray-200">
            <div class="mb-3 sm:mb-4 flex items-center gap-2">
                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <h4 class="text-sm sm:text-base font-semibold text-gray-900">Filter Transactions</h4>
            </div>
            <div class="grid gap-2.5 sm:gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
                <select wire:model.live="filterType" class="select text-xs sm:text-sm">
                    <option value="">All Types</option>
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>
                <input wire:model.live.debounce.300ms="filterCategory" type="text" placeholder="Category" class="input-compact text-xs sm:text-sm" />
                <select wire:model.live="selectedClassId" class="select text-xs sm:text-sm">
                    <option value="">All Classes</option>
                    @foreach ($this->classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStudentId" class="select text-xs sm:text-sm">
                    <option value="">All Students</option>
                    @foreach ($this->students as $student)
                        <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                    @endforeach
                </select>
                <input wire:model.live="filterFrom" type="date" class="input-compact text-xs sm:text-sm" />
                <input wire:model.live="filterTo" type="date" class="input-compact text-xs sm:text-sm" />
            </div>
        </div>

    @elseif ($tab === 'debtors')
        <div class="relative overflow-hidden rounded-xl sm:rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-orange-600/10"></div>
            <div class="relative p-4 sm:p-6">
                <div class="mb-3 sm:mb-4 flex items-center gap-2.5 sm:gap-3">
                    <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-xl bg-orange-500 text-white shrink-0">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">Outstanding Balances</h3>
                        <p class="text-xs sm:text-sm text-gray-600">Students with pending payments</p>
                    </div>
                </div>
                <div class="grid gap-2.5 sm:gap-4 sm:grid-cols-3">
                    <input wire:model.live.debounce.300ms="debtorsCategory" type="text" placeholder="Category (e.g. Tuition)" class="input-compact text-xs sm:text-sm" />
                    <input wire:model.live.debounce.300ms="debtorsSession" type="text" placeholder="Session (e.g. 2025/2026)" class="input-compact text-xs sm:text-sm" />
                    <select wire:model.live="debtorsTerm" class="select text-xs sm:text-sm">
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
                    <button type="button" wire:click="cancelEditFee" class="btn-outline" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Cancel</button>
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
                                        <button type="button" wire:click="cancelVoid" class="btn-outline btn-sm" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Cancel</button>
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
        @if ($errorMessage)
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-600 font-semibold flex items-center gap-2.5 shadow-sm">
                <svg class="h-5 w-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $errorMessage }}</span>
            </div>
        @endif
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


