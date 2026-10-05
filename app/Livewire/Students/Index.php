<?php

namespace App\Livewire\Students;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\SubjectAllocation;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('All Students')]
class Index extends Component
{
    use WithPagination;

    public string $classFilter = 'all';
    public string $sectionFilter = 'all';
    public string $statusFilter = 'all';
    public string $search = '';
    public string $sortBy = 'last_name';
    public string $sortDir = 'asc';

    private ?Collection $teacherClassIdsCache = null;

    #[Computed]
    public function classes()
    {
        $user = auth()->user();
        if ($user?->role === 'teacher') {
            $classIds = $this->teacherClassIds();
            if ($classIds->isEmpty()) {
                return collect();
            }

            return SchoolClass::query()
                ->whereIn('id', $classIds)
                ->orderBy('level')
                ->get();
        }

        return SchoolClass::query()->orderBy('level')->get();
    }

    #[Computed]
    public function sections()
    {
        $user = auth()->user();

        if ($user?->role === 'teacher') {
            $scope = $user->teacherClassSectionScope();
            if (empty($scope)) {
                return collect();
            }

            if ($this->classFilter === 'all') {
                $query = Section::query();
                $query->where(function ($q) use ($scope) {
                    foreach ($scope as $classId => $sectionIds) {
                        $q->orWhere(function ($sub) use ($classId, $sectionIds) {
                            $sub->where('class_id', $classId);
                            if (is_array($sectionIds)) {
                                $sub->whereIn('id', $sectionIds);
                            }
                        });
                    }
                });
                return $query->select('name')->distinct()->orderBy('name')->pluck('name');
            }

            $classId = (int) $this->classFilter;
            if (! array_key_exists($classId, $scope)) {
                return collect();
            }

            $query = Section::query()->where('class_id', $classId);
            $sectionIds = $scope[$classId];
            if (is_array($sectionIds)) {
                $query->whereIn('id', $sectionIds);
            }
            return $query->orderBy('name')->get();
        }

        if ($this->classFilter === 'all') {
            return Section::query()
                ->select('name')
                ->distinct()
                ->orderBy('name')
                ->pluck('name');
        }

        return Section::query()
            ->where('class_id', $this->classFilter)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function students()
    {
        $query = Student::query()->with(['schoolClass', 'section', 'user', 'scores']);
        $user = auth()->user();
        $teacherClassIds = null;

        if ($user?->role === 'teacher') {
            $scope = $user->teacherClassSectionScope();
            if (empty($scope)) {
                return Student::query()->whereRaw('1 = 0')->paginate(15);
            }

            $teacherClassIds = collect(array_keys($scope));

            $query->where(function ($q) use ($scope) {
                foreach ($scope as $classId => $sectionIds) {
                    $q->orWhere(function ($sub) use ($classId, $sectionIds) {
                        $sub->where('class_id', $classId);
                        if (is_array($sectionIds)) {
                            $sub->whereIn('section_id', $sectionIds);
                        }
                    });
                }
            });
        }

        if ($user?->role === 'parent') {
            $query->whereHas('parents', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        if ($this->classFilter !== 'all') {
            if ($teacherClassIds && !$teacherClassIds->contains((int) $this->classFilter)) {
                return Student::query()->whereRaw('1 = 0')->paginate(15);
            }

            $query->where('class_id', $this->classFilter);
        }

        if ($this->sectionFilter !== 'all') {
            if ($this->classFilter === 'all') {
                $query->whereHas('section', fn($q) => $q->where('name', $this->sectionFilter));
            } else {
                $query->where('section_id', $this->sectionFilter);
            }
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $search = trim($this->search);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                    ->orWhere('admission_number', 'like', "%{$search}%")
                    ->orWhere('guardian_name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy($this->sortBy, $this->sortDir)->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        $query = Student::query();
        $user = auth()->user();

        if ($user?->role === 'teacher') {
            $scope = $user->teacherClassSectionScope();
            if (empty($scope)) {
                return ['total' => 0, 'boys' => 0, 'girls' => 0, 'alumni' => 0];
            }

            $query->where(function ($q) use ($scope) {
                foreach ($scope as $classId => $sectionIds) {
                    $q->orWhere(function ($sub) use ($classId, $sectionIds) {
                        $sub->where('class_id', $classId);
                        if (is_array($sectionIds)) {
                            $sub->whereIn('section_id', $sectionIds);
                        }
                    });
                }
            });
        }

        if ($user?->role === 'parent') {
            $query->whereHas('parents', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $row = $query->selectRaw("
            COUNT(*) as total,
            SUM(gender = 'Male') as boys,
            SUM(gender = 'Female') as girls
        ")->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'boys' => (int) ($row->boys ?? 0),
            'girls' => (int) ($row->girls ?? 0),
            'alumni' => 0,
        ];
    }

    public function updated($property): void
    {
        if (in_array($property, ['classFilter', 'sectionFilter', 'statusFilter', 'search'], true)) {
            $this->resetPage();
            // Force refresh of computed properties
            $this->dispatch('$refresh');
        }

        if ($property === 'classFilter') {
            $this->sectionFilter = 'all';
            // Force refresh of sections computed property
            $this->dispatch('$refresh');
        }
    }

    public function sortBy($field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
    }

    public function render()
    {
        return view('livewire.students.index');
    }

    private function teacherClassIds(): Collection
    {
        if ($this->teacherClassIdsCache !== null) {
            return $this->teacherClassIdsCache;
        }

        $user = auth()->user();
        if ($user?->role !== 'teacher') {
            $this->teacherClassIdsCache = collect();
            return $this->teacherClassIdsCache;
        }

        $this->teacherClassIdsCache = SubjectAllocation::query()
            ->where('teacher_id', $user->id)
            ->pluck('class_id')
            ->unique()
            ->values();

        return $this->teacherClassIdsCache;
    }
}
