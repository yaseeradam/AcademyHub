<?php

namespace App\Livewire\Student;

use App\Models\AcademicTerm;
use App\Models\ResultPublication;
use App\Models\Score;
use App\Models\Student;
use Livewire\Component;

class Results extends Component
{
    public string $selectedSession = '';
    public int $selectedTerm = 1;

    private ?Student $student = null;

    public function mount(): void
    {
        $student = $this->getStudent();
        if (! $student) {
            redirect()->route('login');
            return;
        }

        $this->selectedSession = AcademicTerm::activeSessionName() ?? config('academyhub.current_session', '');
        $this->selectedTerm    = AcademicTerm::activeTermNumber();
    }

    private function getStudent(): ?Student
    {
        if ($this->student) return $this->student;
        $this->student = Student::with('schoolClass')->find(session('student_id'));
        return $this->student;
    }

    public function render()
    {
        $student = $this->getStudent();
        abort_unless((bool) $student, 403);

        // All sessions this student has scores in
        $sessions = Score::where('student_id', $student->id)
            ->distinct()->orderBy('session')->pluck('session');

        // Check if results are published for this class/term/session
        $published = ResultPublication::where('class_id', $student->class_id)
            ->where('term', $this->selectedTerm)
            ->where('session', $this->selectedSession)
            ->whereNotNull('published_at')
            ->exists();

        $scores = collect();
        $classSize = 0;
        $overallPosition = null;

        if ($published) {
            $scores = Score::where('student_id', $student->id)
                ->where('session', $this->selectedSession)
                ->where('term', $this->selectedTerm)
                ->with('subject')
                ->orderBy('subject_id')
                ->get();

            // Overall position: rank by sum of totals across all students in same class/term/session
            if ($scores->isNotEmpty()) {
                $classSize = Student::where('class_id', $student->class_id)
                    ->where('status', 'Active')->count();

                $myTotal = $scores->sum('total');

                $higherCount = Score::where('class_id', $student->class_id)
                    ->where('session', $this->selectedSession)
                    ->where('term', $this->selectedTerm)
                    ->selectRaw('student_id, SUM(total) as grand_total')
                    ->groupBy('student_id')
                    ->havingRaw('SUM(total) > ?', [$myTotal])
                    ->count();

                $overallPosition = $higherCount + 1;
            }
        }

        // Stats
        $totalSubjects  = $scores->count();
        $average        = $totalSubjects > 0 ? round($scores->avg('total'), 1) : 0;
        $highest        = $scores->max('total');
        $lowest         = $scores->min('total');
        $passed         = $scores->whereNotIn('grade', ['F', 'E'])->count();
        $failed         = $scores->whereIn('grade', ['F', 'E'])->count();

        $maxTotal = max(1,
            (int) config('academyhub.results_ca1_max', 20) +
            (int) config('academyhub.results_ca2_max', 20) +
            (int) config('academyhub.results_exam_max', 60)
        );

        return view('livewire.student.results', compact(
            'student', 'sessions', 'scores', 'published',
            'totalSubjects', 'average', 'highest', 'lowest',
            'passed', 'failed', 'classSize', 'overallPosition', 'maxTotal'
        ))->layout('layouts.student');
    }
}
