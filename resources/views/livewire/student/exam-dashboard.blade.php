<div class="space-y-8">
    {{-- Header --}}
    <div class="rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-700 p-6 shadow-xl text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">My Exams</h1>
                <p class="mt-1 text-sm text-violet-200">Live exams assigned to your class.</p>
            </div>
            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white/20 shadow-inner">
                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Live Exams --}}
    <div>
        <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
            <span class="inline-flex h-3 w-3 rounded-full bg-green-500 animate-pulse"></span>
            Live Exams
        </h2>

        @if ($this->liveExams->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-12 text-center">
                <div class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 mx-auto">
                    <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="mt-4 text-sm font-semibold text-slate-600">No live exams right now</div>
                <div class="mt-1 text-xs text-slate-500">When your teacher starts an exam, it will appear here.</div>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->liveExams as $item)
                    @php
                        $exam = $item['exam'];
                        $attempt = $item['attempt'];
                        $state = $item['state'];
                        $urgency = $item['urgency'];
                    @endphp

                    <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-md ring-1 ring-slate-200 flex flex-col @if($urgency) ring-2 ring-amber-400 @endif">
                        {{-- Urgency Banner --}}
                        @if($urgency)
                            <div class="absolute top-0 inset-x-0 bg-amber-400 py-1 px-3 text-center text-xs font-bold text-amber-900">
                                ⏰ {{ $urgency }}
                            </div>
                            <div class="h-5"></div>
                        @endif

                        {{-- Status badge --}}
                        <div class="flex items-center justify-between mb-3">
                            @if($state === 'completed')
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-700">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Completed
                                </span>
                            @elseif($state === 'in_progress')
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">
                                    <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    In Progress
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2.5 py-1 text-xs font-bold text-violet-700">
                                    <span class="h-2 w-2 rounded-full bg-violet-500"></span>
                                    Pending
                                </span>
                            @endif

                            <div class="text-xs text-slate-500">{{ $exam->duration_minutes }} min</div>
                        </div>

                        <div class="flex-1">
                            <h3 class="font-bold text-slate-900 leading-tight">{{ $exam->title }}</h3>
                            <div class="mt-1 text-xs text-slate-500">{{ $exam->subject?->name }} — {{ $exam->schoolClass?->name }}</div>

                            @if($exam->ends_at)
                                <div class="mt-2 flex items-center gap-1 text-xs text-slate-500">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Ends {{ $exam->ends_at->format('g:i A') }}
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-100">
                            @if($state === 'completed')
                                <a href="{{ route('cbt.student.take', ['attempt' => $attempt, 'code' => $exam->access_code]) }}"
                                   class="block text-center w-full rounded-xl bg-green-50 px-4 py-2.5 text-sm font-bold text-green-700 hover:bg-green-100 transition-colors">
                                    View Submission
                                </a>
                            @elseif($state === 'in_progress')
                                <a href="{{ route('cbt.student.take', ['attempt' => $attempt, 'code' => $exam->access_code]) }}"
                                   class="block text-center w-full rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600 transition-colors shadow-md">
                                    Continue Exam →
                                </a>
                            @else
                                <a href="{{ route('cbt.student') }}?code={{ $exam->access_code }}"
                                   class="block text-center w-full rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-700 transition-colors shadow-md">
                                    Start Exam →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Completed Exams Results --}}
    @if($this->completedExams->isNotEmpty())
        <div>
            <h2 class="text-lg font-bold text-slate-900 mb-4">Past Submissions</h2>
            <div class="overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-slate-200">
                <div class="divide-y divide-slate-100">
                    @foreach($this->completedExams as $attempt)
                        @php
                            $exam = $attempt->exam;
                            $showScore = $exam && $exam->show_score && $exam->results_released_at;
                        @endphp
                        <div class="flex items-center justify-between px-5 py-4">
                            <div>
                                <div class="font-semibold text-slate-900 text-sm">{{ $exam?->title ?? 'Exam' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $exam?->subject?->name }} • {{ $attempt->submitted_at?->format('M j, Y g:i A') }}</div>
                            </div>
                            <div class="text-right">
                                @if($showScore)
                                    <div class="text-lg font-black text-emerald-600">{{ (int) $attempt->score }}/{{ (int) $attempt->max_score }}</div>
                                    <div class="text-xs text-slate-500">{{ (int) $attempt->percent }}%</div>
                                @else
                                    <div class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Pending Release</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
