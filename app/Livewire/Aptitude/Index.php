<?php

namespace App\Livewire\Aptitude;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Applicant;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Str;

#[Layout('layouts.app')]
#[Title('Aptitude Tests — Applicant Screening')]
class Index extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public ?int $class_id = null;

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name'  => 'required|string|max:255',
        'email'      => 'nullable|email|max:255',
        'phone'      => 'nullable|string|max:20',
        'class_id'   => 'required|exists:classes,id',
    ];

    public function addApplicant(): void
    {
        $this->validate();

        $tenantId = auth()->user()->tenant_id;

        Applicant::create([
            'tenant_id'  => $tenantId,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email ?: null,
            'phone'      => $this->phone ?: null,
            'class_id'   => $this->class_id,
            'status'     => 'Pending Test',
        ]);

        $this->reset(['first_name', 'last_name', 'email', 'phone', 'class_id']);
        $this->dispatch('alert', message: 'Applicant registered successfully!', type: 'success');
    }

    public function admitStudent(int $applicantId): void
    {
        $applicant = Applicant::findOrFail($applicantId);
        
        if ($applicant->status !== 'Passed') {
            $this->dispatch('alert', message: 'Only applicants who passed the screening test can be admitted.', type: 'error');
            return;
        }

        $tenantId = auth()->user()->tenant_id;

        // Resolve first section for that class
        $section = Section::where('class_id', $applicant->class_id)->first();
        $sectionId = $section ? $section->id : 1;

        // Generate dynamic unique admission number
        $year = now()->format('Y');
        $lastStudent = Student::where('tenant_id', $tenantId)
            ->where('admission_number', 'like', "STU{$year}%")
            ->orderByDesc('id')
            ->first();
        
        if ($lastStudent) {
            $lastNum = (int) substr($lastStudent->admission_number, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }
        $admissionNumber = "STU{$year}{$nextNum}";

        // Register Student
        $student = Student::create([
            'tenant_id'        => $tenantId,
            'admission_number' => $admissionNumber,
            'first_name'       => $applicant->first_name,
            'last_name'        => $applicant->last_name,
            'class_id'         => $applicant->class_id,
            'section_id'       => $sectionId,
            'gender'           => 'Male', // Default fallback
            'status'           => 'Active',
            'password'         => 'password', // Auto-hashed by model cast
        ]);

        // Update Applicant Status to Admitted
        $applicant->update(['status' => 'Admitted']);

        $this->dispatch('alert', message: "Applicant admitted successfully as {$student->full_name}! Admission Number: {$admissionNumber}", type: 'success');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;
        $applicants = Applicant::where('tenant_id', $tenantId)
            ->with('schoolClass')
            ->orderByDesc('created_at')
            ->get();

        $classes = SchoolClass::where('tenant_id', $tenantId)->get();

        return view('livewire.aptitude.index', compact('applicants', 'classes'));
    }
}
