<?php

namespace App\Livewire\TeacherAppointments;

use App\Models\User;
use App\Support\NumberToWords;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Teacher Appointment Letter Generator - AI Integrated Academy')]
class Index extends Component
{
    // Selected teacher from database (optional)
    public ?int $selectedTeacherId = null;

    // Configurable fields requested by user
    public string $teacherName = 'Abdulmalik Muhammad';
    public string $staffId = 'AIA/26/N005';
    public string $salaryAmount = '50,000';
    public string $salaryWords = 'Fifty Thousand Naira';
    public string $appointmentDate = '1st September, 2026';

    // Additional configurable terms
    public string $position = 'Teacher';
    public string $employmentType = 'Full-Time';
    public string $probationPeriod = 'three-month';
    public string $noticePeriod = 'two months\'';
    public string $holidayLeave = '14 working days of leave during every school holiday, taking effect after one full session of working with the school.';
    public string $maternityLeave = 'Female staff are entitled to a two-month maternity leave with half salary during the period.';

    // School letterhead parameters
    public string $schoolName = 'AI INTEGRATED ACADEMY ARGUNGU';
    public string $schoolMotto = 'Learning Today Leading Tomorrow';
    public string $schoolAddress = "Behind Buben Ta'Ololo's Residence, Tudun Wada, Argungu, Kebbi State";
    public string $schoolPhone = '08069676697, 07034784861';
    public string $schoolEmail = 'alijabaintegratedacademyarg@gmail.com';
    public string $signatoryName = "Prof. Murtala Ahmed Rufa'i";
    public string $signatoryTitle = 'Executive Director';
    public string $logoUrl = '/logo.jpg';

    public function mount(): void
    {
        // Load custom tenant settings if present
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

    #[Computed]
    public function teachers()
    {
        return User::where('role', 'teacher')->orderBy('name')->get();
    }

    public function updatedSelectedTeacherId($teacherId): void
    {
        if (!$teacherId) return;

        $teacher = User::find($teacherId);
        if ($teacher) {
            $this->teacherName = $teacher->name;
            // Generate staff ID if not set
            $this->staffId = 'AIA/' . now()->format('y') . '/T' . str_pad((string) $teacher->id, 3, '0', STR_PAD_LEFT);
        }
    }

    public function updatedSalaryAmount($value): void
    {
        // Clean numeric value
        $clean = preg_replace('/[^\d.]/', '', (string) $value);
        if (is_numeric($clean) && (float) $clean > 0) {
            $this->salaryWords = NumberToWords::toNairaWords($clean);
        }
    }

    public function resetToDefaultTemplate(): void
    {
        $this->teacherName = 'Abdulmalik Muhammad';
        $this->staffId = 'AIA/26/N005';
        $this->salaryAmount = '50,000';
        $this->salaryWords = 'Fifty Thousand Naira';
        $this->appointmentDate = '1st September, 2026';
        $this->position = 'Teacher';
        $this->employmentType = 'Full-Time';
        $this->probationPeriod = 'three-month';
        $this->noticePeriod = 'two months\'';
        $this->selectedTeacherId = null;
    }

    public function render()
    {
        return view('livewire.teacher-appointments.index');
    }
}
