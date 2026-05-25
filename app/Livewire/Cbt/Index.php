<?php

namespace App\Livewire\Cbt;

use App\Models\AcademicSession;
use App\Models\CbtExam;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectAllocation;
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

    public string $title = '';
    public ?int $classId = null;
    public ?int $subjectId = null;
    public int $durationMinutes = 30;
    public ?int $term = null;
    public string $session = '';

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
        $this->creating = true;
        $this->resetValidation();
    }

    public function cancelCreate(): void
    {
        $this->creating = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->title = '';
        $this->classId = null;
        $this->subjectId = null;
        $this->durationMinutes = 30;
        $this->term = \App\Models\AcademicTerm::activeTermNumber();
        $this->session = AcademicSession::activeName() ?: $this->defaultSession();
    }

    public function updatedClassId(): void
    {
        $this->subjectId = null;
        $this->resetValidation();
        unset($this->subjects);
    }

    public function updatedSubjectId(): void
    {
        $this->resetValidation();
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
        abort_unless((bool) $user, 403);

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

    public function createExam()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'classId' => ['required', 'integer', 'exists:classes,id'],
            'subjectId' => ['required', 'integer', 'exists:subjects,id'],
            'session' => ['nullable', 'string', 'max:9'],
            'term' => ['required', 'integer', 'min:1', 'max:3'],
            'durationMinutes' => ['required', 'integer', 'min:1', 'max:300'],
        ]);

        if ($user->role === 'teacher') {
            $allocated = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->where('class_id', $data['classId'])
                ->where('subject_id', $data['subjectId'])
                ->exists();

            if (! $allocated) {
                $this->addError('subjectId', 'You are not allocated to this subject for this class.');
                return;
            }
        }

        $exam = DB::transaction(function () use ($user, $data) {
            $code = $this->generateAccessCode();

            return CbtExam::query()->create([
                'title' => trim($data['title']),
                'class_id' => (int) $data['classId'],
                'subject_id' => (int) $data['subjectId'],
                'term' => (int) $data['term'],
                'session' => trim((string) ($data['session'] ?? '')) !== '' ? trim((string) $data['session']) : null,
                'duration_minutes' => (int) $data['durationMinutes'],
                'status' => 'draft',
                'access_code' => $code,
                'created_by' => $user->id,
                'assigned_teacher_id' => $user->id,
            ]);
        });

        $this->cancelCreate();

        return redirect()->route('cbt.exams.edit', ['exam' => $exam, 'tab' => 'questions']);
    }

    private function generateAccessCode(): string
    {
        for ($i = 0; $i < 20; $i++) {
            $code = 'CBT-'.strtoupper(bin2hex(random_bytes(3)));
            if (! CbtExam::query()->where('access_code', $code)->exists()) {
                return $code;
            }
        }
        return 'CBT-'.strtoupper(\Illuminate\Support\Str::random(8));
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
            ])
            ->withCount(['questions', 'attempts'])
            ->orderByRaw("CASE status WHEN 'live' THEN 0 WHEN 'pending_approval' THEN 1 WHEN 'draft' THEN 2 WHEN 'ended' THEN 3 ELSE 4 END")
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
