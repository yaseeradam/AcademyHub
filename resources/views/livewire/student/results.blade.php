@php
    $termLabel = ['1' => 'First Term', '2' => 'Second Term', '3' => 'Third Term'];
    $gradeConfig = [
        'A' => ['label' => 'Excellent',    'bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'bar' => 'bg-emerald-500'],
        'B' => ['label' => 'Good',         'bg' => 'bg-blue-100',    'text' => 'text-blue-800',    'bar' => 'bg-blue-500'],
        'C' => ['label' => 'Average',      'bg' => 'bg-yellow-100',  'text' => 'text-yellow-800',  'bar' => 'bg-yellow-500'],
        'D' => ['label' => 'Below Avg',    'bg' => 'bg-orange-100',  'text' => 'text-orange-800',  'bar' => 'bg-orange-500'],
        'E' => ['label' => 'Poor',         'bg' => 'bg-red-100',     'text' => 'text-red-700',     'bar' => 'bg-red-400'],
        'F' => ['label' => 'Fail',         'bg' => 'bg-red-200',     'text' => 'text-red-900',     'bar' => 'bg-red-600'],
    ];
    $avgPct = $maxTotal > 0 ? round(($average / $maxTotal) * 100) : 0;
    $avgColor = $avgPct >= 70 ? 'text-emerald-600' : ($avgPct >= 50 ? 'text-amber-600' : 'text-red-600');
    $ordinal = fn($n) => $n . match(true) { $n % 100 >= 11 && $n % 100 <= 13 => 'th', $n % 10 === 1 => 'st', $n % 10 === 2 => 'nd', $n % 10 === 3 => 'rd', default => 'th' };
@endphp

<div class="space-y-6">

    {{-- ── Page Header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Results</h1>
            <p class="text-sm text-gray-500">{{ $student->full_name }} &bull; {{ $student->schoolClass?->name }}</p>
        </div>
        @if ($published && $scores->isNotEmpty())
            <a href="{{ route('student.report-card', ['term' => $selectedTerm, 'session' => $selectedSession]) }}"
               target="_blank"
               class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-violet-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Report Card
            </a>
        @endif
    </div>

    {{-- ── Filters ── --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500">Session</label>
                <select wire:model.live="selectedSession"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-800 focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                    @forelse ($sessions as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @empty
                        <option value="{{ $selectedSession }}">{{ $selectedSession }}</option>
                    @endforelse
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500">Term</label>
                <select wire:model.live="selectedTerm"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-800 focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                    <option value="1">First Term</option>
                    <option value="2">Second Term</option>
                    <option value="3">Third Term</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ── Not Published ── --}}
    @if (! $published)
        <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-white py-16 text-center shadow-sm">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                <svg class="h-8 w-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-800">Results Not Yet Published</h3>
            <p class="mt-1 max-w-sm text-sm text-gray-500">
                Results for <strong>{{ $termLabel[$selectedTerm] ?? 'Term '.$selectedTerm }}</strong> &bull; <strong>{{ $selectedSession }}</strong> have not been released yet. Check back later.
            </p>
        </div>

    @elseif ($scores->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-white py-16 text-center shadow-sm">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-800">No Scores Recorded</h3>
            <p class="mt-1 text-sm text-gray-500">No scores have been entered for this term yet.</p>
        </div>

    @else
        {{-- ── Summary Stats ── --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            {{-- Average --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 to-purple-700 p-5 text-white shadow-lg">
                <div class="text-xs font-bold uppercase tracking-wider text-violet-200">Average</div>
                <div class="mt-2 text-4xl font-extrabold">{{ $average }}</div>
                <div class="text-sm font-semibold text-violet-200">out of {{ $maxTotal }}</div>
                <div class="mt-1 text-xs text-violet-300">{{ $avgPct }}%</div>
                <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            </div>

            {{-- Position --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 p-5 text-white shadow-lg">
                <div class="text-xs font-bold uppercase tracking-wider text-amber-100">Position</div>
                @if ($overallPosition)
                    <div class="mt-2 text-4xl font-extrabold">{{ $ordinal($overallPosition) }}</div>
                    <div class="text-sm font-semibold text-amber-100">out of {{ $classSize }}</div>
                @else
                    <div class="mt-2 text-3xl font-extrabold">—</div>
                    <div class="text-sm text-amber-100">Not ranked</div>
                @endif
                <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            </div>

            {{-- Passed --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-lg">
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-100">Passed</div>
                <div class="mt-2 text-4xl font-extrabold">{{ $passed }}</div>
                <div class="text-sm font-semibold text-emerald-100">of {{ $totalSubjects }} subjects</div>
                <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            </div>

            {{-- Failed --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-5 text-white shadow-lg">
                <div class="text-xs font-bold uppercase tracking-wider text-rose-100">Failed</div>
                <div class="mt-2 text-4xl font-extrabold">{{ $failed }}</div>
                <div class="text-sm font-semibold text-rose-100">subject{{ $failed !== 1 ? 's' : '' }}</div>
                <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            </div>
        </div>

        {{-- ── Subject Score Cards ── --}}
        <div class="space-y-3">
            @foreach ($scores as $score)
                @php
                    $pct   = $maxTotal > 0 ? round(($score->total / $maxTotal) * 100) : 0;
                    $gc    = $gradeConfig[$score->grade] ?? $gradeConfig['F'];
                @endphp
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        {{-- Subject name + grade badge --}}
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $gc['bg'] }}">
                                <span class="text-sm font-extrabold {{ $gc['text'] }}">{{ $score->grade }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-bold text-gray-900">{{ $score->subject?->name }}</div>
                                <div class="text-xs text-gray-500">{{ $gc['label'] }}</div>
                            </div>
                        </div>

                        {{-- CA1 / CA2 / Exam chips --}}
                        <div class="flex items-center gap-2 text-xs font-semibold">
                            <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-blue-700">CA1: {{ $score->ca1 ?? '—' }}</span>
                            <span class="rounded-lg bg-indigo-50 px-2.5 py-1 text-indigo-700">CA2: {{ $score->ca2 ?? '—' }}</span>
                            <span class="rounded-lg bg-purple-50 px-2.5 py-1 text-purple-700">Exam: {{ $score->exam ?? '—' }}</span>
                        </div>

                        {{-- Total + percent --}}
                        <div class="text-right flex-shrink-0">
                            <div class="text-xl font-extrabold text-gray-900">{{ $score->total }}<span class="text-sm font-semibold text-gray-400">/{{ $maxTotal }}</span></div>
                            <div class="text-xs font-semibold text-gray-500">{{ $pct }}%</div>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full {{ $gc['bar'] }} transition-all duration-700"
                             style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Full Table ── --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Score Sheet — {{ $termLabel[$selectedTerm] ?? 'Term '.$selectedTerm }} &bull; {{ $selectedSession }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-500">
                            <th class="px-5 py-3 text-left">Subject</th>
                            <th class="px-4 py-3 text-center">CA1</th>
                            <th class="px-4 py-3 text-center">CA2</th>
                            <th class="px-4 py-3 text-center">Exam</th>
                            <th class="px-4 py-3 text-center">Total</th>
                            <th class="px-4 py-3 text-center">Grade</th>
                            <th class="px-4 py-3 text-center">Position</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($scores as $score)
                            @php $gc = $gradeConfig[$score->grade] ?? $gradeConfig['F']; @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3.5 font-semibold text-gray-900">{{ $score->subject?->name }}</td>
                                <td class="px-4 py-3.5 text-center text-gray-700">{{ $score->ca1 ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-center text-gray-700">{{ $score->ca2 ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-center text-gray-700">{{ $score->exam ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-gray-900">{{ $score->total }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $gc['bg'] }} {{ $gc['text'] }}">
                                        {{ $score->grade }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center text-gray-600">
                                    {{ $score->position ? $ordinal($score->position) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-bold text-gray-800">
                            <td class="px-5 py-3.5">Total</td>
                            <td class="px-4 py-3.5 text-center">{{ $scores->sum('ca1') }}</td>
                            <td class="px-4 py-3.5 text-center">{{ $scores->sum('ca2') }}</td>
                            <td class="px-4 py-3.5 text-center">{{ $scores->sum('exam') }}</td>
                            <td class="px-4 py-3.5 text-center">{{ $scores->sum('total') }}</td>
                            <td colspan="2" class="px-4 py-3.5 text-center text-violet-700">
                                Avg: {{ $average }} ({{ $avgPct }}%)
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ── Grade Distribution ── --}}
        @php
            $gradeCounts = $scores->groupBy('grade')->map->count();
        @endphp
        @if ($gradeCounts->isNotEmpty())
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500">Grade Distribution</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach ($gradeConfig as $g => $cfg)
                        @if ($gradeCounts->has($g))
                            <div class="flex items-center gap-2 rounded-xl {{ $cfg['bg'] }} px-4 py-2.5">
                                <span class="text-lg font-extrabold {{ $cfg['text'] }}">{{ $g }}</span>
                                <div>
                                    <div class="text-xs font-bold {{ $cfg['text'] }}">{{ $gradeCounts[$g] }} subject{{ $gradeCounts[$g] > 1 ? 's' : '' }}</div>
                                    <div class="text-xs {{ $cfg['text'] }} opacity-75">{{ $cfg['label'] }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

    @endif
</div>
