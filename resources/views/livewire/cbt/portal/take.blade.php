@php
    $submitted = (bool) $attempt->submitted_at;
    $questions = $exam?->questions?->values() ?? collect();
    $totalQuestions = $questions->count();
    $hasTheory = $questions->contains(fn ($q) => $q->type === 'theory');
@endphp

<div
    class="cbt-take-root min-h-screen bg-gradient-to-br from-amber-50 via-white to-teal-50 text-slate-900"
    @if(! $submitted) wire:poll.30s="heartbeatTick" @endif
    x-data="{
        remaining: 0,
        timerDisplay: '--:--',
        canSubmit: false,
        _interval: null,
        localAnswers: @js($this->answers),
        localTheoryAnswers: @js($this->theoryAnswers),
        currentIndex: @entangle('currentIndex'),
        questions: @js($questions->map(fn($q) => [
            'id' => $q->id,
            'type' => $q->type,
            'options' => $q->options->map(fn($o) => ['id' => $o->id])->values()->all(),
        ])->values()->all()),
        _cfg: {
            startedAt: @js($this->startedAtIso ?? null),
            dur: @js($this->durationSeconds ?? 0),
            submitted: @js($submitted),
            minSub: @js($submitted ? 0 : $this->minSubmitSeconds()),
        },

        get answeredCount() {
            return this.questions.filter(q => {
                if (q.type === 'theory') {
                    const val = this.localTheoryAnswers[q.id];
                    return val !== undefined && val !== null && String(val).trim() !== '';
                }
                const val = this.localAnswers[q.id];
                return val !== undefined && val !== null && Number(val) > 0;
            }).length;
        },

        init() {
            if (this._cfg.submitted) {
                this.remaining = 0;
                this.timerDisplay = '00:00';
                return;
            }
            this._tick();
            this._interval = setInterval(() => this._tick(), 1000);
        },

        destroy() {
            if (this._interval) clearInterval(this._interval);
        },

        _tick() {
            if (!this._cfg.startedAt || !this._cfg.dur) {
                this.remaining = 0;
                this.timerDisplay = '00:00';
                return;
            }
            const started = new Date(this._cfg.startedAt).getTime();
            const elapsed = Math.floor((Date.now() - started) / 1000);
            this.remaining = Math.max(0, this._cfg.dur - elapsed);

            const mm = String(Math.floor(this.remaining / 60)).padStart(2, '0');
            const ss = String(this.remaining % 60).padStart(2, '0');
            this.timerDisplay = mm + ':' + ss;

            this.canSubmit = elapsed >= this._cfg.minSub || this.remaining <= 0;

            if (this.remaining <= 0 && this._interval) {
                clearInterval(this._interval);
                this._interval = null;
                this.$wire.submitExam();
            }
        },

        handleKeyPress(event) {
            if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return;
            if (event.ctrlKey || event.metaKey || event.altKey) return;

            const key = event.key.toLowerCase();
            if (key === 'arrowright' || key === 'arrowdown') {
                event.preventDefault();
                this.currentIndex = Math.min(this.questions.length - 1, this.currentIndex + 1);
            } else if (key === 'arrowleft' || key === 'arrowup') {
                event.preventDefault();
                this.currentIndex = Math.max(0, this.currentIndex - 1);
            } else if (['a', 'b', 'c', 'd'].includes(key)) {
                event.preventDefault();
                const optIdx = key.charCodeAt(0) - 97;
                const currentQ = this.questions[this.currentIndex];
                if (currentQ && currentQ.type !== 'theory' && currentQ.options && currentQ.options[optIdx]) {
                    const optId = currentQ.options[optIdx].id;
                    this.localAnswers[currentQ.id] = optId;
                    this.$wire.selectOption(currentQ.id, optId);
                }
            }
        }
    }"
    @keydown.window="handleKeyPress($event)"
>
    <div class="flex flex-col">
        <div class="mx-auto w-full max-w-7xl px-6 pt-6">
            <div class="rounded-2xl bg-white/80 p-4 shadow-md ring-1 ring-slate-100 backdrop-blur">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    @if ($student->passport_photo_url)
                        <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name ?? 'Student' }}" class="h-24 w-24 rounded-full object-cover shadow-lg ring-4 ring-white" />
                    @else
                        <div class="grid h-24 w-24 place-items-center rounded-full bg-amber-100 shadow-lg ring-4 ring-white">
                            <span class="text-3xl font-bold text-amber-700">{{ mb_substr($student->first_name ?? 'S', 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">CBT Session</div>
                        <h1 class="cbt-title text-2xl font-bold text-slate-900">{{ $exam->title }}</h1>
                        <div class="mt-1 text-sm text-slate-600">
                            {{ $student->full_name ?? ($student->first_name.' '.$student->last_name) }}
                            <span class="mx-1 text-slate-300">|</span>
                            <span class="font-mono text-xs text-slate-500">#{{ $student->admission_number }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Timer: driven by JS, zero server calls --}}
                    <div class="rounded-xl px-3.5 py-2 shadow-sm" :class="remaining <= 60 ? 'bg-rose-100' : 'bg-emerald-100'">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Time Left</div>
                        <div class="mt-1 font-mono text-xl font-bold" :class="remaining <= 60 ? 'text-rose-700' : 'text-emerald-700'" x-text="timerDisplay">
                            --:--
                        </div>
                    </div>
                    <div class="rounded-xl bg-slate-100 px-3.5 py-2 shadow-sm">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Progress</div>
                        <div class="mt-1 text-xl font-bold text-slate-800"><span x-text="answeredCount"></span>/{{ $totalQuestions }}</div>
                        <div class="mt-2 h-1.5 w-24 overflow-hidden rounded-full bg-white">
                            <div class="h-full rounded-full bg-slate-700 transition-all duration-300" :style="'width: ' + (questions.length > 0 ? (answeredCount / questions.length) * 100 : 0) + '%'"></div>
                        </div>
                    </div>
                    <div class="rounded-xl bg-amber-100 px-3.5 py-2 shadow-sm">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Question</div>
                        <div class="mt-1 text-xl font-bold text-amber-800"><span x-text="currentIndex + 1"></span> of {{ $totalQuestions }}</div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="mx-auto w-full max-w-7xl px-6 pb-6 pt-5">
        @if ($submitted)
            @php
                $scorePercent   = (int) $attempt->percent;
                $scoreInt       = (int) $attempt->score;
                $maxInt         = (int) $attempt->max_score;
                $showScore      = $exam->show_score && $exam->results_released_at && !$hasTheory;
                $pendingRelease = $exam->show_score && (!$exam->results_released_at || $hasTheory);
                // Score colour thresholds
                $ringColor = $scorePercent >= 70 ? '#10b981' : ($scorePercent >= 50 ? '#f59e0b' : '#ef4444');
                $ringBg    = $scorePercent >= 70 ? '#d1fae5' : ($scorePercent >= 50 ? '#fef3c7' : '#fee2e2');
                $grade     = $scorePercent >= 70 ? 'Excellent' : ($scorePercent >= 50 ? 'Good' : 'Needs Work');
                // SVG circle maths (r=54, circumference=339.3)
                $circ = 339.3;
                $dash = round($circ * $scorePercent / 100, 1);
            @endphp

            {{-- Full-page submitted state --}}
            <div class="flex min-h-[70vh] flex-col items-center justify-center py-10"
                 x-data="{ visible: false }" x-init="setTimeout(() => visible = true, 80)">

                {{-- Card --}}
                <div class="w-full max-w-2xl"
                     x-show="visible"
                     x-transition:enter="transition duration-500 ease-out"
                     x-transition:enter-start="opacity-0 translate-y-8"
                     x-transition:enter-end="opacity-100 translate-y-0">

                    {{-- Hero banner --}}
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500 px-8 py-10 text-center shadow-2xl">
                        {{-- Decorative blobs --}}
                        <div class="pointer-events-none absolute -left-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                        <div class="pointer-events-none absolute -bottom-10 -right-10 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>

                        {{-- Animated check icon --}}
                        <div class="relative mx-auto mb-5 flex h-24 w-24 items-center justify-center"
                             x-data="{ done: false }" x-init="setTimeout(() => done = true, 300)">
                            <svg class="absolute inset-0 h-24 w-24 -rotate-90" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="8"/>
                                <circle cx="60" cy="60" r="54" fill="none" stroke="white" stroke-width="8"
                                        stroke-linecap="round"
                                        stroke-dasharray="339.3"
                                        :stroke-dashoffset="done ? 0 : 339.3"
                                        style="transition: stroke-dashoffset 1.2s cubic-bezier(0.4,0,0.2,1)"/>
                            </svg>
                            <div class="relative flex h-16 w-16 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm"
                                 :class="done ? 'scale-100' : 'scale-0'"
                                 style="transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1) 0.6s">
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>

                        <h2 class="text-3xl font-extrabold tracking-tight text-white">Exam Submitted!</h2>
                        <p class="mt-2 text-base font-medium text-white/80">{{ $exam->title }}</p>
                        <p class="mt-1 text-sm text-white/60">
                            {{ $student->full_name ?? trim($student->first_name.' '.$student->last_name) }}
                            &nbsp;&bull;&nbsp;
                            <span class="font-mono">{{ $student->admission_number }}</span>
                        </p>
                    </div>

                    {{-- Stats row --}}
                    <div class="mt-4 grid grid-cols-3 gap-4">
                        {{-- Questions answered --}}
                        <div class="flex flex-col items-center justify-center rounded-2xl bg-white px-4 py-5 shadow-md ring-1 ring-slate-100">
                            <div class="text-3xl font-extrabold text-slate-800">{{ $totalQuestions }}</div>
                            <div class="mt-1 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Questions</div>
                        </div>
                        {{-- Submitted time --}}
                        <div class="flex flex-col items-center justify-center rounded-2xl bg-white px-4 py-5 shadow-md ring-1 ring-slate-100">
                            <div class="text-2xl font-extrabold text-slate-800">{{ $attempt->submitted_at?->format('g:i') }}<span class="text-base font-semibold text-slate-400"> {{ $attempt->submitted_at?->format('A') }}</span></div>
                            <div class="mt-1 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $attempt->submitted_at?->format('M j, Y') }}</div>
                        </div>
                        {{-- Duration used --}}
                        @php
                            $usedSeconds = $attempt->started_at && $attempt->submitted_at
                                ? (int) $attempt->started_at->diffInSeconds($attempt->submitted_at)
                                : 0;
                            $usedMin = floor($usedSeconds / 60);
                            $usedSec = $usedSeconds % 60;
                        @endphp
                        <div class="flex flex-col items-center justify-center rounded-2xl bg-white px-4 py-5 shadow-md ring-1 ring-slate-100">
                            <div class="text-2xl font-extrabold text-slate-800">{{ $usedMin }}<span class="text-base font-semibold text-slate-400">m {{ $usedSec }}s</span></div>
                            <div class="mt-1 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Time Used</div>
                        </div>
                    </div>

                    {{-- Score card --}}
                    <div class="mt-4 rounded-2xl bg-white p-6 shadow-md ring-1 ring-slate-100">
                        @if ($showScore)
                            <div class="flex flex-col items-center gap-6 sm:flex-row">
                                {{-- SVG score ring --}}
                                <div class="relative flex-shrink-0">
                                    <svg class="-rotate-90" width="140" height="140" viewBox="0 0 140 140">
                                        <circle cx="70" cy="70" r="54" fill="none" stroke="{{ $ringBg }}" stroke-width="12"/>
                                        <circle cx="70" cy="70" r="54" fill="none" stroke="{{ $ringColor }}" stroke-width="12"
                                                stroke-linecap="round"
                                                stroke-dasharray="{{ $circ }}"
                                                stroke-dashoffset="{{ $circ - $dash }}"
                                                style="transition: stroke-dashoffset 1.4s cubic-bezier(0.4,0,0.2,1)"/>
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-3xl font-extrabold" style="color:{{ $ringColor }}">{{ $scorePercent }}%</span>
                                        <span class="text-xs font-bold text-slate-500">{{ $grade }}</span>
                                    </div>
                                </div>
                                {{-- Score details --}}
                                <div class="flex-1 text-center sm:text-left">
                                    <div class="text-sm font-semibold uppercase tracking-wide text-slate-500">Your Score</div>
                                    <div class="mt-1 text-5xl font-extrabold text-slate-900">
                                        {{ $scoreInt }}<span class="text-2xl font-semibold text-slate-400">/{{ $maxInt }}</span>
                                    </div>
                                    @if ($hasTheory)
                                        <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/><path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                            Theory questions will be marked separately
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif ($pendingRelease)
                            <div class="flex items-center gap-5">
                                <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-amber-100">
                                    <svg class="h-8 w-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-slate-800">Results Pending</div>
                                    @if ($hasTheory)
                                        <div class="mt-0.5 text-sm text-slate-500">This exam has theory questions that require manual marking. Your teacher will release results after grading.</div>
                                    @else
                                        <div class="mt-0.5 text-sm text-slate-500">Your teacher will release scores shortly. Check back later.</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-5">
                                <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-sky-100">
                                    <svg class="h-8 w-8 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-slate-800">Answers Recorded</div>
                                    <div class="mt-0.5 text-sm text-slate-500">Your submission is safe. Results will be announced by your teacher.</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- CTA --}}
                    <div class="mt-6 flex justify-center">
                        <a href="{{ route('cbt.student', ['code' => $examCode]) }}"
                           class="inline-flex items-center gap-3 rounded-2xl bg-slate-900 px-10 py-4 text-base font-bold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-800 hover:shadow-xl">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Exams
                        </a>
                    </div>

                </div>
            </div>

        @else
            <!-- Question Map Toggle (Mobile Only) -->
            <button @click="$dispatch('toggle-question-map')" class="mb-4 flex w-full items-center justify-between rounded-xl bg-white p-3 shadow-md ring-1 ring-slate-100 lg:hidden">
                <span class="text-sm font-bold text-slate-900">Question Map</span>
                <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="grid gap-5 lg:grid-cols-[280px,1fr]">
                <div class="hidden space-y-5 lg:block" x-data="{ open: false }" @toggle-question-map.window="open = !open" :class="{ '!block fixed inset-0 z-50 bg-black/50 p-4': open }" @click.self="open = false">
                    <div class="rounded-2xl bg-white p-4 shadow-md ring-1 ring-slate-100" :class="{ 'max-w-sm mx-auto': open }" @click.stop>
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-900">Question Map</h3>
                            <span class="text-xs font-semibold text-slate-500">Tap to jump</span>
                        </div>
                        <div class="mt-4 grid grid-cols-5 gap-2">
                            @foreach ($questions as $idx => $q)
                                <button
                                    type="button"
                                    @click="currentIndex = {{ $idx }}"
                                    class="h-11 w-11 rounded-xl text-xs font-bold transition-all"
                                    :class="currentIndex === {{ $idx }} ? 'bg-amber-500 text-white shadow-md' : (localAnswers[{{ $q->id }}] > 0 || (localTheoryAnswers[{{ $q->id }}] && String(localTheoryAnswers[{{ $q->id }}]).trim() !== '') ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600 hover:bg-slate-200')"
                                >
                                    {{ $idx + 1 }}
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-6 space-y-2 border-t border-slate-100 pt-4 text-xs">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded bg-amber-500"></div>
                                <span class="text-slate-700">Current</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded bg-emerald-100"></div>
                                <span class="text-slate-700">Answered</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded bg-slate-100"></div>
                                <span class="text-slate-700">Unanswered</span>
                            </div>
                        </div>
                        <button @click="$dispatch('toggle-question-map')" class="mt-4 w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white lg:hidden">
                            Close
                        </button>
                    </div>
                </div>

                <div>
                    @if ($totalQuestions === 0)
                        <div class="rounded-2xl bg-white p-8 shadow-md ring-1 ring-slate-100">
                            <p class="text-lg text-slate-500">No questions available.</p>
                        </div>
                    @else
                        @foreach ($questions as $idx => $q)
                            @php
                                $qType = strtolower((string) ($q->type ?? 'mcq'));
                            @endphp
                            <div x-show="currentIndex === {{ $idx }}" x-cloak class="rounded-2xl bg-white p-5 shadow-md ring-1 ring-slate-100">
                                <div class="flex flex-col gap-5">
                                    <div>
                                        <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                            Question {{ $idx + 1 }} of {{ $totalQuestions }}
                                        </div>
                                        <h3 class="mt-3 text-lg font-bold leading-relaxed text-slate-900">
                                            {{ $q->prompt }}
                                        </h3>
                                        <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            {{ (int) $q->marks }} {{ (int) $q->marks === 1 ? 'mark' : 'marks' }}
                                        </div>
                                    </div>

                                    @if ($qType === 'theory')
                                        <div class="space-y-2">
                                            <textarea
                                                x-model="localTheoryAnswers[{{ $q->id }}]"
                                                @input.debounce.600ms="$wire.set('theoryAnswers.{{ $q->id }}', $event.target.value)"
                                                rows="6"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-amber-400 focus:ring-amber-300"
                                                placeholder="Type your answer here..."
                                            ></textarea>
                                            <div class="text-xs text-slate-500">Your response is saved automatically.</div>
                                        </div>
                                    @else
                                        <div class="space-y-2.5">
                                            @foreach ($q->options as $opt)
                                                <button
                                                    type="button"
                                                    class="group flex w-full cursor-pointer items-start gap-3 rounded-xl border border-slate-100 p-3.5 text-left transition-all"
                                                    :class="localAnswers[{{ $q->id }}] === {{ $opt->id }} ? 'bg-amber-500 text-white shadow-md' : 'bg-slate-50 hover:bg-white hover:shadow-sm'"
                                                    data-option-index="{{ $loop->index }}"
                                                    data-option-id="{{ $opt->id }}"
                                                    data-question-id="{{ $q->id }}"
                                                    @click="localAnswers[{{ $q->id }}] = {{ $opt->id }}; $wire.selectOption({{ $q->id }}, {{ $opt->id }})"
                                                >
                                                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-xs font-bold"
                                                         :class="localAnswers[{{ $q->id }}] === {{ $opt->id }} ? 'bg-white text-amber-700' : 'bg-white text-slate-700 group-hover:text-amber-700'">
                                                        {{ chr(65 + $loop->index) }}
                                                    </div>
                                                    <span class="min-w-0 flex-1 text-sm leading-relaxed"
                                                          :class="localAnswers[{{ $q->id }}] === {{ $opt->id }} ? 'font-semibold text-white' : 'font-medium text-slate-800'">
                                                        {{ $opt->label }}
                                                    </span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-4 rounded-2xl bg-white p-5 shadow-md ring-1 ring-slate-100">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-sm font-semibold text-slate-700">
                                    <span class="text-emerald-600" x-text="answeredCount"></span> answered
                                    <span class="mx-2 text-slate-300">|</span>
                                    <span class="text-slate-900" x-text="questions.length - answeredCount"></span> remaining
                                    @if ($lastSavedAt)
                                        <span class="mx-2 text-slate-300">|</span>
                                        <span class="text-xs text-slate-400">saved {{ $lastSavedAt }}</span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        @click="currentIndex = Math.max(0, currentIndex - 1)"
                                        :disabled="currentIndex === 0"
                                        class="rounded-lg bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-40"
                                    >
                                        Previous
                                    </button>
                                    <button
                                        type="button"
                                        @click="currentIndex = Math.min(questions.length - 1, currentIndex + 1)"
                                        :disabled="currentIndex >= questions.length - 1"
                                        class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-white hover:bg-amber-600 disabled:cursor-not-allowed disabled:opacity-40"
                                    >
                                        Next
                                    </button>
                                    <button
                                        type="button"
                                        @click="$dispatch('open-submit-modal')"
                                        :disabled="!canSubmit"
                                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Submit Exam
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    </div>

    <style>
        .cbt-take-root {
            font-family: 'Space Grotesk', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
        }
        .cbt-title {
            letter-spacing: -0.01em;
        }
        [x-cloak] { display: none !important; }
    </style>

    <!-- Submit Confirmation Modal -->
    <div x-data="{ open: false }" @open-submit-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="open = false">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @click.stop>
            <div class="mb-4 flex items-center gap-3">
                <div class="grid h-12 w-12 place-items-center rounded-full bg-amber-100">
                    <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Submit Exam?</h3>
            </div>
            <p class="text-sm text-slate-600">You cannot change answers after submitting. Are you sure you want to submit now?</p>
            <div class="mt-6 flex gap-3">
                <button @click="open = false" class="flex-1 rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-200">
                    Cancel
                </button>
                <button @click="open = false; $wire.submitExam()" class="flex-1 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                    Submit
                </button>
            </div>
        </div>
    </div>


</div>
