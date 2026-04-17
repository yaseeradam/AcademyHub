<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\CbtExam;
use App\Models\CbtAttempt;

class Exams extends Component
{
    public function enterExamCode($code)
    {
        $code = trim($code);
        if ($code === '') {
            $this->dispatch('alert', message: 'Enter an exam code', type: 'error');
            return;
        }

        return redirect()->route('cbt.student', ['code' => $code]);
    }

    public function render()
    {
        $studentId = session('student_id');
        if (!$studentId) {
            $user = auth()->user();
            if ($user && $user->role === 'student') {
                $studentId = \App\Models\Student::where('admission_number', $user->email)->first()?->id;
            }
        }

        $student = \App\Models\Student::find($studentId);
        abort_unless((bool) $student, 403);

        $now = now();

        // Get all live exams matching student's class
        $exams = CbtExam::query()
            ->where('status', 'live')
            ->where('class_id', $student->class_id)
            ->with(['subject', 'attempts' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }, 'questions' => function ($query) {
                $query->select('exam_id', 'id', 'type');
            }])
            ->orderByRaw('CASE WHEN ends_at IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('ends_at', 'asc')
            ->get();

        $queue = [];

        foreach ($exams as $exam) {
            $attempt = $exam->attempts->first();
            $status = 'pending';
            $hasTheory = $exam->questions->where('type', 'theory')->isNotEmpty();
            $theoryStatus = $attempt ? ($attempt->theory_status ?? 'pending') : null;
            $needsReview = false;

            if ($attempt) {
                if ($attempt->submitted_at || $attempt->terminated_at) {
                    $status = 'completed';
                    if ($hasTheory && $theoryStatus !== 'marked') {
                        $needsReview = true;
                    }
                } elseif ($attempt->started_at) {
                    $status = 'in_progress';
                }
            }

            $queue[] = [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject' => $exam->subject?->name ?? 'General',
                'duration' => $exam->duration_minutes,
                'starts_at' => $exam->starts_at,
                'ends_at' => $exam->ends_at,
                'access_code' => $exam->access_code,
                'status' => $status,
                'needs_review' => $needsReview,
                // Show score only if show_score=true AND results have been released
                'score' => ($status === 'completed' && !$needsReview && $exam->show_score && $exam->results_released_at) ? $attempt->score : null,
                'max_score' => ($status === 'completed' && !$needsReview && $exam->show_score && $exam->results_released_at) ? $attempt->max_score : null,
                'score_pending' => $status === 'completed' && $exam->show_score && !$exam->results_released_at,
            ];
        }

        // Prioritize in-progress first, then pending ending soon, then pending without end date, then completed
        usort($queue, function ($a, $b) {
            $order = [
                'in_progress' => 0,
                'pending' => 1,
                'completed' => 2,
            ];
            
            if ($order[$a['status']] !== $order[$b['status']]) {
                return $order[$a['status']] <=> $order[$b['status']];
            }

            if ($a['status'] === 'pending') {
                if ($a['ends_at'] && $b['ends_at']) {
                    return $a['ends_at'] <=> $b['ends_at'];
                }
                if ($a['ends_at']) return -1;
                if ($b['ends_at']) return 1;
            }

            return 0;
        });

        return view('livewire.student.exams', [
            'exams' => $queue,
        ])->layout('layouts.student');
    }
}
