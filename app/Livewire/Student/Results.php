<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\Score;
use Livewire\Component;

class Results extends Component
{
    public $student;
    public $selectedSession;
    public $selectedTerm;
    public $scores;
    public $sessions;
    public $terms = [1, 2, 3];

    public function mount()
    {
        $studentId = session('student_id');
        if (!$studentId) {
            return redirect()->route('login');
        }

        $this->student = Student::with(['schoolClass'])->find($studentId);
        
        $this->selectedSession = config('myacademy.current_session');
        $this->selectedTerm = config('myacademy.current_term');
        
        $this->sessions = Score::where('student_id', $this->student->id)
            ->distinct()
            ->pluck('session')
            ->sort()
            ->values();

        $this->loadScores();
    }

    public function loadScores()
    {
        $this->scores = Score::where('student_id', $this->student->id)
            ->where('session', $this->selectedSession)
            ->where('term', $this->selectedTerm)
            ->with('subject')
            ->orderBy('subject_id')
            ->get();
    }

    public function updatedSelectedSession()
    {
        $this->loadScores();
    }

    public function updatedSelectedTerm()
    {
        $this->loadScores();
    }

    public function render()
    {
        return view('livewire.student.results')
            ->layout('layouts.student');
    }
}
