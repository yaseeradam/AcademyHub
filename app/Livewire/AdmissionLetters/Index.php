<?php

namespace App\Livewire\AdmissionLetters;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Support\TenantSettings;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Admission Letters - AI Integrated Academy')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $classId = null;

    #[Url]
    public ?int $sectionId = null;

    public string $academicSession = '2026/2027';
    public string $resumptionDate = '14th September, 2026';
    public string $admissionDate = '';

    public string $schoolName = 'AI INTEGRATED ACADEMY ARGUNGU';
    public string $schoolMotto = 'Learning Today Leading Tomorrow';
    public string $schoolAddress = "Behind Buben Ta'Ololo's Residence, Tudun Wada, Argungu, Kebbi State";
    public string $schoolPhone = '08069676697, 07034784861';
    public string $schoolEmail = 'alijabaintegratedacademyarg@gmail.com';
    public string $signatoryName = "Prof. Murtala Ahmed Rufa'i";
    public string $signatoryTitle = 'Executive Director';
    public string $logoUrl = '/logo.jpg';

    // Preview Modal state
    public ?int $previewStudentId = null;
    public bool $showPreviewModal = false;

    // Bulk selection
    public array $selectedStudentIds = [];
    public bool $selectAll = false;

    public function mount(): void
    {
        $this->admissionDate = now()->format('jS F, Y');

        // Check if tenant has customized settings
        $tenantSchoolName = config('academyhub.school_name');
        if (!empty($tenantSchoolName) && $tenantSchoolName !== 'AcademyHub') {
            $this->schoolName = $tenantSchoolName;
        }

        $tenantMotto = config('academyhub.school_motto');
        if (!empty($tenantMotto)) {
            $this->schoolMotto = $tenantMotto;
        }

        $tenantAddress = config('academyhub.school_address');
        if (!empty($tenantAddress)) {
            $this->schoolAddress = $tenantAddress;
        }

        $tenantPhone = config('academyhub.school_phone');
        if (!empty($tenantPhone)) {
            $this->schoolPhone = $tenantPhone;
        }

        $tenantEmail = config('academyhub.school_email');
        if (!empty($tenantEmail)) {
            $this->schoolEmail = $tenantEmail;
        }

        $tenantLogo = config('academyhub.school_logo');
        if (!empty($tenantLogo)) {
            $this->logoUrl = $tenantLogo;
        }
    }

    public function updatedClassId(): void
    {
        $this->sectionId = null;
        $this->resetPage();
        $this->selectedStudentIds = [];
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedStudentIds = $this->allFilteredStudentIds;
        } else {
            $this->selectedStudentIds = [];
        }
    }

    #[Computed]
    public function classes()
    {
        return SchoolClass::orderBy('name')->get();
    }

    #[Computed]
    public function sections()
    {
        if (!$this->classId) {
            return collect();
        }
        return Section::where('class_id', $this->classId)->orderBy('name')->get();
    }

    #[Computed]
    public function allFilteredStudentIds(): array
    {
        $query = Student::query();

        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }

        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }

        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                  ->orWhere('last_name', 'like', $term)
                  ->orWhere('admission_number', 'like', $term);
            });
        }

        return $query->pluck('id')->map(fn($id) => (int) $id)->toArray();
    }

    #[Computed]
    public function students()
    {
        $query = Student::with(['schoolClass', 'section']);

        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }

        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }

        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                  ->orWhere('last_name', 'like', $term)
                  ->orWhere('admission_number', 'like', $term);
            });
        }

        return $query->orderBy('first_name')->paginate(15);
    }

    #[Computed]
    public function previewStudent(): ?Student
    {
        if (!$this->previewStudentId) {
            return null;
        }
        return Student::with(['schoolClass', 'section'])->find($this->previewStudentId);
    }

    public function openPreview(int $studentId): void
    {
        $this->previewStudentId = $studentId;
        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewStudentId = null;
    }

    public function getStudentClassArm(?Student $student): string
    {
        if (!$student) return 'N/A';
        $className = $student->schoolClass?->name ?? 'General Class';
        $sectionName = $student->section?->name;
        return $sectionName ? "{$className} {$sectionName}" : $className;
    }

    public function render()
    {
        return view('livewire.admission-letters.index');
    }
}
