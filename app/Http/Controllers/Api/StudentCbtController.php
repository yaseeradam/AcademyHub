<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CbtAnswer;
use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\CbtQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

class StudentCbtController extends Controller
{
    public function exams(Request $request)
    {
        $student = $request->user();

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        $exams = CbtExam::with(['subject:id,name'])
            ->where('class_id', $student->class_id)
            ->where('status', 'live')
            ->whereNotNull('published_at')
            ->get();

        $attempts = CbtAttempt::where('student_id', $student->id)
            ->whereIn('exam_id', $exams->pluck('id'))
            ->get()
            ->keyBy('exam_id');

        $result = $exams->map(function ($exam) use ($attempts) {
            $attempt = $attempts->get($exam->id);
            $state = 'not_started';
            if ($attempt) {
                if ($attempt->terminated_at) {
                    $state = 'terminated';
                } elseif ($attempt->submitted_at) {
                    $state = 'submitted';
                } elseif ($attempt->started_at) {
                    $state = 'in_progress';
                }
            }

            return [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'subject' => $exam->subject?->name,
                'duration_minutes' => $exam->duration_minutes,
                'has_pin' => !empty($exam->pin),
                'starts_at' => $exam->starts_at?->toIso8601String(),
                'ends_at' => $exam->ends_at?->toIso8601String(),
                'show_score' => $exam->show_score,
                'state' => $state,
                'attempt' => $attempt ? [
                    'uuid' => $attempt->uuid,
                    'started_at' => $attempt->started_at?->toIso8601String(),
                    'submitted_at' => $attempt->submitted_at?->toIso8601String(),
                    'score' => $exam->show_score && $attempt->submitted_at ? $attempt->score : null,
                    'max_score' => $exam->show_score && $attempt->submitted_at ? $attempt->max_score : null,
                    'percent' => $exam->show_score && $attempt->submitted_at ? $attempt->percent : null,
                ] : null,
            ];
        });

        return response()->json($result);
    }

    public function startExam(Request $request, $examId)
    {
        $student = $request->user();

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        $exam = CbtExam::with(['questions.options'])
            ->where('class_id', $student->class_id)
            ->where('status', 'live')
            ->whereNotNull('published_at')
            ->findOrFail($examId);

        // Access pin verification
        if (!empty($exam->pin)) {
            $request->validate([
                'pin' => 'required|string',
            ]);
            if ($request->pin !== $exam->pin) {
                return response()->json(['message' => 'Invalid exam access PIN.'], 422);
            }
        }

        // Timing checks
        if ($exam->starts_at && now()->lt($exam->starts_at)) {
            return response()->json(['message' => 'This exam has not started yet.'], 403);
        }
        if ($exam->ends_at) {
            $grace = (int) ($exam->grace_minutes ?? 0);
            $end = $exam->ends_at->copy()->addMinutes(max(0, $grace));
            if (now()->gt($end)) {
                return response()->json(['message' => 'This exam has ended.'], 403);
            }
        }

        // CIDR network isolation check
        $ip = $request->ip();
        $allowedCidrs = trim((string) ($exam->allowed_cidrs ?? ''));
        if ($allowedCidrs !== '') {
            $cidrs = collect(preg_split('/[,\s]+/', $allowedCidrs) ?: [])
                ->map(fn($v) => trim((string) $v))
                ->filter()
                ->values()
                ->all();

            if ($cidrs && !IpUtils::checkIp($ip, $cidrs)) {
                return response()->json(['message' => 'This exam is restricted to the school network.'], 403);
            }
        }

        // Check or create attempt
        $attempt = CbtAttempt::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $student->id,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'ip_address' => $ip,
                'started_at' => now(),
                'last_activity_at' => now(),
            ]
        );

        if ($attempt->terminated_at) {
            return response()->json(['message' => 'Your exam attempt was terminated by an admin.'], 403);
        }

        if ($attempt->submitted_at) {
            return response()->json(['message' => 'Your exam has already been submitted.'], 403);
        }

        // Lock to device / IP check
        $lockedIp = trim((string) ($attempt->ip_address ?? ''));
        $allowedIp = trim((string) ($attempt->allowed_ip ?? ''));
        if ($lockedIp !== '' && $lockedIp !== $ip) {
            if ($allowedIp !== '' && $allowedIp === $ip) {
                $attempt->update(['ip_address' => $ip, 'allowed_ip' => null]);
            } else {
                return response()->json(['message' => 'This attempt is locked to another device. Ask an admin to reset.'], 403);
            }
        }

        // Questions retrieval & optional shuffle
        $questions = $exam->questions;
        if ($exam->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        $formattedQuestions = $questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'question_image' => $q->question_image ? asset('uploads/' . $q->question_image) : null,
                'type' => $q->type ?? 'mcq',
                'marks' => $q->marks,
                'options' => $q->options->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'option_text' => $o->option_text,
                    ];
                }),
            ];
        });

        // Load existing answers if any (in case of disconnection/retake)
        $existingAnswers = CbtAnswer::where('attempt_id', $attempt->id)->get()->map(function ($a) {
            return [
                'question_id' => $a->question_id,
                'option_id' => $a->option_id,
                'text_answer' => $a->text_answer,
            ];
        });

        return response()->json([
            'attempt_uuid' => $attempt->uuid,
            'duration_minutes' => $exam->duration_minutes,
            'started_at' => $attempt->started_at->toIso8601String(),
            'questions' => $formattedQuestions,
            'existing_answers' => $existingAnswers,
        ]);
    }

    public function submitExam(Request $request, $attemptUuid)
    {
        $student = $request->user();

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        $attempt = CbtAttempt::with(['exam.questions.options'])
            ->where('uuid', $attemptUuid)
            ->where('student_id', $student->id)
            ->firstOrFail();

        if ($attempt->terminated_at) {
            return response()->json(['message' => 'Your exam attempt was terminated by an admin.'], 403);
        }

        if ($attempt->submitted_at) {
            return response()->json([
                'message' => 'Your exam has already been submitted.',
                'score' => $attempt->exam->show_score ? $attempt->score : null,
                'max_score' => $attempt->exam->show_score ? $attempt->max_score : null,
                'percent' => $attempt->exam->show_score ? $attempt->percent : null,
            ]);
        }

        $request->validate([
            'answers' => 'nullable|array', // question_id => option_id
            'theory_answers' => 'nullable|array', // question_id => text
        ]);

        $submittedAnswers = $request->input('answers', []);
        $submittedTheory = $request->input('theory_answers', []);

        DB::transaction(function () use ($attempt, $submittedAnswers, $submittedTheory) {
            $maxScore = 0;
            $score = 0;

            foreach ($attempt->exam->questions as $question) {
                $questionType = $question->type ?? 'mcq';
                $maxScore += (int) ($question->marks ?? 0);

                if ($questionType === 'theory') {
                    $textAnswer = trim((string) ($submittedTheory[$question->id] ?? ''));

                    CbtAnswer::updateOrCreate(
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
                $selectedOptionId = (int) ($submittedAnswers[$question->id] ?? 0);

                if ($selectedOptionId > 0 && !$question->options->contains('id', $selectedOptionId)) {
                    $selectedOptionId = 0;
                }

                $isCorrect = $selectedOptionId > 0 && $selectedOptionId === $correctOptionId;
                if ($isCorrect) {
                    $score += (int) ($question->marks ?? 0);
                }

                CbtAnswer::updateOrCreate(
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

        $attempt->refresh();

        return response()->json([
            'message' => 'Exam submitted successfully.',
            'score' => $attempt->exam->show_score ? $attempt->score : null,
            'max_score' => $attempt->exam->show_score ? $attempt->max_score : null,
            'percent' => $attempt->exam->show_score ? $attempt->percent : null,
        ]);
    }
}
