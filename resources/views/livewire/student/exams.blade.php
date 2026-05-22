    {{-- Page Header --}}
    <div class="rounded-xl bg-slate-900 px-4 py-3 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-sm sm:text-base font-bold text-white">My Exams</h1>
                <p class="mt-0.5 text-[11px] text-slate-400">View and take your assigned CBT exams</p>
            </div>
            <div x-data="{ code: '' }" class="flex items-center gap-1.5 self-end sm:self-auto">
                <input x-model="code" type="text" placeholder="Access Code" class="rounded-lg border-0 bg-slate-800 px-2.5 py-1.5 text-xs text-white placeholder-slate-400 font-mono uppercase w-32 focus:ring-1 focus:ring-indigo-500" @keydown.enter="$wire.enterExamCode(code)" />
                <button @click="$wire.enterExamCode(code)" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-700 transition shadow-sm">Join</button>
            </div>
        </div>
    </div>

    @if (empty($exams))
        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-xs text-slate-500 bg-slate-50">
            No exams currently available.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($exams as $exam)
                @php
                    $isMarked = $exam['can_view_marks'];
                    $isPending = $exam['score_pending'] ?? false;
                    $isTheoryPending = $exam['status'] === 'completed' && $exam['has_theory'] && $exam['theory_status'] !== 'marked';
                @endphp
                <div class="relative overflow-hidden rounded-xl border
                    {{ $exam['status'] === 'in_progress' ? 'border-amber-400 bg-amber-50/40 shadow-sm ring-2 ring-amber-400/20' : ($isMarked ? 'border-emerald-400 bg-emerald-50/40' : 'border-slate-100 bg-white') }}
                    p-4 transition-all hover:shadow-md">

                    {{-- Status badge --}}
                    @if ($exam['status'] === 'in_progress')
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-amber-400 px-2.5 py-0.5 text-[9px] font-bold text-amber-900 shadow-sm">IN PROGRESS</div>
                    @elseif ($isMarked)
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-emerald-500 px-2.5 py-0.5 text-[9px] font-bold text-white shadow-sm">MARKED ✓</div>
                    @elseif ($exam['status'] === 'completed')
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-slate-200 px-2.5 py-0.5 text-[9px] font-bold text-slate-700">COMPLETED</div>
                    @elseif ($exam['ends_at'] && \Carbon\Carbon::parse($exam['ends_at'])->diffInHours(now()) <= 24)
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-red-100 px-2.5 py-0.5 text-[9px] font-bold text-red-800">ENDING SOON</div>
                    @endif

                    <div class="mb-2.5 mt-1">
                        <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">{{ $exam['subject'] }}</div>
                        <h3 class="mt-0.5 text-sm font-bold text-slate-900 line-clamp-2 leading-tight">{{ $exam['title'] }}</h3>
                    </div>

                    <div class="space-y-1 text-xs text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ $exam['duration'] }} minutes</span>
                        </div>
                        @if ($exam['ends_at'])
                            <div class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>Due: {{ \Carbon\Carbon::parse($exam['ends_at'])->format('M d, Y h:i A') }}</span>
                            </div>
                        @endif

                        {{-- Score display --}}
                        @if ($isMarked)
                            @php
                                $pct = (int) $exam['percent'];
                                $scoreColor = $pct >= 70 ? 'text-emerald-700' : ($pct >= 50 ? 'text-amber-600' : 'text-red-600');
                            @endphp
                            <div class="mt-3 flex items-center justify-between rounded-lg bg-white px-3 py-2 ring-1 ring-emerald-200">
                                <span class="text-xs font-semibold text-gray-500">Score</span>
                                <span class="text-base font-extrabold {{ $scoreColor }}">
                                    {{ $exam['score'] }}/{{ $exam['max_score'] }}
                                    <span class="text-xs font-semibold">({{ $pct }}%)</span>
                                </span>
                            </div>
                        @elseif ($isTheoryPending)
                            <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Awaiting theory marking
                            </div>
                        @elseif ($isPending)
                            <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Results pending release
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 flex flex-col gap-2">
                        @if ($exam['status'] === 'completed')
                            @if ($isMarked && $exam['attempt_id'])
                                <button
                                    wire:click="viewResult({{ $exam['attempt_id'] }})"
                                    class="w-full rounded-lg {{ $viewingAttempt && $viewingAttempt->id === $exam['attempt_id'] ? 'bg-violet-600 text-white' : 'bg-violet-100 text-violet-700 hover:bg-violet-200' }} py-2.5 text-sm font-semibold transition">
                                    {{ $viewingAttempt && $viewingAttempt->id === $exam['attempt_id'] ? 'Hide Marks' : 'View Marks' }}
                                </button>
                            @else
                                <button disabled class="w-full rounded-lg bg-gray-100 py-2.5 text-sm font-semibold text-gray-400">Exam completed</button>
                            @endif
                        @else
                            <a href="{{ route('cbt.student', ['code' => $exam['access_code']]) }}"
                               class="block w-full text-center rounded-lg {{ $exam['status'] === 'in_progress' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-gray-900 hover:bg-gray-800' }} py-2.5 text-sm font-semibold text-white transition">
                                {{ $exam['status'] === 'in_progress' ? 'Resume Exam' : 'Start Exam' }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Question-by-question breakdown --}}
    @if ($viewingAttempt)
        @php
            $vExam      = $viewingAttempt->exam;
            $vAnswers   = $viewingAttempt->answers->keyBy('question_id');
            $vQuestions = $vExam->questions->sortBy('position')->values();
            $totalMarks = $vQuestions->sum('marks');
            $earnedMarks = (int) $viewingAttempt->score;
            $pct = (int) $viewingAttempt->percent;
            $grade = $pct >= 70 ? 'Excellent' : ($pct >= 50 ? 'Good' : 'Needs Work');
            $gradeColor = $pct >= 70 ? 'text-emerald-600' : ($pct >= 50 ? 'text-amber-600' : 'text-red-600');
        @endphp
        <div class="rounded-2xl border border-violet-200 bg-white shadow-lg overflow-hidden">
                {-- Page Header --}
    <div class="rounded-xl bg-slate-900 px-5 py-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-base font-bold text-white">Exams</h1>
            
        </div>
    </div>

    {{-- Questions --}}
            <div class="divide-y divide-gray-100">
                @foreach ($vQuestions as $idx => $q)
                    @php
                        $answer     = $vAnswers->get($q->id);
                        $qType      = $q->type ?? 'mcq';
                        $awarded    = $qType === 'theory' ? (int) ($answer?->awarded_marks ?? 0) : ($answer?->is_correct ? (int) $q->marks : 0);
                        $maxMark    = (int) $q->marks;
                        $isCorrect  = $qType === 'mcq' ? (bool) ($answer?->is_correct) : null;
                        $rowBg      = $qType === 'mcq'
                            ? ($isCorrect ? 'bg-emerald-50' : 'bg-red-50')
                            : ($awarded >= $maxMark ? 'bg-emerald-50' : ($awarded > 0 ? 'bg-amber-50' : 'bg-red-50'));
                    @endphp
                    <div class="px-6 py-4 {{ $rowBg }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-bold text-gray-500">Q{{ $idx + 1 }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $qType === 'theory' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ $qType }}</span>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">{{ $q->prompt }}</p>

                                @if ($qType === 'mcq')
                                    @php
                                        $selectedOption = $answer?->option_id
                                            ? $q->options->firstWhere('id', $answer->option_id)
                                            : null;
                                        $correctOption = $q->options->firstWhere('is_correct', true);
                                    @endphp
                                    <div class="mt-2 space-y-1">
                                        @foreach ($q->options as $opt)
                                            @php
                                                $isSelected = $answer?->option_id === $opt->id;
                                                $isRight    = (bool) $opt->is_correct;
                                            @endphp
                                            <div class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs
                                                {{ $isRight ? 'bg-emerald-100 font-semibold text-emerald-800' : ($isSelected && !$isRight ? 'bg-red-100 font-semibold text-red-800' : 'text-gray-600') }}">
                                                <span class="font-bold">{{ chr(65 + $loop->index) }}.</span>
                                                <span>{{ $opt->label }}</span>
                                                @if ($isSelected && $isRight) <span class="ml-auto">✓ Your answer</span>
                                                @elseif ($isSelected && !$isRight) <span class="ml-auto">✗ Your answer</span>
                                                @elseif ($isRight) <span class="ml-auto">✓ Correct</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Theory --}}
                                    <div class="mt-2 rounded-lg bg-white px-3 py-2 text-xs text-gray-700 ring-1 ring-gray-200">
                                        {{ trim((string) ($answer?->text_answer ?? '')) ?: 'No answer submitted.' }}
                                    </div>
                                    @if ($answer?->teacher_comment)
                                        <div class="mt-1 text-xs text-violet-700 font-semibold">Teacher: {{ $answer->teacher_comment }}</div>
                                    @endif
                                @endif
                            </div>

                            {{-- Mark badge --}}
                            <div class="flex-shrink-0 text-center min-w-[52px]">
                                <div class="rounded-xl px-3 py-2 text-center
                                    {{ $awarded === $maxMark ? 'bg-emerald-100 text-emerald-800' : ($awarded > 0 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                    <div class="text-lg font-extrabold">{{ $awarded }}</div>
                                    <div class="text-[10px] font-semibold">/{{ $maxMark }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Footer total --}}
            <div class="flex items-center justify-between bg-gray-50 px-6 py-4 border-t border-gray-200">
                <span class="text-sm font-bold text-gray-700">Total Score</span>
                <span class="text-xl font-extrabold {{ $gradeColor }}">{{ $earnedMarks }} / {{ $totalMarks }} ({{ $pct }}%)</span>
            </div>
        </div>
    @endif
</div>
