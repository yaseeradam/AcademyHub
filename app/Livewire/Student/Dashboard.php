<?php

namespace App\Livewire\Student;

use App\Models\AcademicTerm;
use App\Models\Student;
use App\Models\AttendanceMark;
use App\Models\Score;
use Livewire\Component;

class Dashboard extends Component
{
    public $student;
    public $stats = [];

    public function mount()
    {
        $studentId = session('student_id');
        if (!$studentId) {
            return redirect()->route('login');
        }

        $this->student = Student::with(['schoolClass', 'section'])->find($studentId);
        
        if (!$this->student) {
            session()->forget(['student_id', 'student_name', 'student_admission', 'student_class']);
            return redirect()->route('login');
        }

        $this->loadStats();
    }

    public function loadStats()
    {
        $currentSession = AcademicTerm::activeSessionName() ?? config('myacademy.current_session', '');
        $currentTerm = AcademicTerm::activeTermNumber();

        $this->stats['current_session'] = $currentSession;
        $this->stats['current_term'] = $currentTerm;

        // Attendance stats
        $totalAttendance = AttendanceMark::where('student_id', $this->student->id)
            ->whereHas('sheet', function($query) use ($currentSession, $currentTerm) {
                $query->where('session', $currentSession)
                      ->where('term', $currentTerm);
            })
            ->count();

        $presentCount = AttendanceMark::where('student_id', $this->student->id)
            ->where('status', 'Present')
            ->whereHas('sheet', function($query) use ($currentSession, $currentTerm) {
                $query->where('session', $currentSession)
                      ->where('term', $currentTerm);
            })
            ->count();

        $this->stats['attendance_rate'] = $totalAttendance > 0 
            ? round(($presentCount / $totalAttendance) * 100, 1) 
            : 0;
        $this->stats['total_days'] = $totalAttendance;
        $this->stats['present_days'] = $presentCount;

        // Academic performance
        $scores = Score::where('student_id', $this->student->id)
            ->where('session', $currentSession)
            ->where('term', $currentTerm)
            ->get();

        $this->stats['total_subjects'] = $scores->count();
        $this->stats['average_score'] = $scores->count() > 0 
            ? round($scores->avg('total'), 1) 
            : 0;

        // Grade distribution
        $this->stats['grades'] = $scores->groupBy('grade')->map->count()->toArray();

        // Position in class
        $classScores = Score::where('class_id', $this->student->class_id)
            ->where('session', $currentSession)
            ->where('term', $currentTerm)
            ->selectRaw('student_id, SUM(total) as total_score')
            ->groupBy('student_id')
            ->orderByDesc('total_score')
            ->get();

        $position = $classScores->search(function($item) {
            return $item->student_id == $this->student->id;
        });

        $this->stats['position'] = $position !== false ? $position + 1 : null;
        $this->stats['total_students'] = $classScores->count();

        // Homework stats
        $homework = $this->student->getHomeworkForStudent();
        $this->stats['pending_homework'] = $homework->filter(function($hw) {
            return $hw->submissions->isEmpty() && $hw->due_date >= now();
        })->count();
        
        $this->stats['overdue_homework'] = $homework->filter(function($hw) {
            return $hw->submissions->isEmpty() && $hw->due_date < now();
        })->count();
    }

    public function render()
    {
        return view('livewire.student.dashboard')
            ->layout('layouts.student');
    }
}
