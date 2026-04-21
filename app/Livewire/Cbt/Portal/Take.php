<?php

namespace App\Livewire\Cbt\Portal;

use App\Models\CbtAnswer;
use App\Models\CbtAttempt;
use App\Models\CbtOption;
use App\Models\CbtQuestion;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.portal')]
#[Title('Take CBT Exam')]
class Take extends Component
{
    public int $attemptId;
    public string $examCode = '';

    /** @var array<int,int> question_id => option_id */
    public array $answers = [];

    /** @var array<int,string> question_id => text answer */
    public array $theoryAnswers = [];

    public int $currentIndex = 0;
    public ?string $lastSavedAt = null;

    /* ── Cached attempt to avoid re-querying within a single request ── */
    private ?CbtAttempt $cachedAttempt = null;

    /* ── Timer data sent to JS on mount (avoids per-second polling) ── */
    public int $durationSeconds = 0;
    public ?string $startedAtIso = null;

    public function minSubmitSeconds(): int
    {
        $attempt = $this->attempt();

        $durationMinutes = (int) ($attempt->exam?->duration_minutes ?? 0);
        $durationMinutes = max(1, $durationMinutes);

        return (int) ceil(($durationMinutes * 60) / 2);
    }

    public function elapsedSeconds(): int
    {
        $attempt = $this->attempt();
        $startedAt = $attempt->started_at ?? $attempt->created_at;
        if (!$startedAt) {
            return 0;
        }

        return max(0, (int) $startedAt->diffInSeconds(now()));
    }

    public function canSubmitNow(): bool
    {
        $attempt = $this->attempt();
        if ($attempt->submitted_at || $attempt->terminated_at) {
            return false;
        }

        if ($this->remainingSeconds() <= 0) {
            return true;
        }

        return $this->elapsedSeconds() >= $this->minSubmitSeconds();
    }

    public function submitUnlockInSeconds(): int
    {
        $attempt = $this->attempt();
        if ($attempt->submitted_at || $attempt->terminated_at) {
            return 0;
        }

        $remaining = $this->remainingSeconds();
        if ($remaining <= 0) {
            return 0;
        }

        $need = $this->minSubmitSeconds() - $this->elapsedSeconds();

        return max(0, (int) $need);
    }

    /**
     * Called every 30s by wire:poll (instead of every 1s).
     * The client-side JS timer handles the countdown display.
     */
    public function heartbeatTick(): void
    {
        $attempt = $this->attempt();

        if ($attempt->terminated_at) {
            session()->flash('error', 'Your exam attempt was terminated by an admin.');
            $this->redirect(route('student.exams'));
            return;
        }

        if ($attempt->submitted_at) {
            return;
        }

        // Update last_activity
        CbtAttempt::query()
            ->whereKey($attempt->id)
            ->whereNull('submitted_at')
            ->whereNull('terminated_at')
            ->update(['last_activity_at' => now()]);

        // Auto-submit if time is up
        if ($this->remainingSeconds() <= 0) {
            $this->submitExam();
        }

        // Bust cache so render() gets fresh data
        $this->cachedAttempt = null;
    }

    public function mount(CbtAttempt $attempt): void
    {
        $this->attemptId = (int) $attempt->id;
        $this->examCode = strtoupper(trim((string) request('code', '')));

        $attempt = $this->attempt();

        abort_unless($attempt->exam && in_array($attempt->exam->status, ['live', 'approved']), 403, 'Exam is not active.');
        abort_unless((bool) $attempt->exam->published_at, 403, 'Exam is not live.');
        abort_unless($attempt->student && $attempt->student->status === 'Active', 403, 'Student is not active.');
        abort_unless((int) $attempt->student->class_id === (int) $attempt->exam->class_id, 403);
        if ($attempt->terminated_at) {
            session()->flash('error', 'Your exam attempt was terminated by an admin.');
            redirect()->route('student.exams');
            return;
        }

        $ip = (string) request()->ip();
        $allowedCidrs = trim((string) ($attempt->exam->allowed_cidrs ?? ''));
        if ($allowedCidrs !== '') {
            $cidrs = collect(preg_split('/[,\s]+/', $allowedCidrs) ?: [])
                ->map(fn($v) => trim((string) $v))
                ->filter()
                ->values()
                ->all();

            if ($cidrs && !\Symfony\Component\HttpFoundation\IpUtils::checkIp($ip, $cidrs)) {
                abort(403, 'This exam is restricted to the school network.');
            }
        }

        if ($attempt->exam->starts_at && now()->lt($attempt->exam->starts_at)) {
            abort(403, 'This exam has not started yet.');
        }
        if ($attempt->exam->ends_at) {
            $grace = (int) ($attempt->exam->grace_minutes ?? 0);
            $end = $attempt->exam->ends_at->copy()->addMinutes(max(0, $grace));
            if (now()->gt($end) && !$attempt->started_at) {
                abort(403, 'This exam has ended.');
            }
        }

        if (!$attempt->started_at) {
            $attempt->forceFill(['started_at' => now()])->save();
            $this->cachedAttempt = null;
            $attempt = $this->attempt();
        }

        $lockedIp = trim((string) ($attempt->ip_address ?? ''));
        $allowedIp = trim((string) ($attempt->allowed_ip ?? ''));

        if ($lockedIp === '') {
            $attempt->forceFill(['ip_address' => $ip])->save();
        } elseif ($lockedIp !== $ip) {
            if ($allowedIp !== '' && $allowedIp === $ip) {
                $attempt->forceFill(['ip_address' => $ip, 'allowed_ip' => null])->save();
            } else {
                abort(403, 'This attempt is locked to another device/IP. Ask an admin to update your IP.');
            }
        }

        $attempt->forceFill(['last_activity_at' => now()])->save();

        // Prepare timer data for JS countdown
        $startedAt = $attempt->started_at ?? $attempt->created_at;
        $this->startedAtIso = $startedAt?->toIso8601String();
        $this->durationSeconds = max(1, (int) ($attempt->exam->duration_minutes ?? 1)) * 60;

        // Shuffle question order per-student if enabled (seed by attempt id for consistency)
        if ($attempt->exam->shuffle_questions) {
            $questions = $attempt->exam->questions->shuffle();
            $attempt->exam->setRelation('questions', $questions);
        }

        $this->loadAnswers();
        $this->currentIndex = 0;
    }

    /**
     * Return the attempt with eager-loaded relations. Cached within a single
     * Livewire request so repeated calls (selectOption, remainingSeconds, render)
     * don't hit the database multiple times.
     */
    public function attempt(): CbtAttempt
    {
        if ($this->cachedAttempt) {
            return $this->cachedAttempt;
        }

        $this->cachedAttempt = CbtAttempt::query()
            ->with([
                'student:id,admission_number,first_name,last_name,passport_photo,class_id,status',
                'exam' => fn($q) => $q->with(['schoolClass:id,name', 'subject:id,name', 'questions.options']),
            ])
            ->findOrFail($this->attemptId);

        return $this->cachedAttempt;
    }

    private function loadAnswers(): void
    {
        $this->answers = CbtAnswer::query()
            ->where('attempt_id', $this->attemptId)
            ->whereNotNull('option_id')
            ->pluck('option_id', 'question_id')
            ->map(fn($v) => (int) $v)
            ->all();

        $this->theoryAnswers = CbtAnswer::query()
            ->where('attempt_id', $this->attemptId)
            ->whereNotNull('text_answer')
            ->pluck('text_answer', 'question_id')
            ->map(fn($v) => (string) $v)
            ->all();
    }

    public function updatedTheoryAnswers($value, $key): void
    {
        $questionId = (int) $key;
        $attempt = $this->attempt();
        if ($attempt->terminated_at || $attempt->submitted_at) {
            return;
        }
        if (!$attempt->exam?->published_at) {
            $this->dispatch('alert', message: 'Exam is paused by admin.', type: 'warning');
            return;
        }
        if ($this->remainingSeconds() <= 0) {
            $this->submitExam();
            return;
        }

        // Validate via the already-loaded questions (no extra DB query)
        $question = $attempt->exam->questions->firstWhere('id', $questionId);
        if (!$question || $question->type !== 'theory') {
            return;
        }

        $answer = trim((string) $value);
        CbtAnswer::query()->updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $questionId,
            ],
            [
                'option_id' => null,
                'text_answer' => $answer !== '' ? $answer : null,
                'awarded_marks' => null,
                'is_correct' => null,
            ]
        );

        $this->lastSavedAt = now()->format('H:i:s');
    }

    public function goTo(int $index): void
    {
        $attempt = $this->attempt();
        $total = (int) ($attempt->exam?->questions?->count() ?? 0);
        if ($total <= 0) {
            $this->currentIndex = 0;
            return;
        }

        $this->currentIndex = max(0, min($total - 1, (int) $index));
    }

    public function next(): void
    {
        $this->goTo($this->currentIndex + 1);
    }

    public function prev(): void
    {
        $this->goTo($this->currentIndex - 1);
    }

    public function remainingSeconds(): int
    {
        $attempt = $this->attempt();
        if ($attempt->terminated_at) {
            return 0;
        }
        if ($attempt->submitted_at) {
            return 0;
        }

        $startedAt = $attempt->started_at ?? $attempt->created_at;
        if (!$startedAt) {
            return 0;
        }

        $durationMinutes = (int) ($attempt->exam?->duration_minutes ?? 0);
        $durationMinutes = max(1, $durationMinutes);

        $endsAt = $startedAt->copy()->addSeconds($durationMinutes * 60);
        $seconds = now()->diffInSeconds($endsAt, false);

        return max(0, (int) $seconds);
    }

    /**
     * Optimised: validate question/option from already-loaded eager data
     * instead of running 2 extra queries per click.
     */
    public function selectOption(int $questionId, int $optionId): void
    {
        $attempt = $this->attempt();
        if ($attempt->terminated_at || $attempt->submitted_at) {
            return;
        }
        if (!$attempt->exam?->published_at) {
            $this->dispatch('alert', message: 'Exam is paused by admin.', type: 'warning');
            return;
        }
        if ($this->remainingSeconds() <= 0) {
            $this->submitExam();
            return;
        }

        // Use eager-loaded data — zero extra queries
        $question = $attempt->exam->questions->firstWhere('id', $questionId);
        abort_unless($question, 404);

        $option = $question->options->firstWhere('id', $optionId);
        abort_unless($option, 404);

        CbtAnswer::query()->updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $questionId,
            ],
            [
                'option_id' => $optionId,
                'text_answer' => null,
                'awarded_marks' => null,
                'is_correct' => null,
            ]
        );

        $this->answers[$questionId] = $optionId;
        $this->lastSavedAt = now()->format('H:i:s');
    }

    public function submitExam(): void
    {
        $attempt = $this->attempt();
        if ($attempt->terminated_at) {
            return;
        }
        if ($attempt->submitted_at) {
            return;
        }

        if (!$this->canSubmitNow()) {
            $secs = $this->submitUnlockInSeconds();
            $mins = (int) ceil($secs / 60);
            $this->dispatch('alert', message: "You can only submit after half of the exam time. Try again in about {$mins} minute(s).", type: 'warning');
            return;
        }

        DB::transaction(function () use ($attempt) {
            $attempt->refresh();
            if ($attempt->submitted_at) {
                return;
            }

            $attempt->load(['exam.questions.options']);

            $maxScore = 0;
            $score = 0;

            foreach ($attempt->exam->questions as $question) {
                $questionType = $question->type ?? 'mcq';
                $maxScore += (int) ($question->marks ?? 0);

                if ($questionType === 'theory') {
                    $textAnswer = trim((string) ($this->theoryAnswers[$question->id] ?? ''));

                    CbtAnswer::query()->updateOrCreate(
                        [
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                        ],
                        [
                            'option_id' => null,
                            'text_answer' => $textAnswer !== '' ? $textAnswer : null,
                            'is_correct' => null,
                        ]
                    );

                    continue;
                }

                $correctOptionId = (int) ($question->options->firstWhere('is_correct', true)?->id ?? 0);
                $selectedOptionId = (int) ($this->answers[$question->id] ?? 0);
                if ($selectedOptionId > 0 && !$question->options->contains('id', $selectedOptionId)) {
                    $selectedOptionId = 0;
                }

                $isCorrect = $selectedOptionId > 0 && $selectedOptionId === $correctOptionId;
                if ($isCorrect) {
                    $score += (int) ($question->marks ?? 0);
                }

                CbtAnswer::query()->updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'option_id' => $selectedOptionId > 0 ? $selectedOptionId : null,
                        'text_answer' => null,
                        'awarded_marks' => null,
                        'is_correct' => $isCorrect,
                    ]
                );
            }

            $percent = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0;

            $attempt->forceFill([
                'score' => $score,
                'max_score' => $maxScore,
                'percent' => $percent,
                'submitted_at' => now(),
            ])->save();
        });

        $this->cachedAttempt = null;
        $this->loadAnswers();
        $this->dispatch('alert', message: 'Exam submitted.', type: 'success');
    }

    public function render()
    {
        $attempt = $this->attempt();

        return view('livewire.cbt.portal.take', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'student' => $attempt->student,
        ]);
    }
}
