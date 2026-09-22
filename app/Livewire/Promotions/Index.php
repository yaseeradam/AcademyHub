<?php

namespace App\Livewire\Promotions;

use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Promote Students')]
class Index extends Component
{
    public ?int $fromClassId = null;
    public ?int $fromSectionId = null;
    public ?int $toClassId = null;
    public ?int $toSectionId = null;
    public string $note = '';

    /**
     * @var array<int|string>
     */
    public array $selected = [];

    public bool $selectAll = false;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);
    }

    #[Computed]
    public function classes()
    {
        return SchoolClass::query()->orderBy('level')->get();
    }

    #[Computed]
    public function fromSections()
    {
        if (! $this->fromClassId) {
            return collect();
        }

        return Section::query()->where('class_id', $this->fromClassId)->orderBy('name')->get();
    }

    #[Computed]
    public function toSections()
    {
        if (! $this->toClassId) {
            return collect();
        }

        return Section::query()->where('class_id', $this->toClassId)->orderBy('name')->get();
    }

    #[Computed]
    public function students()
    {
        if (! $this->fromClassId) {
            return collect();
        }

        return Student::query()
            ->with(['schoolClass', 'section', 'user'])
            ->where('class_id', $this->fromClassId)
            ->when($this->fromSectionId, fn ($q) => $q->where('section_id', $this->fromSectionId))
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->get();
    }

    public function updatedFromClassId(): void
    {
        $this->fromSectionId = null;
        $this->selected = [];
        $this->selectAll = false;
        unset($this->fromSections, $this->students);
    }

    public function updatedFromSectionId(): void
    {
        $this->selected = [];
        $this->selectAll = false;
        unset($this->students);
    }

    public function updatedToClassId(): void
    {
        $this->toSectionId = null;
        unset($this->toSections);
    }

    public function toggleSelectAll(): void
    {
        $this->selectAll = ! $this->selectAll;

        if ($this->selectAll) {
            $this->selected = $this->students->pluck('id')->all();
        } else {
            $this->selected = [];
        }
    }

    public function promoteSelected(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        if (! $this->fromClassId || ! $this->toClassId) {
            throw ValidationException::withMessages([
                'fromClassId' => 'Select from class and to class.',
            ]);
        }

        $ids = array_values(array_filter(array_map('intval', $this->selected)));
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => $id > 0)));

        if (count($ids) === 0) {
            throw ValidationException::withMessages([
                'selected' => 'Select at least one student.',
            ]);
        }

        $students = Student::query()->whereIn('id', $ids)->with('section:id,name')->get();
        if ($students->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'selected' => 'Some selected students could not be found.',
            ]);
        }

        $fromClassId = (int) $this->fromClassId;
        $fromSectionId = $this->fromSectionId ? (int) $this->fromSectionId : null;
        $toClassId = (int) $this->toClassId;
        $toSectionId = $this->toSectionId ? (int) $this->toSectionId : null;
        $note = trim($this->note) !== '' ? trim($this->note) : null;

        $toSections = Section::query()
            ->where('class_id', $toClassId)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($toSections->isEmpty()) {
            throw ValidationException::withMessages([
                'toSectionId' => 'Destination class has no sections. Create a section first.',
            ]);
        }

        if ($toSectionId) {
            $belongs = $toSections->contains(fn (Section $s) => $s->id === $toSectionId);
            if (! $belongs) {
                throw ValidationException::withMessages([
                    'toSectionId' => 'Selected section does not belong to destination class.',
                ]);
            }
        }

        $toSectionsByName = $toSections
            ->filter(fn (Section $s) => trim((string) $s->name) !== '')
            ->keyBy(fn (Section $s) => strtolower(trim((string) $s->name)));

        $singleToSectionId = $toSections->count() === 1 ? (int) $toSections->first()->id : null;

        DB::transaction(function () use ($students, $user, $fromClassId, $fromSectionId, $toClassId, $toSectionId, $singleToSectionId, $toSectionsByName, $note) {
            foreach ($students as $student) {
                if ($student->class_id !== $fromClassId) {
                    throw ValidationException::withMessages([
                        'selected' => 'Selected students must belong to the selected "From class".',
                    ]);
                }

                if ($fromSectionId && $student->section_id !== $fromSectionId) {
                    throw ValidationException::withMessages([
                        'selected' => 'Selected students must belong to the selected "From section".',
                    ]);
                }

                $resolvedToSectionId = $toSectionId;
                if (! $resolvedToSectionId) {
                    $fromName = strtolower(trim((string) ($student->section?->name ?? '')));
                    $mapped = $fromName !== '' ? ($toSectionsByName->get($fromName)?->id ?? null) : null;

                    $resolvedToSectionId = $mapped ? (int) $mapped : ($singleToSectionId ?: null);
                }

                if (! $resolvedToSectionId) {
                    throw ValidationException::withMessages([
                        'toSectionId' => 'Select a destination section.',
                    ]);
                }

                $promotion = Promotion::query()->create([
                    'student_id' => $student->id,
                    'from_class_id' => $student->class_id,
                    'from_section_id' => $student->section_id,
                    'to_class_id' => $toClassId,
                    'to_section_id' => $resolvedToSectionId,
                    'promoted_by' => $user->id,
                    'promoted_at' => now(),
                    'note' => $note,
                ]);

                $student->class_id = $toClassId;
                $student->section_id = $resolvedToSectionId;
                $student->save();

                // Clear subject overrides when promoted to new class
                $student->subjectOverrides()->detach();
            }
        });

        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('alert', message: 'Students promoted.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $recent = Promotion::query()
            ->with(['student:id,first_name,last_name,admission_number', 'fromClass:id,name', 'toClass:id,name'])
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('livewire.promotions.index', [
            'recentPromotions' => $recent,
        ]);
    }
}
