<?php

namespace App\Livewire\Analytics;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AcademicTerm;
use App\Models\SubjectAllocation;
use App\Support\StudentPerformanceService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Student Performance Analytics')]
class StudentPerformance extends Component
{
    public ?int $selectedStudent = null;
    public ?int $selectedClass = null;
    public ?int $selectedTerm = null;
    public ?string $selectedSession = null;
    public string $searchTerm = '';

    protected StudentPerformanceService $performanceService;

    public function boot(StudentPerformanceService $service)
    {
        $this->performanceService = $service;
    }

    public function mount()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        $currentTerm = AcademicTerm::active() ?? AcademicTerm::latest()->first();
        $this->selectedTerm = $currentTerm?->term_number ?? 1;
        $this->selectedSession = $currentTerm?->academicSession?->name ?? now()->format('Y') . '/' . (now()->format('Y') + 1);

        if (request()->has('student')) {
            $studentId = (int) request('student');
            // For teachers, verify the student belongs to one of their assigned classes
            if ($user->role === 'teacher') {
                $assignedClassIds = $this->teacherClassIds();
                $student = Student::where('id', $studentId)->whereIn('class_id', $assignedClassIds)->first();
                $this->selectedStudent = $student?->id;
            } else {
                $this->selectedStudent = $studentId;
            }
        }
    }

    private function teacherClassIds(): array
    {
        return SubjectAllocation::where('teacher_id', auth()->id())
            ->distinct()->pluck('class_id')->toArray();
    }

    #[Computed]
    public function classes()
    {
        $user = auth()->user();
        if ($user->role === 'teacher') {
            $classIds = $this->teacherClassIds();
            return SchoolClass::whereIn('id', $classIds)->orderBy('level')->get();
        }
        return SchoolClass::orderBy('level')->get();
    }

    #[Computed]
    public function students()
    {
        $user = auth()->user();
        $query = Student::query()->where('status', 'Active');

        if ($user->role === 'teacher') {
            $assignedClassIds = $this->teacherClassIds();
            $query->whereIn('class_id', $assignedClassIds);
        }

        if ($this->selectedClass) {
            $query->where('class_id', $this->selectedClass);
        }

        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('admission_number', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return $query->with('schoolClass')->orderBy('first_name')->get();
    }

    #[Computed]
    public function selectedStudentModel(): ?Student
    {
        return $this->selectedStudent ? Student::find($this->selectedStudent) : null;
    }

    #[Computed]
    public function performanceData(): array
    {
        if (!$this->selectedStudentModel) {
            return [];
        }

        return $this->performanceService->getPerformanceAnalysis(
            $this->selectedStudentModel,
            $this->selectedTerm,
            $this->selectedSession
        );
    }

    #[Computed]
    public function availableTerms(): array
    {
        return [
            ['value' => 1, 'label' => 'First Term'],
            ['value' => 2, 'label' => 'Second Term'],
            ['value' => 3, 'label' => 'Third Term'],
        ];
    }

    public function selectStudent(int $studentId)
    {
        $user = auth()->user();
        if ($user->role === 'teacher') {
            $assignedClassIds = $this->teacherClassIds();
            if (!Student::where('id', $studentId)->whereIn('class_id', $assignedClassIds)->exists()) {
                return;
            }
        }
        $this->selectedStudent = $studentId;
    }

    public function clearSelection()
    {
        $this->selectedStudent = null;
    }

    public function render()
    {
        return view('livewire.analytics.student-performance');
    }
}
