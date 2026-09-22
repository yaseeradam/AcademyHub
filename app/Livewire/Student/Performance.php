<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\AcademicTerm;
use App\Support\StudentPerformanceService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;

#[Title('Performance Tracking')]
class Performance extends Component
{
    public ?int $selectedTerm = null;
    public ?string $selectedSession = null;
    public string $activeTab = 'overview';

    protected StudentPerformanceService $performanceService;

    public function boot(StudentPerformanceService $service)
    {
        $this->performanceService = $service;
    }

    public function mount()
    {
        // Support both session-based student login and auth-based parent/teacher login
        if (!session('student_id') && !auth()->check()) {
            abort(403);
        }

        if (auth()->check()) {
            abort_unless(in_array(auth()->user()->role, ['student', 'parent', 'admin', 'teacher'], true), 403);
        }

        $currentTerm = AcademicTerm::active() ?? AcademicTerm::latest()->first();
        $this->selectedTerm = $currentTerm?->term_number ?? 1;
        $this->selectedSession = $currentTerm?->academicSession?->name ?? now()->format('Y') . '/' . (now()->format('Y') + 1);
    }

    #[Computed]
    public function student(): ?Student
    {
        // Session-based student login
        if (session('student_id')) {
            return Student::find(session('student_id'));
        }

        $user = auth()->user();

        if ($user?->role === 'student') {
            return Student::where('admission_number', $user->email)->first();
        }

        if ($user?->role === 'parent') {
            $tenant = $user->tenant;
            if (!$tenant || $tenant->isSubscriptionExpired()) {
                return null;
            }
            $dbComponent = $tenant->activeMarketplaceComponents()->where('slug', 'student-dashboard')->first();
            if (!$dbComponent) {
                return null;
            }
            $allowedClassIds = $dbComponent->pivot->allowed_class_ids ?? [];
            if (is_string($allowedClassIds)) {
                $allowedClassIds = json_decode($allowedClassIds, true) ?: [];
            }
            $allowedClassIds = is_array($allowedClassIds) ? $allowedClassIds : [];

            return $user->students()->whereIn('class_id', $allowedClassIds)->first();
        }

        return null;
    }

    #[Computed]
    public function performanceData(): array
    {
        if (!$this->student) {
            return [];
        }

        return $this->performanceService->getPerformanceAnalysis(
            $this->student,
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

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $layout = session('student_id') ? 'layouts.student' : 'layouts.app';

        return view('livewire.student.performance')->layout($layout);
    }
}
