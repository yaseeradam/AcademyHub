<?php

namespace App\Livewire\Cbt;

use App\Models\CbtAnswer;
use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Theory Review')]
class TheoryReview extends Component
{
    public int $examId;

    // null = student list, int = marking a specific attempt
    public ?int $reviewAttemptId = null;

    public array $theoryMarks    = [];
    public array $theoryComments = [];

    public function mount(CbtExam $exam): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        if ($user->role === 'teacher') {
            $ok = (int) $exam->created_by === (int) $user->id
               || (int) ($exam->assigned_teacher_id ?? 0) === (int) $user->id;
            abort_unless($ok, 403);
        }

        abort_unless($exam->questions()->where('type', 'theory')->exists(), 404);

        $this->examId = $exam->id;

        $attemptId = request()->query('attempt');
        if ($attemptId) {
            $this->openAttempt((int) $attemptId);
        }
    }

    #[Computed]
    public function exam(): CbtExam
    {
        return CbtExam::with(['schoolClass:id,name', 'subject:id,name', 'questions'])
            ->findOrFail($this->examId);
    }

    #[Computed]
    public function attempts()
    {
        return CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->whereNotNull('submitted_at')
            ->with('student:id,admission_number,first_name,last_name,passport_photo')
            ->orderByRaw("CASE WHEN theory_status IS NULL OR theory_status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function theoryQuestions()
    {
        return $this->exam->questions->where('type', 'theory')->values();
    }

    #[Computed]
    public function currentAttempt(): ?CbtAttempt
    {
        if (! $this->reviewAttemptId) return null;

        return CbtAttempt::with(['student:id,admission_number,first_name,last_name', 'answers'])
            ->where('exam_id', $this->examId)
            ->find($this->reviewAttemptId);
    }

    public function openAttempt(int $attemptId): void
    {
        $this->reviewAttemptId = $attemptId;
        $this->theoryMarks     = [];
        $this->theoryComments  = [];

        $attempt = CbtAttempt::with('answers')
            ->where('exam_id', $this->examId)
            ->findOrFail($attemptId);

        foreach ($this->theoryQuestions as $q) {
            $answer = $attempt->answers->firstWhere('question_id', $q->id);
            $this->theoryMarks[$q->id]    = $answer?->awarded_marks ?? '';
            $this->theoryComments[$q->id] = $answer?->teacher_comment ?? '';
        }

        unset($this->currentAttempt);
    }

    public function saveAndNext(): void
    {
        $this->save();

        // Find next unmarked attempt
        $next = $this->attempts
            ->where('id', '!=', $this->reviewAttemptId)
            ->whereIn('theory_status', [null, 'pending', 'forwarded'])
            ->first();

        if ($next) {
            $this->openAttempt($next->id);
        } else {
            $this->reviewAttemptId = null;
            unset($this->attempts);
        }
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);
        abort_unless($this->reviewAttemptId, 422);

        $attempt = CbtAttempt::with(['exam.questions.options', 'answers', 'student'])
            ->where('exam_id', $this->examId)
            ->findOrFail($this->reviewAttemptId);

        $errors = [];
        foreach ($this->theoryQuestions as $q) {
            $raw = $this->theoryMarks[$q->id] ?? null;
            if ($raw === '' || $raw === null) continue;
            if (! is_numeric($raw)) {
                $errors["theoryMarks.{$q->id}"] = 'Must be a number.';
                continue;
            }
            if ((int) $raw < 0 || (int) $raw > (int) $q->marks) {
                $errors["theoryMarks.{$q->id}"] = "0 – {$q->marks}";
            }
        }
        if ($errors) throw ValidationException::withMessages($errors);

        $answers = $attempt->answers->keyBy('question_id');

        foreach ($this->theoryQuestions as $q) {
            $raw   = $this->theoryMarks[$q->id] ?? null;
            $value = ($raw === '' || $raw === null) ? null : max(0, min((int) $raw, (int) $q->marks));
            $text  = trim((string) ($answers->get($q->id)?->text_answer ?? ''));

            CbtAnswer::query()->updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $q->id],
                [
                    'option_id'       => null,
                    'text_answer'     => $text !== '' ? $text : null,
                    'awarded_marks'   => $value,
                    'teacher_comment' => $this->theoryComments[$q->id] ?? null,
                    'is_correct'      => null,
                ]
            );
        }

        $attempt->forceFill(['theory_status' => 'marked', 'marked_at' => now()])->save();
        $this->recalculate($attempt);

        Audit::log('cbt.theory_marked', $this->exam, [
            'attempt_id' => $attempt->id,
            'student_id' => $attempt->student_id,
        ]);

        unset($this->attempts, $this->currentAttempt);
        $this->dispatch('alert', message: 'Marks saved.', type: 'success');
    }

    public function autoMarkQuestion(int $questionId): void
    {
        $attempt = $this->currentAttempt;
        if (! $attempt) return;

        $question = $this->theoryQuestions->firstWhere('id', $questionId);
        if (! $question) return;

        $answer = $attempt->answers->firstWhere('question_id', $questionId);
        $studentAnswer = trim((string) ($answer?->text_answer ?? ''));

        if ($studentAnswer === '') {
            $this->theoryMarks[$questionId] = 0;
            $this->theoryComments[$questionId] = 'No answer submitted.';
            return;
        }

        $rawKeys = config('services.groq.key') ?: env('GROQ_API_KEY') ?: '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';
        $keys = array_filter(array_map('trim', explode(',', $rawKeys)));

        if (empty($keys)) {
            $this->dispatch('alert', message: 'API key not configured.', type: 'error');
            return;
        }

        $apiKey = $keys[array_rand($keys)];
        $prompt = "You are an academic examiner grading a student's answer.\n" .
                  "Question: {$question->prompt}\n" .
                  "Maximum Marks: {$question->marks}\n" .
                  "Student's Answer: \"{$studentAnswer}\"\n\n" .
                  "Evaluate the student's answer and assign a score out of {$question->marks}.\n" .
                  "Format your response EXACTLY as a JSON object with 'marks' (an integer from 0 to {$question->marks}) and 'comment' (a brief constructive remark, max 12 words) keys. Do not include markdown formatting or backticks around the JSON. Example:\n" .
                  "{\"marks\": 3, \"comment\": \"Good effort, but missed key details.\"}\n" .
                  "JSON Response:";

        try {
            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'    => 'llama-3.3-70b-versatile',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.2,
                    'max_tokens'  => 100,
                ]);

            if ($response->successful()) {
                $content = trim($response->json()['choices'][0]['message']['content'] ?? '');
                if (str_starts_with($content, '```')) {
                    $content = preg_replace('/^```(?:json)?\n?|```$/s', '', $content);
                }
                $json = json_decode(trim($content), true);

                if (is_array($json) && isset($json['marks'])) {
                    $this->theoryMarks[$questionId] = max(0, min((int) $json['marks'], (int) $question->marks));
                    $this->theoryComments[$questionId] = trim((string) ($json['comment'] ?? ''));
                    return;
                }
            }

            $this->dispatch('alert', message: 'Failed to parse AI response.', type: 'error');
        } catch (\Throwable $e) {
            $this->dispatch('alert', message: 'AI marking error: ' . $e->getMessage(), type: 'error');
        }
    }

    public function autoMarkAll(): void
    {
        $attempt = $this->currentAttempt;
        if (! $attempt) return;

        foreach ($this->theoryQuestions as $q) {
            $this->autoMarkQuestion($q->id);
        }

        $this->dispatch('alert', message: 'AI marking completed for all questions.', type: 'success');
    }

    public function back(): void
    {
        $this->reviewAttemptId = null;
        $this->theoryMarks     = [];
        $this->theoryComments  = [];
        unset($this->attempts, $this->currentAttempt);
    }

    private function recalculate(CbtAttempt $attempt): void
    {
        $attempt->loadMissing(['exam.questions.options', 'answers']);
        $answers  = $attempt->answers->keyBy('question_id');
        $maxScore = 0;
        $score    = 0;

        foreach ($attempt->exam->questions as $q) {
            $maxScore += (int) $q->marks;

            if ($q->type === 'theory') {
                $awarded = $answers->get($q->id)?->awarded_marks;
                if (is_numeric($awarded)) {
                    $score += max(0, min((int) $awarded, (int) $q->marks));
                }
                continue;
            }

            $correctId  = (int) ($q->options->firstWhere('is_correct', true)?->id ?? 0);
            $selectedId = (int) ($answers->get($q->id)?->option_id ?? 0);
            if ($selectedId > 0 && $selectedId === $correctId) {
                $score += (int) $q->marks;
            }
        }

        $attempt->forceFill([
            'score'     => $score,
            'max_score' => $maxScore,
            'percent'   => $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0,
        ])->save();
    }

    public function render()
    {
        return view('livewire.cbt.theory-review', [
            'exam' => $this->exam,
        ]);
    }
}
