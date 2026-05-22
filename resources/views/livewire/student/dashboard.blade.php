<div class="space-y-6">

    {{-- Page Header --}}
    <div class="rounded-xl bg-slate-900 px-4 py-3 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
            <div>
                <h1 class="text-sm sm:text-base font-bold text-white">Dashboard</h1>
                <p class="mt-0.5 text-[11px] text-slate-400">Welcome back, {{ $student->first_name }} &mdash; {{ $student->schoolClass?->name ?? '' }}{{ $student->section ? ' · '.$student->section->name : '' }}</p>
            </div>
            <div class="flex flex-wrap gap-1.5">
                <span class="inline-flex items-center gap-1 rounded-lg bg-slate-800 px-2 py-0.5 text-[10px] font-semibold text-slate-300">{{ $student->admission_number }}</span>
                <span class="inline-flex items-center gap-1 rounded-lg bg-slate-800 px-2 py-0.5 text-[10px] font-semibold text-slate-300">{{ $stats['current_session'] ?? '' }} · Term {{ $stats['current_term'] ?? '' }}</span>
            </div>
        </div>
    </div>

    {{-- Bottom Row: Quick Actions (left) + Grade Distribution (right) --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 items-start">

        {{-- Quick Actions --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-100">
            <div class="border-b border-slate-100 px-4 py-3">
                <div class="text-sm font-bold text-slate-800">Quick Actions</div>
                <div class="mt-0.5 text-[11px] text-slate-400">Navigate to your portal sections</div>
            </div>
            <div class="divide-y divide-slate-50 p-1.5">
                @php
                    $actions = [
                        ['route'=>'student.homework',    'label'=>'Homework',    'sub'=>'Check & submit assignments',  'icon'=>'<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                        ['route'=>'student.results',     'label'=>'Results',     'sub'=>'View your term scores',       'icon'=>'<path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
                        ['route'=>'student.attendance',  'label'=>'Attendance',  'sub'=>'View your attendance record', 'icon'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>'],
                        ['route'=>'student.performance', 'label'=>'Performance', 'sub'=>'Track your progress',         'icon'=>'<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>'],
                        ['route'=>'student.exams',       'label'=>'Exams',       'sub'=>'Upcoming CBT exams',          'icon'=>'<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>'],
                        ['route'=>'student.profile',     'label'=>'My Profile',  'sub'=>'View & update your info',     'icon'=>'<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
                    ];
                @endphp
                @foreach($actions as $action)
                    <a href="{{ route($action['route']) }}"
                       class="group flex items-center gap-2.5 rounded-lg px-2.5 py-2 transition hover:bg-slate-50">
                        <div class="grid h-8 w-8 flex-shrink-0 place-items-center rounded-lg bg-slate-50 border border-slate-100 text-slate-500 transition group-hover:bg-indigo-50 group-hover:text-indigo-600 group-hover:border-indigo-100 shadow-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $action['icon'] !!}</svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800">{{ $action['label'] }}</div>
                            <div class="text-[10px] text-slate-400 leading-tight">{{ $action['sub'] }}</div>
                        </div>
                        <svg class="h-3.5 w-3.5 flex-shrink-0 text-slate-300 transition group-hover:text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Grade Distribution --}}
        <div class="lg:col-span-2 rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <div class="text-base font-bold text-slate-800">Grade Distribution</div>
                    <div class="mt-0.5 text-xs text-slate-400">Current term performance by grade</div>
                </div>
                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-600">This Term</span>
            </div>
            <div class="p-5">
                @if(count($stats['grades']) > 0)
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                        @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                            @php
                                $count = $stats['grades'][$grade] ?? 0;
                                $active = [
                                    'A' => 'bg-emerald-50 border-emerald-300 text-emerald-600',
                                    'B' => 'bg-blue-50 border-blue-300 text-blue-600',
                                    'C' => 'bg-amber-50 border-amber-300 text-amber-600',
                                    'D' => 'bg-orange-50 border-orange-300 text-orange-600',
                                    'E' => 'bg-red-50 border-red-300 text-red-500',
                                    'F' => 'bg-red-50 border-red-300 text-red-600',
                                ];
                                $cls = $count > 0 ? ($active[$grade] ?? 'bg-slate-50 border-slate-200 text-slate-400') : 'bg-slate-50 border-slate-100 text-slate-300';
                            @endphp
                            <div class="flex flex-col items-center justify-center rounded-xl border-2 py-5 {{ $cls }}">
                                <div class="text-2xl font-black">{{ $grade }}</div>
                                <div class="mt-1 text-xs font-semibold">{{ $count }} subj</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-5 space-y-2">
                        @foreach(['A' => ['Excellent','bg-emerald-500'], 'B' => ['Very Good','bg-blue-500'], 'C' => ['Good','bg-amber-500'], 'F' => ['Fail','bg-red-500']] as $g => [$label, $bar])
                            @php $cnt = $stats['grades'][$g] ?? 0; $pct = $stats['total_subjects'] > 0 ? round(($cnt / $stats['total_subjects']) * 100) : 0; @endphp
                            @if($cnt > 0)
                            <div class="flex items-center gap-3">
                                <div class="w-6 text-xs font-bold text-slate-500">{{ $g }}</div>
                                <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="w-8 text-right text-xs font-semibold text-slate-500">{{ $pct }}%</div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center">
                        <svg class="mx-auto h-10 w-10 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <p class="mt-2 text-sm font-semibold text-slate-400">No results yet this term</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
