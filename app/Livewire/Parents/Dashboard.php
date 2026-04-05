<?php

namespace App\Livewire\Parents;

use App\Models\Score;
use App\Models\Student;
use App\Models\AcademicTerm;
use App\Models\AcademicSession;
use App\Models\AttendanceMark;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Parent Dashboard')]
class Dashboard extends Component
{
    public ?int $selectedChildId = null;
    public ?int $term = null;
    public string $session = '';

    public function mount(): void
    {
        $this->term = $this->term ?: AcademicTerm::activeTermNumber();
        $this->session = $this->session ?: $this->defaultSession();
        
        // Auto-select first child if only one
        $children = $this->children;
        if ($children->count() === 1) {
            $this->selectedChildId = $children->first()->id;
        }
    }

    #[Computed]
    public function children(): Collection
    {
        return auth()->user()->students()
            ->with(['schoolClass', 'section'])
            ->orderBy('first_name')
            ->get();
    }

    #[Computed]
    public function selectedChild(): ?Student
    {
        if (!$this->selectedChildId) {
            return null;
        }

        return $this->children->firstWhere('id', $this->selectedChildId);
    }

    #[Computed]
    public function childScores(): Collection
    {
        if (!$this->selectedChild) {
            return collect();
        }

        return Score::query()
            ->where('student_id', $this->selectedChild->id)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function childAttendance(): array
    {
        if (!$this->selectedChild) {
            return ['present' => 0, 'absent' => 0, 'total' => 0, 'percentage' => 0];
        }

        $attendance = AttendanceMark::query()
            ->where('student_id', $this->selectedChild->id)
            ->whereHas('attendanceSheet', function ($query) {
                $query->where('term', $this->term)
                      ->where('session', $this->session);
            })
            ->get();

        $present = $attendance->where('status', 'Present')->count();
        $absent = $attendance->where('status', 'Absent')->count();
        $total = $attendance->count();
        $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        return [
            'present' => $present,
            'absent' => $absent,
            'total' => $total,
            'percentage' => $percentage
        ];
    }

    #[Computed]
    public function childFees(): array
    {
        if (!$this->selectedChild) {
            return ['paid' => 0, 'outstanding' => 0, 'total' => 0];
        }

        $transactions = Transaction::query()
            ->where('student_id', $this->selectedChild->id)
            ->where('session', $this->session)
            ->where('is_void', false)
            ->get();

        $paid = $transactions->where('type', 'Payment')->sum('amount');
        $charges = $transactions->where('type', 'Charge')->sum('amount');
        $outstanding = max(0, $charges - $paid);

        return [
            'paid' => $paid,
            'outstanding' => $outstanding,
            'total' => $charges
        ];
    }

    #[Computed]
    public function childPerformanceStats(): array
    {
        if (!$this->selectedChild || $this->childScores->isEmpty()) {
            return ['average' => 0, 'total' => 0, 'subjects' => 0, 'grade' => 'N/A'];
        }

        $scores = $this->childScores;
        $total = $scores->sum('total');
        $subjects = $scores->count();
        $average = $subjects > 0 ? round($total / $subjects, 1) : 0;
        
        $grade = $this->calculateGrade($average);

        return [
            'average' => $average,
            'total' => $total,
            'subjects' => $subjects,
            'grade' => $grade
        ];
    }

    private function calculateGrade(float $average): string
    {
        if ($average >= 90) return 'A+';
        if ($average >= 80) return 'A';
        if ($average >= 70) return 'B';
        if ($average >= 60) return 'C';
        if ($average >= 50) return 'D';
        return 'F';
    }

    public function getGrade(float $score): string
    {
        return $this->calculateGrade($score);
    }

    private function defaultSession(): string
    {
        $active = AcademicSession::activeName();
        if ($active) {
            return $active;
        }

        $year = (int) now()->format('Y');
        $next = $year + 1;

        return "{$year}/{$next}";
    }

    public function selectChild(int $childId): void
    {
        $this->selectedChildId = $childId;
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && $user->isParent(), 403);

        return view('livewire.parents.dashboard');
    }
}