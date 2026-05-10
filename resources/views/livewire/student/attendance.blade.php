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

    {{-- ── Header ── --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Attendance</h1>
        <p class="text-sm text-gray-500">
            {{ $student->full_name }} &bull; {{ $student->schoolClass?->name }}
            &bull; {{ $termLabels[$currentTerm] ?? 'Term '.$currentTerm }} &bull; {{ $currentSession }}
        </p>
    </div>

    {{-- ── Term Stats ── --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

        {{-- Attendance Rate Ring --}}
        <div class="col-span-2 sm:col-span-1 relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $rateBg }} p-5 text-white shadow-lg flex flex-col items-center justify-center">
            <div class="text-xs font-bold uppercase tracking-wider text-white/70 mb-3">Attendance Rate</div>
            <div class="relative">
                <svg width="100" height="100" viewBox="0 0 100 100" class="-rotate-90">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="8"/>
                    <circle cx="50" cy="50" r="40" fill="none" stroke="white" stroke-width="8"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $circ }}"
                            stroke-dashoffset="{{ $circ - $dash }}"
                            style="transition: stroke-dashoffset 1s ease"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-extrabold text-white">{{ $rate }}%</span>
                </div>
            </div>
            <div class="mt-2 text-sm font-bold text-white/90">{{ $rateLabel }}</div>
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        </div>

        {{-- Present --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-lg">
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-100">Present</div>
            <div class="mt-2 text-4xl font-extrabold">{{ $present }}</div>
            <div class="text-sm text-emerald-100">of {{ $total }} days</div>
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        </div>

        {{-- Absent --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 p-5 text-white shadow-lg">
            <div class="text-xs font-bold uppercase tracking-wider text-red-100">Absent</div>
            <div class="mt-2 text-4xl font-extrabold">{{ $absent }}</div>
            <div class="text-sm text-red-100">day{{ $absent !== 1 ? 's' : '' }} missed</div>
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        </div>

        {{-- Late + Streak --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 p-5 text-white shadow-lg">
            <div class="text-xs font-bold uppercase tracking-wider text-amber-100">Late</div>
            <div class="mt-2 text-4xl font-extrabold">{{ $late }}</div>
            <div class="text-sm text-amber-100">arrival{{ $late !== 1 ? 's' : '' }}</div>
            @if ($streak > 0)
                <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">
                    🔥 {{ $streak }}-day streak
                </div>
            @endif
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        </div>
    </div>

    {{-- ── Term Progress Bar ── --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
        <div class="mb-3 flex items-center justify-between text-sm">
            <span class="font-bold text-gray-700">Term Attendance Overview</span>
            <span class="font-semibold {{ $rateColor }}">{{ $rate }}%</span>
        </div>
        <div class="flex h-4 w-full overflow-hidden rounded-full bg-gray-100">
            @if ($total > 0)
                <div class="h-full bg-emerald-500 transition-all duration-700" style="width: {{ $total > 0 ? round($present/$total*100) : 0 }}%" title="Present"></div>
                <div class="h-full bg-amber-400 transition-all duration-700" style="width: {{ $total > 0 ? round($late/$total*100) : 0 }}%" title="Late"></div>
                <div class="h-full bg-red-400 transition-all duration-700" style="width: {{ $total > 0 ? round($absent/$total*100) : 0 }}%" title="Absent"></div>
            @endif
        </div>
        <div class="mt-3 flex flex-wrap gap-4 text-xs font-semibold text-gray-600">
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-emerald-500"></span>Present ({{ $present }})</span>
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-amber-400"></span>Late ({{ $late }})</span>
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-red-400"></span>Absent ({{ $absent }})</span>
        </div>
    </div>

    {{-- ── Calendar ── --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">

        {{-- Calendar header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <button wire:click="previousMonth"
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="text-center">
                <div class="text-base font-bold text-gray-900">{{ $monthName }}</div>
                @if ($mTotal > 0)
                    <div class="text-xs text-gray-500">{{ $mPresent }} present &bull; {{ $mAbsent }} absent &bull; {{ $mLate }} late &bull; {{ $mRate }}% rate</div>
                @else
                    <div class="text-xs text-gray-400">No records this month</div>
                @endif
            </div>
            <button wire:click="nextMonth"
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <div class="p-4 sm:p-6">
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
