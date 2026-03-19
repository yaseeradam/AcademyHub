<?php

namespace App\Livewire\Cbt;

use App\Models\AcademicSession;
use App\Models\CbtExam;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('CBT')]
class Index extends Component
{
    public bool $creating = false;
    public bool $requesting = false;

    public string $title = '';
    public string $description = '';
    public ?int $classId = null;
    public ?int $subjectId = null;
    public int $durationMinutes = 30;
    public ?int $term = null;
    public string $session = '';

    public ?int $teacherId = null;
    public string $requestNote = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->term = $this->term ?: \App\Models\AcademicTerm::activeTermNumber();
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        if (trim($this->session) === '') {
            $this->session = AcademicSession::activeName() ?: $this->defaultSession();
        }
    }

    public function startCreate(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'teacher', 403);

        $this->creating = true;
        $this->requesting = false;
        $this->resetValidation();
    }

    public function startRequest(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $this->requesting = true;
        $this->creating = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function cancelCreate(): void
    {
        $this->creating = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function cancelRequest(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $this->requesting = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->title = '';
        $this->description = '';
        $this->classId = null;
        $this->subjectId = null;
        $this->durationMinutes = 30;
        $this->term = \App\Models\AcademicTerm::activeTermNumber();
        $this->session = AcademicSession::activeName() ?: $this->defaultSession();
        $this->teacherId = null;
        $this->requestNote = '';
    }

    public function updatedClassId(): void
    {
        $this->subjectId = null;
        $this->teacherId = null;
        $this->resetValidation();
        unset($this->subjects, $this->teachers);
    }

    public function updatedSubjectId(): void
    {
        $this->teacherId = null;
        $this->resetValidation();
        unset($this->teachers);
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
        if (! $this->classId) {
            return collect();
        }

        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->role === 'admin') {
            $ids = SubjectAllocation::query()->where('class_id', $this->classId)->pluck('subject_id')->unique();

            return $ids->isNotEmpty()
                ? Subject::query()->whereIn('id', $ids)->orderBy('name')->get()
                : Subject::query()->orderBy('name')->get();
        }

        $ids = SubjectAllocation::query()
            ->where('class_id', $this->classId)
            ->where('teacher_id', $user->id)
            ->pluck('subject_id')
            ->unique();

        return Subject::query()->whereIn('id', $ids)->orderBy('name')->get();
    }

    #[Computed]
    public function teachers()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->role !== 'admin') {
            return collect();
        }

        if (! $this->classId || ! $this->subjectId) {
            return collect();
        }

        $teacherIds = SubjectAllocation::query()
            ->where('class_id', $this->classId)
            ->where('subject_id', $this->subjectId)
            ->pluck('teacher_id')
            ->unique()
            ->filter();

        if ($teacherIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $teacherIds)
            ->where('role', 'teacher')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function createExam()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'teacher', 403);

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'classId' => ['required', 'integer', 'exists:classes,id'],
            'subjectId' => ['required', 'integer', 'exists:subjects,id'],
            'session' => ['nullable', 'string', 'max:9'],
            'term' => ['required', 'integer', 'min:1', 'max:3'],
            'durationMinutes' => ['required', 'integer', 'min:1', 'max:300'],
        ]);

        $allocated = SubjectAllocation::query()
            ->where('teacher_id', $user->id)
            ->where('class_id', $data['classId'])
            ->where('subject_id', $data['subjectId'])
            ->exists();

        if (! $allocated) {
            $this->addError('subjectId', 'You are not allocated to this subject for this class.');
            return;
        }

        $exam = DB::transaction(function () use ($user, $data) {
            return CbtExam::query()->create([
                'title' => trim($data['title']),
                'description' => trim((string) ($data['description'] ?? '')) !== '' ? trim((string) $data['description']) : null,
                'class_id' => (int) $data['classId'],
                'subject_id' => (int) $data['subjectId'],
                'term' => (int) $data['term'],
                'session' => trim((string) ($data['session'] ?? '')) !== '' ? trim((string) $data['session']) : null,
                'duration_minutes' => (int) $data['durationMinutes'],
                'status' => 'draft',
                'created_by' => $user->id,
                'assigned_teacher_id' => $user->id,
            ]);
        });

        $this->cancelCreate();

        return redirect()->route('cbt.exams.edit', $exam);
    }

    public function createRequest()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'classId' => ['required', 'integer', 'exists:classes,id'],
            'subjectId' => ['required', 'integer', 'exists:subjects,id'],
            'teacherId' => ['required', 'integer', 'exists:users,id'],
            'requestNote' => ['nullable', 'string', 'max:2000'],
            'session' => ['nullable', 'string', 'max:9'],
            'term' => ['required', 'integer', 'min:1', 'max:3'],
            'durationMinutes' => ['required', 'integer', 'min:1', 'max:300'],
        ]);

        $allocated = SubjectAllocation::query()
            ->where('teacher_id', (int) $data['teacherId'])
            ->where('class_id', (int) $data['classId'])
            ->where('subject_id', (int) $data['subjectId'])
            ->exists();

        if (! $allocated) {
            $this->addError('teacherId', 'Selected teacher is not allocated to this subject for this class.');
            return;
        }

        $teacher = User::query()
            ->whereKey((int) $data['teacherId'])
            ->where('role', 'teacher')
            ->where('is_active', true)
            ->first();

        if (! $teacher) {
            $this->addError('teacherId', 'Teacher account not found or inactive.');
            return;
        }

        $exam = DB::transaction(function () use ($user, $teacher, $data) {
            return CbtExam::query()->create([
                'title' => trim($data['title']),
                'description' => trim((string) ($data['description'] ?? '')) !== '' ? trim((string) $data['description']) : null,
                'class_id' => (int) $data['classId'],
                'subject_id' => (int) $data['subjectId'],
                'term' => (int) $data['term'],
                'session' => trim((string) ($data['session'] ?? '')) !== '' ? trim((string) $data['session']) : null,
                'duration_minutes' => (int) $data['durationMinutes'],
                'status' => 'assigned',
                'created_by' => $user->id,
                'assigned_teacher_id' => (int) $teacher->id,
                'requested_by' => (int) $user->id,
                'requested_at' => now(),
                'request_note' => trim((string) ($data['requestNote'] ?? '')) !== '' ? trim((string) $data['requestNote']) : null,
            ]);
        });

        \App\Models\InAppNotification::query()->create([
            'user_id' => (int) $teacher->id,
            'title' => 'CBT question request',
            'body' => "Admin requested CBT questions: {$exam->title}.",
            'link' => route('cbt.exams.edit', $exam),
        ]);

        $this->cancelRequest();

        return redirect()->route('cbt.exams.edit', $exam);
    }

    private function defaultSession(): string
    {
        $year = (int) now()->format('Y');
        $next = $year + 1;

        return "{$year}/{$next}";
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        $query = CbtExam::query()
            ->with([
                'schoolClass:id,name',
                'subject:id,name',
                'creator:id,name',
                'assignedTeacher:id,name',
                'requester:id,name',
            ])
            ->withCount(['questions', 'attempts'])
            ->orderByRaw("CASE status WHEN 'submitted' THEN 0 WHEN 'rejected' THEN 1 WHEN 'assigned' THEN 2 WHEN 'draft' THEN 3 WHEN 'approved' THEN 4 ELSE 5 END")
            ->orderByDesc('id');

        if ($user->role === 'teacher') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_teacher_id', $user->id);
            });
        }

        if (trim($this->statusFilter) !== '') {
            $query->where('status', trim($this->statusFilter));
        }

        $exams = $query->limit(100)->get();

        return view('livewire.cbt.index', [
            'me' => $user,
            'exams' => $exams,
        ]);
    }
}
