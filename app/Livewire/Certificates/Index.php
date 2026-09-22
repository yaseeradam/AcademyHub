<?php

namespace App\Livewire\Certificates;

use App\Models\Certificate;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Certificates')]
class Index extends Component
{
    public ?int $studentId = null;
    public array $selectedStudents = [];
    public ?int $classId = null;
    public string $type = 'General';
    public string $title = 'Certificate';
    public string $body = 'This is to certify that the above-named student has successfully completed the requirements and is hereby awarded this certificate.';
    public string $issuedOn = '';

    public string $search = '';

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless(in_array($user->role, ['admin', 'teacher'], true), 403);

        $this->applyCertificateDefaults();
        $this->issuedOn = now()->toDateString();
    }

    private function applyCertificateDefaults(): void
    {
        $this->type = (string) config('academyhub.certificate_default_type', 'General');
        $this->title = (string) config('academyhub.certificate_default_title', 'Certificate');

        $defaultBody = config('academyhub.certificate_default_body');
        $this->body = is_string($defaultBody) && trim($defaultBody) !== ''
            ? $defaultBody
            : 'This is to certify that the above-named student has successfully completed the requirements and is hereby awarded this certificate.';
    }

    #[Computed]
    public function students()
    {
        $query = Student::query()
            ->where('status', 'Active')
            ->orderBy('last_name');

        $search = trim($this->search);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        return $query->limit(20)->get(['id', 'first_name', 'last_name', 'admission_number']);
    }

    #[Computed]
    public function classes()
    {
        return \App\Models\SchoolClass::query()->orderBy('name')->get(['id', 'name']);
    }

    public function selectClass(): void
    {
        if (!$this->classId) return;
        
        $students = Student::query()
            ->where('status', 'Active')
            ->where('school_class_id', $this->classId)
            ->pluck('id')
            ->toArray();
        
        $this->selectedStudents = array_unique(array_merge($this->selectedStudents, $students));
        $this->classId = null;
    }

    public function toggleStudent(int $id): void
    {
        if (in_array($id, $this->selectedStudents)) {
            $this->selectedStudents = array_values(array_diff($this->selectedStudents, [$id]));
        } else {
            $this->selectedStudents[] = $id;
        }
    }

    public function create(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless(in_array($user->role, ['admin', 'teacher'], true), 403);

        $data = $this->validate([
            'selectedStudents' => ['required', 'array', 'min:1'],
            'selectedStudents.*' => ['integer', 'exists:students,id'],
            'type' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:8000'],
            'issuedOn' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($user, $data) {
            foreach ($data['selectedStudents'] as $studentId) {
                Certificate::query()->create([
                    'student_id' => $studentId,
                    'type' => trim($data['type']),
                    'title' => trim($data['title']),
                    'body' => trim($data['body']),
                    'issued_on' => $data['issuedOn'],
                    'serial_number' => 'CERT-'.now()->format('YmdHis').'-'.mt_rand(1000, 9999),
                    'issued_by' => $user->id,
                ]);
            }
        });

        $count = count($data['selectedStudents']);
        $this->dispatch('alert', message: "{$count} certificate(s) created.", type: 'success');
        $this->selectedStudents = [];
        $this->applyCertificateDefaults();
    }

    public function delete(int $id): void
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless(in_array($user->role, ['admin', 'teacher'], true), 403);

        Certificate::query()->whereKey($id)->delete();
        $this->dispatch('alert', message: 'Certificate deleted.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless(in_array($user->role, ['admin', 'teacher'], true), 403);

        $certificates = Certificate::query()
            ->with(['student:id,first_name,last_name,admission_number', 'issuer:id,name'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('livewire.certificates.index', [
            'certificates' => $certificates,
        ]);
    }
}
