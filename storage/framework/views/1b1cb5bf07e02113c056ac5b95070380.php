<?php
    $submitted = (bool) $attempt->submitted_at;
    $questions = $exam?->questions?->values() ?? collect();
    $totalQuestions = $questions->count();
    $answered = $questions->filter(function ($q) {
        $questionType = strtolower((string) ($q->type ?? 'mcq'));
        if ($questionType === 'theory') {
            return trim((string) ($this->theoryAnswers[$q->id] ?? '')) !== '';
        }

        return (int) ($this->answers[$q->id] ?? 0) > 0;
    })->count();
    $currentIndex = max(0, min((int) $this->currentIndex, max(0, $totalQuestions - 1)));
    $currentQuestion = $totalQuestions > 0 ? $questions->get($currentIndex) : null;
    $progressPercent = $totalQuestions > 0 ? round(($answered / $totalQuestions) * 100, 1) : 0;
    $hasTheory = $questions->contains(fn ($q) => $q->type === 'theory');
?>

<div
    class="cbt-take-root min-h-screen bg-gradient-to-br from-amber-50 via-white to-teal-50 text-slate-900"
    <?php if(! $submitted): ?> wire:poll.30s="heartbeatTick" <?php endif; ?>
    x-data="{
        remaining: 0,
        timerDisplay: '--:--',
        canSubmit: false,
        _interval: null,
        _cfg: {
            startedAt: <?php echo \Illuminate\Support\Js::from($this->startedAtIso ?? null)->toHtml() ?>,
            dur: <?php echo \Illuminate\Support\Js::from($this->durationSeconds ?? 0)->toHtml() ?>,
            submitted: <?php echo \Illuminate\Support\Js::from($submitted)->toHtml() ?>,
            minSub: <?php echo \Illuminate\Support\Js::from($submitted ? 0 : $this->minSubmitSeconds())->toHtml() ?>,
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
                const btn = this.$el.querySelector('button[wire\\:click=\'next\']');
                if (btn && !btn.disabled) btn.click();
            } else if (key === 'arrowleft' || key === 'arrowup') {
                event.preventDefault();
                const btn = this.$el.querySelector('button[wire\\:click=\'prev\']');
                if (btn && !btn.disabled) btn.click();
            } else if (['a', 'b', 'c', 'd'].includes(key)) {
                event.preventDefault();
                const opt = this.$el.querySelector('button[data-option-index=\'' + (key.charCodeAt(0) - 97) + '\']');
                if (opt) opt.click();
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->passport_photo_url): ?>
                        <img src="<?php echo e($student->passport_photo_url); ?>" alt="<?php echo e($student->full_name ?? 'Student'); ?>" class="h-24 w-24 rounded-full object-cover shadow-lg ring-4 ring-white" />
                    <?php else: ?>
                        <div class="grid h-24 w-24 place-items-center rounded-full bg-amber-100 shadow-lg ring-4 ring-white">
                            <span class="text-3xl font-bold text-amber-700"><?php echo e(mb_substr($student->first_name ?? 'S', 0, 1)); ?></span>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">CBT Session</div>
                        <h1 class="cbt-title text-2xl font-bold text-slate-900"><?php echo e($exam->title); ?></h1>
                        <div class="mt-1 text-sm text-slate-600">
                            <?php echo e($student->full_name ?? ($student->first_name.' '.$student->last_name)); ?>

                            <span class="mx-1 text-slate-300">|</span>
                            <span class="font-mono text-xs text-slate-500">#<?php echo e($student->admission_number); ?></span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    
                    <div class="rounded-xl px-3.5 py-2 shadow-sm" :class="remaining <= 60 ? 'bg-rose-100' : 'bg-emerald-100'">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Time Left</div>
                        <div class="mt-1 font-mono text-xl font-bold" :class="remaining <= 60 ? 'text-rose-700' : 'text-emerald-700'" x-text="timerDisplay">
                            --:--
                        </div>
                    </div>
                    <div class="rounded-xl bg-slate-100 px-3.5 py-2 shadow-sm">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Progress</div>
                        <div class="mt-1 text-xl font-bold text-slate-800"><?php echo e($answered); ?>/<?php echo e($totalQuestions); ?></div>
                        <div class="mt-2 h-1.5 w-24 overflow-hidden rounded-full bg-white">
                            <div class="h-full rounded-full bg-slate-700" style="width: <?php echo e($progressPercent); ?>%"></div>
                        </div>
                    </div>
                    <div class="rounded-xl bg-amber-100 px-3.5 py-2 shadow-sm">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Question</div>
                        <div class="mt-1 text-xl font-bold text-amber-800"><?php echo e($currentIndex + 1); ?> of <?php echo e($totalQuestions); ?></div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="mx-auto w-full max-w-7xl px-6 pb-6 pt-5">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($submitted): ?>
            <div class="rounded-2xl bg-white p-7 shadow-lg ring-1 ring-slate-100">
                <div>
                    <div class="mb-6 grid h-20 w-20 place-items-center rounded-full bg-emerald-100">
                        <svg class="h-10 w-10 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-slate-900">Exam Submitted</h2>
                    <p class="mt-2 text-base text-slate-600">Your answers have been recorded.</p>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2">
                    <?php
                        $showScoreNow = $exam->show_score && $exam->results_released_at;
                        $pendingRelease = !$exam->show_score || !$exam->results_released_at;
                    ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam->show_score && $exam->results_released_at): ?>
                        
                        <div class="rounded-2xl bg-emerald-50 p-6 text-center shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-600">Your Score</div>
                            <div class="mt-3 text-5xl font-bold text-emerald-700">
                                <?php echo e((int) $attempt->score); ?>

                                <span class="text-3xl text-slate-400">/<?php echo e((int) $attempt->max_score); ?></span>
                            </div>
                            <div class="mt-2 text-sm font-semibold text-emerald-600">
                                <?php echo e((int) $attempt->percent); ?>%
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasTheory): ?>
                                <div class="mt-2 text-xs font-semibold text-slate-500">Theory questions marked separately.</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php elseif($exam->show_score && !$exam->results_released_at): ?>
                        
                        <div class="rounded-2xl bg-amber-50 p-6 text-center shadow-sm border border-amber-200">
                            <div class="grid h-12 w-12 place-items-center rounded-full bg-amber-100 mx-auto mb-3">
                                <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="text-sm font-bold text-amber-800">Results Pending</div>
                            <div class="mt-1 text-xs text-amber-700">Your teacher will release scores shortly.</div>
                        </div>
                    <?php else: ?>
                        
                        <div class="rounded-2xl bg-sky-50 p-6 text-center shadow-sm border border-sky-200">
                            <div class="grid h-12 w-12 place-items-center rounded-full bg-sky-100 mx-auto mb-3">
                                <svg class="h-6 w-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="text-sm font-bold text-sky-800">Submission Successful</div>
                            <div class="mt-1 text-xs text-sky-700">Results will be released by your teacher.</div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="rounded-2xl bg-sky-50 p-6 text-center shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-600">Submitted At</div>
                        <div class="mt-3 text-xl font-bold text-slate-900"><?php echo e($attempt->submitted_at?->format('g:i A')); ?></div>
                        <div class="mt-1 text-sm text-slate-600"><?php echo e($attempt->submitted_at?->format('M j, Y')); ?></div>
                    </div>
                </div>

                <div class="mt-10">
                    <a href="<?php echo e(route('cbt.student', ['code' => $examCode])); ?>" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-8 py-4 text-base font-bold text-white shadow-md hover:bg-slate-800">
                        Back to Exams
                    </a>
                </div>
            </div>

        <?php else: ?>
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $questionType = strtolower((string) ($q->type ?? 'mcq'));
                                    $isAnswered = $questionType === 'theory'
                                        ? trim((string) ($this->theoryAnswers[$q->id] ?? '')) !== ''
                                        : (int) ($this->answers[$q->id] ?? 0) > 0;
                                    $isCurrent = (int) $idx === (int) $currentIndex;
                                ?>
                                <button
                                    type="button"
                                    wire:click="goTo(<?php echo e($idx); ?>)"
                                    class="h-11 w-11 rounded-xl text-xs font-bold transition-all <?php echo e($isCurrent ? 'bg-amber-500 text-white shadow-md' : ($isAnswered ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600 hover:bg-slate-200')); ?>"
                                >
                                    <?php echo e($idx + 1); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $currentQuestion): ?>
                        <div class="rounded-2xl bg-white p-8 shadow-md ring-1 ring-slate-100">
                            <p class="text-lg text-slate-500">No questions available.</p>
                        </div>
                    <?php else: ?>
                        <?php
                            $selected = (int) ($this->answers[$currentQuestion->id] ?? 0);
                            $currentQuestionType = strtolower((string) ($currentQuestion->type ?? 'mcq'));
                        ?>

                        <div class="rounded-2xl bg-white p-5 shadow-md ring-1 ring-slate-100">
                            <div class="flex flex-col gap-5">
                                <div>
                                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                        Question <?php echo e($currentIndex + 1); ?> of <?php echo e($totalQuestions); ?>

                                    </div>
                                    <h3 class="mt-3 text-lg font-bold leading-relaxed text-slate-900">
                                        <?php echo e($currentQuestion->prompt); ?>

                                    </h3>
                                    <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        <?php echo e((int) $currentQuestion->marks); ?> <?php echo e((int) $currentQuestion->marks === 1 ? 'mark' : 'marks'); ?>

                                    </div>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentQuestionType === 'theory'): ?>
                                    <div class="space-y-2">
                                        <textarea
                                            wire:model.debounce.600ms="theoryAnswers.<?php echo e($currentQuestion->id); ?>"
                                            rows="6"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-amber-400 focus:ring-amber-300"
                                            placeholder="Type your answer here..."
                                        ></textarea>
                                        <div class="text-xs text-slate-500">Your response is saved automatically.</div>
                                    </div>
                                <?php else: ?>
                                    <div class="space-y-2.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $currentQuestion->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php ($isSelected = $selected === (int) $opt->id); ?>
                                            <button
                                                type="button"
                                                class="group flex w-full cursor-pointer items-start gap-3 rounded-xl border border-slate-100 p-3.5 text-left transition-all <?php echo e($isSelected ? 'bg-amber-500 text-white shadow-md' : 'bg-slate-50 hover:bg-white hover:shadow-sm'); ?>"
                                                data-option-index="<?php echo e($loop->index); ?>"
                                                data-option-id="<?php echo e($opt->id); ?>"
                                                data-question-id="<?php echo e($currentQuestion->id); ?>"
                                                wire:click="selectOption(<?php echo e($currentQuestion->id); ?>, <?php echo e($opt->id); ?>)"
                                                wire:loading.attr="disabled"
                                                wire:target="selectOption"
                                            >
                                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-xs font-bold <?php echo e($isSelected ? 'bg-white text-amber-700' : 'bg-white text-slate-700 group-hover:text-amber-700'); ?>">
                                                    <?php echo e(chr(65 + $loop->index)); ?>

                                                </div>
                                                <span class="min-w-0 flex-1 text-sm leading-relaxed <?php echo e($isSelected ? 'font-semibold text-white' : 'font-medium text-slate-800'); ?>">
                                                    <?php echo e($opt->label); ?>

                                                </span>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <div class="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="text-sm font-semibold text-slate-700">
                                        <span class="text-emerald-600"><?php echo e($answered); ?></span> answered
                                        <span class="mx-2 text-slate-300">|</span>
                                        <span class="text-slate-900"><?php echo e(max(0, $totalQuestions - $answered)); ?></span> remaining
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastSavedAt): ?>
                                            <span class="mx-2 text-slate-300">|</span>
                                            <span class="text-xs text-slate-400">saved <?php echo e($lastSavedAt); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        <button
                                            type="button"
                                            wire:click="prev"
                                            <?php if($currentIndex === 0): echo 'disabled'; endif; ?>
                                            class="rounded-lg bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-40"
                                        >
                                            Previous
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="next"
                                            <?php if($currentIndex >= $totalQuestions - 1): echo 'disabled'; endif; ?>
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
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/cbt/portal/take.blade.php ENDPATH**/ ?>