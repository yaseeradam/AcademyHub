<?php

namespace App\Livewire\Students;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\CustomField;
use App\Models\User;
use App\Support\Audit;
use App\Traits\DispatchesModals;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Student')]
class Form extends Component
{
    use WithFileUploads, DispatchesModals;

    public ?Student $student = null;

    public string $admission_number = '';
    public bool $auto_admission = true;
    public string $first_name = '';
    public string $last_name = '';
    public ?int $class_id = null;
    public ?int $section_id = null;
    public string $gender = 'Male';
    public ?string $dob = null;
    public ?string $blood_group = null;
    public ?string $guardian_name = null;
    public ?string $guardian_phone = null;
    public ?string $guardian_address = null;
    public string $status = 'Active';
    public array $customFieldValues = [];
    public array $parent_ids = [];

    // Parent registration fields
    public bool $create_parent_account = false;
    public string $parent_name = '';
    public string $parent_email = '';
    public string $parent_phone = '';
    public string $parent_password = '';

    public $passport = null;

    public function mount(?Student $student = null): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $this->student = $student;

        if ($student) {
            $this->admission_number = $student->admission_number;
            $this->auto_admission = false;
            $this->first_name = $student->first_name;
            $this->last_name = $student->last_name;
            $this->class_id = $student->class_id;
            $this->section_id = $student->section_id;
            $this->gender = $student->gender;
            $this->dob = $student->dob?->format('Y-m-d');
            $this->blood_group = $student->blood_group;
            $this->guardian_name = $student->guardian_name;
            $this->guardian_phone = $student->guardian_phone;
            $this->guardian_address = $student->guardian_address;
            $this->status = $student->status;
            $this->customFieldValues = $student->custom_fields ?? [];
            $this->parent_ids = $student->parents()->pluck('users.id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->admission_number = $this->generateAdmissionNumber();
        }
    }

    #[Computed]
    public function customFields()
    {
        return CustomField::active()->ordered()->where('form_type', 'student')->get();
    }

    #[Computed]
    public function availableParents()
    {
        return \App\Models\User::query()->where('role', 'parent')->orderBy('name')->get();
    }

    #[Computed]
    public function classes()
    {
        return SchoolClass::query()->orderBy('level')->get();
    }

    #[Computed]
    public function sections()
    {
        if (! $this->class_id) {
            return collect();
        }

        return Section::query()->where('class_id', $this->class_id)->orderBy('name')->get();
    }

    public function updatedClassId(): void
    {
        $this->section_id = null;
        // Bust Livewire 3 computed property cache so sections dropdown updates immediately
        unset($this->sections);
    }

    private function generateAdmissionNumber(): string
    {
        $year = now()->format('Y');
        $lastStudent = Student::query()
            ->where('admission_number', 'like', "STU{$year}%")
            ->orderByDesc('id')
            ->first();
        
        if ($lastStudent) {
            $lastNum = (int) substr($lastStudent->admission_number, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }
        
        return "STU{$year}{$nextNum}";
    }

    public function save()
    {
        $id = $this->student?->id;

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'integer', Rule::exists('classes', 'id')],
            'section_id' => ['required', 'integer', Rule::exists('sections', 'id')],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'dob' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['Active', 'Graduated', 'Expelled'])],
            'parent_ids' => ['array'],
            'parent_ids.*' => ['exists:users,id'],
            'create_parent_account' => ['boolean'],
        ];

        // Add parent account validation if creating parent
        if ($this->create_parent_account) {
            $rules['parent_name'] = ['required', 'string', 'max:255'];
            $rules['parent_email'] = ['required', 'email', 'unique:users,email'];
            $rules['parent_phone'] = ['nullable', 'string', 'max:20'];
            $rules['parent_password'] = ['required', 'string', 'min:6'];
        }

        if ($this->passport) {
            $rules['passport'] = ['image', 'max:2048'];
        }

        // Add custom field validation
        foreach ($this->customFields as $field) {
            $fieldRules = [];
            if ($field->required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            
            switch ($field->type) {
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
                default:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:255';
            }
            
            $rules["customFieldValues.{$field->name}"] = $fieldRules;
        }

        if (!$this->auto_admission || $id) {
            $rules['admission_number'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('students', 'admission_number')->ignore($id),
            ];
        } else {
            $this->admission_number = $this->generateAdmissionNumber();
        }

        try {
            $data = $this->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validation-error');
            throw $e;
        }

        // Create parent account if requested
        $parentUser = null;
        if ($this->create_parent_account) {
            $normalizedPhone = preg_replace('/\D+/', '', $this->parent_phone);
            $normalizedPhone = $normalizedPhone !== '' ? $normalizedPhone : null;

            $parentUser = User::create([
                'name' => $this->parent_name,
                'email' => $this->parent_email,
                'password' => Hash::make($this->parent_password),
                'role' => 'parent',
                'is_active' => true,
                'custom_fields' => $this->parent_phone ? ['phone' => $this->parent_phone] : null,
                'whatsapp_phone' => $normalizedPhone,
                'whatsapp_verified' => (bool) $normalizedPhone,
                'whatsapp_subscribed' => (bool) $normalizedPhone,
            ]);

            Audit::log('parents.created_during_student_registration', $parentUser, [
                'parent_name' => $parentUser->name,
                'parent_email' => $parentUser->email,
                'student_name' => $this->first_name . ' ' . $this->last_name,
            ]);
        }

        unset($data['passport']);
        unset($data['parent_ids']);
        unset($data['create_parent_account']);
        unset($data['parent_name']);
        unset($data['parent_email']);
        unset($data['parent_phone']);
        unset($data['parent_password']);
        $data['admission_number'] = $this->admission_number;
        $data['custom_fields'] = $this->customFieldValues;

        $student = $this->student ?? new Student();
        $student->fill($data);

        if ($this->passport) {
            try {
                File::ensureDirectoryExists(public_path('uploads/passports'));
                $ext = $this->passport->getClientOriginalExtension() ?: 'jpg';
                $safeAdmission = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $data['admission_number']);
                $safeAdmission = trim((string) $safeAdmission, '-');
                $safeAdmission = $safeAdmission !== '' ? $safeAdmission : 'student';
                $filename = $safeAdmission.'-'.now()->format('YmdHis').'.'.$ext;
                $path = $this->passport->storeAs('passports', $filename, 'uploads');
                $path = str_replace('\\', '/', $path);

                if ($student->exists && $student->passport_photo && $student->passport_photo !== $path) {
                    Storage::disk('uploads')->delete($student->passport_photo);
                }

                $student->passport_photo = $path;
            } catch (\Exception $e) {
                $this->dispatch('upload-error', ['message' => 'Passport upload failed: ' . $e->getMessage()]);
                return;
            }
        }

        $student->save();
        
        // Link parent to student
        $parentIds = $this->parent_ids;
        if ($parentUser) {
            $parentIds[] = (string) $parentUser->id;
        }
        $student->parents()->sync($parentIds);

        $isNew = !$this->student;
        $this->dispatch('student-saved', [
            'isNew' => $isNew,
            'name' => $student->full_name,
            'admission' => $student->admission_number,
            'downloadUrl' => route('students.admission-form', $student),
            'parentCreated' => $parentUser ? true : false,
            'parentEmail' => $parentUser?->email,
            'parentPassword' => $this->create_parent_account ? $this->parent_password : null,
        ]);

        return null;
    }

    public function render()
    {
        return view('livewire.students.form');
    }
}
