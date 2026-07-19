@php
    $rateColor  = $rate >= 80 ? 'text-emerald-600' : ($rate >= 60 ? 'text-amber-600' : 'text-red-600');
    $rateBg     = $rate >= 80 ? 'from-emerald-500 to-teal-600' : ($rate >= 60 ? 'from-amber-500 to-orange-500' : 'from-red-500 to-rose-600');
    $rateLabel  = $rate >= 80 ? 'Excellent' : ($rate >= 60 ? 'Fair' : 'Poor');

    $monthName  = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y');
    $startDate  = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1);
    $daysInMonth   = $startDate->daysInMonth;
    $startDayOfWeek = $startDate->dayOfWeek; // 0=Sun

    // SVG ring for attendance rate (r=40, circ=251.3)
    $circ = 251.3;
    $dash = round($circ * $rate / 100, 1);

    $termLabels = [1 => 'First Term', 2 => 'Second Term', 3 => 'Third Term'];
@endphp

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="rounded-xl bg-slate-900 px-4 py-3 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
            <div>
                <h1 class="text-sm sm:text-base font-bold text-white">My Attendance</h1>
                <p class="mt-0.5 text-[11px] text-slate-400">
                    {{ $student->full_name }} &bull; {{ $student->schoolClass?->name }} &bull; {{ $termLabels[$currentTerm] ?? 'Term '.$currentTerm }} &bull; {{ $currentSession }}
                </p>
            </div>
        </div>
    </div>

    {{-- ── Term Stats ── --}}
    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">

        {{-- Attendance Rate Ring --}}
        <div class="col-span-2 sm:col-span-1 relative overflow-hidden rounded-lg bg-white border border-slate-200/80 p-2.5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Attendance Rate</div>
                <div class="mt-0.5 flex items-baseline gap-1">
                    <span class="text-base font-extrabold text-slate-900">{{ $rate }}%</span>
                    <span class="text-[10px] font-semibold text-slate-500">({{ $rateLabel }})</span>
                </div>
            </div>
            <div class="relative shrink-0">
                <svg width="42" height="42" viewBox="0 0 100 100" class="-rotate-90">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#0f172a" stroke-width="10"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $circ }}"
                            stroke-dashoffset="{{ $circ - $dash }}"
                            style="transition: stroke-dashoffset 1s ease"/>
                </svg>
            </div>
        </div>

        {{-- Present --}}
        <div class="relative overflow-hidden rounded-lg bg-white border border-slate-200/80 p-2.5 shadow-sm border-l-4 border-l-emerald-500 flex flex-col justify-between">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Present</div>
            <div class="mt-0.5 flex items-baseline justify-between">
                <span class="text-base font-extrabold text-slate-800">{{ $present }}</span>
                <span class="text-[10px] font-medium text-slate-400">of {{ $total }} days</span>
            </div>
        </div>

        {{-- Absent --}}
        <div class="relative overflow-hidden rounded-lg bg-white border border-slate-200/80 p-2.5 shadow-sm border-l-4 border-l-rose-500 flex flex-col justify-between">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Absent</div>
            <div class="mt-0.5 flex items-baseline justify-between">
                <span class="text-base font-extrabold text-slate-800">{{ $absent }}</span>
                <span class="text-[10px] font-medium text-slate-400">day{{ $absent !== 1 ? 's' : '' }}</span>
            </div>
        </div>

        {{-- Late + Streak --}}
        <div class="relative overflow-hidden rounded-lg bg-white border border-slate-200/80 p-2.5 shadow-sm border-l-4 border-l-amber-500 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Late</span>
                @if ($streak > 0)
                    <span class="text-[9px] font-bold text-slate-600">🔥 {{ $streak }}d</span>
                @endif
            </div>
            <div class="mt-0.5 flex items-baseline justify-between">
                <span class="text-base font-extrabold text-slate-800">{{ $late }}</span>
                <span class="text-[10px] font-medium text-slate-400">arrival{{ $late !== 1 ? 's' : '' }}</span>
            </div>
        </div>
    </div>

    {{-- ── Term Progress Bar ── --}}
    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
        <div class="mb-2 flex items-center justify-between text-xs">
            <span class="font-bold text-slate-700">Term Attendance Overview</span>
            <span class="font-semibold {{ $rateColor }}">{{ $rate }}%</span>
        </div>
        <div class="flex h-2 w-full overflow-hidden rounded-full bg-slate-100">
            @if ($total > 0)
                <div class="h-full bg-emerald-500 transition-all duration-700" style="width: {{ $total > 0 ? round($present/$total*100) : 0 }}%" title="Present"></div>
                <div class="h-full bg-amber-400 transition-all duration-700" style="width: {{ $total > 0 ? round($late/$total*100) : 0 }}%" title="Late"></div>
                <div class="h-full bg-red-400 transition-all duration-700" style="width: {{ $total > 0 ? round($absent/$total*100) : 0 }}%" title="Absent"></div>
            @endif
        </div>
        <div class="mt-2.5 flex flex-wrap gap-3.5 text-[10px] font-semibold text-slate-500">
            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Present ({{ $present }})</span>
            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-400"></span>Late ({{ $late }})</span>
            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-red-400"></span>Absent ({{ $absent }})</span>
        </div>
    </div>

    {{-- ── Calendar ── --}}
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">

        {{-- Calendar header --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <button wire:click="previousMonth"
                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="text-center">
                <div class="text-xs sm:text-sm font-bold text-slate-900 leading-tight">{{ $monthName }}</div>
                @if ($mTotal > 0)
                    <div class="text-[9px] sm:text-[10px] text-slate-400 mt-0.5">{{ $mPresent }} present &bull; {{ $mAbsent }} absent &bull; {{ $mLate }} late &bull; {{ $mRate }}% rate</div>
                @else
                    <div class="text-[9px] sm:text-[10px] text-slate-400 mt-0.5">No records this month</div>
                @endif
            </div>
            <button wire:click="nextMonth"
                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <div class="p-3.5">
            {{-- Day labels --}}
            <div class="mb-3 grid grid-cols-7 text-center">
                @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                    <div class="text-xs font-black uppercase tracking-wider text-gray-400 py-1">{{ $d }}</div>
                @endforeach
            </div>

            {{-- Calendar grid --}}
            <div class="grid grid-cols-7 gap-1.5">
                {{-- Leading empty cells --}}
                @for ($i = 0; $i < $startDayOfWeek; $i++)
                    <div></div>
                @endfor

                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dateKey = \Carbon\Carbon::create($selectedYear, $selectedMonth, $day)->format('Y-m-d');
                        $mark    = $monthMarks->get($dateKey);
                        $isToday = $dateKey === now()->format('Y-m-d');
                        $isWeekend = \Carbon\Carbon::create($selectedYear, $selectedMonth, $day)->isWeekend();

                        $status = $mark?->status;
                    @endphp
                    <div class="group relative">
                        <div class="flex flex-col items-center justify-center rounded-2xl py-2 sm:py-3 transition-all
                            {{ $isToday ? 'ring-2 ring-violet-500 ring-offset-2' : '' }}
                            @if($status === 'Present') bg-emerald-500 shadow-md shadow-emerald-200
                            @elseif($status === 'Absent') bg-red-500 shadow-md shadow-red-200
                            @elseif($status === 'Late') bg-amber-400 shadow-md shadow-amber-200
                            @elseif($isWeekend) bg-gray-50
                            @else bg-gray-100
                            @endif">

                            {{-- Day number --}}
                            <span class="text-sm font-black leading-none
                                @if($status) text-white
                                @elseif($isWeekend) text-gray-300
                                @else text-gray-600
                                @endif">{{ $day }}</span>

                            {{-- Status icon --}}
                            @if($status === 'Present')
                                <svg class="mt-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif($status === 'Absent')
                                <svg class="mt-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            @elseif($status === 'Late')
                                <svg class="mt-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @else
                                <div class="mt-1 h-4"></div>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>

            {{-- Legend --}}
            <div class="mt-5 flex flex-wrap justify-center gap-3 sm:gap-5">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500 shadow-sm shadow-emerald-200">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-sm font-bold text-gray-700">Present</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-500 shadow-sm shadow-red-200">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <span class="text-sm font-bold text-gray-700">Absent</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-400 shadow-sm shadow-amber-200">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-sm font-bold text-gray-700">Late</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-xl bg-gray-100 ring-1 ring-gray-200"></div>
                    <span class="text-sm font-bold text-gray-500">No Record</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-xl bg-gray-100 ring-2 ring-violet-500 ring-offset-1"></div>
                    <span class="text-sm font-bold text-gray-500">Today</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Monthly Records List ── --}}
    @if ($monthMarks->isNotEmpty())
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ $monthName }} — Daily Records</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach ($monthMarks->sortByDesc(fn($m) => $m->sheet->date) as $mark)
                    @php
                        $s = $mark->status;
                        $rowBg   = match($s) { 'Present' => 'bg-emerald-50', 'Absent' => 'bg-red-50', 'Late' => 'bg-amber-50', default => '' };
                        $iconBg  = match($s) { 'Present' => 'bg-emerald-100 text-emerald-600', 'Absent' => 'bg-red-100 text-red-600', 'Late' => 'bg-amber-100 text-amber-600', default => 'bg-gray-100 text-gray-500' };
                        $badge   = match($s) { 'Present' => 'bg-emerald-100 text-emerald-800', 'Absent' => 'bg-red-100 text-red-800', 'Late' => 'bg-amber-100 text-amber-800', default => 'bg-gray-100 text-gray-600' };
                    @endphp
                    <div class="flex items-center justify-between px-6 py-3.5 {{ $rowBg }} transition hover:brightness-95">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl {{ $iconBg }}">
                                @if ($s === 'Present')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @elseif ($s === 'Absent')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $mark->sheet->date->format('l, d M Y') }}</div>
                                @if ($mark->note)
                                    <div class="text-xs text-gray-500">{{ $mark->note }}</div>
                                @endif
                            </div>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $badge }}">{{ $s }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

