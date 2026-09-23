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
    public ?int $selectedTopicSubjectId = null;

    public function mount(): void
    {
        $tenant = auth()->user()?->tenant;
        if ($tenant && $tenant->isSubscriptionExpired()) {
            abort(403, 'Your school\'s subscription has expired. Access to Parent Portal is disabled.');
        }

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
        $tenant = auth()->user()?->tenant;
        if (!$tenant) {
            return collect();
        }

        $dbComponent = $tenant->activeMarketplaceComponents()->where('slug', 'student-dashboard')->first();
        if (!$dbComponent) {
            return collect();
        }

        $allowedClassIds = $dbComponent->pivot->allowed_class_ids ?? [];
        if (is_string($allowedClassIds)) {
            $allowedClassIds = json_decode($allowedClassIds, true) ?: [];
        }
        $allowedClassIds = is_array($allowedClassIds) ? $allowedClassIds : [];

        return auth()->user()->students()
            ->whereIn('class_id', $allowedClassIds)
            ->with(['schoolClass', 'section', 'user', 'scores'])
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
        $this->selectedTopicSubjectId = null;
        $this->activeTab = 'overview';
    }

    #[Computed]
    public function childSubjects(): Collection
    {
        if (! $this->selectedChild) return collect();
        return SchoolClass::allSubjectsForClass($this->selectedChild->class_id);
    }

    #[Computed]
    public function subjectTopics(): Collection
    {
        if (! $this->selectedChild) return collect();

        $query = \App\Models\SubjectTopic::query()
            ->where('class_id', $this->selectedChild->class_id)
            ->where('term', $this->term);

        if (! empty($this->session)) {
            $query->where('session', $this->session);
        }

        if ($this->selectedTopicSubjectId) {
            $query->where('subject_id', $this->selectedTopicSubjectId);
        }

        return $query->with('subject')
            ->orderByRaw('week_number IS NULL, week_number ASC')
            ->orderBy('id', 'asc')
            ->get();
    }

    #[Computed]
    public function topicStats(): array
    {
        $topics = $this->subjectTopics;
        $total = $topics->count();
        $completed = $topics->where('status', 'completed')->count();
        $inProgress = $topics->where('status', 'in_progress')->count();
        $upcoming = $topics->where('status', 'upcoming')->count();
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return compact('total', 'completed', 'inProgress', 'upcoming', 'percent');
    }

    #[Computed]
    public function curriculumDocument(): ?\App\Models\CurriculumDocument
    {
        if (! $this->selectedChild || ! $this->selectedTopicSubjectId) return null;

        return \App\Models\CurriculumDocument::forClassSubjectTerm(
            $this->selectedChild->class_id,
            $this->selectedTopicSubjectId,
            $this->term,
            $this->session
        )->latest()->first();
    }

    #[Computed]
    public function classCurriculumDocuments(): Collection
    {
        if (! $this->selectedChild) return collect();

        return \App\Models\CurriculumDocument::query()
            ->where('class_id', $this->selectedChild->class_id)
            ->where('term', $this->term)
            ->when(! empty($this->session), fn ($q) => $q->where('session', $this->session))
            ->with('subject')
            ->get();
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
        if (! $this->selectedChild || ! $this->selectedChild->class_id) {
            return collect();
        }

        $classId = (int) $this->selectedChild->class_id;

        // 1. Get teachers via Subject Allocations
        $allocations = \App\Models\SubjectAllocation::where('class_id', $classId)
            ->with(['teacher', 'subject'])
            ->get();

        $teachers = $allocations->groupBy('teacher_id')->map(function ($group) {
            $teacher = $group->first()->teacher;
            if (!$teacher) {
                return null;
            }
            $teacher->assigned_subjects = $group->pluck('subject.name')->filter()->unique()->values()->all();
            return $teacher;
        })->filter()->values();

        // 2. Also check if any timetable entries include teachers not in subject allocations
        $timetableTeachers = \App\Models\TimetableEntry::where('class_id', $classId)
            ->whereNotNull('teacher_id')
            ->with(['teacher', 'subject'])
            ->get();

        $existingTeacherIds = $teachers->pluck('id')->all();
        $missingGroups = $timetableTeachers->whereNotIn('teacher_id', $existingTeacherIds)->groupBy('teacher_id');

        foreach ($missingGroups as $tGroup) {
            $t = $tGroup->first()->teacher;
            if ($t) {
                $t->assigned_subjects = $tGroup->pluck('subject.name')->filter()->unique()->values()->all();
                if (empty($t->assigned_subjects)) {
                    $t->assigned_subjects = ['Class Instructor'];
                }
                $teachers->push($t);
            }
        }

        // 3. Fallback: If no teachers found for this class, check if any teachers are marked as class teacher in the school
        if ($teachers->isEmpty()) {
            $designated = \App\Models\User::where('role', 'teacher')
                ->where('is_class_teacher', true)
                ->where('is_active', true)
                ->get()
                ->map(function ($t) {
                    $t->assigned_subjects = ['Class Teacher'];
                    return $t;
                });
            if ($designated->isNotEmpty()) {
                $teachers = $designated;
            }
        }

        return $teachers;
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
