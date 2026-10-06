<div class="space-y-6 font-sans">
    @php
        $metrics = $this->financialMetrics;
        $entries = $this->ledgerEntries;
        $totalVol = $metrics['totalInflow'] + $metrics['totalOutflow'];
        $inflowPct = $totalVol > 0 ? round(($metrics['totalInflow'] / $totalVol) * 100) : 50;
        $outflowPct = $totalVol > 0 ? (100 - $inflowPct) : 50;
        $isSurplus = $metrics['netPosition'] >= 0;
    @endphp

    {{-- ══════════════════════════════════════════════════════════════
         1. EXECUTIVE TREASURY HERO HEADER
    ══════════════════════════════════════════════════════════════ --}}
    <x-page-header 
        title="Cash Flow & Institutional Treasury" 
        subtitle="Real-time reconciled ledger integrating tuition revenue collections, procurement disbursements, and liquidity reserves" 
        accent="billing">
        <x-slot name="actions">
            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                <a href="{{ route('billing.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Fee Terminal
                </a>

                <a href="{{ route('procurement.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Procurement Register
                </a>

                <button wire:click="exportFinancialStatement" 
                        type="button" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 transition-all shadow-md shadow-emerald-500/20 active:scale-95">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Statement
                </button>
            </div>
        </x-slot>
    </x-page-header>

    {{-- ══════════════════════════════════════════════════════════════
         2. TIME HORIZON FILTER BAR
    ══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-2">Horizon:</span>
                
                <button wire:click="$set('timeframe', 'this_month')" 
                        type="button" 
                        class="px-3.5 py-1.5 text-xs font-bold rounded-xl transition-all {{ $timeframe === 'this_month' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    This Month
                </button>
                <button wire:click="$set('timeframe', 'this_term')" 
                        type="button" 
                        class="px-3.5 py-1.5 text-xs font-bold rounded-xl transition-all {{ $timeframe === 'this_term' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Current Term
                </button>
                <button wire:click="$set('timeframe', 'this_year')" 
                        type="button" 
                        class="px-3.5 py-1.5 text-xs font-bold rounded-xl transition-all {{ $timeframe === 'this_year' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Academic Year
                </button>
                <button wire:click="$set('timeframe', 'all')" 
                        type="button" 
                        class="px-3.5 py-1.5 text-xs font-bold rounded-xl transition-all {{ $timeframe === 'all' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    All Time
                </button>
                <button wire:click="$set('timeframe', 'custom')" 
                        type="button" 
                        class="px-3.5 py-1.5 text-xs font-bold rounded-xl transition-all {{ $timeframe === 'custom' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Custom Range
                </button>
            </div>

            <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                @if($startDate && $endDate)
                    <div class="inline-flex items-center gap-1.5 bg-slate-100 px-3 py-1 rounded-lg text-slate-700 font-mono text-[11px]">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}</span>
                    </div>
                @else
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Full Reconciled Ledger
                    </span>
                @endif
            </div>
        </div>

        @if($timeframe === 'custom')
            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-600">From:</label>
                    <input type="date" wire:model.live="startDate" class="text-xs rounded-xl border-slate-300 px-3 py-1.5 focus:border-emerald-500 focus:ring-emerald-500 font-medium" />
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-600">To:</label>
                    <input type="date" wire:model.live="endDate" class="text-xs rounded-xl border-slate-300 px-3 py-1.5 focus:border-emerald-500 focus:ring-emerald-500 font-medium" />
                </div>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         3. EXECUTIVE FINANCIAL KPI CARDS (4 TILES)
    ══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Revenue Inflows (Emerald Gradient) --}}
        <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl p-5 sm:p-6 text-white shadow-lg shadow-emerald-500/15 bg-gradient-to-br from-[#10b981] to-[#047857] flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[10px] sm:text-[11px] font-black uppercase tracking-wider text-emerald-100/90">Total Inflows (Revenue)</span>
                    <div class="text-2xl sm:text-3xl font-black text-white mt-1.5 tracking-tight truncate">
                        &#8358;{{ number_format($metrics['totalInflow'], 2) }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-sm text-white flex items-center justify-center shrink-0 shadow-sm border border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/15 flex items-center gap-1.5 text-xs text-emerald-100 font-medium">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-white/20 text-white backdrop-blur-sm">Verified</span>
                <span>{{ number_format($metrics['inflowCount']) }} receipts reconciled</span>
            </div>
        </div>

        {{-- Card 2: Operating Outflows (Rose Gradient) --}}
        <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl p-5 sm:p-6 text-white shadow-lg shadow-rose-500/15 bg-gradient-to-br from-[#f43f5e] to-[#be123c] flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[10px] sm:text-[11px] font-black uppercase tracking-wider text-rose-100/90">Total Outflows (Spend)</span>
                    <div class="text-2xl sm:text-3xl font-black text-white mt-1.5 tracking-tight truncate">
                        &#8358;{{ number_format($metrics['totalOutflow'], 2) }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-sm text-white flex items-center justify-center shrink-0 shadow-sm border border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/15 flex items-center gap-1.5 text-xs text-rose-100 font-medium">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-white/20 text-white backdrop-blur-sm">Expenses</span>
                <span>{{ number_format($metrics['outflowCount']) }} purchases logged</span>
            </div>
        </div>

        {{-- Card 3: Net Cash Flow Position (Indigo Gradient) --}}
        <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl p-5 sm:p-6 text-white shadow-lg shadow-indigo-500/15 bg-gradient-to-br from-[#6366f1] to-[#4338ca] flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[10px] sm:text-[11px] font-black uppercase tracking-wider text-indigo-100/90">Net Liquidity Position</span>
                    <div class="text-2xl sm:text-3xl font-black text-white mt-1.5 tracking-tight truncate">
                        {{ $isSurplus ? '+' : '' }}&#8358;{{ number_format($metrics['netPosition'], 2) }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-sm text-white flex items-center justify-center shrink-0 shadow-sm border border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/15 flex items-center gap-1.5 text-xs text-indigo-100 font-medium">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-white/20 text-white backdrop-blur-sm">
                    {{ $isSurplus ? 'Surplus (+'.$metrics['profitMargin'].'%)' : 'Deficit ('.$metrics['profitMargin'].'%)' }}
                </span>
                <span>Operating Margin</span>
            </div>
        </div>

        {{-- Card 4: Operating Expense Ratio (Cyan Gradient) --}}
        <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl p-5 sm:p-6 text-white shadow-lg shadow-cyan-500/15 bg-gradient-to-br from-[#06b6d4] to-[#0e7490] flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[10px] sm:text-[11px] font-black uppercase tracking-wider text-cyan-100/90">Expense / Inflow Ratio</span>
                    <div class="text-2xl sm:text-3xl font-black text-white mt-1.5 tracking-tight truncate">
                        {{ $metrics['operatingRatio'] }}%
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-sm text-white flex items-center justify-center shrink-0 shadow-sm border border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/15 flex items-center gap-1.5 text-xs text-cyan-100 font-medium">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-white/20 text-white backdrop-blur-sm">
                    {{ $metrics['healthStatus'] }}
                </span>
                <span>Efficiency Index</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         4. CASH FLOW EQUILIBRIUM & BURSARY HEALTH
    ══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            {{-- Left: Inflow vs Outflow Volume Bar --}}
            <div class="lg:col-span-7 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Cash Flow Equilibrium</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Total volume: <span class="font-bold text-slate-800">&#8358;{{ number_format($totalVol, 2) }}</span> across {{ number_format($metrics['inflowCount'] + $metrics['outflowCount']) }} transactions
                        </p>
                    </div>
                </div>

                {{-- Segmented Progress Bar --}}
                <div class="space-y-2">
                    <div class="w-full h-3.5 bg-slate-100 rounded-full overflow-hidden flex">
                        <div class="bg-emerald-500 h-full transition-all duration-500" style="width: {{ $inflowPct }}%"></div>
                        <div class="bg-rose-500 h-full transition-all duration-500" style="width: {{ $outflowPct }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs font-bold">
                        <div class="flex items-center gap-1.5 text-emerald-600">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span>Fee Inflows: {{ $inflowPct }}% (&#8358;{{ number_format($metrics['totalInflow'], 2) }})</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-rose-600">
                            <span>Procurement Outflows: {{ $outflowPct }}% (&#8358;{{ number_format($metrics['totalOutflow'], 2) }})</span>
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Bursary Health Note --}}
            <div class="lg:col-span-5 rounded-xl bg-slate-50 p-4 border border-slate-100">
                <div class="flex items-start gap-3">
                    <div class="rounded-xl p-2.5 {{ $isSurplus ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Bursary Health Review</h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $metrics['healthBadge'] }}">
                                {{ $metrics['healthStatus'] }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-600 leading-relaxed font-medium">
                            {{ $metrics['healthDescription'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         5. STREAMS BREAKDOWN (INFLOWS VS OUTFLOWS)
    ══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Revenue Sources --}}
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Revenue Collections by Stream</h3>
                        <p class="text-xs text-slate-400">Incoming fee components and receipts</p>
                    </div>
                </div>
                <span class="text-xs font-black text-emerald-600 font-mono bg-emerald-50 px-2.5 py-1 rounded-lg">
                    &#8358;{{ number_format($metrics['totalInflow'], 2) }}
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
                            <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                <span class="text-slate-700">{{ $cat ?: 'Tuition Fees' }}</span>
                                <div class="flex items-center gap-1.5 font-mono">
                                    <span class="text-slate-400 text-[11px]">({{ $pct }}%)</span>
                                    <span class="font-bold text-slate-900">&#8358;{{ number_format($amount, 2) }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Operating Expenditures --}}
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Operational Spend by Category</h3>
                        <p class="text-xs text-slate-400">Purchases, assets, and running costs</p>
                    </div>
                </div>
                <span class="text-xs font-black text-rose-600 font-mono bg-rose-50 px-2.5 py-1 rounded-lg">
                    &#8358;{{ number_format($metrics['totalOutflow'], 2) }}
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
                            <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                <span class="text-slate-700">{{ $cat }}</span>
                                <div class="flex items-center gap-1.5 font-mono">
                                    <span class="text-slate-400 text-[11px]">({{ $pct }}%)</span>
                                    <span class="font-bold text-slate-900">&#8358;{{ number_format($amount, 2) }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-rose-500 h-full rounded-full transition-all duration-300" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         6. RECONCILED CASH FLOW LEDGER
    ══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        {{-- Toolbar --}}
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-base font-black text-slate-900">Unified Audit Ledger</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Chronological audit trail of verified revenue collections and authorized disbursements.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Ledger Filter Tabs --}}
                <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200">
                    <button type="button" 
                            wire:click="$set('ledgerTab', 'all')" 
                            class="px-3.5 py-1 text-xs font-bold rounded-lg transition-all {{ $ledgerTab === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        All Records
                    </button>
                    <button type="button" 
                            wire:click="$set('ledgerTab', 'inflow')" 
                            class="px-3.5 py-1 text-xs font-bold rounded-lg transition-all {{ $ledgerTab === 'inflow' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-emerald-600' }}">
                        Inflows
                    </button>
                    <button type="button" 
                            wire:click="$set('ledgerTab', 'outflow')" 
                            class="px-3.5 py-1 text-xs font-bold rounded-lg transition-all {{ $ledgerTab === 'outflow' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:text-rose-600' }}">
                        Outflows
                    </button>
                </div>

                {{-- Search Box --}}
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           wire:model.live.debounce.300ms="ledgerSearch" 
                           placeholder="Search entry, student, ref..." 
                           class="w-full text-xs rounded-xl border-slate-300 bg-white text-slate-900 pl-8 pr-3 py-1.5 focus:border-indigo-500 focus:ring-indigo-500 font-medium" />
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
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
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($entries as $entry)
                        @php
                            $isInflow = $entry['type'] === 'inflow';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                {{ $entry['date'] ? \Carbon\Carbon::parse($entry['date'])->format('M j, Y') : '&mdash;' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($isInflow)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        INFLOW
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200/80">
                                        <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                        OUTFLOW
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $entry['title'] }}</div>
                                <div class="text-[11px] text-slate-400 font-medium">{{ $entry['subtitle'] }}</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700">
                                    {{ $entry['category'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 font-medium">
                                {{ $entry['payment_method'] }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-right font-mono font-black text-sm {{ $isInflow ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $isInflow ? '+' : '&minus;' }}&#8358;{{ number_format($entry['amount'], 2) }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    {{ $entry['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <div class="text-sm font-bold text-slate-700">No Cash Flow Entries Found</div>
                                <p class="text-xs text-slate-400 mt-1">Adjust your time horizon or search query above to load transactions.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Load More Pagination Footer --}}
        @if(count($entries) >= $ledgerLimit)
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 text-center">
                <button type="button" 
                        wire:click="loadMoreLedger" 
                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 shadow-sm transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    Load More Entries
                </button>
            </div>
        @endif
    </div>
</div>