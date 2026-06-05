<?php

namespace App\Livewire\Certificates;

use App\Models\Certificate;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\Audit;
use App\Support\CertificatePdf;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use ZipArchive;

#[Layout('layouts.app')]
#[Title('Certificates')]
class Manager extends Component
{
    use WithFileUploads;

    public ?int $classId = null;
    public ?int $studentId = null;
    public string $type = 'Achievement';
    public string $title = '';
    public string $description = '';
    public string $session = '';
    public ?int $term = null;
    public string $issueDate = '';
    public string $template = 'modern';
    public bool $showModal = false;
    public $templateFile;
    public int $previewIndex = 0;
    public bool $showAllTemplates = false;

    public string $search = '';

    public array $availableTemplates = [
        ['key' => 'modern', 'label' => 'Modern', 'view' => 'pdf.certificate'],
        ['key' => 'classic', 'label' => 'Classic', 'view' => 'pdf.certificate-classic'],
        ['key' => 'elegant', 'label' => 'Elegant', 'view' => 'pdf.certificate-elegant'],
        ['key' => 'vibrant', 'label' => 'Vibrant', 'view' => 'pdf.certificate-vibrant'],
        ['key' => 'minimal', 'label' => 'Minimal', 'view' => 'pdf.certificate-minimal'],
        ['key' => 'royal', 'label' => 'Royal', 'view' => 'pdf.certificate-royal'],
        ['key' => 'prestige', 'label' => 'Prestige', 'view' => 'pdf.certificate-prestige'],
        ['key' => 'botanical', 'label' => 'Botanical', 'view' => 'pdf.certificate-botanical'],
        ['key' => 'aurora', 'label' => 'Aurora', 'view' => 'pdf.certificate-aurora'],
        ['key' => 'heritage', 'label' => 'Heritage', 'view' => 'pdf.certificate-heritage'],
        ['key' => 'obsidian', 'label' => 'Obsidian', 'view' => 'pdf.certificate-obsidian'],
        ['key' => 'sahara', 'label' => 'Sahara', 'view' => 'pdf.certificate-sahara'],
        ['key' => 'oceanic', 'label' => 'Oceanic', 'view' => 'pdf.certificate-oceanic'],
        ['key' => 'crimson', 'label' => 'Crimson', 'view' => 'pdf.certificate-crimson'],
        ['key' => 'ivory', 'label' => 'Ivory', 'view' => 'pdf.certificate-ivory'],
    ];

    public function mount(): void
    {
        $this->term = $this->term ?: \App\Models\AcademicTerm::activeTermNumber();
        $this->session = now()->format('Y') . '/' . (now()->format('Y') + 1);
        $this->issueDate = now()->format('Y-m-d');

        if (trim($this->title) === '') {
            $this->title = 'Certificate of Achievement';
        }

        if (trim($this->description) === '') {
            $this->description = 'For outstanding performance and dedication.';
        }

        $this->template = $this->availableTemplates[0]['key'];
    }

    public function nextTemplate(): void
    {
        $this->previewIndex = ($this->previewIndex + 1) % count($this->availableTemplates);
        $this->template = $this->availableTemplates[$this->previewIndex]['key'];
    }

    public function prevTemplate(): void
    {
        $this->previewIndex = ($this->previewIndex - 1 + count($this->availableTemplates)) % count($this->availableTemplates);
        $this->template = $this->availableTemplates[$this->previewIndex]['key'];
    }

    public function setTemplate(int $index): void
    {
        $index = max(0, min($index, count($this->availableTemplates) - 1));
        $this->previewIndex = $index;
        $this->template = $this->availableTemplates[$this->previewIndex]['key'];
        $this->showAllTemplates = false;
    }

    public function toggleShowAll(): void
    {
        $this->showAllTemplates = !$this->showAllTemplates;
    }

    #[Computed]
    public function templateLabel(): string
    {
        return $this->availableTemplates[$this->previewIndex]['label'] ?? 'Modern';
    }

    #[Computed]
    public function templateCount(): int
    {
        return count($this->availableTemplates);
    }

    #[Computed]
    public function classes()
    {
        return SchoolClass::query()->orderBy('level')->get();
    }

    #[Computed]
    public function students()
    {
        if (!$this->classId) {
            return collect();
        }
        return Student::query()
            ->where('class_id', $this->classId)
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->get();
    }

    #[Computed]
    public function filteredStudents()
    {
        if (!$this->classId) {
            return collect();
        }

        $query = Student::query()
            ->where('class_id', $this->classId)
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

        return $query->limit(200)->get();
    }

    #[Computed]
    public function certificates()
    {
        return Certificate::query()
            ->with(['student', 'issuer'])
            ->latest()
            ->paginate(20);
    }

    public function updatedClassId(): void
    {
        unset($this->students, $this->filteredStudents);
    }

    public function updatedSearch(): void
    {
        unset($this->filteredStudents);
    }

    public function create(): void
    {
        $this->validateCreateFields();

        $certificate = $this->createCertificateForStudent((int) $this->studentId);

        Audit::log('certificate.created', $certificate);

        session()->flash('success', 'Certificate created successfully!');
        $this->studentId = null;
    }

    public function createAndDownload(): void
    {
        $this->validateCreateFields();

        $certificate = $this->createCertificateForStudent((int) $this->studentId);

        $this->dispatch('open-url', url: route('certificates.download', $certificate));
        $this->studentId = null;
    }

    public function issueForStudent(int $studentId): void
    {
        $this->studentId = $studentId;
        $this->validateCreateFields();

        $certificate = $this->createCertificateForStudent($studentId);

        Audit::log('certificate.created', $certificate);

        $this->dispatch('open-url', url: route('certificates.download', $certificate));
        $this->dispatch('alert', message: 'Certificate generated.', type: 'success');
    }

    public function bulkGenerate()
    {
        $this->validate([
            'classId' => 'required|exists:classes,id',
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string|max:2000',
            'session' => 'required|string|max:20',
            'term' => 'required|integer|between:1,3',
            'issueDate' => 'required|date',
            'template' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/'],
        ]);

        $students = $this->filteredStudents;

        if ($students->isEmpty()) {
            session()->flash('error', 'No students found.');
            abort(400);
        }

        $zipPath = storage_path('app/certificates_bulk_' . time() . '.zip');
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            abort(500, 'Unable to create zip.');
        }

        $templateViews = collect($this->availableTemplates)->pluck('view', 'key')->toArray();
        $view = $templateViews[$this->template] ?? 'pdf.certificate';

        $orientation = (string) config('academyhub.certificate_orientation', 'landscape');
        $orientation = in_array($orientation, ['landscape', 'portrait'], true) ? $orientation : 'landscape';

        foreach ($students as $student) {
            $certificate = Certificate::create([
                'student_id' => $student->id,
                'type' => $this->type,
                'title' => $this->title,
                'body' => trim((string) ($this->description ?: '')) !== '' ? (string) $this->description : 'This certificate is proudly presented to the above-named student.',
                'description' => $this->description,
                'session' => $this->session,
                'term' => $this->term,
                'issue_date' => $this->issueDate,
                'issued_on' => $this->issueDate,
                'serial_number' => $this->generateSerialNumber(),
                'template' => $this->template,
                'issued_by' => auth()->id(),
            ]);

            $filename = "certificate_{$student->admission_number}.pdf";

            $pdfContent = CertificatePdf::fromView($view, [
                'certificate' => $certificate,
                'student' => $student,
            ], $orientation);

            $zip->addFromString($filename, $pdfContent);
        }

        $zip->close();

        Audit::log('certificates.bulk_generated', null, ['class_id' => $this->classId, 'count' => $students->count()]);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function uploadTemplate(): void
    {
        $this->validate(['templateFile' => 'required|image|max:10240']);

        $name = trim($this->template);
        if ($name === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            $this->addError('template', 'Template key must be letters, numbers, dash or underscore.');
            return;
        }

        $filename = $name . '.png';

        $dir = public_path('certificates/templates');
        File::ensureDirectoryExists($dir);

        $target = $dir . DIRECTORY_SEPARATOR . $filename;
        $realPath = $this->templateFile->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            session()->flash('error', 'Upload failed. Please try again.');
            return;
        }

        @copy($realPath, $target);

        session()->flash('success', 'Template uploaded successfully!');
        $this->templateFile = null;
    }

    public function render()
    {
        abort_unless(auth()->user()?->role === 'admin' || auth()->user()?->role === 'teacher', 403);
        return view('livewire.certificates.manager');
    }

    private function generateSerialNumber(): string
    {
        $date = now()->format('YmdHis');
        $rand = strtoupper(bin2hex(random_bytes(3)));

        return "CERT-{$date}-{$rand}";
    }

    private function validateCreateFields(): void
    {
        $this->validate([
            'classId' => 'required|exists:classes,id',
            'studentId' => 'required|exists:students,id',
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'session' => 'required|string|max:20',
            'term' => 'required|integer|between:1,3',
            'issueDate' => 'required|date',
            'template' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/'],
        ]);
    }

    private function createCertificateForStudent(int $studentId): Certificate
    {
        return Certificate::create([
            'student_id' => $studentId,
            'type' => $this->type,
            'title' => $this->title,
            'body' => trim((string) ($this->description ?: '')) !== '' ? (string) $this->description : 'This certificate is proudly presented to the above-named student.',
            'description' => $this->description,
            'session' => $this->session,
            'term' => $this->term,
            'issue_date' => $this->issueDate,
            'issued_on' => $this->issueDate,
            'serial_number' => $this->generateSerialNumber(),
            'template' => $this->template,
            'issued_by' => auth()->id(),
        ]);
    }
}
