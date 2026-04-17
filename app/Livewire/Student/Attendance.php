<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\AttendanceMark;
use Livewire\Component;
use Carbon\Carbon;

class Attendance extends Component
{
    public $student;
    public $selectedMonth;
    public $selectedYear;
    public $attendanceRecords;
    public $stats;

    public function mount()
    {
        $studentId = session('student_id');
        if (!$studentId) {
            return redirect()->route('login');
        }

        $this->student = Student::find($studentId);
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        
        $this->loadAttendance();
    }

    public function loadAttendance()
    {
        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $this->attendanceRecords = AttendanceMark::where('student_id', $this->student->id)
            ->whereHas('sheet', function($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->with('sheet')
            ->get()
            ->keyBy(fn($mark) => $mark->sheet->date->format('Y-m-d'));

        $this->calculateStats();
    }

    public function calculateStats()
    {
        $currentSession = config('myacademy.current_session');
        $currentTerm = config('myacademy.current_term');

        $termRecords = AttendanceMark::where('student_id', $this->student->id)
            ->whereHas('sheet', function($query) use ($currentSession, $currentTerm) {
                $query->where('session', $currentSession)
                      ->where('term', $currentTerm);
            })
            ->get();

        $this->stats = [
            'total' => $termRecords->count(),
            'present' => $termRecords->where('status', 'Present')->count(),
            'absent' => $termRecords->where('status', 'Absent')->count(),
            'late' => $termRecords->where('status', 'Late')->count(),
            'rate' => $termRecords->count() > 0 
                ? round(($termRecords->where('status', 'Present')->count() / $termRecords->count()) * 100, 1)
                : 0,
        ];
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
        $this->loadAttendance();
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
        $this->loadAttendance();
    }

    public function render()
    {
        return view('livewire.student.attendance')
            ->layout('layouts.student');
    }
}
