@php
    $avg        = data_get($this, 'performanceData.overview.average_score', 0);
    $pct        = data_get($this, 'performanceData.overview.percentage', 0);
    $grade      = data_get($this, 'performanceData.overview.grade', '-');
    $passed     = data_get($this, 'performanceData.overview.subjects_passed', 0);
    $totalSubs  = data_get($this, 'performanceData.overview.total_subjects', 0);
    $attRate    = data_get($this, 'performanceData.attendance_impact.attendance_rate', 0);
    $hwRate     = data_get($this, 'performanceData.homework_performance.completion_rate', 0);

    $gradeBg = match(true) {
        $pct >= 70 => 'from-emerald-500 to-teal-600',
        $pct >= 50 => 'from-amber-500 to-orange-500',
        default    => 'from-red-500 to-rose-600',
    };
    $gradeLabel = match(true) {
        $pct >= 70 => 'Excellent',
        $pct >= 50 => 'Average',
        default    => 'Needs Work',
    };

    // SVG ring (r=40, circ≈251.3)
    $circ = 251.3;
    $dash = round($circ * $pct / 100, 1);
@endphp

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="rounded-2xl bg-slate-900 p-5 shadow-lg text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-white flex items-center gap-2">
                    <svg class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Student Academic Performance
                </h1>
                <p class="text-xs text-slate-400 font-medium mt-1">Continuous progress tracking, terminal analysis, and cumulative academic dossier.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Session Selector --}}
                <div class="flex items-center gap-1.5 bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700">
                    <span class="text-xs font-bold text-slate-400">Session:</span>
                    <select wire:model.live="selectedSession" class="bg-transparent text-xs font-black text-white border-none focus:ring-0 p-0 cursor-pointer">
                        @foreach($this->availableSessions as $s)
                            <option value="{{ $s }}" class="bg-slate-900 text-white">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Term Selector --}}
                <div class="flex items-center gap-1.5 bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700">
                    <span class="text-xs font-bold text-slate-400">Term:</span>
                    <select wire:model.live="selectedTerm" class="bg-transparent text-xs font-black text-white border-none focus:ring-0 p-0 cursor-pointer">
                        @foreach($this->availableTerms as $t)
                            <option value="{{ $t['value'] }}" class="bg-slate-900 text-white">{{ $t['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                @if($this->student)
                    <a href="{{ route('results.report-booklet', ['student' => $this->student->id, 'session' => $selectedSession, 'download' => 1]) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 text-xs font-black text-white shadow-md hover:from-emerald-500 hover:to-teal-500 transition active:scale-95">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Annual Report Book
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    @if($totalSubs > 0)
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

            {{-- Average Score Ring --}}
            <div class="col-span-2 sm:col-span-1 relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $gradeBg }} p-6 text-white shadow-lg flex flex-col items-center justify-center">
                <div class="text-xs font-bold uppercase tracking-wider text-white/70 mb-3">Average Score</div>
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
                        <span class="text-2xl font-extrabold text-white">{{ $avg }}</span>
                        <span class="text-xs text-white/80">{{ $pct }}%</span>
                    </div>
                </div>
                <div class="mt-2 text-sm font-bold text-white/90">{{ $gradeLabel }}</div>
                <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            </div>

            {{-- Subjects Passed --}}
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-6 text-white shadow-lg">
                    <div class="text-xs font-bold uppercase tracking-wider text-violet-100">Subjects</div>
                    <div class="mt-2 text-4xl font-extrabold">{{ $passed }}</div>
                    <div class="text-sm text-violet-100">of {{ $totalSubs }} passed</div>
                    <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">
                        Grade {{ $grade }}
                    </div>
                <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            </div>

            {{-- Attendance Rate --}}
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 p-6 text-white shadow-lg">
                    <div class="text-xs font-bold uppercase tracking-wider text-cyan-100">Attendance</div>
                    <div class="mt-2 text-4xl font-extrabold">{{ $attRate }}%</div>
                    <div class="text-sm text-cyan-100">
                        {{ data_get($this, 'performanceData.attendance_impact.present_days') }}/{{ data_get($this, 'performanceData.attendance_impact.total_days') }} days
                    </div>
                <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            </div>

            {{-- Homework Rate --}}
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 p-6 text-white shadow-lg">
                    <div class="text-xs font-bold uppercase tracking-wider text-amber-100">Homework</div>
                    <div class="mt-2 text-4xl font-extrabold">{{ $hwRate }}%</div>
                    <div class="text-sm text-amber-100">
                        {{ data_get($this, 'performanceData.homework_performance.submitted') }}/{{ data_get($this, 'performanceData.homework_performance.total_assignments') }} submitted
                    </div>
                <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            </div>
        </div>

        {{-- ── Tabs Panel ── --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">

            {{-- Tab Nav --}}
            <div class="border-b border-gray-100 overflow-x-auto">
                <nav class="flex -mb-px min-w-max px-2">
                    @foreach([
                        ['overview',     'Overview'],
                        ['subjects',     'Subjects'],
                        ['trends',       'Trends'],
                        ['improvement',  'Improvement'],
                        ['booklet',      'Academic Report Book'],
                    ] as [$key, $label])
                        <button wire:click="setTab('{{ $key }}')"
                                class="px-5 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition
                                       {{ $activeTab === $key
                                            ? 'border-violet-500 text-violet-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>

            <div class="p-5 sm:p-6">

                {{-- ── Overview Tab ── --}}
                @if($activeTab === 'overview')
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                        {{-- Strengths --}}
                        <div>
                            <h3 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-gray-500">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                </span>
                                Your Strengths
                            </h3>
                            @if($this->performanceData['strengths_weaknesses']['strengths']->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($this->performanceData['strengths_weaknesses']['strengths'] as $s)
                                        <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3 ring-1 ring-emerald-100">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $s['subject'] }}</p>
                                                <p class="text-xs text-gray-500">Grade {{ $s['grade'] }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xl font-extrabold text-emerald-600">{{ $s['score'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $s['percentage'] }}%</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-400">Keep working hard to identify your strengths!</p>
                            @endif
                        </div>

                        {{-- Weaknesses --}}
                        <div>
                            <h3 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-gray-500">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-red-600">
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                </span>
                                Needs Attention
                            </h3>
                            @if($this->performanceData['strengths_weaknesses']['weaknesses']->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($this->performanceData['strengths_weaknesses']['weaknesses'] as $w)
                                        <div class="flex items-center justify-between rounded-xl bg-red-50 px-4 py-3 ring-1 ring-red-100">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $w['subject'] }}</p>
                                                <p class="text-xs text-gray-500">Grade {{ $w['grade'] }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xl font-extrabold text-red-600">{{ $w['score'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $w['percentage'] }}%</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-400">Great job! No weak areas identified.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Attendance Impact --}}
                    <div class="mt-5 rounded-2xl bg-gradient-to-br from-cyan-50 to-blue-50 p-5 ring-1 ring-cyan-100">
                        <h3 class="mb-1 text-sm font-bold text-gray-700">Attendance Impact</h3>
                        <p class="text-xs text-gray-500">{{ $this->performanceData['attendance_impact']['correlation'] }}</p>
                        <div class="mt-4 grid grid-cols-4 gap-3 text-center">
                            <div>
                                <p class="text-2xl font-extrabold text-cyan-600">{{ $this->performanceData['attendance_impact']['present_days'] }}</p>
                                <p class="text-xs text-gray-500">Present</p>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold text-red-500">{{ $this->performanceData['attendance_impact']['absent_days'] }}</p>
                                <p class="text-xs text-gray-500">Absent</p>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold text-amber-500">{{ $this->performanceData['attendance_impact']['late_days'] }}</p>
                                <p class="text-xs text-gray-500">Late</p>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold text-emerald-600">{{ $this->performanceData['attendance_impact']['attendance_rate'] }}%</p>
                                <p class="text-xs text-gray-500">Rate</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── Subjects Tab ── --}}
                @if($activeTab === 'subjects')
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    <th class="pb-3 text-left text-base font-medium">Subject</th>
                                    <th class="pb-3 text-center text-base font-medium">CA1</th>
                                    <th class="pb-3 text-center text-base font-medium">CA2</th>
                                    <th class="pb-3 text-center text-base font-medium">Exam</th>
                                    <th class="pb-3 text-center text-base font-medium">Total</th>
                                    <th class="pb-3 text-center text-base font-medium">Grade</th>
                                    <th class="pb-3 text-center text-base font-medium">%</th>
                                    <th class="pb-3 text-center text-base font-medium">Pos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($this->performanceData['subject_performance'] as $sub)
                                    @php
                                        $gc = match(true) {
                                            $sub['grade'] === 'A' => 'bg-emerald-100 text-emerald-800',
                                            $sub['grade'] === 'F' => 'bg-red-100 text-red-800',
                                            default               => 'bg-amber-100 text-amber-800',
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-3 pr-4 text-sm font-semibold text-gray-900">{{ $sub['subject'] }}</td>
                                        <td class="py-3 text-center text-sm text-gray-600">{{ $sub['ca1'] }}</td>
                                        <td class="py-3 text-center text-sm text-gray-600">{{ $sub['ca2'] }}</td>
                                        <td class="py-3 text-center text-sm text-gray-600">{{ $sub['exam'] }}</td>
                                        <td class="py-3 text-center text-sm font-bold text-gray-900">{{ $sub['total'] }}</td>
                                        <td class="py-3 text-center">
                                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $gc }}">{{ $sub['grade'] }}</span>
                                        </td>
                                        <td class="py-3 text-center text-sm text-gray-600">{{ $sub['percentage'] }}%</td>
                                        <td class="py-3 text-center text-sm text-gray-600">{{ $sub['position'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- ── Trends Tab ── --}}
                @if($activeTab === 'trends')
                    <div class="space-y-6">

                        {{-- Term comparison cards --}}
                        <div>
                            <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">Term-by-Term</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                @foreach($this->performanceData['term_comparison'] as $term)
                                    <div class="rounded-2xl bg-gradient-to-br from-violet-50 to-purple-50 p-4 ring-1 ring-violet-100">
                                        <p class="text-xs font-semibold text-gray-500">Term {{ $term['term'] }}</p>
                                        <p class="mt-1 text-3xl font-extrabold text-violet-600">{{ $term['average_score'] }}</p>
                                        <p class="text-sm text-gray-600">{{ $term['percentage'] }}% &bull; Grade {{ $term['grade'] }}</p>
                                        <p class="mt-1 text-xs text-gray-400">{{ $term['subjects_count'] }} subjects</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Bar chart --}}
                        @if($this->performanceData['progress_trend']->isNotEmpty())
                            <div>
                                <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">Performance Trend</h3>
                                <div class="rounded-2xl bg-gray-50 p-5 ring-1 ring-gray-100">
                                    <div class="flex items-end justify-around h-48 gap-2">
                                        @foreach($this->performanceData['progress_trend'] as $trend)
                                            <div class="flex flex-col items-center gap-1 flex-1">
                                                <span class="text-xs font-bold text-gray-700">{{ $trend['average'] }}</span>
                                                <div class="w-full rounded-t-xl bg-gradient-to-t from-violet-500 to-purple-400"
                                                     style="height: {{ max(4, $trend['percentage'] * 1.5) }}px;"></div>
                                                <span class="text-xs text-gray-500">{{ $trend['term'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- CBT --}}
                        @if($this->performanceData['cbt_performance']['total_exams'] > 0)
                            <div class="rounded-2xl bg-gradient-to-br from-purple-50 to-violet-50 p-5 ring-1 ring-purple-100">
                                <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">CBT Exam Performance</h3>
                                <div class="grid grid-cols-2 gap-4 text-center sm:grid-cols-4">
                                    <div><p class="text-2xl font-extrabold text-violet-600">{{ $this->performanceData['cbt_performance']['total_exams'] }}</p><p class="text-xs text-gray-500">Total</p></div>
                                    <div><p class="text-2xl font-extrabold text-blue-600">{{ $this->performanceData['cbt_performance']['average_percent'] }}%</p><p class="text-xs text-gray-500">Average</p></div>
                                    <div><p class="text-2xl font-extrabold text-emerald-600">{{ $this->performanceData['cbt_performance']['exams_passed'] }}</p><p class="text-xs text-gray-500">Passed</p></div>
                                    <div><p class="text-2xl font-extrabold text-red-500">{{ $this->performanceData['cbt_performance']['exams_failed'] }}</p><p class="text-xs text-gray-500">Failed</p></div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ── Improvement Tab ── --}}
                @if($activeTab === 'improvement')
                    <div class="space-y-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">Subject-wise Progress</h3>
                        @foreach($this->performanceData['improvement_areas'] as $area)
                            @php
                                $trendColor = match($area['trend']) {
                                    'Improving' => 'bg-emerald-100 text-emerald-800',
                                    'Declining' => 'bg-red-100 text-red-800',
                                    default     => 'bg-gray-100 text-gray-700',
                                };
                                $changeColor = $area['change'] > 0 ? 'text-emerald-600' : ($area['change'] < 0 ? 'text-red-600' : 'text-gray-500');
                            @endphp
                            <div class="flex items-center justify-between rounded-xl px-4 py-3.5 ring-1 transition
                                        {{ $area['needs_attention'] ? 'bg-red-50 ring-red-100' : 'bg-white ring-gray-100 hover:bg-gray-50' }}">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $area['subject'] }}</p>
                                    <div class="mt-0.5 flex flex-wrap gap-3 text-xs text-gray-500">
                                        <span>Prev: <span class="font-semibold text-gray-700">{{ $area['previous_score'] }}</span></span>
                                        <span>Now: <span class="font-semibold text-gray-700">{{ $area['current_score'] }}</span></span>
                                        <span>Change: <span class="font-semibold {{ $changeColor }}">{{ $area['change'] > 0 ? '+' : '' }}{{ $area['change'] }}</span></span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $trendColor }}">{{ $area['trend'] }}</span>
                                    @if($area['needs_attention'])
                                        <span class="text-xs font-semibold text-red-500">Needs Attention</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ── Booklet Dossier Tab ── --}}
                @if($activeTab === 'booklet')
                    @php
                        $dossier = $this->bookletDossier;
                    @endphp

                    @if($dossier)
                        <div class="space-y-6">
                            {{-- Dossier Header Banner --}}
                            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 text-white shadow-xl">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-black uppercase tracking-wider text-indigo-300 ring-1 ring-indigo-400/30">
                                            Continuous Assessment Dossier
                                        </span>
                                        <h2 class="mt-2 text-2xl font-black text-white tracking-tight">
                                            Academic Session {{ $dossier['session'] }}
                                        </h2>
                                        <p class="text-xs text-slate-300 font-medium mt-1">
                                            Class: <span class="font-bold text-white">{{ $dossier['student']->schoolClass?->name ?? 'N/A' }}</span>
                                            @if($dossier['student']->admission_number)
                                                • Admission No: <span class="font-bold text-white">{{ $dossier['student']->admission_number }}</span>
                                            @endif
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('results.report-booklet.preview', ['student' => $this->student->id, 'session' => $selectedSession]) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-xs font-black text-white backdrop-blur hover:bg-white/20 transition active:scale-95">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Preview PDF
                                        </a>

                                        <a href="{{ route('results.report-booklet', ['student' => $this->student->id, 'session' => $selectedSession, 'download' => 1]) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-2.5 text-xs font-black text-white shadow-lg shadow-emerald-900/30 hover:from-emerald-400 hover:to-teal-500 transition active:scale-95">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Download Official PDF
                                        </a>
                                    </div>
                                </div>

                                {{-- Quick Dossier KPIs --}}
                                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 border-t border-white/10 pt-4">
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Cumulative Average</div>
                                        <div class="text-xl font-black text-white">{{ $dossier['cumulativeAnnualAverage'] ?? 0 }}%</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Annual Position</div>
                                        <div class="text-xl font-black text-emerald-400">
                                            {{ $dossier['annualPosition'] ?? '—' }} <span class="text-xs text-emerald-300 font-bold">/ {{ $dossier['totalStudents'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Attendance Rate</div>
                                        <div class="text-xl font-black text-cyan-300">{{ $dossier['attendanceTimeline']['rate'] ?? 0 }}%</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Progression Status</div>
                                        <div class="text-sm font-black text-amber-300 truncate">{{ $dossier['promotion']['status_label'] ?? 'Pending' }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Multi-Term Comparative Matrix Table --}}
                            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                                <div class="bg-gray-50/80 px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-700">Multi-Term Subject Matrix</h3>
                                    <span class="text-xs text-gray-400 font-semibold">{{ count($dossier['subjectRows']) }} Subjects Tracked</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead>
                                            <tr class="border-b border-gray-100 bg-gray-50 text-[10px] font-black uppercase text-gray-400 tracking-wider">
                                                <th class="py-3 px-4">Subject</th>
                                                <th class="py-3 px-3 text-center">Term 1</th>
                                                <th class="py-3 px-3 text-center">Term 2</th>
                                                <th class="py-3 px-3 text-center">Term 3</th>
                                                <th class="py-3 px-3 text-center bg-indigo-50/50 text-indigo-700">Annual Avg</th>
                                                <th class="py-3 px-3 text-center">Grade</th>
                                                <th class="py-3 px-3 text-center">Rank</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 font-medium">
                                            @foreach($dossier['subjectRows'] as $row)
                                                @php
                                                    $t1 = $row['terms'][1] ?? ['is_pending' => true, 'total' => null];
                                                    $t2 = $row['terms'][2] ?? ['is_pending' => true, 'total' => null];
                                                    $t3 = $row['terms'][3] ?? ['is_pending' => true, 'total' => null];
                                                @endphp
                                                <tr class="hover:bg-gray-50/50 transition-colors">
                                                    <td class="py-3 px-4 font-bold text-gray-900">{{ $row['subject']?->name ?? 'Subject' }}</td>
                                                    <td class="py-3 px-3 text-center {{ $t1['is_pending'] ? 'text-gray-300' : 'text-gray-700 font-bold' }}">
                                                        {{ $t1['is_pending'] ? '-' : ($t1['total'] ?? '-') }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center {{ $t2['is_pending'] ? 'text-gray-300' : 'text-gray-700 font-bold' }}">
                                                        {{ $t2['is_pending'] ? '-' : ($t2['total'] ?? '-') }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center {{ $t3['is_pending'] ? 'text-gray-300' : 'text-gray-700 font-bold' }}">
                                                        {{ $t3['is_pending'] ? '-' : ($t3['total'] ?? '-') }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-black bg-indigo-50/30 text-indigo-900">
                                                        {{ $row['annual_average'] !== null ? $row['annual_average'].'%' : '-' }}
                                                    </td>
                                                    <td class="py-3 px-3 text-center font-bold">
                                                        <span class="rounded px-1.5 py-0.5 text-[10px] {{ ($row['annual_average'] ?? 0) >= 50 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                                            {{ $row['annual_grade'] ?? '-' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-3 text-center text-gray-500 font-semibold">{{ $row['annual_rank'] ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Promotion & Longitudinal Summary --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Progression Decision --}}
                                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                                    <h4 class="text-xs font-black uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Promotion Verdict
                                    </h4>
                                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                                        <div class="text-sm font-black text-slate-900">{{ $dossier['promotion']['status_label'] }}</div>
                                        <div class="text-xs text-slate-500 mt-1">{{ $dossier['promotion']['verdict_reason'] }}</div>
                                    </div>
                                    @if(!empty($dossier['principalRemarks']))
                                        <div class="mt-3 text-xs text-slate-600 italic">
                                            "{{ $dossier['principalRemarks'] }}"
                                            <span class="block not-italic text-[10px] text-slate-400 font-bold mt-1">— Principal / Head of School</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Longitudinal Attendance --}}
                                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                                    <h4 class="text-xs font-black uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Attendance Progression
                                    </h4>
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div class="rounded-xl bg-gray-50 p-3">
                                            <div class="text-[10px] font-bold text-gray-400">Term 1</div>
                                            <div class="text-sm font-black text-gray-800">{{ $dossier['attendanceTimeline']['timeline'][1]['present'] ?? 0 }} / {{ $dossier['attendanceTimeline']['timeline'][1]['opened'] ?? 0 }}</div>
                                        </div>
                                        <div class="rounded-xl bg-gray-50 p-3">
                                            <div class="text-[10px] font-bold text-gray-400">Term 2</div>
                                            <div class="text-sm font-black text-gray-800">{{ $dossier['attendanceTimeline']['timeline'][2]['present'] ?? 0 }} / {{ $dossier['attendanceTimeline']['timeline'][2]['opened'] ?? 0 }}</div>
                                        </div>
                                        <div class="rounded-xl bg-gray-50 p-3">
                                            <div class="text-[10px] font-bold text-gray-400">Term 3</div>
                                            <div class="text-sm font-black text-gray-800">{{ $dossier['attendanceTimeline']['timeline'][3]['present'] ?? 0 }} / {{ $dossier['attendanceTimeline']['timeline'][3]['opened'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-xs text-gray-500 flex justify-between items-center px-1">
                                        <span>Total Days Present: <strong>{{ $dossier['attendanceTimeline']['totalPresent'] ?? 0 }}</strong> / {{ $dossier['attendanceTimeline']['totalOpened'] ?? 0 }}</span>
                                        <span class="font-bold text-emerald-600">{{ $dossier['attendanceTimeline']['rate'] ?? 0 }}% Attendance</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl bg-amber-50 p-8 text-center ring-1 ring-amber-100">
                            <p class="text-sm font-semibold text-gray-600">No report booklet dossier available for session {{ $selectedSession }}.</p>
                        </div>
                    @endif
                @endif

            </div>
        </div>

    @else
        <div class="rounded-2xl bg-amber-50 p-8 text-center ring-1 ring-amber-100">
            <svg class="mx-auto mb-3 h-12 w-12 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-sm font-semibold text-gray-600">No performance data available for the selected term.</p>
        </div>
    @endif

</div>
