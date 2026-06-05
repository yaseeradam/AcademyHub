<?php

namespace App\Livewire\Student;

use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\Student;
use Carbon\Carbon;
use Livewire\Component;

class Attendance extends Component
{
    public int $selectedMonth;
    public int $selectedYear;

    private ?Student $student = null;

    public function mount(): void
    {
        if (! session('student_id')) {
            redirect()->route('login');
            return;
        }
        $this->selectedMonth = now()->month;
        $this->selectedYear  = now()->year;
    }

    private function getStudent(): ?Student
    {
        if ($this->student) return $this->student;
        $this->student = Student::with('schoolClass')->find(session('student_id'));
        return $this->student;
    }

    public function previousMonth(): void
    {
        $d = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedMonth = $d->month;
        $this->selectedYear  = $d->year;
    }

    public function nextMonth(): void
    {
        $d = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedMonth = $d->month;
        $this->selectedYear  = $d->year;
    }

    public function render()
    {
        $student = $this->getStudent();
        abort_unless((bool) $student, 403);

        $currentSession = AcademicTerm::activeSessionName() ?? config('academyhub.current_session', '');
        $currentTerm    = AcademicTerm::activeTermNumber();

        // ── Load ALL marks with sheets for this student ──────────────────
        $allMarksWithSheets = AttendanceMark::where('student_id', $student->id)
            ->with('sheet')
            ->get();

        // ── Term-wide stats (filter by session/term) ─────────────────────
        $termMarks = $allMarksWithSheets->filter(fn ($m) => 
            $m->sheet && 
            $m->sheet->session === $currentSession && 
            (int) $m->sheet->term === $currentTerm
        );

        $total   = $termMarks->count();
        $present = $termMarks->where('status', 'Present')->count();
        $absent  = $termMarks->where('status', 'Absent')->count();
        $late    = $termMarks->where('status', 'Late')->count();
        $rate    = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        // ── Current streak (consecutive present days up to today) ────────
        $sortedMarks = $allMarksWithSheets
            ->filter(fn ($m) => $m->sheet)
            ->sortByDesc(fn ($m) => $m->sheet->date);

        $streak = 0;
        foreach ($sortedMarks as $m) {
            if ($m->status === 'Present') $streak++;
            else break;
        }

        // ── Monthly calendar data ────────────────────────────────────────
        $monthStart = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfDay();
        $monthEnd   = $monthStart->copy()->endOfMonth()->endOfDay();

        $monthMarks = $allMarksWithSheets
            ->filter(fn ($m) => 
                $m->sheet && 
                $m->sheet->date >= $monthStart && 
                $m->sheet->date <= $monthEnd
            )
            ->keyBy(fn ($m) => $m->sheet->date->format('Y-m-d'));

        // Monthly mini-stats
        $mPresent = $monthMarks->where('status', 'Present')->count();
        $mAbsent  = $monthMarks->where('status', 'Absent')->count();
        $mLate    = $monthMarks->where('status', 'Late')->count();
        $mTotal   = $monthMarks->count();
        $mRate    = $mTotal > 0 ? round(($mPresent / $mTotal) * 100, 1) : 0;

        return view('livewire.student.attendance', [
            'student'        => $student,
            'total'          => $total,
            'present'        => $present,
            'absent'         => $absent,
            'late'           => $late,
            'rate'           => $rate,
            'streak'         => $streak,
            'monthMarks'     => $monthMarks,
            'mPresent'       => $mPresent,
            'mAbsent'        => $mAbsent,
            'mLate'          => $mLate,
            'mTotal'         => $mTotal,
            'mRate'          => $mRate,
            'currentSession' => $currentSession,
            'currentTerm'    => $currentTerm,
            'selectedMonth'  => $this->selectedMonth,
            'selectedYear'   => $this->selectedYear,
        ])->layout('layouts.student');
    }
}
