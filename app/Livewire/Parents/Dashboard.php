<?php

namespace App\Livewire\Parents;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\Homework;
use App\Models\ResultPublication;
use App\Models\Score;
use App\Models\Student;
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
    public int $term = 1;
    public string $session = '';
    public string $activeTab = 'overview';

    public function mount(): void
    {
        $this->term    = AcademicTerm::activeTermNumber();
        $this->session = AcademicSession::activeName() ?? $this->defaultSession();

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
        if (! $this->selectedChildId) return null;
        return $this->children->firstWhere('id', $this->selectedChildId);
    }

    #[Computed]
    public function resultsPublished(): bool
    {
        if (! $this->selectedChild) return false;
        return ResultPublication::where('class_id', $this->selectedChild->class_id)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->whereNotNull('published_at')
            ->exists();
    }

    #[Computed]
    public function scores(): Collection
    {
        if (! $this->selectedChild || ! $this->resultsPublished) return collect();
        return Score::where('student_id', $this->selectedChild->id)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->with('subject')
            ->orderBy('subject_id')
            ->get();
    }

    #[Computed]
    public function performanceStats(): array
    {
        $scores = $this->scores;
        if ($scores->isEmpty()) return ['average' => 0, 'subjects' => 0, 'passed' => 0, 'failed' => 0, 'position' => null, 'classSize' => 0];

        $maxTotal = max(1,
            (int) config('academyhub.results_ca1_max', 20) +
            (int) config('academyhub.results_ca2_max', 20) +
            (int) config('academyhub.results_exam_max', 60)
        );

        $average   = round($scores->avg('total'), 1);
        $passed    = $scores->whereNotIn('grade', ['F', 'E'])->count();
        $failed    = $scores->whereIn('grade', ['F', 'E'])->count();
        $myTotal   = $scores->sum('total');

        $classSize = Student::where('class_id', $this->selectedChild->class_id)->where('status', 'Active')->count();
        $higher    = Score::where('class_id', $this->selectedChild->class_id)
            ->where('session', $this->session)->where('term', $this->term)
            ->selectRaw('student_id, SUM(total) as grand_total')
            ->groupBy('student_id')
            ->havingRaw('SUM(total) > ?', [$myTotal])
            ->count();
        $position = $higher + 1;

        return compact('average', 'passed', 'failed', 'position', 'classSize', 'maxTotal') + ['subjects' => $scores->count()];
    }

    #[Computed]
    public function attendance(): array
    {
        if (! $this->selectedChild) return ['present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0, 'rate' => 0];

        $marks = AttendanceMark::where('student_id', $this->selectedChild->id)
            ->whereHas('sheet', fn ($q) => $q->where('term', $this->term)->where('session', $this->session))
            ->get();

        $present = $marks->where('status', 'Present')->count();
        $absent  = $marks->where('status', 'Absent')->count();
        $late    = $marks->where('status', 'Late')->count();
        $total   = $marks->count();
        $rate    = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        return compact('present', 'absent', 'late', 'total', 'rate');
    }

    #[Computed]
    public function fees(): array
    {
        if (! $this->selectedChild) return ['paid' => 0, 'outstanding' => 0, 'total' => 0];

        $txns       = Transaction::where('student_id', $this->selectedChild->id)
            ->where('session', $this->session)->where('is_void', false)->get();
        $paid       = $txns->where('type', 'Payment')->sum('amount');
        $charges    = $txns->where('type', 'Charge')->sum('amount');
        $outstanding = max(0, $charges - $paid);

        return compact('paid', 'outstanding') + ['total' => $charges];
    }

    #[Computed]
    public function homework(): Collection
    {
        if (! $this->selectedChild) return collect();

        return Homework::where('class_id', $this->selectedChild->class_id)
            ->where(fn ($q) => $q->whereNull('section_id')->orWhere('section_id', $this->selectedChild->section_id))
            ->with(['subject', 'submissions' => fn ($q) => $q->where('student_id', $this->selectedChild->id)])
            ->orderByDesc('due_date')
            ->limit(6)
            ->get();
    }

    #[Computed]
    public function recentAttendance(): Collection
    {
        if (! $this->selectedChild) return collect();

        return AttendanceMark::where('student_id', $this->selectedChild->id)
            ->with('sheet')
            ->whereHas('sheet')
            ->get()
            ->sortByDesc(fn ($m) => $m->sheet->date)
            ->take(7);
    }

    public function selectChild(int $id): void
    {
        $this->selectedChildId = $id;
        $this->activeTab = 'overview';
    }

    #[Computed]
    public function announcements(): Collection
    {
        return \App\Models\Announcement::query()
            ->whereIn('audience', ['parent', 'all'])
            ->latest('created_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function timetable(): Collection
    {
        if (! $this->selectedChild) return collect();
        return \App\Models\TimetableEntry::where('class_id', $this->selectedChild->class_id)
            ->with(['subject', 'teacher'])
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->get();
    }

    #[Computed]
    public function transactions(): Collection
    {
        if (! $this->selectedChild) return collect();
        return Transaction::where('student_id', $this->selectedChild->id)
            ->where('session', $this->session)
            ->orderByDesc('date')
            ->get();
    }

    #[Computed]
    public function classTeachers(): Collection
    {
        if (! $this->selectedChild) return collect();
        return \App\Models\SubjectAllocation::where('class_id', $this->selectedChild->class_id)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->filter()
            ->unique('id');
    }

    private function defaultSession(): string
    {
        $y = (int) now()->format('Y');
        return "{$y}/".($y + 1);
    }

    public function render()
    {
        abort_unless(auth()->user()?->isParent(), 403);
        return view('livewire.parents.dashboard');
    }
}
