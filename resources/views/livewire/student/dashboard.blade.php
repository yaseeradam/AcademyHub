<div class="space-y-6">

    {{-- Hero Card --}}
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background: linear-gradient(135deg, #166534 0%, #15803d 60%, #16a34a 100%);">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #14532d 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>

        <div class="relative flex items-end justify-between">
            {{-- Left: content --}}
            <div class="flex-1 px-8 py-8">
                <div class="flex items-center gap-2 mb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-300 animate-pulse"></span>
                    <span class="text-sm font-semibold uppercase tracking-widest" style="color:#6ee7b7;">Student Portal</span>
                </div>
                <h2 class="text-4xl font-bold text-white tracking-tight">Welcome, {{ $student->first_name }}!</h2>
                <p class="mt-2 text-lg font-medium" style="color:#6ee7b7;">
                    {{ $student->schoolClass?->name ?? 'N/A' }}{{ $student->section ? ' · ' . $student->section->name : '' }}
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-sm font-semibold text-white" style="background:rgba(255,255,255,0.15);">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        {{ $student->admission_number }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-sm font-semibold text-white" style="background:rgba(255,255,255,0.15);">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $stats['current_session'] ?? '' }} - Term {{ $stats['current_term'] ?? '' }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-sm font-semibold text-white" style="background:rgba(255,255,255,0.15);">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ now()->format('l, F j') }}
                    </span>
                </div>
            </div>

            {{-- Right: avatar --}}
            <div class="hidden lg:block flex-shrink-0 self-end pr-8">
                @if($student->passport_photo_url)
                    <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name }}"
                         class="h-40 w-auto object-contain object-bottom" style="display:block;">
                @else
                    <div class="h-40 w-32 flex items-center justify-center" style="background:rgba(255,255,255,0.1);">
                        <div class="h-16 w-16 rounded-full flex items-center justify-center text-white text-3xl font-black"
                             style="background:rgba(255,255,255,0.2);">
                            {{ mb_strtoupper(mb_substr($student->first_name, 0, 1)) }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-green-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ $stats['attendance_rate'] }}%</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Attendance</div>
                    <div class="mt-0.5 text-xs text-white/60">{{ $stats['present_days'] }}/{{ $stats['total_days'] }} days</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ $stats['average_score'] }}%</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Average Score</div>
                    <div class="mt-0.5 text-xs text-white/60">{{ $stats['total_subjects'] }} subjects</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ $stats['position'] ? '#'.$stats['position'] : 'N/A' }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Class Position</div>
                    <div class="mt-0.5 text-xs text-white/60">of {{ $stats['total_students'] }} students</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $stats['overdue_homework'] > 0 ? 'from-red-500 to-rose-600' : 'from-violet-500 to-purple-600' }} p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ $stats['pending_homework'] }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Pending HW</div>
                    <div class="mt-0.5 text-xs text-white/60">
                        {{ $stats['overdue_homework'] > 0 ? $stats['overdue_homework'].' overdue' : 'All up to date' }}
                    </div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row: Quick Actions (left) + Grade Distribution (right) --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 items-start">

        {{-- Quick Actions --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="text-base font-bold text-slate-800">Quick Actions</div>
                <div class="mt-0.5 text-xs text-slate-400">Navigate to your portal sections</div>
            </div>
            <div class="divide-y divide-slate-50 p-2">
                @php
                    $actions = [
                        ['route'=>'student.homework',    'label'=>'Homework',    'sub'=>'Check & submit assignments',  'from'=>'from-violet-500', 'to'=>'to-purple-600',  'icon'=>'<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                        ['route'=>'student.results',     'label'=>'Results',     'sub'=>'View your term scores',       'from'=>'from-blue-500',   'to'=>'to-indigo-600',  'icon'=>'<path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
                        ['route'=>'student.attendance',  'label'=>'Attendance',  'sub'=>'View your attendance record', 'from'=>'from-teal-400',   'to'=>'to-emerald-500', 'icon'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>'],
                        ['route'=>'student.performance', 'label'=>'Performance', 'sub'=>'Track your progress',         'from'=>'from-amber-400',  'to'=>'to-orange-500',  'icon'=>'<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>'],
                        ['route'=>'student.exams',       'label'=>'Exams',       'sub'=>'Upcoming CBT exams',          'from'=>'from-rose-400',   'to'=>'to-pink-500',    'icon'=>'<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>'],
                        ['route'=>'student.profile',     'label'=>'My Profile',  'sub'=>'View & update your info',     'from'=>'from-slate-500',  'to'=>'to-slate-700',   'icon'=>'<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
                    ];
                @endphp
                @foreach($actions as $action)
                    <a href="{{ route($action['route']) }}"
                       class="group flex items-center gap-3 rounded-xl px-3 py-3 transition hover:bg-slate-50">
                        <div class="grid h-10 w-10 flex-shrink-0 place-items-center rounded-xl bg-gradient-to-br {{ $action['from'] }} {{ $action['to'] }} text-white shadow-sm transition group-hover:scale-105">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $action['icon'] !!}</svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-bold text-slate-800">{{ $action['label'] }}</div>
                            <div class="text-xs text-slate-400">{{ $action['sub'] }}</div>
                        </div>
                        <svg class="h-4 w-4 flex-shrink-0 text-slate-300 transition group-hover:text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
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
                    <div class="grid grid-cols-6 gap-3">
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
