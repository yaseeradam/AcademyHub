<div class="space-y-6">
    {{-- Page Header --}}
    <div class="sm:flex sm:items-center sm:justify-between pb-5 border-b border-slate-200 dark:border-slate-700">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Cash Flow &amp; Financial Position</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-300 dark:border-indigo-800">
                    {{ $session }} • Term {{ $term }}
                </span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Unified real-time reconciliation of student fee revenues, procurement expenses, and net liquidity reserves.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap items-center gap-2.5">
            <a href="{{ route('procurement.index') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 shadow-sm transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Procurement Register
            </a>
            <a href="{{ route('billing.index') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 shadow-sm transition-colors">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Fee Terminal
            </a>
            <button wire:click="exportFinancialStatement" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export CSV Statement
            </button>
        </div>
    </div>

    {{-- Timeframe Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mr-2">Time Horizon:</span>
                
                <button wire:click="$set('timeframe', 'this_month')" 
                        type="button" 
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $timeframe === 'this_month' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}">
                    This Month
                </button>
                <button wire:click="$set('timeframe', 'this_term')" 
                        type="button" 
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $timeframe === 'this_term' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}">
                    Current Term
                </button>
                <button wire:click="$set('timeframe', 'this_year')" 
                        type="button" 
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $timeframe === 'this_year' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}">
                    Academic Year
                </button>
                <button wire:click="$set('timeframe', 'all')" 
                        type="button" 
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $timeframe === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}">
                    All Time
                </button>
                <button wire:click="$set('timeframe', 'custom')" 
                        type="button" 
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $timeframe === 'custom' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}">
                    Custom Range
                </button>
            </div>

            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                @if($startDate && $endDate)
                    <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-700 px-2.5 py-1 rounded-md text-slate-700 dark:text-slate-300 font-mono text-[11px]">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}</span>
                    </div>
                @else
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                        Full Ledger
                    </span>
                @endif
            </div>
        </div>

        {{-- Custom Date Pickers --}}
        @if($timeframe === 'custom')
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">From:</label>
                    <input type="date" wire:model.live="startDate" class="text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-2.5 py-1.5 focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">To:</label>
                    <input type="date" wire:model.live="endDate" class="text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-2.5 py-1.5 focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
            </div>
        @endif
    </div>

    {{-- Clean Executive Stat Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Fee Collections (Inflow) --}}
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fee Revenues (Inflow)</div>
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 truncate">
                    ₦{{ number_format($metrics['totalInflow'], 2) }}
                </div>
                <div class="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">Verified</span>
                    <span>{{ number_format($metrics['inflowCount']) }} receipts reconciled</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
        </div>

        {{-- Card 2: Operating Expenses (Outflow) --}}
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Procurement (Outflow)</div>
                <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1 truncate">
                    ₦{{ number_format($metrics['totalOutflow'], 2) }}
                </div>
                <div class="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">Expenses</span>
                    <span>{{ number_format($metrics['outflowCount']) }} purchases logged</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
        </div>

        {{-- Card 3: Net Cash Flow Position --}}
        @php
            $isSurplus = $metrics['netPosition'] >= 0;
        @endphp
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Net Liquidity Position</div>
                <div class="text-2xl font-bold {{ $isSurplus ? 'text-slate-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }} mt-1 truncate">
                    {{ $isSurplus ? '+' : '' }}₦{{ number_format($metrics['netPosition'], 2) }}
                </div>
                <div class="text-xs mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $isSurplus ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' }}">
                        {{ $isSurplus ? 'Surplus (+'.$metrics['profitMargin'].'%)' : 'Deficit ('.$metrics['profitMargin'].'%)' }}
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl {{ $isSurplus ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400' }} flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>

        {{-- Card 4: Operating Expense Ratio --}}
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Expense / Inflow Ratio</div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1 truncate">
                    {{ $metrics['operatingRatio'] }}%
                </div>
                <div class="text-xs text-slate-400 mt-1">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $metrics['healthBadge'] }}">
                        {{ $metrics['healthStatus'] }}
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Clean Cash Flow Balance & Bursary Summary Card --}}
    @php
        $totalVol = $metrics['totalInflow'] + $metrics['totalOutflow'];
        $inflowPct = $totalVol > 0 ? round(($metrics['totalInflow'] / $totalVol) * 100) : 50;
        $outflowPct = $totalVol > 0 ? (100 - $inflowPct) : 50;
    @endphp
    <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            {{-- Left: Inflow vs Outflow Volume Bar --}}
            <div class="lg:col-span-7 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Cash Flow Equilibrium</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Total volume: <span class="font-semibold text-slate-700 dark:text-slate-200">₦{{ number_format($totalVol, 2) }}</span> across {{ number_format($metrics['inflowCount'] + $metrics['outflowCount']) }} transactions
                        </p>
                    </div>
                </div>

                {{-- Segmented Progress Bar --}}
                <div class="space-y-2">
                    <div class="w-full h-3 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden flex">
                        <div class="bg-emerald-500 h-full rounded-l-full transition-all duration-500" style="width: {{ $inflowPct }}%"></div>
                        <div class="bg-rose-500 h-full rounded-r-full transition-all duration-500" style="width: {{ $outflowPct }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs font-semibold">
                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span>Fee Inflows: {{ $inflowPct }}% (₦{{ number_format($metrics['totalInflow'], 2) }})</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400">
                            <span>Procurement Outflows: {{ $outflowPct }}% (₦{{ number_format($metrics['totalOutflow'], 2) }})</span>
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Bursary Health Note --}}
            <div class="lg:col-span-5 rounded-lg bg-slate-50 dark:bg-slate-700/50 p-4 border border-slate-200 dark:border-slate-600">
                <div class="flex items-start gap-3">
                    <div class="rounded-lg p-2 {{ $isSurplus ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300' }} shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Bursary Health Review</h4>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $metrics['healthBadge'] }}">
                                {{ $metrics['healthStatus'] }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ $metrics['healthDescription'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Categorical Breakdown (Inflows vs Outflows) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Revenue Sources --}}
        <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Revenue Collections by Stream</h3>
                        <p class="text-xs text-slate-400">Incoming fee components and receipts</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono bg-emerald-50 dark:bg-emerald-950/50 px-2 py-1 rounded">
                    ₦{{ number_format($metrics['totalInflow'], 2) }}
                </span>
            </div>

            @if(empty($metrics['inflowsByCategory']))
                <div class="py-8 text-center text-slate-400 text-xs">
                    No fee income records found in this timeframe.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($metrics['inflowsByCategory'] as $cat => $amount)
                        @php
                            $pct = $metrics['totalInflow'] > 0 ? round(($amount / $metrics['totalInflow']) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs font-medium mb-1">
                                <span class="text-slate-700 dark:text-slate-300">{{ $cat ?: 'Tuition Fees' }}</span>
                                <div class="flex items-center gap-1.5 font-mono">
                                    <span class="text-slate-400 text-[11px]">({{ $pct }}%)</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">₦{{ number_format($amount, 2) }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Operating Expenditures --}}
        <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Operational Spend by Category</h3>
                        <p class="text-xs text-slate-400">Purchases, assets, and running costs</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-rose-600 dark:text-rose-400 font-mono bg-rose-50 dark:bg-rose-950/50 px-2 py-1 rounded">
                    ₦{{ number_format($metrics['totalOutflow'], 2) }}
                </span>
            </div>

            @if(empty($metrics['outflowsByCategory']))
                <div class="py-8 text-center text-slate-400 text-xs">
                    No procurement expenses logged in this timeframe.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($metrics['outflowsByCategory'] as $cat => $amount)
                        @php
                            $pct = $metrics['totalOutflow'] > 0 ? round(($amount / $metrics['totalOutflow']) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs font-medium mb-1">
                                <span class="text-slate-700 dark:text-slate-300">{{ $cat }}</span>
                                <div class="flex items-center gap-1.5 font-mono">
                                    <span class="text-slate-400 text-[11px]">({{ $pct }}%)</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">₦{{ number_format($amount, 2) }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                <div class="bg-rose-500 h-full rounded-full transition-all duration-300" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Reconciled Cash Flow Ledger & Activity Stream --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        {{-- Table Toolbar --}}
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Cash Flow Ledger</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Unified audit trail of student revenue inflows and operational expense disbursements.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Ledger Filter Tabs --}}
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-900/60 rounded-lg border border-slate-200 dark:border-slate-700">
                    <button type="button" 
                            wire:click="$set('ledgerTab', 'all')" 
                            class="px-3 py-1 text-xs font-semibold rounded-md transition-colors {{ $ledgerTab === 'all' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        All Records
                    </button>
                    <button type="button" 
                            wire:click="$set('ledgerTab', 'inflow')" 
                            class="px-3 py-1 text-xs font-semibold rounded-md transition-colors {{ $ledgerTab === 'inflow' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400' }}">
                        Inflows
                    </button>
                    <button type="button" 
                            wire:click="$set('ledgerTab', 'outflow')" 
                            class="px-3 py-1 text-xs font-semibold rounded-md transition-colors {{ $ledgerTab === 'outflow' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400' }}">
                        Outflows
                    </button>
                </div>

                {{-- Search Box --}}
                <div class="relative w-full sm:w-60">
                    <input type="text" 
                           wire:model.live.debounce.300ms="ledgerSearch" 
                           placeholder="Search entry, student, item..." 
                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white pl-8 pr-3 py-1.5 focus:border-indigo-500 focus:ring-indigo-500" />
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-900/40 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Flow Type</th>
                        <th class="px-5 py-3 text-left">Particulars &amp; Reference</th>
                        <th class="px-5 py-3 text-left">Category</th>
                        <th class="px-5 py-3 text-left">Channel</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                    @forelse($ledgerEntries as $entry)
                        @php
                            $isInflow = $entry['type'] === 'inflow';
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                {{ $entry['date'] ? \Carbon\Carbon::parse($entry['date'])->format('M j, Y') : '—' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($isInflow)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                        INFLOW
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800">
                                        <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                                        OUTFLOW
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $entry['title'] }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500">{{ $entry['subtitle'] }}</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $entry['category'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 dark:text-slate-400">
                                {{ $entry['payment_method'] }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-right font-mono font-bold text-sm {{ $isInflow ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $isInflow ? '+' : '−' }}₦{{ number_format($entry['amount'], 2) }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    {{ $entry['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400 dark:text-slate-500">
                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">No Cash Flow Entries Found</div>
                                <p class="text-xs text-slate-400 mt-1">Adjust your timeframe or search query above to load transactions.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Load More Pagination Footer --}}
        @if(count($ledgerEntries) >= $ledgerLimit)
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20 text-center">
                <button type="button" 
                        wire:click="loadMoreLedger" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    Load More Entries
                </button>
            </div>
        @endif
    </div>
</div>