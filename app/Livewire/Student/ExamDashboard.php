<?php

namespace App\Livewire\Student;

use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Exams')]
class ExamDashboard extends Component
{
    public function mount(): void
    {
        $studentId = session('student_id');
        if (!$studentId) {
            $user = auth()->user();
            abort_unless($user && in_array($user->role, ['admin', 'teacher', 'parent'], true) === false, 403);
        }
    }

    #[Computed]
    public function student(): ?Student
    {
        $studentId = session('student_id');
        if ($studentId) {
            return Student::with(['schoolClass'])->find($studentId);
        }

        $user = auth()->user();
        if (!$user) return null;

        return Student::query()
            ->where('email', $user->email)
            ->orWhere('admission_number', $user->admission_number ?? '')
            ->first();
    }

    #[Computed]
    public function liveExams()
    {
        $student = $this->student;
        if (!$student) return collect();

        return CbtExam::query()
            ->with(['subject:id,name', 'schoolClass:id,name'])
            ->where('class_id', $student->class_id)
            ->where('status', 'live')
            ->whereNotNull('published_at')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now()->subMinutes(5));
            })
            ->orderByRaw("CASE WHEN ends_at IS NOT NULL THEN 0 ELSE 1 END") // End-dated first (most urgent)
            ->orderBy('ends_at')
            ->get()
            ->map(function (CbtExam $exam) use ($student) {
                $attempt = CbtAttempt::query()
                    ->where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->first();

                $state = 'pending';
                if ($attempt) {
                    if ($attempt->submitted_at || $attempt->terminated_at) {
                        $state = 'completed';
                    } elseif ($attempt->started_at) {
                        $state = 'in_progress';
                    }
                }

                $urgency = null;
                if ($exam->ends_at) {
                    $mins = now()->diffInMinutes($exam->ends_at, false);
                    if ($mins > 0 && $mins <= 30) {
                        $urgency = "Ends in {$mins} min";
                    }
                }

                return [
                    'exam' => $exam,
                    'attempt' => $attempt,
                    'state' => $state,
                    'urgency' => $urgency,
                ];
            });
    }

    #[Computed]
    public function completedExams()
    {
        $student = $this->student;
        if (!$student) return collect();

        return CbtAttempt::query()
            ->with(['exam:id,title,subject_id,class_id,duration_minutes,show_score,results_released_at', 'exam.subject:id,name'])
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.student.exam-dashboard');
    }
}
