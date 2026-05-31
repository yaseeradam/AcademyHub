<div class="space-y-6">
    {{-- Dynamic Glassmorphism Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 p-6 shadow-lg border border-slate-800">
        {{-- Absolute Decorative Gradients --}}
        <div class="absolute -right-16 -top-16 h-36 w-36 rounded-full bg-indigo-500/10 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-36 w-36 rounded-full bg-violet-500/10 blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-[10px] font-extrabold text-indigo-400 uppercase tracking-wider border border-indigo-500/20 mb-2">
                    Student Portal
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Computer Based Testing</h1>
                <p class="text-xs text-slate-400 font-medium mt-0.5">Access, take, and view results for all your assigned CBT examinations.</p>
            </div>
            
            {{-- Join Exam Access Panel --}}
            <div x-data="{ code: '' }" class="flex items-center gap-2 self-start md:self-auto w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-initial">
                    <input x-model="code" 
                           type="text" 
                           placeholder="Enter Access Code" 
                           class="w-full sm:w-44 rounded-xl border border-slate-700 bg-slate-900/60 px-4 py-2.5 text-xs text-white placeholder-slate-500 font-bold uppercase tracking-wider transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-slate-950" 
                           @keydown.enter="$wire.enterExamCode(code)" />
                </div>
                <button @click="$wire.enterExamCode(code)" 
                        class="relative overflow-hidden group rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-xs font-extrabold text-white transition-all hover:from-indigo-500 hover:to-violet-500 shadow-md active:scale-95 flex items-center gap-1">
                    <span>Join</span>
                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </div>
        </div>
    </div>

    @if (empty($exams))
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 p-12 text-center bg-white shadow-sm">
            <div class="rounded-full bg-slate-50 p-4 border border-slate-100 mb-4">
                <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">No Exams Available</h3>
            <p class="mt-1 text-xs text-slate-500 max-w-xs leading-normal">You don't have any assigned exams right now. Contact your teacher if you believe this is an error.</p>
        </div>
    @else
        {{-- Premium Card Grid --}}
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($exams as $exam)
                @php
                    $isMarked = $exam['can_view_marks'];
                    $isPending = $exam['score_pending'] ?? false;
                    $isTheoryPending = $exam['status'] === 'completed' && $exam['has_theory'] && $exam['theory_status'] !== 'marked';
                    
                    // Style attributes based on status
                    $cardStyle = 'bg-white border-slate-100 hover:border-indigo-100 hover:shadow-lg';
                    $accentBar = 'bg-slate-200';
                    
                    if ($exam['status'] === 'in_progress') {
                        $cardStyle = 'bg-gradient-to-b from-amber-50/20 to-white border-amber-300 hover:border-amber-400 shadow-md shadow-amber-500/5 ring-1 ring-amber-400/20 hover:shadow-lg hover:shadow-amber-500/10';
                        $accentBar = 'bg-gradient-to-r from-amber-400 to-amber-500';
                    } elseif ($isMarked) {
                        $cardStyle = 'bg-gradient-to-b from-emerald-50/20 to-white border-emerald-300 hover:border-emerald-400 hover:shadow-lg hover:shadow-emerald-500/5';
                        $accentBar = 'bg-gradient-to-r from-emerald-400 to-emerald-500';
                    } elseif ($exam['status'] === 'completed') {
                        $cardStyle = 'bg-slate-50/30 border-slate-200 grayscale-[20%]';
                        $accentBar = 'bg-slate-400';
                    }
                @endphp
                <div class="group relative overflow-hidden rounded-2xl border p-5 transition-all duration-300 flex flex-col justify-between min-h-[220px] {{ $cardStyle }}">
                    
                    {{-- Status Accent Line --}}
                    <div class="absolute left-0 top-0 right-0 h-1 {{ $accentBar }}"></div>

                    {{-- Card Header --}}
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="space-y-1">
                            <span class="inline-block text-[9px] font-extrabold text-indigo-600 bg-indigo-50/80 px-2 py-0.5 rounded-md uppercase tracking-wider">
                                {{ $exam['subject'] }}
                            </span>
                            <h3 class="text-sm font-black text-slate-800 line-clamp-2 leading-snug group-hover:text-indigo-950 transition-colors">
                                {{ $exam['title'] }}
                            </h3>
                        </div>

                        {{-- Premium Badges --}}
                        <div class="flex-shrink-0">
                            @if ($exam['status'] === 'in_progress')
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-500 px-2.5 py-0.5 text-[9px] font-black text-amber-950 shadow-sm border border-amber-400/20 animate-pulse">
                                    <span class="h-1 w-1 rounded-full bg-amber-950"></span>
                                    IN PROGRESS
                                </span>
                            @elseif ($isMarked)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500 px-2.5 py-0.5 text-[9px] font-black text-white shadow-sm border border-emerald-400/20">
                                    ✓ MARKED
                                </span>
                            @elseif ($exam['status'] === 'completed')
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2.5 py-0.5 text-[9px] font-black text-slate-700">
                                    COMPLETED
                                </span>
                            @elseif ($exam['ends_at'] && \Carbon\Carbon::parse($exam['ends_at'])->diffInHours(now()) <= 24)
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-500 px-2.5 py-0.5 text-[9px] font-black text-white shadow-sm border border-rose-400/20 animate-bounce">
                                    ENDING SOON
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Info Metrics --}}
                    <div class="space-y-2 mt-auto">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[11px] font-bold text-slate-500 border-t border-slate-50 pt-3">
                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>{{ $exam['duration'] }} Mins</span>
                            </div>
                            @if ($exam['ends_at'])
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span>Due: {{ \Carbon\Carbon::parse($exam['ends_at'])->format('M d, h:i A') }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Score display --}}
                        @if ($isMarked)
                            @php
                                $pct = (int) $exam['percent'];
                                $badgeColor = $pct >= 70 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($pct >= 50 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');
                            @endphp
                            <div class="flex items-center justify-between rounded-xl px-3.5 py-2.5 border {{ $badgeColor }} bg-white shadow-sm mt-2">
                                <span class="text-[10px] font-black uppercase tracking-wider">Exam Grade</span>
                                <span class="text-sm font-black flex items-baseline gap-0.5">
                                    {{ $exam['score'] }}<span class="text-[10px] font-bold text-slate-400">/{{ $exam['max_score'] }}</span>
                                    <span class="text-xs font-black ml-1.5">({{ $pct }}%)</span>
                                </span>
                            </div>
                        @elseif ($isTheoryPending)
                            <div class="flex items-center gap-1.5 rounded-xl bg-amber-50 border border-amber-200 px-3 py-2 text-[10px] font-extrabold text-amber-700 mt-2">
                                <svg class="h-4 w-4 animate-spin text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Awaiting Theory Marking
                            </div>
                        @elseif ($isPending)
                            <div class="flex items-center gap-1.5 rounded-xl bg-violet-50 border border-violet-200 px-3 py-2 text-[10px] font-extrabold text-violet-700 mt-2">
                                <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Results Pending Release
                            </div>
                        @endif
                    </div>

                    {{-- Actions block --}}
                    <div class="mt-4 flex flex-col gap-2">
                        @if ($exam['status'] === 'completed')
                            @if ($isMarked && $exam['attempt_id'])
                                @php
                                    $isActiveBtn = $viewingAttempt && $viewingAttempt->id === $exam['attempt_id'];
                                @endphp
                                <button
                                    wire:click="viewResult({{ $exam['attempt_id'] }})"
                                    class="w-full rounded-xl py-2.5 text-xs font-extrabold transition-all duration-200 flex items-center justify-center gap-1.5 shadow-sm active:scale-95
                                    {{ $isActiveBtn ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200/50' }}">
                                    <span>{{ $isActiveBtn ? 'Hide Score Analysis' : 'View Score Analysis' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </button>
                            @else
                                <button disabled class="w-full rounded-xl bg-slate-100 py-2.5 text-xs font-bold text-slate-400 cursor-not-allowed border border-slate-200/50 flex items-center justify-center gap-1.5">
                                    <span>Exam Completed</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endif
                        @else
                            @php
                                $btnClass = $exam['status'] === 'in_progress' ? 'from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 shadow-amber-500/10' : 'from-slate-900 to-indigo-950 hover:from-slate-800 hover:to-indigo-900 shadow-slate-950/10';
                            @endphp
                            <a href="{{ route('cbt.student', ['code' => $exam['access_code']]) }}"
                               class="relative overflow-hidden group w-full text-center rounded-xl bg-gradient-to-r py-2.5 text-xs font-black text-white transition-all shadow-md hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-1.5 {{ $btnClass }}">
                                <span>{{ $exam['status'] === 'in_progress' ? 'Resume Examination' : 'Start Examination' }}</span>
                                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
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
            $gradeColor = $pct >= 70 ? 'text-emerald-600' : ($pct >= 50 ? 'text-amber-600' : 'text-rose-600');
        @endphp
        <div class="rounded-2xl border border-indigo-100 bg-white shadow-xl overflow-hidden mt-8 transition-all animate-fadeIn">
            {{-- Questions Section Header --}}
            <div class="bg-gradient-to-r from-slate-950 to-indigo-950 px-6 py-4 shadow-sm relative overflow-hidden">
                <div class="absolute -right-8 -top-8 h-20 w-20 rounded-full bg-indigo-500/10 blur-xl"></div>
                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-500/20 px-2 py-0.5 text-[9px] font-black text-indigo-300 uppercase tracking-wider mb-1 border border-indigo-500/30">
                            Performance Analysis
                        </span>
                        <h2 class="text-base font-black text-white tracking-tight">Exam Questions & Answers Breakdown</h2>
                    </div>
                    <div class="flex items-center gap-2 bg-slate-900/60 border border-slate-800 rounded-xl px-4 py-2 self-start sm:self-auto">
                        <span class="text-xs font-bold text-slate-400">Total Score:</span>
                        <span class="text-sm font-black text-white">
                            {{ $earnedMarks }}<span class="text-xs text-slate-500">/{{ $totalMarks }}</span>
                            <span class="text-xs font-black text-indigo-400 ml-1">({{ $pct }}%)</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Questions --}}
            <div class="divide-y divide-slate-100">
                @foreach ($vQuestions as $idx => $q)
                    @php
                        $answer     = $vAnswers->get($q->id);
                        $qType      = $q->type ?? 'mcq';
                        $awarded    = $qType === 'theory' ? (int) ($answer?->awarded_marks ?? 0) : ($answer?->is_correct ? (int) $q->marks : 0);
                        $maxMark    = (int) $q->marks;
                        $isCorrect  = $qType === 'mcq' ? (bool) ($answer?->is_correct) : null;
                        
                        // Premium row bg and border styles
                        $rowStyle   = 'bg-white border-l-4 border-l-slate-300';
                        if ($qType === 'mcq') {
                            $rowStyle = $isCorrect ? 'bg-emerald-50/20 border-l-4 border-l-emerald-500' : 'bg-rose-50/20 border-l-4 border-l-rose-500';
                        } else {
                            $rowStyle = $awarded >= $maxMark 
                                ? 'bg-emerald-50/20 border-l-4 border-l-emerald-500' 
                                : ($awarded > 0 ? 'bg-amber-50/20 border-l-4 border-l-amber-500' : 'bg-rose-50/20 border-l-4 border-l-rose-500');
                        }
                    @endphp
                    <div class="px-6 py-5 transition-colors hover:bg-slate-50/30 {{ $rowStyle }}">
                        <div class="flex flex-col md:flex-row items-start justify-between gap-4">
                            <div class="flex-1 w-full">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Question {{ $idx + 1 }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-wider {{ $qType === 'theory' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                        {{ $qType }}
                                    </span>
                                </div>
                                <p class="text-sm font-extrabold text-slate-800 mb-3 leading-relaxed">{{ $q->prompt }}</p>

                                @if ($qType === 'mcq')
                                    @php
                                        $selectedOption = $answer?->option_id
                                            ? $q->options->firstWhere('id', $answer->option_id)
                                            : null;
                                        $correctOption = $q->options->firstWhere('is_correct', true);
                                    @endphp
                                    <div class="space-y-2 max-w-2xl">
                                        @foreach ($q->options as $opt)
                                            @php
                                                $isSelected = $answer?->option_id === $opt->id;
                                                $isRight    = (bool) $opt->is_correct;
                                                
                                                // Dynamic colors for options
                                                $optStyle = 'bg-slate-50 border-slate-200 text-slate-600';
                                                $optBadge = null;
                                                if ($isRight) {
                                                    $optStyle = 'bg-emerald-50 border-emerald-300 font-extrabold text-emerald-800 ring-1 ring-emerald-500/10';
                                                    $optBadge = '✓ Correct Answer';
                                                    if ($isSelected) {
                                                        $optBadge = '✓ Correct & Chosen';
                                                    }
                                                } elseif ($isSelected && !$isRight) {
                                                    $optStyle = 'bg-rose-50 border-rose-300 font-extrabold text-rose-800 ring-1 ring-rose-500/10';
                                                    $optBadge = '✗ Your Choice (Incorrect)';
                                                }
                                            @endphp
                                            <div class="flex items-center justify-between rounded-xl border px-4 py-2.5 text-xs transition-all {{ $optStyle }}">
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-lg bg-white border border-inherit text-[10px] font-black text-inherit uppercase shadow-sm">
                                                        {{ chr(65 + $loop->index) }}
                                                    </span>
                                                    <span class="font-bold">{{ $opt->label }}</span>
                                                </div>
                                                @if ($optBadge)
                                                    <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-white/60 shadow-sm border border-inherit">
                                                        {{ $optBadge }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Theory Answer Display --}}
                                    <div class="space-y-2 max-w-2xl">
                                        <span class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider">Your Submitted Answer:</span>
                                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-xs font-bold text-slate-700 whitespace-pre-wrap leading-relaxed shadow-inner">
                                            {{ trim((string) ($answer?->text_answer ?? '')) ?: 'No answer was submitted.' }}
                                        </div>
                                        @if ($answer?->teacher_comment)
                                            <div class="flex items-start gap-2 rounded-xl bg-indigo-50/50 border border-indigo-100 p-3 mt-2">
                                                <span class="text-xs">💬</span>
                                                <div class="text-xs text-indigo-900 leading-relaxed font-bold">
                                                    <span class="font-black text-indigo-950">Teacher's Comment:</span> {{ $answer->teacher_comment }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Mark Score Badge --}}
                            <div class="flex-shrink-0 self-end md:self-start text-center min-w-[70px]">
                                <div class="rounded-2xl border px-3 py-2.5 text-center shadow-sm bg-white
                                    {{ $awarded === $maxMark ? 'border-emerald-300 text-emerald-800 ring-1 ring-emerald-500/5' : ($awarded > 0 ? 'border-amber-300 text-amber-800 ring-1 ring-amber-500/5' : 'border-rose-300 text-rose-800 ring-1 ring-rose-500/5') }}">
                                    <div class="text-xl font-black tracking-tight leading-none">{{ $awarded }}</div>
                                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-wider mt-1">/ {{ $maxMark }} Marks</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Footer Summary --}}
            <div class="flex items-center justify-between bg-slate-50 border-t border-slate-100 px-6 py-5">
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Overall Performance</span>
                    <h4 class="text-xs font-bold text-slate-700 mt-0.5">Ranked in Class: <span class="text-slate-900 font-extrabold">{{ $grade }}</span></h4>
                </div>
                <div class="flex items-baseline gap-1 text-slate-800">
                    <span class="text-xs font-bold">Total Score:</span>
                    <span class="text-lg font-black {{ $gradeColor }}">{{ $earnedMarks }}</span>
                    <span class="text-xs font-extrabold text-slate-400">/ {{ $totalMarks }}</span>
                    <span class="text-xs font-black text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded ml-2">({{ $pct }}%)</span>
                </div>
            </div>
        </div>
    @endif
</div>
