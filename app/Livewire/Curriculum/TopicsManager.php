<?php

namespace App\Livewire\Curriculum;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\SubjectTopic;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Curriculum & Scheme of Work')]
class TopicsManager extends Component
{
    public ?int $classId = null;
    public ?int $subjectId = null;
    public int $term = 1;
    public string $session = '';

    public string $search = '';
    public string $statusFilter = 'all';

    // Modal Form fields
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $weekNumber = 1;
    public string $title = '';
    public string $learningObjectives = '';
    public string $status = 'upcoming';

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, ['admin', 'teacher', 'proprietor'], true), 403);

        $this->term = AcademicTerm::activeTermNumber() ?? 1;
        $this->session = AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        $firstClass = $this->classes->first();
        if ($firstClass) {
            $this->classId = $firstClass->id;
            $firstSubject = $this->subjects->first();
            if ($firstSubject) {
                $this->subjectId = $firstSubject->id;
            }
        }
    }

    public function updatedClassId(): void
    {
        $firstSubject = $this->subjects->first();
        $this->subjectId = $firstSubject?->id;
    }

    #[Computed]
    public function classes(): Collection
    {
        $user = auth()->user();
        if ($user?->role === 'teacher') {
            $classIds = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->pluck('class_id')
                ->unique();

            return SchoolClass::query()
                ->whereIn('id', $classIds)
                ->orderBy('level')
                ->get();
        }

        return SchoolClass::query()->orderBy('level')->get();
    }

    #[Computed]
    public function subjects(): Collection
    {
        if (! $this->classId) {
            return collect();
        }

        $user = auth()->user();
        if ($user?->role === 'teacher') {
            $subjectIds = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->where('class_id', $this->classId)
                ->pluck('subject_id')
                ->unique();

            return Subject::query()
                ->whereIn('id', $subjectIds)
                ->orderBy('name')
                ->get();
        }

        return SchoolClass::allSubjectsForClass($this->classId);
    }

    #[Computed]
    public function selectedClass(): ?SchoolClass
    {
        return $this->classId ? SchoolClass::find($this->classId) : null;
    }

    #[Computed]
    public function selectedSubject(): ?Subject
    {
        return $this->subjectId ? Subject::find($this->subjectId) : null;
    }

    #[Computed]
    public function topics(): Collection
    {
        if (! $this->classId || ! $this->subjectId) {
            return collect();
        }

        $query = SubjectTopic::query()
            ->where('class_id', $this->classId)
            ->where('subject_id', $this->subjectId)
            ->where('term', $this->term);

        if (! empty($this->session)) {
            $query->where('session', $this->session);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (! empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('learning_objectives', 'like', $term);
            });
        }

        return $query->orderByRaw('week_number IS NULL, week_number ASC')
                     ->orderBy('id', 'asc')
                     ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $all = SubjectTopic::query()
            ->where('class_id', $this->classId)
            ->where('subject_id', $this->subjectId)
            ->where('term', $this->term)
            ->when(! empty($this->session), fn ($q) => $q->where('session', $this->session))
            ->get();

        $total = $all->count();
        $completed = $all->where('status', 'completed')->count();
        $inProgress = $all->where('status', 'in_progress')->count();
        $upcoming = $all->where('status', 'upcoming')->count();
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return compact('total', 'completed', 'inProgress', 'upcoming', 'percent');
    }

    public function openCreateModal(): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $this->editingId = null;
        $maxWeek = SubjectTopic::query()
            ->where('class_id', $this->classId)
            ->where('subject_id', $this->subjectId)
            ->where('term', $this->term)
            ->max('week_number');

        $this->weekNumber = $maxWeek ? $maxWeek + 1 : 1;
        $this->title = '';
        $this->learningObjectives = '';
        $this->status = 'upcoming';
        $this->showModal = true;
    }

    public function editTopic(int $id): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $topic = SubjectTopic::findOrFail($id);
        $this->editingId = $topic->id;
        $this->weekNumber = $topic->week_number;
        $this->title = $topic->title;
        $this->learningObjectives = (string) $topic->learning_objectives;
        $this->status = $topic->status;
        $this->showModal = true;
    }

    public function saveTopic(): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $this->validate([
            'classId'            => 'required|exists:classes,id',
            'subjectId'          => 'required|exists:subjects,id',
            'term'               => 'required|integer|between:1,3',
            'title'              => 'required|string|max:255',
            'weekNumber'         => 'nullable|integer|min:1|max:52',
            'learningObjectives' => 'nullable|string',
            'status'             => 'required|in:upcoming,in_progress,completed',
        ]);

        SubjectTopic::updateOrCreate(
            ['id' => $this->editingId],
            [
                'class_id'            => $this->classId,
                'subject_id'          => $this->subjectId,
                'term'                => $this->term,
                'session'             => $this->session,
                'week_number'         => $this->weekNumber,
                'title'               => trim($this->title),
                'learning_objectives' => trim($this->learningObjectives) ?: null,
                'status'              => $this->status,
                'created_by'          => auth()->id(),
            ]
        );

        $this->showModal = false;
        $this->dispatch('alert', message: $this->editingId ? 'Topic updated successfully!' : 'New topic added to curriculum!', type: 'success');
        $this->editingId = null;
    }

    public function toggleStatus(int $id): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $topic = SubjectTopic::findOrFail($id);
        $nextStatus = match ($topic->status) {
            'upcoming'    => 'in_progress',
            'in_progress' => 'completed',
            'completed'   => 'upcoming',
            default       => 'upcoming',
        };

        $topic->status = $nextStatus;
        $topic->save();

        $this->dispatch('alert', message: "Topic marked as {$nextStatus}!", type: 'success');
    }

    public function deleteTopic(int $id): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $topic = SubjectTopic::findOrFail($id);
        $topic->delete();

        $this->dispatch('alert', message: 'Topic deleted from curriculum.', type: 'info');
    }

    public function render()
    {
        return view('livewire.curriculum.topics-manager');
    }
}
