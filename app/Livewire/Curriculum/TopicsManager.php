<?php

namespace App\Livewire\Curriculum;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\CurriculumDocument;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\SubjectTopic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Curriculum & Scheme of Work')]
class TopicsManager extends Component
{
    use WithFileUploads;

    public ?int $classId = null;
    public ?int $subjectId = null;
    public int $term = 1;
    public string $session = '';

    public string $search = '';
    public string $statusFilter = 'all';

    // Syllabus Document Upload
    public $curriculumDocFile;

    // Bulk Import Modal fields
    public bool $showImportModal = false;
    public $csvFile;
    public string $rawTextTopics = '';

    // Modal Form fields
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $weekNumber = 1;
    public string $title = '';
    public string $learningObjectives = '';
    public string $status = 'upcoming';

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, ['admin', 'teacher', 'proprietor'], true), 403);

        $this->term = AcademicTerm::activeTermNumber() ?? 1;
        $this->session = AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        $firstClass = $this->classes->first();
        if ($firstClass) {
            $this->classId = $firstClass->id;
            $firstSubject = $this->subjects->first();
            if ($firstSubject) {
                $this->subjectId = $firstSubject->id;
            }
        }
    }

    public function updatedClassId(): void
    {
        $firstSubject = $this->subjects->first();
        $this->subjectId = $firstSubject?->id;
    }

    #[Computed]
    public function classes(): Collection
    {
        $user = auth()->user();
        if ($user?->role === 'teacher') {
            $classIds = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->pluck('class_id')
                ->unique();

            return SchoolClass::query()
                ->whereIn('id', $classIds)
                ->orderBy('level')
                ->get();
        }

        return SchoolClass::query()->orderBy('level')->get();
    }

    #[Computed]
    public function subjects(): Collection
    {
        if (! $this->classId) {
            return collect();
        }

        $user = auth()->user();
        if ($user?->role === 'teacher') {
            $subjectIds = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->where('class_id', $this->classId)
                ->pluck('subject_id')
                ->unique();

            return Subject::query()
                ->whereIn('id', $subjectIds)
                ->orderBy('name')
                ->get();
        }

        return SchoolClass::allSubjectsForClass($this->classId);
    }

    #[Computed]
    public function selectedClass(): ?SchoolClass
    {
        return $this->classId ? SchoolClass::find($this->classId) : null;
    }

    #[Computed]
    public function selectedSubject(): ?Subject
    {
        return $this->subjectId ? Subject::find($this->subjectId) : null;
    }

    #[Computed]
    public function topics(): Collection
    {
        if (! $this->classId || ! $this->subjectId) {
            return collect();
        }

        $query = SubjectTopic::query()
            ->where('class_id', $this->classId)
            ->where('subject_id', $this->subjectId)
            ->where('term', $this->term);

        if (! empty($this->session)) {
            $query->where('session', $this->session);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (! empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('learning_objectives', 'like', $term);
            });
        }

        return $query->orderByRaw('week_number IS NULL, week_number ASC')
                     ->orderBy('id', 'asc')
                     ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $all = SubjectTopic::query()
            ->where('class_id', $this->classId)
            ->where('subject_id', $this->subjectId)
            ->where('term', $this->term)
            ->when(! empty($this->session), fn ($q) => $q->where('session', $this->session))
            ->get();

        $total = $all->count();
        $completed = $all->where('status', 'completed')->count();
        $inProgress = $all->where('status', 'in_progress')->count();
        $upcoming = $all->where('status', 'upcoming')->count();
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return compact('total', 'completed', 'inProgress', 'upcoming', 'percent');
    }

    public function openCreateModal(): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $this->editingId = null;
        $maxWeek = SubjectTopic::query()
            ->where('class_id', $this->classId)
            ->where('subject_id', $this->subjectId)
            ->where('term', $this->term)
            ->max('week_number');

        $this->weekNumber = $maxWeek ? $maxWeek + 1 : 1;
        $this->title = '';
        $this->learningObjectives = '';
        $this->status = 'upcoming';
        $this->showModal = true;
    }

    public function editTopic(int $id): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $topic = SubjectTopic::findOrFail($id);
        $this->editingId = $topic->id;
        $this->weekNumber = $topic->week_number;
        $this->title = $topic->title;
        $this->learningObjectives = (string) $topic->learning_objectives;
        $this->status = $topic->status;
        $this->showModal = true;
    }

    public function saveTopic(): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $this->validate([
            'classId'            => 'required|exists:classes,id',
            'subjectId'          => 'required|exists:subjects,id',
            'term'               => 'required|integer|between:1,3',
            'title'              => 'required|string|max:255',
            'weekNumber'         => 'nullable|integer|min:1|max:52',
            'learningObjectives' => 'nullable|string',
            'status'             => 'required|in:upcoming,in_progress,completed',
        ]);

        SubjectTopic::updateOrCreate(
            ['id' => $this->editingId],
            [
                'class_id'            => $this->classId,
                'subject_id'          => $this->subjectId,
                'term'                => $this->term,
                'session'             => $this->session,
                'week_number'         => $this->weekNumber,
                'title'               => trim($this->title),
                'learning_objectives' => trim($this->learningObjectives) ?: null,
                'status'              => $this->status,
                'created_by'          => auth()->id(),
            ]
        );

        $this->showModal = false;
        $this->dispatch('alert', message: $this->editingId ? 'Topic updated successfully!' : 'New topic added to curriculum!', type: 'success');
        $this->editingId = null;
    }

    public function toggleStatus(int $id): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $topic = SubjectTopic::findOrFail($id);
        $nextStatus = match ($topic->status) {
            'upcoming'    => 'in_progress',
            'in_progress' => 'completed',
            'completed'   => 'upcoming',
            default       => 'upcoming',
        };

        $topic->status = $nextStatus;
        $topic->save();

        $this->dispatch('alert', message: "Topic marked as {$nextStatus}!", type: 'success');
    }

    public function deleteTopic(int $id): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $topic = SubjectTopic::findOrFail($id);
        $topic->delete();

        $this->dispatch('alert', message: 'Topic deleted from curriculum.', type: 'info');
    }

    #[Computed]
    public function curriculumDocument(): ?CurriculumDocument
    {
        if (! $this->classId || ! $this->subjectId) {
            return null;
        }

        return CurriculumDocument::forClassSubjectTerm(
            $this->classId,
            $this->subjectId,
            $this->term,
            $this->session
        )->latest()->first();
    }

    public function uploadCurriculumDocument(): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $this->validate([
            'classId'            => 'required|exists:classes,id',
            'subjectId'          => 'required|exists:subjects,id',
            'term'               => 'required|integer|between:1,3',
            'curriculumDocFile'  => 'required|file|mimes:pdf,doc,docx|max:15360',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $path = $this->curriculumDocFile->store("curriculum/{$tenantId}", 'public');

        // Check if existing document exists to replace
        $existing = CurriculumDocument::forClassSubjectTerm(
            $this->classId,
            $this->subjectId,
            $this->term,
            $this->session
        )->first();

        if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
            Storage::disk('public')->delete($existing->file_path);
        }

        CurriculumDocument::updateOrCreate(
            [
                'tenant_id'  => $tenantId,
                'class_id'   => $this->classId,
                'subject_id' => $this->subjectId,
                'term'       => $this->term,
                'session'    => $this->session,
            ],
            [
                'title'        => ($this->selectedSubject?->name ?? 'Subject') . " Term {$this->term} Scheme of Work",
                'file_path'    => $path,
                'file_name'    => $this->curriculumDocFile->getClientOriginalName(),
                'file_size'    => $this->curriculumDocFile->getSize(),
                'file_type'    => strtolower($this->curriculumDocFile->getClientOriginalExtension() ?: 'pdf'),
                'uploaded_by'  => auth()->id(),
            ]
        );

        $this->curriculumDocFile = null;
        $this->dispatch('alert', message: 'Syllabus document uploaded successfully!', type: 'success');
    }

    public function deleteCurriculumDocument(int $id): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $doc = CurriculumDocument::findOrFail($id);
        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();

        $this->dispatch('alert', message: 'Syllabus document removed.', type: 'info');
    }

    public function openImportModal(): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $this->csvFile = null;
        $this->rawTextTopics = '';
        $this->showImportModal = true;
    }

    public function downloadCsvTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="curriculum_topics_template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Week', 'Topic Title', 'Learning Objectives']);
            fputcsv($handle, [1, 'Number Systems & Place Value', 'Understand whole numbers and expanded place value notation']);
            fputcsv($handle, [2, 'Fractions and Decimals', 'Addition, subtraction, and conversion of common fractions']);
            fputcsv($handle, [3, 'Basic Algebra & Variables', 'Introduction to variables, algebraic expressions and terms']);
            fclose($handle);
        }, 200, $headers);
    }

    public function importCsvTopics(): void
    {
        abort_if(auth()->user()?->role === 'proprietor', 403, 'Proprietor has read-only access.');

        $this->validate([
            'classId'   => 'required|exists:classes,id',
            'subjectId' => 'required|exists:subjects,id',
            'term'      => 'required|integer|between:1,3',
        ]);

        $importedCount = 0;

        if ($this->csvFile) {
            $this->validate([
                'csvFile' => 'required|file|mimes:csv,txt|max:5120',
            ]);

            $path = $this->csvFile->getRealPath();
            if (($handle = fopen($path, 'r')) !== false) {
                $headerRow = fgetcsv($handle);
                $headers = array_map(fn($h) => strtolower(trim((string)$h)), $headerRow ?: []);

                $weekIdx = -1;
                $titleIdx = -1;
                $objIdx = -1;

                foreach ($headers as $idx => $h) {
                    if (str_contains($h, 'week')) $weekIdx = $idx;
                    elseif (str_contains($h, 'topic') || str_contains($h, 'title')) $titleIdx = $idx;
                    elseif (str_contains($h, 'objective') || str_contains($h, 'learn') || str_contains($h, 'desc')) $objIdx = $idx;
                }

                if ($titleIdx === -1) {
                    $weekIdx = 0;
                    $titleIdx = 1;
                    $objIdx = 2;
                }

                while (($row = fgetcsv($handle)) !== false) {
                    if (empty(array_filter($row))) continue;

                    $title = isset($row[$titleIdx]) ? trim((string)$row[$titleIdx]) : '';
                    if (empty($title)) continue;

                    $rawWeek = isset($row[$weekIdx]) ? trim((string)$row[$weekIdx]) : '';
                    preg_match('/\d+/', $rawWeek, $matches);
                    $weekNum = !empty($matches[0]) ? (int)$matches[0] : null;

                    $objectives = ($objIdx !== -1 && isset($row[$objIdx])) ? trim((string)$row[$objIdx]) : null;

                    SubjectTopic::create([
                        'tenant_id'           => auth()->user()->tenant_id,
                        'class_id'            => $this->classId,
                        'subject_id'          => $this->subjectId,
                        'term'                => $this->term,
                        'session'             => $this->session,
                        'week_number'         => $weekNum,
                        'title'               => $title,
                        'learning_objectives' => $objectives ?: null,
                        'status'              => 'upcoming',
                        'created_by'          => auth()->id(),
                    ]);
                    $importedCount++;
                }
                fclose($handle);
            }
        } elseif (! empty(trim($this->rawTextTopics))) {
            $lines = explode("\n", str_replace("\r", '', $this->rawTextTopics));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $weekNum = null;
                $title = $line;
                $objectives = null;

                if (preg_match('/^(?:Week\s*)?(\d+)[:.\-\)]\s*(.*)$/i', $line, $wMatches)) {
                    $weekNum = (int)$wMatches[1];
                    $rest = trim($wMatches[2]);
                    if (str_contains($rest, ' - ')) {
                        [$title, $objectives] = explode(' - ', $rest, 2);
                    } elseif (str_contains($rest, ' : ')) {
                        [$title, $objectives] = explode(' : ', $rest, 2);
                    } else {
                        $title = $rest;
                    }
                } elseif (str_contains($line, ' - ')) {
                    [$title, $objectives] = explode(' - ', $line, 2);
                }

                SubjectTopic::create([
                    'tenant_id'           => auth()->user()->tenant_id,
                    'class_id'            => $this->classId,
                    'subject_id'          => $this->subjectId,
                    'term'                => $this->term,
                    'session'             => $this->session,
                    'week_number'         => $weekNum,
                    'title'               => trim($title),
                    'learning_objectives' => $objectives ? trim($objectives) : null,
                    'status'              => 'upcoming',
                    'created_by'          => auth()->id(),
                ]);
                $importedCount++;
            }
        }

        $this->csvFile = null;
        $this->rawTextTopics = '';
        $this->showImportModal = false;

        $this->dispatch('alert', message: "Successfully imported {$importedCount} topic(s) into the scheme of work!", type: 'success');
    }

    public function render()
    {
        return view('livewire.curriculum.topics-manager');
    }
}
