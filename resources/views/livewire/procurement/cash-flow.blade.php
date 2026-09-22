<div class="space-y-6">
    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between pb-5 border-b border-slate-200 dark:border-slate-700">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('procurement.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 dark:text-slate-400 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Procurement Records
                </a>
                <span class="text-slate-300 dark:text-slate-600">/</span>
                <span class="text-sm font-semibold text-slate-800 dark:text-white">Cash Flow Intelligence</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">School Cash Flow &amp; Financial Position</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Real-time reconciliation of school fee revenues vs. operational procurement expenses.
            </p>
        </div>

        <div class="mt-4 sm:mt-0 flex flex-wrap items-center gap-2.5">
            <a href="{{ route('procurement.index') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Purchases Register
            </a>
            <button wire:click="exportFinancialStatement" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-sm hover:bg-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Financial Statement (CSV)
            </button>
        </div>
    </div>

    {{-- Timeframe Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mr-2">Reporting Period:</span>
            <button wire:click="$set('timeframe', 'this_month')" type="button" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $timeframe === 'this_month' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300' }}">
                This Month
            </button>
            <button wire:click="$set('timeframe', 'this_term')" type="button" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $timeframe === 'this_term' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300' }}">
                This Term
            </button>
            <button wire:click="$set('timeframe', 'this_year')" type="button" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $timeframe === 'this_year' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300' }}">
                Academic Year
            </button>
            <button wire:click="$set('timeframe', 'all')" type="button" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $timeframe === 'all' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300' }}">
                All Time
            </button>
        </div>

        @if($startDate && $endDate)
            <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                {{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}
            </div>
        @endif
    </div>

    {{-- Executive Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Inflow --}}
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between text-xs font-semibold uppercase text-slate-500">
                <span>Fee Revenues (Inflow)</span>
                <span class="text-emerald-500 font-bold">IN</span>
            </div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">
                ₦{{ number_format($metrics['totalInflow'], 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">{{ $metrics['inflowCount'] }} payments reconciled</div>
        </div>

        {{-- Total Outflow --}}
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between text-xs font-semibold uppercase text-slate-500">
                <span>Procurement (Outflow)</span>
                <span class="text-rose-500 font-bold">OUT</span>
            </div>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-2">
                ₦{{ number_format($metrics['totalOutflow'], 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">{{ $metrics['outflowCount'] }} purchases logged</div>
        </div>

        {{-- Net Position --}}
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between text-xs font-semibold uppercase text-slate-500">
                <span>Net School Position</span>
                <span class="text-xs font-bold {{ $metrics['netPosition'] >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                    {{ $metrics['netPosition'] >= 0 ? 'SURPLUS' : 'DEFICIT' }}
                </span>
            </div>
            <div class="text-2xl font-bold {{ $metrics['netPosition'] >= 0 ? 'text-slate-900 dark:text-white' : 'text-rose-600' }} mt-2">
                ₦{{ number_format($metrics['netPosition'], 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Inflows minus operating costs</div>
        </div>

        {{-- Operating Ratio --}}
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between text-xs font-semibold uppercase text-slate-500">
                <span>Operating Expense Ratio</span>
                <span class="text-indigo-500 font-bold">%</span>
            </div>
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-2">
                {{ $metrics['operatingRatio'] }}%
            </div>
            <div class="text-xs text-slate-400 mt-1">Of collected revenue spent</div>
        </div>
    </div>

    {{-- Visual Cash Flow Balance Bar --}}
    @php
        $totalVol = $metrics['totalInflow'] + $metrics['totalOutflow'];
        $inflowPct = $totalVol > 0 ? round(($metrics['totalInflow'] / $totalVol) * 100) : 50;
        $outflowPct = $totalVol > 0 ? (100 - $inflowPct) : 50;
    @endphp
    <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center justify-between text-xs font-semibold mb-2">
            <span class="text-emerald-600 dark:text-emerald-400">Total Inflow: {{ $inflowPct }}% (₦{{ number_format($metrics['totalInflow'], 2) }})</span>
            <span class="text-rose-600 dark:text-rose-400">Total Outflow: {{ $outflowPct }}% (₦{{ number_format($metrics['totalOutflow'], 2) }})</span>
        </div>
        <div class="w-full h-3 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden flex">
            <div class="bg-emerald-500 h-full transition-all duration-500" style="width: {{ $inflowPct }}%"></div>
            <div class="bg-rose-500 h-full transition-all duration-500" style="width: {{ $outflowPct }}%"></div>
        </div>
    </div>

    {{-- Categorical Breakdown Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Revenue Sources --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    Fee Collections by Category
                </h3>
                <span class="text-xs font-bold text-emerald-600 font-mono">₦{{ number_format($metrics['totalInflow'], 2) }}</span>
            </div>

            @if(empty($metrics['inflowsByCategory']))
                <p class="text-xs text-slate-400 py-6 text-center">No fee income records logged in this timeframe.</p>
            @else
                <div class="space-y-3">
                    @foreach($metrics['inflowsByCategory'] as $cat => $amount)
                        @php
                            $pct = $metrics['totalInflow'] > 0 ? round(($amount / $metrics['totalInflow']) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs font-medium mb-1">
                                <span class="text-slate-700 dark:text-slate-300">{{ $cat ?: 'Tuition Fees' }}</span>
                                <span class="font-mono font-bold text-slate-900 dark:text-white">₦{{ number_format($amount, 2) }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Expenditure Breakdown --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    Operational Outflow by Category
                </h3>
                <span class="text-xs font-bold text-rose-600 font-mono">₦{{ number_format($metrics['totalOutflow'], 2) }}</span>
            </div>

            @if(empty($metrics['outflowsByCategory']))
                <p class="text-xs text-slate-400 py-6 text-center">No procurement expenses logged in this timeframe.</p>
            @else
                <div class="space-y-3">
                    @foreach($metrics['outflowsByCategory'] as $cat => $amount)
                        @php
                            $pct = $metrics['totalOutflow'] > 0 ? round(($amount / $metrics['totalOutflow']) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs font-medium mb-1">
                                <span class="text-slate-700 dark:text-slate-300">{{ $cat }}</span>
                                <span class="font-mono font-bold text-slate-900 dark:text-white">₦{{ number_format($amount, 2) }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
