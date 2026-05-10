<?php

namespace App\Livewire\Student;

use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\Student;
use Livewire\Component;

class Exams extends Component
{
    public ?int $viewingAttemptId = null;

    public function enterExamCode($code)
    {
        $code = trim($code);
        if ($code === '') {
            $this->dispatch('alert', message: 'Enter an exam code', type: 'error');
            return;
        }

        return redirect()->route('cbt.student', ['code' => $code]);
    }

    public function viewResult(int $attemptId): void
    {
        $this->viewingAttemptId = $this->viewingAttemptId === $attemptId ? null : $attemptId;
    }

    public function render()
    {
        $studentId = session('student_id');
        $student = Student::find($studentId);
        abort_unless((bool) $student, 403);

        $exams = CbtExam::query()
            ->whereIn('status', ['live', 'ended'])
            ->where('class_id', $student->class_id)
            ->with([
                'subject',
                'questions:id,exam_id,type,prompt,marks',
                'attempts' => fn ($q) => $q->where('student_id', $student->id)
                    ->with(['answers.question']),
            ])
            ->orderByRaw('CASE WHEN ends_at IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('ends_at', 'asc')
            ->get();

        $queue = [];

        foreach ($exams as $exam) {
            $attempt   = $exam->attempts->first();
            $hasTheory = $exam->questions->contains('type', 'theory');
            $released  = (bool) $exam->results_released_at;
            $showScore = $exam->show_score && $released && !$hasTheory;
            $showScoreWithTheory = $exam->show_score && $released && $hasTheory
                && $attempt && $attempt->theory_status === 'marked';

            $status = 'pending';
            if ($attempt) {
                if ($attempt->submitted_at || $attempt->terminated_at) {
                    $status = 'completed';
                } elseif ($attempt->started_at) {
                    $status = 'in_progress';
                }
            }

            $canViewMarks = $status === 'completed'
                && ($showScore || $showScoreWithTheory);

            $queue[] = [
                'id'           => $exam->id,
                'title'        => $exam->title,
                'subject'      => $exam->subject?->name ?? 'General',
                'duration'     => $exam->duration_minutes,
                'starts_at'    => $exam->starts_at,
                'ends_at'      => $exam->ends_at,
                'access_code'  => $exam->access_code,
                'status'       => $status,
                'has_theory'   => $hasTheory,
                'theory_status'=> $attempt?->theory_status,
                'score'        => $canViewMarks ? $attempt->score : null,
                'max_score'    => $canViewMarks ? $attempt->max_score : null,
                'percent'      => $canViewMarks ? $attempt->percent : null,
                'score_pending'=> $status === 'completed' && $exam->show_score && !$canViewMarks,
                'can_view_marks' => $canViewMarks,
                'attempt_id'   => $attempt?->id,
                'attempt'      => $canViewMarks ? $attempt : null,
                'questions'    => $canViewMarks ? $exam->questions : collect(),
            ];
        }

        usort($queue, function ($a, $b) {
            $order = ['in_progress' => 0, 'pending' => 1, 'completed' => 2];
            if ($order[$a['status']] !== $order[$b['status']]) {
                return $order[$a['status']] <=> $order[$b['status']];
            }
            if ($a['status'] === 'pending') {
                if ($a['ends_at'] && $b['ends_at']) return $a['ends_at'] <=> $b['ends_at'];
                if ($a['ends_at']) return -1;
                if ($b['ends_at']) return 1;
            }
            return 0;
        });

        // Load detailed attempt for the one being viewed
        $viewingAttempt = null;
        if ($this->viewingAttemptId) {
            $viewingAttempt = CbtAttempt::with([
                'exam.questions.options',
                'answers',
            ])->find($this->viewingAttemptId);

            // Security: ensure it belongs to this student
            if ($viewingAttempt && (int) $viewingAttempt->student_id !== (int) $student->id) {
                $viewingAttempt = null;
                $this->viewingAttemptId = null;
            }
        }

        return view('livewire.student.exams', [
            'exams'          => $queue,
            'viewingAttempt' => $viewingAttempt,
        ])->layout('layouts.student');
    }
}
