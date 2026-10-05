@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- ══════════════════════════════════════
             PROPRIETOR EXECUTIVE HEADER
        ══════════════════════════════════════ --}}
        <div class="relative overflow-hidden rounded-3xl shadow-xl" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #31104b 100%);">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-amber-500/15 via-transparent to-transparent pointer-events-none"></div>
            <div class="relative flex flex-col gap-6 px-8 py-8 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-400/20 text-amber-300 border border-amber-400/30">
                            <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                            Executive Cockpit
                        </span>
                        <span class="text-xs font-semibold text-slate-400">Read-Only Oversight</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                        School Executive Overview
                    </h1>
                    <p class="text-xs sm:text-sm font-medium text-slate-300 max-w-xl">
                        Real-time institutional health, academic excellence metrics, financial collection status, and staff discipline.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <div class="rounded-2xl bg-white/10 backdrop-blur-md px-4 py-2 border border-white/15 text-center">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Current Session</div>
                        <div class="text-xs sm:text-sm font-black text-white">{{ $session }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 backdrop-blur-md px-4 py-2 border border-white/15 text-center">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Academic Term</div>
                        <div class="text-xs sm:text-sm font-black text-amber-300">Term {{ $term }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════
             KEY EXECUTIVE KPI METRICS (5 TILES)
        ══════════════════════════════════════ --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4">

            {{-- 1. Student Enrollment --}}
            <div class="rounded-2xl bg-white p-3.5 sm:p-5 border border-slate-100 shadow-sm active:scale-[0.98] transition-all">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-[10px] sm:text-xs font-extrabold uppercase tracking-wider text-slate-500 truncate">Total Students</span>
                    <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-lg sm:rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 truncate">{{ number_format($totalStudents) }}</div>
                <div class="mt-1.5 sm:mt-2 flex items-center gap-1.5 sm:gap-2 text-[10px] sm:text-[11px] font-semibold text-slate-500 truncate">
                    <span class="text-blue-600 font-bold">👦 {{ $maleStudents }} Boys</span> &bull;
                    <span class="text-pink-600 font-bold">👧 {{ $femaleStudents }} Girls</span>
                </div>
            </div>

            {{-- 2. Staff & Punctuality --}}
            <div class="rounded-2xl bg-white p-3.5 sm:p-5 border border-slate-100 shadow-sm active:scale-[0.98] transition-all">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-[10px] sm:text-xs font-extrabold uppercase tracking-wider text-slate-500 truncate">Teaching Staff</span>
                    <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-lg sm:rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 truncate">{{ number_format($totalTeachers) }}</div>
                <div class="mt-1.5 sm:mt-2 flex items-center gap-1.5 text-[10px] sm:text-[11px] font-bold truncate">
                    @if(($markedCount ?? 0) === 0)
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">
                            Awaiting Clock-ins
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-md {{ $staffPunctualityRate >= 80 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $staffPunctualityRate }}% On-Time Today
                        </span>
                    @endif
                </div>
            </div>

            {{-- 3. Total Fees Collected --}}
            <a href="{{ route('billing.index', ['tab' => 'debtors']) }}" class="group block rounded-2xl bg-white p-3.5 sm:p-5 border border-slate-100 shadow-sm active:scale-[0.98] hover:border-emerald-200 transition-all">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-[10px] sm:text-xs font-extrabold uppercase tracking-wider text-slate-500 group-hover:text-emerald-600 transition-colors truncate">Fees Collected &rarr;</span>
                    <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-lg sm:rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-emerald-600 truncate">₦{{ number_format($totalCollected, 0) }}</div>
                <div class="mt-1.5 sm:mt-2 text-[10px] sm:text-[11px] font-bold text-slate-500 truncate">
                    <span class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">{{ $collectionRate }}%</span> of ₦{{ number_format($totalExpected, 0) }}
                </div>
            </a>

            {{-- 4. Outstanding Debt --}}
            <a href="{{ route('billing.index', ['tab' => 'debtors']) }}" class="group block rounded-2xl bg-white p-3.5 sm:p-5 border border-slate-100 shadow-sm active:scale-[0.98] hover:border-rose-200 transition-all">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-[10px] sm:text-xs font-extrabold uppercase tracking-wider text-slate-500 group-hover:text-rose-600 transition-colors truncate">Uncollected Debt &rarr;</span>
                    <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-lg sm:rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-rose-600 truncate">₦{{ number_format($outstandingDebt, 0) }}</div>
                <div class="mt-1.5 sm:mt-2 text-[10px] sm:text-[11px] font-semibold text-slate-500 truncate">
                    Tuition balance pending collection
                </div>
            </a>

            {{-- 5. Net Operating Position --}}
            <div class="col-span-2 sm:col-span-1 rounded-2xl bg-white p-3.5 sm:p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-[10px] sm:text-xs font-extrabold uppercase tracking-wider text-slate-400 truncate">Operating Net</span>
                    <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-lg sm:rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black {{ $netPosition >= 0 ? 'text-purple-700' : 'text-rose-600' }} truncate">
                    ₦{{ number_format($netPosition, 0) }}
                </div>
                <div class="mt-1.5 sm:mt-2 text-[10px] sm:text-[11px] font-medium text-slate-500 truncate">
                    Revenue minus ₦{{ number_format($totalExpenses, 0) }} costs
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════
             ACADEMIC LEADERBOARD & RISK RADAR ("BEST & LOW")
        ══════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- 🏆 STAR PERFORMERS (BEST STUDENTS) --}}
            <div class="rounded-3xl bg-white border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-emerald-50/50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-black text-lg">
                            🏆
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Star Performers (Top 5)</h3>
                            <p class="text-xs text-slate-400">Highest overall academic average across school</p>
                        </div>
                    </div>
                    <a href="{{ route('results.broadsheet') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                        Broadsheet &rarr;
                    </a>
                </div>

                <div class="p-5 divide-y divide-slate-100 flex-1">
                    @forelse($starStudents as $index => $student)
                        <div class="py-3.5 flex items-center justify-between first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full font-black text-xs
                                    {{ $index === 0 ? 'bg-amber-100 text-amber-800 border-2 border-amber-300' : ($index === 1 ? 'bg-slate-200 text-slate-700 border-2 border-slate-300' : ($index === 2 ? 'bg-orange-100 text-orange-800 border-2 border-orange-300' : 'bg-slate-100 text-slate-600')) }}">
                                    #{{ $index + 1 }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-extrabold text-sm text-slate-900 truncate">{{ $student['name'] }}</div>
                                    <div class="text-[11px] font-semibold text-slate-400">
                                        {{ $student['class_name'] }} &bull; ADM: {{ $student['adm_no'] }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-base font-black text-emerald-600">{{ $student['average'] }}%</div>
                                <div class="text-[10px] font-bold text-slate-400">{{ $student['total_subjects'] }} subjects</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs font-medium">
                            No exam score records available yet for this term.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ⚠️ ACADEMIC WATCHLIST (STRUGGLING / LOWEST STUDENTS) --}}
            <div class="rounded-3xl bg-white border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-rose-50/50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center font-black text-lg">
                            ⚠️
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Academic Watchlist (Needs Intervention)</h3>
                            <p class="text-xs text-slate-400">Students with lowest averages or failing &ge; 2 subjects</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                        Attention Required
                    </span>
                </div>

                <div class="p-5 divide-y divide-slate-100 flex-1">
                    @forelse($watchlistStudents as $index => $student)
                        <div class="py-3.5 flex items-center justify-between first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full font-black text-xs bg-rose-50 text-rose-600 border border-rose-200">
                                    !
                                </div>
                                <div class="min-w-0">
                                    <div class="font-extrabold text-sm text-slate-900 truncate">{{ $student['name'] }}</div>
                                    <div class="text-[11px] font-semibold text-slate-400">
                                        {{ $student['class_name'] }} &bull; ADM: {{ $student['adm_no'] }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-base font-black text-rose-600">{{ $student['average'] }}%</div>
                                @if($student['failed_count'] > 0)
                                    <div class="text-[10px] font-extrabold text-rose-500">{{ $student['failed_count'] }} failing subjects</div>
                                @else
                                    <div class="text-[10px] font-semibold text-slate-400">Needs boost</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-500 text-xs font-semibold flex flex-col items-center justify-center gap-1">
                            <span class="text-emerald-500 text-base">✓</span>
                            <span>All students are in good academic standing (No failing averages)</span>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════
             CLASS RANKINGS & SUBJECT HEALTH
        ══════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 🏫 CLASS RANKINGS --}}
            <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Class Academic Rankings</h3>
                        <p class="text-xs text-slate-500">Ranked by combined average student scores</p>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                        {{ $classRankings->count() }} Classes
                    </span>
                </div>

                @if($classRankings->isNotEmpty())
                    <div class="space-y-3.5 max-h-[440px] overflow-y-auto pr-1">
                        @foreach($classRankings as $rank => $cls)
                            <div class="flex items-center gap-4 p-3 rounded-2xl bg-slate-50/70 border border-slate-100 hover:border-slate-200 transition-colors">
                                <span class="font-black text-xs text-slate-400 w-5 text-center">{{ $rank + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="font-bold text-sm text-slate-900 truncate">{{ $cls['name'] }}</span>
                                        <span class="font-black text-xs {{ $cls['average'] >= 60 ? 'text-emerald-600' : ($cls['average'] >= 45 ? 'text-amber-600' : 'text-rose-600') }}">
                                            {{ $cls['average'] }}% Avg
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                        <div class="h-full rounded-full {{ $cls['average'] >= 60 ? 'bg-emerald-500' : ($cls['average'] >= 45 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                             style="width: {{ min(100, $cls['average']) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-slate-400 text-xs font-medium">
                        No class score summaries available yet.
                    </div>
                @endif
            </div>

            {{-- 📚 SUBJECT HEALTH INDEX --}}
            <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 mb-1">Subject Performance Index</h3>
                    <p class="text-xs text-slate-400 mb-4">Highlights curriculum strengths and bottlenecks</p>

                    {{-- Top Subjects --}}
                    <div class="mb-5">
                        <div class="text-[10px] font-black uppercase tracking-wider text-emerald-600 mb-2 flex items-center gap-1">
                            <span>▲</span> Top Performing Subjects
                        </div>
                        <div class="space-y-2">
                            @forelse($bestSubjects as $sub)
                                <div class="p-2.5 rounded-xl bg-emerald-50/50 border border-emerald-100 flex items-center justify-between">
                                    <span class="font-bold text-xs text-emerald-950 truncate">{{ $sub['name'] }}</span>
                                    <span class="font-black text-xs text-emerald-600">{{ $sub['average'] }}%</span>
                                </div>
                            @empty
                                <div class="text-[11px] text-slate-400">No subject data</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Struggling Subjects --}}
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-rose-600 mb-2 flex items-center gap-1">
                            <span>▼</span> Struggling Subjects (Attention)
                        </div>
                        <div class="space-y-2">
                            @forelse($strugglingSubjects as $sub)
                                <div class="p-2.5 rounded-xl bg-rose-50/50 border border-rose-100 flex items-center justify-between">
                                    <span class="font-bold text-xs text-rose-950 truncate">{{ $sub['name'] }}</span>
                                    <span class="font-black text-xs text-rose-600">{{ $sub['average'] }}%</span>
                                </div>
                            @empty
                                <div class="text-[11px] text-slate-400">No subject data</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="{{ route('results.broadsheet') }}" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors border border-slate-200">
                        View Broadsheet Scores &rarr;
                    </a>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════
             STAFF BIOMETRIC PUNCTUALITY & SHIFTS
        ══════════════════════════════════════ --}}
        <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Today's Staff Biometric Attendance Radar</h3>
                    <p class="text-xs text-slate-400">Physical K40 scans, arrival times, and dual-shift punctuality</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                        Western: {{ $westernStaffCount }} Teachers (7:00-12:30)
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Islamic: {{ $islamicStaffCount }} Teachers (12:30-5:00)
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100 text-center">
                    <div class="text-2xl font-black text-emerald-600">{{ $presentTeachers }}</div>
                    <div class="text-xs font-bold text-emerald-800 mt-0.5">Present On-Time</div>
                </div>
                <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-100 text-center">
                    <div class="text-2xl font-black text-amber-600">{{ $lateTeachers }}</div>
                    <div class="text-xs font-bold text-amber-800 mt-0.5">Arrived Late</div>
                </div>
                <div class="p-4 rounded-2xl bg-rose-50/50 border border-rose-100 text-center">
                    <div class="text-2xl font-black text-rose-600">{{ $absentTeachers }}</div>
                    <div class="text-xs font-bold text-rose-800 mt-0.5">Absent / Unverified</div>
                </div>
            </div>

            @if($recentMarks->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-black uppercase tracking-wider text-slate-500">
                                <th class="py-2.5 px-3">Teacher</th>
                                <th class="py-2.5 px-3">Shift</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3 text-center">Punch In</th>
                                <th class="py-2.5 px-3 text-center">Punch Out</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($recentMarks as $m)
                                @php
                                    $punchInDisplay = '—';
                                    if (!empty($m->punch_in_time)) {
                                        try {
                                            $punchInDisplay = \Carbon\Carbon::parse($m->punch_in_time)->format('g:i A');
                                        } catch (\Throwable $e) {
                                            $punchInDisplay = (string) $m->punch_in_time;
                                        }
                                    }
                                    $punchOutDisplay = '—';
                                    if (!empty($m->punch_out_time)) {
                                        try {
                                            $punchOutDisplay = \Carbon\Carbon::parse($m->punch_out_time)->format('g:i A');
                                        } catch (\Throwable $e) {
                                            $punchOutDisplay = (string) $m->punch_out_time;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-slate-900">{{ $m->teacher?->name ?? 'Staff' }}</td>
                                    <td class="py-2.5 px-3 text-slate-500">{{ $m->teacher?->getShiftLabel() ?? 'Western' }}</td>
                                    <td class="py-2.5 px-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $m->status === 'Present' ? 'bg-emerald-100 text-emerald-800' : ($m->status === 'Late' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                            {{ $m->status }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-emerald-600">
                                        {{ $punchInDisplay }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-rose-500">
                                        {{ $punchOutDisplay }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-8 text-center text-slate-500 text-xs font-medium">
                    No staff attendance punches recorded yet today.
                </div>
            @endif

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Timesheets track detailed payroll deductions for unexcused lateness and absence.</span>
                <a href="{{ route('attendance.staff-timesheet') }}" class="text-xs font-bold text-amber-600 hover:text-amber-700">
                    Open Full Staff Timesheets &rarr;
                </a>
            </div>
        </div>

    </div>
@endsection
