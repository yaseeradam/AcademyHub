<?php

namespace App\Livewire\IdCards;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\QrCodeGenerator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Student & Parent Pickup ID Cards - AI Integrated Academy')]
class Index extends Component
{
    #[Url]
    public string $activeTab = 'preview'; // 'preview' | 'simulator'

    #[Url]
    public string $search = '';

    #[Url]
    public string $selectedClass = 'all';

    public string $schoolName = 'AI INTEGRATED ACADEMY';
    public string $subTitle = 'STUDENT IDENTITY CARD • ARGUNGU';
    public string $logoUrl = '/logo.jpg';

    // Gate Simulator State
    public ?int $simulatedStudentId = null;
    public array $pickupLogs = [];
    public bool $justApproved = false;

    public function mount(): void
    {
        $tenantSchoolName = config('academyhub.school_name');
        if (!empty($tenantSchoolName) && $tenantSchoolName !== 'AcademyHub') {
            $this->schoolName = $tenantSchoolName;
        }

        $tenantLogo = config('academyhub.school_logo');
        if (!empty($tenantLogo)) {
            $this->logoUrl = $tenantLogo;
        }

        // Initialize simulated student
        $firstStudent = Student::first();
        if ($firstStudent) {
            $this->simulatedStudentId = $firstStudent->id;
        }
    }

    #[Computed]
    public function classes()
    {
        return SchoolClass::orderBy('name')->get();
    }

    #[Computed]
    public function students()
    {
        $query = Student::with(['schoolClass', 'section']);

        if ($this->selectedClass !== 'all') {
            $query->where('class_id', $this->selectedClass);
        }

        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                  ->orWhere('last_name', 'like', $term)
                  ->orWhere('admission_number', 'like', $term);
            });
        }

        return $query->orderBy('first_name')->get();
    }

    #[Computed]
    public function simulatedStudent(): ?Student
    {
        if (!$this->simulatedStudentId) {
            return $this->students->first();
        }
        return Student::with(['schoolClass', 'section'])->find($this->simulatedStudentId);
    }

    public function getStudentClassArm(?Student $student): string
    {
        if (!$student) return 'N/A';
        $className = $student->schoolClass?->name ?? 'General Class';
        $sectionName = $student->section?->name;
        return $sectionName ? "{$className} {$sectionName}" : $className;
    }

    public function generateQrCodeSvg(Student $student): string
    {
        $payload = json_encode([
            'id' => $student->id,
            'fn' => $student->first_name,
            'ln' => $student->last_name,
            'admNo' => $student->admission_number,
            'cls' => $this->getStudentClassArm($student),
            'fa' => $student->guardian_name ?: 'N/A',
            'ph' => $student->guardian_phone ?: 'N/A'
        ], JSON_UNESCAPED_SLASHES);

        return 'data:image/svg+xml;base64,' . base64_encode(QrCodeGenerator::generateSvg($payload, 80));
    }

    public function approvePickup(int $studentId): void
    {
        $student = Student::find($studentId);
        if (!$student) return;

        array_unshift($this->pickupLogs, [
            'id' => $student->id,
            'name' => $student->full_name,
            'time' => now()->format('h:i:s A'),
            'status' => 'APPROVED & RELEASED ✓'
        ]);

        $this->justApproved = true;
    }

    public function render()
    {
        return view('livewire.id-cards.index');
    }
}
