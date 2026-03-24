<?php

namespace App\Livewire\Results;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Support\Audit;
use App\Support\ReportCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

#[Layout('layouts.app')]
#[Title('Broadsheet')]
class Broadsheet extends Component
{
    public ?int $classId = null;
    public ?int $term = null;
    public string $session = '';

    public function mount(): void
    {
        $this->term = $this->term ?: \App\Models\AcademicTerm::activeTermNumber();
        $this->session = $this->session ?: $this->defaultSession();
    }

    public function updatedClassId(): void
    {
        unset($this->subjects, $this->rows, $this->isPublished);
    }

    public function updatedTerm(): void
    {
        unset($this->rows, $this->isPublished);
    }

    public function updatedSession(): void
    {
        unset($this->rows, $this->isPublished);
    }

    #[Computed]
    public function isPublished(): bool
    {
        if (!$this->classId || !$this->session) {
            return false;
        }

        return \App\Models\ResultPublication::query()
            ->where('class_id', $this->classId)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->whereNotNull('published_at')
            ->exists();
    }

    #[Computed]
    public function classes()
    {
        $user = auth()->user();

        if ($user?->role === 'admin') {
            return SchoolClass::query()->orderBy('level')->get();
        }

        return SchoolClass::query()
            ->whereIn('id', SubjectAllocation::query()->where('teacher_id', $user->id)->pluck('class_id'))
            ->orderBy('level')
            ->get();
    }

    #[Computed]
    public function subjects()
    {
        if (!$this->classId) {
            return collect();
        }

        $ids = SubjectAllocation::query()
            ->where('class_id', $this->classId)
            ->pluck('subject_id')
            ->unique();

        if ($ids->isEmpty()) {
            return Subject::query()->orderBy('name')->get();
        }

        return Subject::query()->whereIn('id', $ids)->orderBy('name')->get();
    }

    #[Computed]
    public function rows(): Collection
    {
        if (!$this->classId || !$this->session) {
            return collect();
        }

        $students = Student::query()
            ->where('class_id', $this->classId)
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->get();

        $subjectIds = $this->subjects->pluck('id');

        $scores = Score::query()
            ->where('class_id', $this->classId)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->whereIn('subject_id', $subjectIds)
            ->get();

        $byStudent = $scores
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->keyBy('subject_id'));

        $subjectCount = max(1, (int) $subjectIds->count());

        $rows = $students->map(function (Student $student) use ($subjectIds, $byStudent, $subjectCount) {
            $map = $byStudent->get($student->id, collect());
            $subjectTotals = [];
            $grandTotal = 0;

            foreach ($subjectIds as $subjectId) {
                /** @var \App\Models\Score|null $score */
                $score = $map->get($subjectId);
                $subjectTotals[$subjectId] = $score?->total ?? null;
                $grandTotal += (int) ($score?->total ?? 0);
            }

            $average = round($grandTotal / $subjectCount, 2);

            return [
                'student' => $student,
                'subjectTotals' => $subjectTotals,
                'grandTotal' => $grandTotal,
                'average' => $average,
            ];
        });

        $sorted = $rows->sortByDesc('grandTotal')->values();

        $rank = 0;
        $lastScore = null;
        $sorted = $sorted->map(function (array $row, int $i) use (&$rank, &$lastScore) {
            if ($lastScore === null || $row['grandTotal'] !== $lastScore) {
                $rank = $i + 1;
                $lastScore = $row['grandTotal'];
            }

            $row['position'] = $rank;

            return $row;
        });

        return $sorted;
    }

    private function defaultSession(): string
    {
        $active = AcademicSession::activeName();
        if ($active) {
            return $active;
        }

        $year = (int) now()->format('Y');
        $next = $year + 1;

        return "{$year}/{$next}";
    }

    public function generateBulk(): BinaryFileResponse
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('results.broadsheet'), 403);

        if (!$this->classId) {
            session()->flash('error', 'Please select a class.');
            abort(400, 'Please select a class.');
        }

        if (!$this->isPublished) {
            session()->flash('error', 'Results must be published first.');
            abort(400, 'Results must be published first.');
        }

        set_time_limit(0);

        $students = Student::query()
            ->where('class_id', $this->classId)
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->get();

        if ($students->isEmpty()) {
            session()->flash('error', 'No active students found.');
            abort(400, 'No active students found.');
        }

        $service = app(ReportCardService::class);
        $safeSession = str_replace('/', '-', $this->session);
        $tmpDir = storage_path('app/_bulk_report_cards/' . Str::random(12));
        $pdfDir = $tmpDir . DIRECTORY_SEPARATOR . 'pdfs';
        File::ensureDirectoryExists($pdfDir);

        $timestamp = now()->format('Y-m-d_H-i-s');
        $zipDir = storage_path('app/report-cards');
        File::ensureDirectoryExists($zipDir);
        $zipPath = $zipDir . DIRECTORY_SEPARATOR . "report_cards_class{$this->classId}_{$safeSession}_T{$this->term}_{$timestamp}.zip";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            File::deleteDirectory($tmpDir);
            abort(500, 'Unable to create zip archive.');
        }

        try {
            foreach ($students as $student) {
                $payload = $service->build($student, $this->term, $this->session);
                $template = (string) config('myacademy.report_card_template', 'standard');
                $view = match ($template) {
                    'compact' => 'pdf.report-card-compact',
                    'elegant' => 'pdf.report-card-elegant',
                    'modern' => 'pdf.report-card-modern',
                    'classic' => 'pdf.report-card-classic',
                    default => 'pdf.report-card',
                };
                $pdf = Pdf::loadView($view, $payload)->setPaper('a4');
                $safeAdm = preg_replace('/[^A-Za-z0-9\\-_.]+/', '-', (string) $student->admission_number) ?: (string) $student->id;
                $filename = "report-card-{$safeAdm}-{$safeSession}-T{$this->term}.pdf";
                $path = $pdfDir . DIRECTORY_SEPARATOR . $filename;
                File::put($path, $pdf->output());
                $zip->addFile($path, $filename);
            }
        } finally {
            $zip->close();
            File::deleteDirectory($tmpDir);
        }

        Audit::log('results.bulk_report_cards_generated', null, [
            'class_id' => $this->classId,
            'term' => $this->term,
            'session' => $this->session,
            'count' => $students->count(),
        ]);

        return response()->download($zipPath)->deleteFileAfterSend(true)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function publish(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        if (!$this->classId || !$this->session) {
            $this->dispatch('alert', message: 'Select class and session first.', type: 'error');
            return;
        }

        \App\Models\ResultPublication::query()->updateOrCreate(
            [
                'class_id' => $this->classId,
                'term' => $this->term,
                'session' => $this->session,
            ],
            [
                'published_at' => now(),
                'published_by' => $user->id,
            ]
        );

        Audit::log('results.published', null, [
            'class_id' => $this->classId,
            'term' => $this->term,
            'session' => $this->session,
        ]);

        $this->dispatch('alert', message: 'Results published successfully.', type: 'success');
    }

    public function unpublish(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        if (!$this->classId || !$this->session) {
            $this->dispatch('alert', message: 'Select class and session first.', type: 'error');
            return;
        }

        \App\Models\ResultPublication::query()
            ->where('class_id', $this->classId)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->delete();

        Audit::log('results.unpublished', null, [
            'class_id' => $this->classId,
            'term' => $this->term,
            'session' => $this->session,
        ]);

        $this->dispatch('alert', message: 'Results unpublished successfully.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('results.broadsheet'), 403);

        return view('livewire.results.broadsheet');
    }
}
