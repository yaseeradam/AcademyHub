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
            <div class="inline-flex flex-wrap items-center gap-1.5 p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200/80 shadow-2xs">
                @if ($canTransactions)
                    <button wire:click="$set('tab', 'transactions')" 
                            class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-1.5 {{ $tab === 'transactions' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-600/25 ring-1 ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-white/70' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        <svg class="w-3.5 h-3.5 {{ $tab === 'transactions' ? 'text-emerald-100' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Transactions</span>
                    </button>
                @endif
                <button wire:click="$set('tab', 'debtors')" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-1.5 {{ $tab === 'debtors' ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/25 ring-1 ring-amber-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-white/70' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    <svg class="w-3.5 h-3.5 {{ $tab === 'debtors' ? 'text-amber-100' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Outstanding Balances</span>
                </button>
                <button wire:click="$set('tab', 'class-register')" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-1.5 {{ $tab === 'class-register' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-600/25 ring-1 ring-blue-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-white/70' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    <svg class="w-3.5 h-3.5 {{ $tab === 'class-register' ? 'text-blue-100' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Class Fee Register</span>
                </button>
                @if ($canFees)
                    <button wire:click="$set('tab', 'fees')" 
                            class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-1.5 {{ $tab === 'fees' ? 'bg-gradient-to-r from-violet-600 to-purple-600 text-white shadow-md shadow-violet-600/25 ring-1 ring-violet-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-white/70' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        <svg class="w-3.5 h-3.5 {{ $tab === 'fees' ? 'text-violet-100' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                        <span>Fee Structures</span>
                    </button>
                @endif
                <button wire:click="$set('tab', 'plugin-bills')" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-1.5 {{ $tab === 'plugin-bills' ? 'bg-gradient-to-r from-fuchsia-600 to-pink-600 text-white shadow-md shadow-fuchsia-600/25 ring-1 ring-fuchsia-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-white/70' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    <svg class="w-3.5 h-3.5 {{ $tab === 'plugin-bills' ? 'text-fuchsia-100' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                    <span>Plugin Invoices</span>
                </button>
            </div>
        </x-slot:after>
    </x-page-header>

    {{-- Financial Overview Cards --}}
    @if ($user?->role === 'admin' || $user?->role === 'bursar' || $user?->role === 'proprietor')
        <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 mb-4 sm:mb-6">
            {{-- Total Income Card --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-50/90 via-white to-teal-50/40 rounded-2xl p-4 sm:p-5 lg:p-6 shadow-sm hover:shadow-md border-2 border-emerald-200/80 flex items-center justify-between active:scale-[0.98] transition-all group">
                <div class="min-w-0 flex-1 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <p class="text-[10px] sm:text-xs font-bold text-emerald-800 uppercase tracking-wider truncate">Total Income (Inflow)</p>
                    </div>
                    <h3 class="text-xl sm:text-2xl lg:text-3xl font-black text-emerald-600 font-mono tracking-tight truncate">
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($this->totalIncome, 2) }}
                    </h3>
                    <div class="mt-1 flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-100/90 text-emerald-800">Verified Fees &amp; Collections</span>
                    </div>
                </div>
                <div class="h-11 w-11 sm:h-14 sm:w-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/25 group-hover:scale-105 transition-transform">
                    <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
            </div>

            {{-- Total Expenses Card --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-rose-50/90 via-white to-pink-50/40 rounded-2xl p-4 sm:p-5 lg:p-6 shadow-sm hover:shadow-md border-2 border-rose-200/80 flex items-center justify-between active:scale-[0.98] transition-all group">
                <div class="min-w-0 flex-1 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <p class="text-[10px] sm:text-xs font-bold text-rose-800 uppercase tracking-wider truncate">Total Expenses (Outflow)</p>
                    </div>
                    <h3 class="text-xl sm:text-2xl lg:text-3xl font-black text-rose-600 font-mono tracking-tight truncate">
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($this->totalExpenses, 2) }}
                    </h3>
                    <div class="mt-1 flex items-center gap-1 text-[11px] font-semibold text-rose-700">
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-100/90 text-rose-800">Operational Spending</span>
                    </div>
                </div>
                <div class="h-11 w-11 sm:h-14 sm:w-14 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-500/25 group-hover:scale-105 transition-transform">
                    <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </div>
            </div>

            {{-- Net Balance Card --}}
            <div class="col-span-2 sm:col-span-1 relative overflow-hidden bg-gradient-to-br from-indigo-50/90 via-white to-blue-50/40 rounded-2xl p-4 sm:p-5 lg:p-6 shadow-sm hover:shadow-md border-2 border-indigo-200/80 flex items-center justify-between active:scale-[0.98] transition-all group">
                <div class="min-w-0 flex-1 pr-2">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="w-2 h-2 rounded-full {{ $this->netBalance >= 0 ? 'bg-indigo-500' : 'bg-rose-500' }}"></span>
                        <p class="text-[10px] sm:text-xs font-bold text-indigo-900 uppercase tracking-wider truncate">Net School Balance</p>
                    </div>
                    <h3 class="text-xl sm:text-2xl lg:text-3xl font-black font-mono tracking-tight truncate {{ $this->netBalance >= 0 ? 'text-indigo-700' : 'text-rose-600' }}">
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($this->netBalance, 2) }}
                    </h3>
                    <div class="mt-1 flex items-center gap-1 text-[11px] font-semibold {{ $this->netBalance >= 0 ? 'text-indigo-700' : 'text-rose-700' }}">
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold {{ $this->netBalance >= 0 ? 'bg-indigo-100/90 text-indigo-800' : 'bg-rose-100/90 text-rose-800' }}">
                            {{ $this->netBalance >= 0 ? 'Liquid Reserve Available' : 'Deficit' }}
                        </span>
                    </div>
                </div>
                <div class="h-11 w-11 sm:h-14 sm:w-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-600/25 group-hover:scale-105 transition-transform">
                    <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
            </div>
        </div>
    @endif

    @if ($canTransactions)
        <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-white border border-slate-200/80 shadow-lg sm:shadow-xl border-t-4 {{ $type === 'Income' ? 'border-t-emerald-500' : 'border-t-rose-500' }} transition-all duration-300">
            <!-- Header bar with subtle gradient background and mode toggle -->
            <div class="px-4 sm:px-6 py-3.5 sm:py-5 bg-gradient-to-r {{ $type === 'Income' ? 'from-emerald-50/90 via-teal-50/30 to-white' : 'from-rose-50/90 via-pink-50/30 to-white' }} border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3 sm:gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl {{ $type === 'Income' ? 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-500/25' : 'bg-gradient-to-br from-rose-500 to-pink-600 shadow-rose-500/25' }} text-white flex items-center justify-center shadow-md shrink-0">
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
                            <h2 class="text-sm sm:text-lg font-bold text-slate-900">
                                {{ $type === 'Income' ? 'Cashier & Fee Payment Terminal' : 'Record School Expense' }}
                            </h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-bold {{ $type === 'Income' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                {{ $type === 'Income' ? 'Fee Inflow' : 'Operating Outflow' }}
                            </span>
                        </div>
                        <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">
                            {{ $type === 'Income' ? 'Receive cash, bank transfers, or card payments with automatic ledger reconciliation' : 'Record school maintenance, supplies, or staff reimbursements' }}
                        </p>
                    </div>
                </div>

                <!-- Segmented Type Selector (Income / Expense) -->
                <div class="inline-flex p-1 bg-slate-200/80 rounded-xl border border-slate-300/80 w-full sm:w-auto shadow-2xs">
                    <button type="button" 
                            wire:click="$set('type', 'Income')" 
                            class="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 sm:gap-2 px-3.5 sm:px-4 py-1.5 text-xs font-bold rounded-lg transition-all active:scale-95 {{ $type === 'Income' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-600/25 ring-1 ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Fee Inflow (Income)
                    </button>
                    <button type="button" 
                            wire:click="$set('type', 'Expense')" 
                            class="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 sm:gap-2 px-3.5 sm:px-4 py-1.5 text-xs font-bold rounded-lg transition-all active:scale-95 {{ $type === 'Expense' ? 'bg-gradient-to-r from-rose-600 to-pink-600 text-white shadow-md shadow-rose-600/25 ring-1 ring-rose-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
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
                            {{-- Fast Student Search & Picker --}}
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-semibold text-slate-700 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <span>Search Student (Name or Admission #) *</span>
                                    </label>
                                    @if($this->selectedStudent)
                                        <button type="button" wire:click="clearSelectedStudent" class="text-xs text-rose-500 hover:text-rose-700 font-bold flex items-center gap-1">
                                            <span>✕ Clear Selection</span>
                                        </button>
                                    @endif
                                </div>

                                {{-- Live Search Autocomplete Input --}}
                                <div class="relative">
                                    <div class="relative rounded-xl shadow-xs">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <input type="text" 
                                               wire:model.live.debounce.250ms="studentSearch" 
                                               placeholder="Type student name or admission number (e.g. John or 2024/001)..." 
                                               class="w-full pl-10 pr-10 py-2.5 text-sm font-medium rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                        @if($studentSearch)
                                            <button type="button" wire:click="clearSelectedStudent" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-rose-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Dropdown Instant Matches --}}
                                    @if($this->searchResults->isNotEmpty())
                                        <div class="absolute z-30 mt-1 w-full bg-white rounded-2xl shadow-xl border border-slate-200 max-h-64 overflow-y-auto divide-y divide-slate-100">
                                            @foreach($this->searchResults as $res)
                                                <button type="button" 
                                                        wire:click="selectStudent({{ $res->id }})" 
                                                        class="w-full text-left px-4 py-2.5 hover:bg-emerald-50 flex items-center justify-between group transition-colors">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                            {{ strtoupper(substr($res->first_name, 0, 1) . substr($res->last_name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-bold text-slate-900 group-hover:text-emerald-700">
                                                                {{ $res->full_name }}
                                                            </div>
                                                            <div class="text-xs text-slate-500 flex items-center gap-2">
                                                                <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">{{ $res->admission_number ?? 'No Adm #' }}</span>
                                                                <span>&bull;</span>
                                                                <span>{{ $res->schoolClass?->name ?? 'No Class' }} @if($res->section)({{ $res->section->name }})@endif</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="text-xs font-bold text-emerald-600 group-hover:translate-x-0.5 transition-transform flex items-center gap-1">
                                                        Select
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                    </span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- Collapsible Class List Browser for alternate selection --}}
                                <details class="text-xs text-slate-500 cursor-pointer">
                                    <summary class="hover:text-slate-700 font-medium">Or choose student from class list...</summary>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2 pt-2 border-t border-slate-100">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Filter Class</label>
                                            <div class="relative">
                                                <select wire:model.live="selectedClassId" class="w-full appearance-none text-xs rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 pr-8 shadow-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-300 transition-all cursor-pointer">
                                                    <option value="">All Classes</option>
                                                    @foreach ($this->classes as $class)
                                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Student</label>
                                            <div class="relative">
                                                <select wire:model.live="studentId" class="w-full appearance-none text-xs rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2 pr-8 shadow-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-300 transition-all cursor-pointer">
                                                    <option value="">-- Choose Student --</option>
                                                    @foreach ($this->students as $st)
                                                        <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->admission_number ?? 'No Adm #' }})</option>
                                                    @endforeach
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </details>
                                @error('studentId') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            {{-- Student Financial Ledger Snapshot --}}
                            @if ($this->selectedStudent)
                                @php
                                    $balanceInfo = $this->selectedStudentBalance;
                                    $student = $this->selectedStudent;
                                @endphp
                                <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-emerald-50/80 via-teal-50/30 to-white border-2 border-emerald-300/90 shadow-sm transition-all">
                                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3.5 pb-3 border-b border-emerald-100">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-emerald-500/25 shrink-0">
                                                {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                                                    {{ $student->full_name }}
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-mono font-bold border border-slate-200">
                                                        {{ $student->admission_number }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-slate-500 mt-0.5">
                                                    Class: <span class="font-bold text-slate-800">{{ $student->schoolClass?->name ?? 'N/A' }}</span>
                                                    @if($student->section) &middot; Section: <span class="font-bold text-slate-800">{{ $student->section->name }}</span> @endif
                                                </div>
                                            </div>
                                        </div>

                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Active Student Ledger
                                        </span>
                                    </div>

                                    @if ($balanceInfo)
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                            <div class="bg-white p-2.5 rounded-xl border border-slate-200 shadow-2xs text-center">
                                                <div class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Current Term Due</div>
                                                <div class="text-sm sm:text-base font-black text-slate-900 font-mono mt-0.5">₦{{ number_format($balanceInfo['current_due'] ?? $balanceInfo['due'], 2) }}</div>
                                            </div>
                                            <div class="bg-amber-50 p-2.5 rounded-xl border border-amber-200 shadow-2xs text-center">
                                                <div class="text-amber-700 text-[10px] uppercase tracking-wider font-bold">Past Arrears</div>
                                                <div class="text-sm sm:text-base font-black text-amber-700 font-mono mt-0.5">₦{{ number_format($balanceInfo['past_arrears'] ?? 0, 2) }}</div>
                                            </div>
                                            <div class="bg-emerald-50 p-2.5 rounded-xl border border-emerald-200 shadow-2xs text-center">
                                                <div class="text-emerald-700 text-[10px] uppercase tracking-wider font-bold">Total Paid</div>
                                                <div class="text-sm sm:text-base font-black text-emerald-700 font-mono mt-0.5">₦{{ number_format($balanceInfo['paid'], 2) }}</div>
                                            </div>
                                            <div class="bg-rose-50 p-2.5 rounded-xl border-2 border-rose-300 shadow-xs ring-1 ring-rose-200 text-center">
                                                <div class="text-rose-600 text-[10px] uppercase tracking-wider font-extrabold">Net Balance Due</div>
                                                <div class="text-sm sm:text-base font-black text-rose-600 font-mono mt-0.5">₦{{ number_format($balanceInfo['balance'], 2) }}</div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($balanceInfo && $balanceInfo['balance'] > 0)
                                        <div class="mt-3.5 pt-3 border-t border-emerald-100 flex flex-wrap items-center justify-between gap-2">
                                            <span class="text-xs text-slate-600 flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                @if (!empty($balanceInfo['has_arrears']))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Includes ₦{{ number_format($balanceInfo['past_arrears'], 2) }} prior arrears</span>
                                                @else
                                                    <span>Unpaid balance ready for instant receipting</span>
                                                @endif
                                            </span>
                                            <button type="button" 
                                                    wire:click="applyStudentBalance" 
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-extrabold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-600/20 active:scale-95 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Auto-Fill Outstanding (₦{{ number_format($balanceInfo['balance'], 2) }})
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @else
                            {{-- Expense Description / Purpose --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Expense Purpose / Description *
                                </label>
                                <input wire:model.live="category" type="text" placeholder="e.g. Generator Fuel, Science Lab Chemicals, Classroom Chalk" class="w-full text-sm rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-rose-500 focus:ring-rose-500" />
                                @error('category') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        {{-- Fee Category & Academic Timeline --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Payment Category *
                                </label>
                                <input wire:model.live="category" type="text" list="category-suggestions" placeholder="e.g. Tuition" class="w-full text-sm rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
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
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Academic Session</label>
                                    <input wire:model.live="session" type="text" placeholder="2025/2026" class="w-full text-sm rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Academic Term</label>
                                    <div class="relative">
                                        <select wire:model.live="term" class="w-full appearance-none text-sm rounded-xl border border-slate-300 bg-white text-slate-900 px-3 py-2.5 pr-9 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-300 transition-all cursor-pointer">
                                            <option value="">Full Year / General</option>
                                            <option value="1">Term 1 (First Term)</option>
                                            <option value="2">Term 2 (Second Term)</option>
                                            <option value="3">Term 3 (Third Term)</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Transaction Date *</label>
                                    <input wire:model.live="date" type="date" class="w-full text-sm rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-rose-500 focus:ring-rose-500" />
                                </div>
                            @endif
                        </div>

                        @if ($type === 'Income')
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Transaction Date *</label>
                                <input wire:model.live="date" type="date" class="w-full text-sm rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                @error('date') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    {{-- Right Section: Payment Register & Amount (5 Cols on desktop) --}}
                    <div class="lg:col-span-5 bg-gradient-to-b from-slate-50 via-slate-50/70 to-white rounded-2xl p-4 sm:p-5 lg:p-6 border-2 border-slate-200/90 shadow-sm flex flex-col justify-between space-y-4">
                        
                        {{-- Payment Method Pills (Cash, Transfer, POS) --}}
                        @if ($type === 'Income')
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2 flex items-center justify-between">
                                    <span>Payment Method</span>
                                    <span class="text-[11px] text-emerald-700 font-extrabold capitalize">{{ $paymentMethod }} Selected</span>
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    <!-- CASH -->
                                    <button type="button" 
                                            wire:click="setPaymentMethod('Cash')" 
                                            class="relative flex flex-col items-center justify-center py-3 px-2 rounded-xl border-2 transition-all {{ $paymentMethod === 'Cash' ? 'border-emerald-500 bg-emerald-50/90 text-emerald-800 shadow-md ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-200 hover:bg-emerald-50/30' }}">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center mb-1 {{ $paymentMethod === 'Cash' ? 'bg-emerald-200/60 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </div>
                                        <span class="text-xs font-black">Cash</span>
                                        @if($paymentMethod === 'Cash')
                                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-600 flex items-center justify-center text-[10px] text-white shadow-xs">✓</span>
                                        @endif
                                    </button>

                                    <!-- TRANSFER -->
                                    <button type="button" 
                                            wire:click="setPaymentMethod('Transfer')" 
                                            class="relative flex flex-col items-center justify-center py-3 px-2 rounded-xl border-2 transition-all {{ $paymentMethod === 'Transfer' ? 'border-indigo-500 bg-indigo-50/90 text-indigo-800 shadow-md ring-2 ring-indigo-500/20' : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:bg-indigo-50/30' }}">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center mb-1 {{ $paymentMethod === 'Transfer' ? 'bg-indigo-200/60 text-indigo-800' : 'bg-slate-100 text-slate-500' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                            </svg>
                                        </div>
                                        <span class="text-xs font-black">Transfer</span>
                                        @if($paymentMethod === 'Transfer')
                                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-indigo-600 flex items-center justify-center text-[10px] text-white shadow-xs">✓</span>
                                        @endif
                                    </button>

                                    <!-- POS -->
                                    <button type="button" 
                                            wire:click="setPaymentMethod('POS')" 
                                            class="relative flex flex-col items-center justify-center py-3 px-2 rounded-xl border-2 transition-all {{ $paymentMethod === 'POS' ? 'border-purple-500 bg-purple-50/90 text-purple-800 shadow-md ring-2 ring-purple-500/20' : 'border-slate-200 bg-white text-slate-600 hover:border-purple-200 hover:bg-purple-50/30' }}">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center mb-1 {{ $paymentMethod === 'POS' ? 'bg-purple-200/60 text-purple-800' : 'bg-slate-100 text-slate-500' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                        </div>
                                        <span class="text-xs font-black">POS / Card</span>
                                        @if($paymentMethod === 'POS')
                                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-purple-600 flex items-center justify-center text-[10px] text-white shadow-xs">✓</span>
                                        @endif
                                    </button>
                                </div>
                            </div>

                            {{-- Payment Specific Details (Transfer / POS reference, Payer Name) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                                        {{ $paymentMethod === 'Transfer' ? 'Bank Transfer / Teller Ref #' : ($paymentMethod === 'POS' ? 'POS Terminal RRN / Code' : 'Payment Reference (Optional)') }}
                                    </label>
                                    <input type="text" 
                                           wire:model.live="reference" 
                                           placeholder="{{ $paymentMethod === 'Transfer' ? 'e.g. GTB/TRF/482019' : ($paymentMethod === 'POS' ? 'e.g. 000482918' : 'Receipt / Ref #') }}" 
                                           class="w-full text-xs rounded-xl border-slate-300 bg-white text-slate-900 font-mono shadow-xs focus:border-emerald-500 focus:ring-emerald-500" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                                        Depositor / Payer Name
                                    </label>
                                    <input type="text" 
                                           wire:model.live="payerName" 
                                           placeholder="e.g. Mr. &amp; Mrs. Adeleke" 
                                           class="w-full text-xs rounded-xl border-slate-300 bg-white text-slate-900 shadow-xs focus:border-emerald-500 focus:ring-emerald-500" />
                                </div>
                            </div>
                        @endif

                        {{-- Cashier Memo / Notes --}}
                        <div class="pt-0.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                                Cashier Memo / Notes (Optional)
                            </label>
                            <input type="text" 
                                   wire:model.live="notes" 
                                   placeholder="e.g. Part payment, balance to be cleared next week" 
                                   class="w-full text-xs rounded-xl border-slate-300 bg-white text-slate-900 shadow-xs focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>

                        {{-- Prominent Amount Register --}}
                        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border-2 border-emerald-200/90 shadow-2xs">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Amount to Record (₦) *</span>
                                </label>
                                @if((float)$amountPaid > 0)
                                    <button type="button" wire:click="clearAmount" class="text-xs font-bold text-rose-500 hover:text-rose-700 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200">
                                        Clear ✕
                                    </button>
                                @endif
                            </div>

                            <div class="relative rounded-xl shadow-xs">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <span class="text-sm font-bold text-emerald-600 leading-none">₦</span>
                                </div>
                                <input wire:model.live="amountPaid" 
                                       type="number" 
                                       step="0.01" 
                                       min="0" 
                                       placeholder="0.00" 
                                       class="w-full pl-8 pr-4 py-3 sm:py-3.5 text-xl sm:text-2xl font-black font-mono tracking-tight text-slate-900 bg-slate-50/50 rounded-xl border-2 {{ $errors->has('amountPaid') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 focus:bg-white' }} transition-all" />
                            </div>
                            @error('amountPaid') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror

                            {{-- Cash Preset Fast-Tap Chips --}}
                            <div class="mt-3 pt-2.5 border-t border-slate-100">
                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">⚡ Quick Tap Presets:</div>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" wire:click="addQuickAmount(1000)" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 text-emerald-800 border border-emerald-300 hover:bg-emerald-100 hover:border-emerald-400 transition-all shadow-2xs active:scale-95">
                                        +₦1k
                                    </button>
                                    <button type="button" wire:click="addQuickAmount(5000)" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-gradient-to-r from-teal-50 to-cyan-50 text-teal-800 border border-teal-300 hover:bg-teal-100 hover:border-teal-400 transition-all shadow-2xs active:scale-95">
                                        +₦5k
                                    </button>
                                    <button type="button" wire:click="addQuickAmount(10000)" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-800 border border-blue-300 hover:bg-blue-100 hover:border-blue-400 transition-all shadow-2xs active:scale-95">
                                        +₦10k
                                    </button>
                                    <button type="button" wire:click="addQuickAmount(20000)" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-800 border border-indigo-300 hover:bg-indigo-100 hover:border-indigo-400 transition-all shadow-2xs active:scale-95">
                                        +₦20k
                                    </button>
                                    <button type="button" wire:click="addQuickAmount(50000)" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-gradient-to-r from-purple-50 to-pink-50 text-purple-800 border border-purple-300 hover:bg-purple-100 hover:border-purple-400 transition-all shadow-2xs active:scale-95">
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
                <div class="relative">
                    <select wire:model.live="filterType" class="select appearance-none text-xs sm:text-sm pr-8 cursor-pointer w-full">
                        <option value="">All Types</option>
                        <option value="Income">Income</option>
                        <option value="Expense">Expense</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <input wire:model.live.debounce.300ms="filterCategory" type="text" placeholder="Category" class="input-compact text-xs sm:text-sm" />
                <div class="relative">
                    <select wire:model.live="selectedClassId" class="select appearance-none text-xs sm:text-sm pr-8 cursor-pointer w-full">
                        <option value="">All Classes</option>
                        @foreach ($this->classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div class="relative">
                    <select wire:model.live="filterStudentId" class="select appearance-none text-xs sm:text-sm pr-8 cursor-pointer w-full">
                        <option value="">All Students</option>
                        @foreach ($this->students as $student)
                            <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <input wire:model.live="filterFrom" type="date" class="input-compact text-xs sm:text-sm" />
                <input wire:model.live="filterTo" type="date" class="input-compact text-xs sm:text-sm" />
            </div>
        </div>

    @elseif ($tab === 'debtors')
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-white shadow-sm border-2 border-amber-200/90 p-4 sm:p-6">
            <div class="relative">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/25 shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Outstanding Balances &amp; Debtors Register</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                Pending Fees
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Quickly identify and collect outstanding balances across terms and classes</p>
                    </div>
                </div>
                <div class="grid gap-2.5 sm:gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-5">
                    {{-- Category Select --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Fee Category</label>
                        <div class="relative">
                            <select wire:model.live="debtorsCategory" class="select appearance-none text-xs sm:text-sm w-full font-medium pr-8 cursor-pointer">
                                @foreach($this->availableFeeCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Session Select --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Session</label>
                        <div class="relative">
                            <select wire:model.live="debtorsSession" class="select appearance-none text-xs sm:text-sm w-full font-medium pr-8 cursor-pointer">
                                <option value="">All Sessions</option>
                                @foreach($this->availableSessions as $sess)
                                    <option value="{{ $sess }}">{{ $sess }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Term Select --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Term</label>
                        <div class="relative">
                            <select wire:model.live="debtorsTerm" class="select appearance-none text-xs sm:text-sm w-full font-medium pr-8 cursor-pointer">
                                <option value="">All Terms</option>
                                <option value="1">Term 1</option>
                                <option value="2">Term 2</option>
                                <option value="3">Term 3</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Class Select --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Class</label>
                        <div class="relative">
                            <select wire:model.live="debtorsClassId" class="select appearance-none text-xs sm:text-sm w-full font-medium pr-8 cursor-pointer">
                                <option value="">All Classes</option>
                                @foreach($this->classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Student Search --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Search Student</label>
                        <div class="relative">
                            <input wire:model.live.debounce.300ms="debtorsSearch" type="text" placeholder="Name or Adm No..." class="input-compact text-xs sm:text-sm w-full pl-8" />
                            <svg class="h-3.5 w-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-table>
            <thead class="bg-slate-100/80 text-xs font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3">Student</th>
                    <th class="px-5 py-3">Class</th>
                    <th class="px-5 py-3 text-right">Current Term Due</th>
                    <th class="px-5 py-3 text-right">Previous Arrears</th>
                    <th class="px-5 py-3 text-right">Total Paid</th>
                    <th class="px-5 py-3 text-right">Total Outstanding</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($this->debtors as $row)
                    <tr class="bg-white hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-bold text-slate-900">{{ $row['student']->full_name }}</div>
                            <div class="mt-0.5 text-xs text-slate-500 font-mono">{{ $row['student']->admission_number }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-700">
                            {{ $row['student']->schoolClass?->name ?? '-' }}@if($row['student']->section) / {{ $row['student']->section->name }}@endif
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-900 font-mono">
                            {{ config('academyhub.currency_symbol') }}{{ number_format($row['current_due'] ?? $row['due'], 2) }}
                        </td>
                        <td class="px-5 py-4 text-right text-sm">
                            @if (($row['past_arrears'] ?? 0) > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 font-mono">
                                    {{ config('academyhub.currency_symbol') }}{{ number_format($row['past_arrears'], 2) }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400 font-mono">{{ config('academyhub.currency_symbol') }}0.00</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-emerald-600 font-mono">
                            {{ config('academyhub.currency_symbol') }}{{ number_format($row['paid'], 2) }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-black bg-rose-50 text-rose-700 border-2 border-rose-300 font-mono shadow-2xs">
                                {{ config('academyhub.currency_symbol') }}{{ number_format($row['balance'], 2) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button type="button" 
                                    wire:click="quickCollect({{ $row['student']->id }}, {{ $row['balance'] }}, '{{ $row['category'] ?? $debtorsCategory }}')" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/20 transition-all active:scale-95">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span>Collect Fee</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No debtors found matching the filter criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
    @elseif ($tab === 'class-register')
        @php
            $reg = $this->classRegisterData;
        @endphp
        <div class="space-y-4">
            <!-- Filter & Selection Bar -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-50/70 via-indigo-50/30 to-white shadow-sm border-2 border-blue-200/90 p-4 sm:p-5">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-600/25 shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-bold text-slate-900">Class Fee Register &amp; Roster</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                    Term Roster
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Class-by-class student fee ledger, payment reconciliation &amp; collection terminal</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-6">
                    {{-- Class Select --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Select Class *</label>
                        <div class="relative">
                            <select wire:model.live="registerClassId" class="select appearance-none text-xs sm:text-sm w-full font-bold text-blue-900 bg-white pr-8 cursor-pointer">
                                <option value="">-- Choose Class --</option>
                                @foreach($this->classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Arm / Section Select --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Arm / Section</label>
                        <div class="relative">
                            <select wire:model.live="registerSectionId" class="select appearance-none text-xs sm:text-sm w-full font-medium bg-white pr-8 cursor-pointer">
                                <option value="">All Arms (All Sections)</option>
                                @foreach($this->registerSections as $sec)
                                    <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Fee Category --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Fee Category</label>
                        <div class="relative">
                            <select wire:model.live="registerCategory" class="select appearance-none text-xs sm:text-sm w-full font-medium bg-white pr-8 cursor-pointer">
                                @foreach($this->availableFeeCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Session Select --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Session</label>
                        <div class="relative">
                            <select wire:model.live="registerSession" class="select appearance-none text-xs sm:text-sm w-full font-medium bg-white pr-8 cursor-pointer">
                                @foreach($this->availableSessions as $sess)
                                    <option value="{{ $sess }}">{{ $sess }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Term Select --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Term</label>
                        <div class="relative">
                            <select wire:model.live="registerTerm" class="select appearance-none text-xs sm:text-sm w-full font-medium bg-white pr-8 cursor-pointer">
                                <option value="1">Term 1 (First Term)</option>
                                <option value="2">Term 2 (Second Term)</option>
                                <option value="3">Term 3 (Third Term)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Search in Class --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Filter in Class</label>
                        <div class="relative">
                            <input wire:model.live.debounce.250ms="registerSearch" type="text" placeholder="Search student..." class="input-compact text-xs sm:text-sm w-full pl-8 bg-white" />
                            <svg class="h-3.5 w-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Class Register KPI Summary Cards -->
            <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-5">
                <div class="bg-gradient-to-br from-blue-50/80 via-white to-indigo-50/30 rounded-2xl p-4 shadow-xs border-2 border-blue-200/90 hover:shadow-sm transition-all">
                    <p class="text-[11px] font-bold text-blue-800 uppercase tracking-wider mb-1">Students</p>
                    <h3 class="text-xl sm:text-2xl font-black text-blue-900">
                        {{ number_format($reg['total_students']) }}
                    </h3>
                    <p class="text-[10px] text-blue-600 font-semibold mt-0.5">Enrolled in roster</p>
                </div>

                <div class="bg-gradient-to-br from-indigo-50/80 via-white to-purple-50/30 rounded-2xl p-4 shadow-xs border-2 border-indigo-200/90 hover:shadow-sm transition-all">
                    <p class="text-[11px] font-bold text-indigo-800 uppercase tracking-wider mb-1">Total Expected</p>
                    <h3 class="text-xl sm:text-2xl font-black text-indigo-900 font-mono">
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($reg['total_expected'], 2) }}
                    </h3>
                    <p class="text-[10px] text-indigo-600 font-semibold mt-0.5">Term Due + Arrears</p>
                </div>

                <div class="bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/30 rounded-2xl p-4 shadow-xs border-2 border-emerald-300 hover:shadow-sm transition-all">
                    <p class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider mb-1">Total Collected</p>
                    <h3 class="text-xl sm:text-2xl font-black text-emerald-600 font-mono">
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($reg['total_paid'], 2) }}
                    </h3>
                    <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">Cash / Trf / POS</p>
                </div>

                <div class="bg-gradient-to-br from-rose-50/80 via-white to-pink-50/30 rounded-2xl p-4 shadow-xs border-2 border-rose-300 hover:shadow-sm transition-all">
                    <p class="text-[11px] font-bold text-rose-800 uppercase tracking-wider mb-1">Outstanding</p>
                    <h3 class="text-xl sm:text-2xl font-black text-rose-600 font-mono">
                        {{ config('academyhub.currency_symbol', '₦') }}{{ number_format($reg['total_balance'], 2) }}
                    </h3>
                    <p class="text-[10px] text-rose-500 font-semibold mt-0.5">Unpaid balance</p>
                </div>

                <div class="col-span-2 sm:col-span-1 bg-gradient-to-br from-teal-50/80 via-white to-cyan-50/30 rounded-2xl p-4 shadow-xs border-2 border-teal-200/90 flex flex-col justify-between hover:shadow-sm transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <p class="text-[11px] font-bold text-teal-800 uppercase tracking-wider">Collection Rate</p>
                            <span class="text-xs font-black text-teal-700 font-mono">{{ $reg['collection_rate'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-200/80 h-2.5 rounded-full overflow-hidden p-0.5">
                            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $reg['collection_rate'] }}%"></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-teal-700 font-semibold mt-1">Class target progress</p>
                </div>
            </div>

            <!-- Student Roster Table -->
            <x-table>
                <thead class="bg-slate-100/80 text-xs font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">#</th>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Arm</th>
                        <th class="px-4 py-3 text-right">Term Due</th>
                        <th class="px-4 py-3 text-right">Past Arrears</th>
                        <th class="px-4 py-3 text-right">Total Paid</th>
                        <th class="px-4 py-3 text-right">Balance</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reg['students'] as $idx => $row)
                        <tr class="bg-white hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3.5 text-center text-xs text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5">
                                <div class="text-sm font-bold text-slate-900">{{ $row['student']->full_name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $row['student']->admission_number ?? 'No Adm #' }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600 font-semibold">
                                {{ $row['student']->section?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 font-mono">
                                {{ config('academyhub.currency_symbol') }}{{ number_format($row['due'], 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-xs font-mono">
                                @if($row['arrears'] > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                        {{ config('academyhub.currency_symbol') }}{{ number_format($row['arrears'], 2) }}
                                    </span>
                                @else
                                    <span class="text-slate-400">{{ config('academyhub.currency_symbol') }}0.00</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right text-xs font-bold text-emerald-600 font-mono">
                                {{ config('academyhub.currency_symbol') }}{{ number_format($row['paid'], 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-xs font-mono">
                                @if($row['balance'] > 0)
                                    <span class="font-extrabold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200">
                                        {{ config('academyhub.currency_symbol') }}{{ number_format($row['balance'], 2) }}
                                    </span>
                                @else
                                    <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">₦0.00</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($row['status'] === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                        ✓ Paid in Full
                                    </span>
                                @elseif($row['status'] === 'partial')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 border border-blue-300 shadow-2xs">
                                        Part Payment
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs">
                                        Owing
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <button type="button" 
                                        wire:click="quickCollect({{ $row['student']->id }}, {{ $row['balance'] > 0 ? $row['balance'] : null }}, '{{ $registerCategory }}')" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-sm transition-all active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span>Collect</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-sm text-slate-500">
                                @if(!$registerClassId)
                                    Please select a class above to load the student fee register.
                                @else
                                    No students found for the selected class and filters.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>
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
                <div class="relative">
                    <select wire:model.live="feeClassId" class="select appearance-none pr-8 cursor-pointer w-full">
                        <option value="">Select Class</option>
                        @foreach ($this->classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <input wire:model.live="feeCategory" type="text" placeholder="Category" class="input-compact" />
                <input wire:model.live="feeAmountDue" type="number" step="0.01" placeholder="Amount" class="input-compact" />
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <select wire:model.live="feeTerm" class="select appearance-none pr-8 cursor-pointer w-full">
                            <option value="">All Terms</option>
                            <option value="1">Term 1</option>
                            <option value="2">Term 2</option>
                            <option value="3">Term 3</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
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
                <div class="relative">
                    <select wire:model.live="feeFilterClassId" class="select appearance-none pr-8 cursor-pointer w-full">
                        <option value="">All Classes</option>
                        @foreach ($this->classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <input wire:model.live.debounce.300ms="feeFilterCategory" type="text" placeholder="Category" class="input-compact" />
                <div class="relative">
                    <select wire:model.live="feeFilterTerm" class="select appearance-none pr-8 cursor-pointer w-full">
                        <option value="">All Terms</option>
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
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
                <thead class="bg-slate-100/80 text-xs font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200">
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
                <tbody class="divide-y divide-slate-100">
                    @forelse ($this->transactions as $t)
                        <tr class="bg-white hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 text-sm text-slate-700 font-medium">{{ $t->date?->format('M j, Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-bold text-slate-900">{{ $t->student?->full_name ?: '-' }}</div>
                                <div class="mt-0.5 text-xs text-slate-500 font-mono">{{ $t->student?->admission_number ?: '' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if ($t->is_void)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Voided</span>
                                @elseif ($t->type === 'Income')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Income
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Expense
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700">
                                <div class="font-bold text-slate-900">{{ $t->category }}</div>
                                <div class="mt-0.5 text-xs text-slate-500">
                                    @if ($t->session)
                                        {{ $t->session }}
                                    @endif
                                    @if ($t->term)
                                        &middot; Term {{ $t->term }}
                                    @endif
                                    @if ($t->payment_method)
                                        &middot; <span class="font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">{{ $t->payment_method }}</span>
                                    @endif
                                </div>
                                @if ($t->reference || $t->payer_name)
                                    <div class="mt-1 text-[11px] text-slate-500 flex flex-wrap items-center gap-1.5">
                                        @if ($t->reference)
                                            <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-[10px] text-slate-700 border border-slate-200">Ref: {{ $t->reference }}</span>
                                        @endif
                                        @if ($t->payer_name)
                                            <span>Paid By: <strong class="text-slate-800">{{ $t->payer_name }}</strong></span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right text-sm font-black font-mono {{ $t->type === 'Income' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $t->type === 'Income' ? '+' : '-' }}{{ config('academyhub.currency_symbol') }}{{ number_format((float) $t->amount_paid, 2) }}
                            </td>
                            <td class="px-5 py-4 text-sm font-medium text-slate-700">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-800 border border-slate-200">{{ $t->receipt_number ?: '-' }}</span>
                                    @if ($t->is_void)
                                        <span class="text-[10px] font-bold text-rose-600">VOID</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-1.5">
                                    @if ($t->receipt_number)
                                        <a href="{{ route('billing.receipt', $t) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 shadow-2xs transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>Receipt</span>
                                        </a>
                                    @endif
                                    @if ($canVoid && !$t->is_void)
                                        <button type="button" wire:click="startVoid({{ $t->id }})" class="btn-ghost btn-sm text-rose-600 hover:bg-rose-50">Void</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if ($canVoid && $voidingTransactionId === $t->id)
                            <tr class="bg-rose-50 border-y border-rose-200">
                                <td colspan="7" class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <input wire:model.live="voidReason" type="text" class="input-compact flex-1 bg-white border-rose-300" placeholder="Void reason (optional)" />
                                        <button type="button" wire:click="cancelVoid" class="btn-outline btn-sm" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Cancel</button>
                                        <button type="button" wire:click="confirmVoid({{ $t->id }})" class="btn-primary btn-sm bg-rose-600 hover:bg-rose-700 border-none text-white">Confirm Void</button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No transactions recorded yet.</td>
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

    {{-- Instant Receipt Popup Modal --}}
    @if ($showReceiptModal && $this->receiptTransaction)
        @php
            $rt = $this->receiptTransaction;
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border-2 border-emerald-200/90 animate-in fade-in zoom-in duration-200">
                <div class="text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white mb-4 shadow-lg shadow-emerald-500/30">
                        <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 mb-1.5">
                        ✓ Transaction Successful
                    </span>
                    <h3 class="text-lg sm:text-xl font-black text-slate-900">Payment Recorded Successfully!</h3>
                    <p class="text-xs text-slate-500 mt-1">Official school receipt has been generated and filed.</p>
                </div>

                <div class="mt-5 p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-emerald-50/20 border border-emerald-100/80 space-y-2.5 text-xs">
                    <div class="flex justify-between items-center pb-2.5 border-b border-slate-200">
                        <span class="text-slate-500 font-semibold">Receipt Number</span>
                        <span class="font-mono font-black text-sm text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">{{ $rt->receipt_number }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Student</span>
                        <span class="font-bold text-slate-900">{{ $rt->student?->full_name }}</span>
                    </div>
                    @if($rt->student?->schoolClass)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Class &amp; Arm</span>
                            <span class="text-slate-800 font-semibold">{{ $rt->student->schoolClass->name }} @if($rt->student->section)({{ $rt->student->section->name }})@endif</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center py-1">
                        <span class="text-slate-600 font-bold uppercase tracking-wider text-[11px]">Amount Paid</span>
                        <span class="text-base font-black text-emerald-600 font-mono">{{ config('academyhub.currency_symbol', '₦') }}{{ number_format($rt->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Payment Mode</span>
                        <span class="font-bold text-emerald-800 bg-emerald-100/70 px-2 py-0.5 rounded text-[11px]">{{ $rt->payment_method ?: 'Cash' }}</span>
                    </div>
                    @if($rt->reference)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Reference / Teller #</span>
                            <span class="font-mono text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">{{ $rt->reference }}</span>
                        </div>
                    @endif
                    @if($rt->payer_name)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Paid By (Payer)</span>
                            <span class="text-slate-800 font-semibold">{{ $rt->payer_name }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                        <span class="text-slate-500 font-medium">Date</span>
                        <span class="text-slate-700 font-semibold">{{ $rt->date?->format('M j, Y') }}</span>
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-2.5">
                    <a href="{{ route('billing.receipt', $rt->id) }}" 
                       target="_blank"
                       class="flex-1 py-3 px-4 rounded-xl text-center font-bold text-xs text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print / Download Receipt</span>
                    </a>
                    <button type="button" 
                            wire:click="closeReceiptModal" 
                            class="py-3 px-4 rounded-xl text-center font-bold text-xs text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all">
                        Done / Next
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>


