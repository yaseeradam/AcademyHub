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
    public function availableSessions(): array
    {
        return \App\Models\AcademicSession::query()
            ->orderByDesc('name')
            ->pluck('name')
            ->toArray() ?: [now()->format('Y') . '/' . (now()->format('Y') + 1)];
    }

    #[Computed]
    public function availableTerms(): array
    {
        $termsMap = AcademicTerm::availableTermsForSession($this->selectedSession);
        $result = [];
        foreach ($termsMap as $val => $label) {
            $result[] = ['value' => $val, 'label' => $label];
        }
        return $result;
    }

    public function updatedSelectedSession($value): void
    {
        $available = array_keys(AcademicTerm::availableTermsForSession($value));
        if (!in_array($this->selectedTerm, $available, true)) {
            $this->selectedTerm = !empty($available) ? end($available) : 1;
        }
    }

    #[Computed]
    public function feeStatus(): array
    {
        if (!$this->student) {
            return ['is_debtor' => false, 'balance' => 0.0, 'block_access' => false];
        }

        $blockDebtors = (bool) \App\Support\TenantSettings::get('rc_block_debtors', true);
        if (!$blockDebtors) {
            return ['is_debtor' => false, 'balance' => 0.0, 'block_access' => false];
        }

        try {
            $ledger = \App\Support\BillingService::getStudentTermLedger($this->student, $this->selectedSession);
            $balance = (float) ($ledger['total_balance'] ?? 0.0);
            return [
                'is_debtor'    => $balance > 0,
                'balance'      => $balance,
                'block_access' => $balance > 0,
            ];
        } catch (\Throwable $e) {
            return ['is_debtor' => false, 'balance' => 0.0, 'block_access' => false];
        }
    }

    #[Computed]
    public function bookletDossier(): ?array
    {
        if (!$this->student || !$this->selectedSession) {
            return null;
        }

        $isParentOrStudent = !auth()->check() || in_array(auth()->user()->role, ['student', 'parent'], true);

        try {
            return app(\App\Support\StudentDossierService::class)->buildDossier(
                $this->student,
                $this->selectedSession,
                ['only_published' => $isParentOrStudent]
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function __get($property)
    {
        if (method_exists($this, $property)) {
            return $this->{$property}();
        }
        return parent::__get($property);
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
