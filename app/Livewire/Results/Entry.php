<?php

namespace App\Livewire\Results;

use App\Support\Audit;
use App\Models\AcademicSession;
use App\Models\InAppNotification;
use App\Models\ResultPublication;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\ScoreSubmission;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Score Entry')]
class Entry extends Component
{
    public $classId = null;
    public $subjectId = null;
    public $term = null;
    public string $session = '';
    public ?int $rejectingId = null;
    public string $rejectNote = '';
    
    /**
     * @var array<int, string> Track validation errors for specific student fields
     */
    public array $validationErrors = [];

    /**
     * @var array<int, array{ca1:int|null, ca2:int|null, exam:int|null}>
     */
    public array $scores = [];

    public string $activeTab = 'scores';
    public array $bulkPsychomotorScores = [];
    public array $psychomotorTraits = [
        'Handwriting', 'Verbal Fluency', 'Games / Sports', 'Craft / Drawing',
        'Musical Skills', 'Punctuality', 'Neatness', 'Politeness / Courtesy',
        'Honesty', 'Self-Control', 'Attentiveness', 'Relationship with Others'
    ];

    public bool $showPsychomotorModal = false;
    public ?int $selectedStudentId = null;
    public string $selectedStudentName = '';
    public array $psychomotorScores = [];

    /**
     * @return array{ca1:int, ca2:int, exam:int}
     */
    public function maxMarks(): array
    {
        return [
            'ca1' => max(0, (int) config('academyhub.results_ca1_max', 20)),
            'ca2' => max(0, (int) config('academyhub.results_ca2_max', 20)),
            'exam' => max(0, (int) config('academyhub.results_exam_max', 60)),
        ];
    }

    /**
     * @return array<string, string> Map of slugified trait keys to display/DB names
     */
    public function traitMap(): array
    {
        return [
            'handwriting' => 'Handwriting',
            'verbal_fluency' => 'Verbal Fluency',
            'games_sports' => 'Games / Sports',
            'craft_drawing' => 'Craft / Drawing',
            'musical_skills' => 'Musical Skills',
            'punctuality' => 'Punctuality',
            'neatness' => 'Neatness',
            'politeness_courtesy' => 'Politeness / Courtesy',
            'honesty' => 'Honesty',
            'self_control' => 'Self-Control',
            'attentiveness' => 'Attentiveness',
            'relationship_with_others' => 'Relationship with Others',
        ];
    }

    public function mount(): void
    {
        $this->session = $this->session ?: $this->defaultSession();

        $user = auth()->user();
        $defaultClassId = (int) request('class', 0);
        $defaultSubjectId = (int) request('subject', 0);
        $defaultTerm = (int) request('term', 0);
        $defaultSession = trim((string) request('session', ''));

        if ($user?->role === 'teacher' && $defaultClassId > 0) {
            $allowedClass = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->where('class_id', $defaultClassId)
                ->exists();

            if (! $allowedClass) {
                $defaultClassId = 0;
                $defaultSubjectId = 0;
            }
        }

        if ($user?->role === 'teacher' && $defaultClassId > 0 && $defaultSubjectId > 0) {
            $allowedSubject = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->where('class_id', $defaultClassId)
                ->where('subject_id', $defaultSubjectId)
                ->exists();

            if (! $allowedSubject) {
                $defaultSubjectId = 0;
            }
        }

        if ($defaultClassId > 0) {
            $this->classId = $defaultClassId;
        }

        if ($defaultSubjectId > 0) {
            $this->subjectId = $defaultSubjectId;
        }

        if ($defaultTerm >= 1 && $defaultTerm <= 3) {
            $this->term = $defaultTerm;
        } else {
            $this->term = $this->term ?: \App\Models\AcademicTerm::activeTermNumber();
        }

        if ($defaultSession !== '') {
            $this->session = $defaultSession;
        }

        if ($this->classId && $this->subjectId) {
            $this->loadExistingScores();
        }
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

        if ($user?->role === 'admin') {
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
    public function students()
    {
        if (! $this->classId) {
            return collect();
        }

        $user = auth()->user();
        if ($user?->role === 'teacher') {
            $allowed = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->where('class_id', $this->classId)
                ->exists();

            if (! $allowed) {
                return collect();
            }
        }

        return Student::query()
            ->where('class_id', $this->classId)
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->get();
    }

    #[Computed]
    public function isPublished(): bool
    {
        if (! $this->classId || ! $this->session) {
            return false;
        }

        return ResultPublication::query()
            ->where('class_id', $this->classId)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->whereNotNull('published_at')
            ->exists();
    }

    #[Computed]
    public function submission()
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'teacher') {
            return null;
        }

        if (! $this->classId || ! $this->subjectId || ! $this->session) {
            return null;
        }

        return ScoreSubmission::query()
            ->where('teacher_id', $user->id)
            ->where('class_id', $this->classId)
            ->where('subject_id', $this->subjectId)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->first();
    }

    public function openPsychomotor(int $studentId, string $studentName): void
    {
        $this->selectedStudentId = $studentId;
        $this->selectedStudentName = $studentName;
        
        $existing = \App\Models\PsychomotorScore::where('student_id', $studentId)
            ->where('class_id', $this->classId)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->first();

        $traits = $existing?->traits ?? [];
        $this->psychomotorScores = [];

        foreach ($this->traitMap() as $slug => $label) {
            $this->psychomotorScores[$slug] = $traits[$label] ?? $traits[$slug] ?? ($label === 'Games / Sports' || $label === 'Musical Skills' ? 'Average' : ($label === 'Punctuality' || $label === 'Politeness / Courtesy' || $label === 'Honesty' || $label === 'Relationship with Others' ? 'Excellent' : 'Good'));
        }

        $this->showPsychomotorModal = true;
    }

    public function savePsychomotor(): void
    {
        if (!$this->selectedStudentId) return;

        $dbTraits = [];
        foreach ($this->traitMap() as $slug => $label) {
            $dbTraits[$label] = $this->psychomotorScores[$slug] ?? 'Good';
        }

        \App\Models\PsychomotorScore::updateOrCreate(
            [
                'student_id' => $this->selectedStudentId,
                'class_id' => $this->classId,
                'term' => $this->term,
                'session' => $this->session,
            ],
            [
                'traits' => $dbTraits,
            ]
        );

        $this->showPsychomotorModal = false;
        $this->dispatch('alert', message: 'Psychomotor scores saved successfully!', type: 'success');
    }

    public function closePsychomotor(): void
    {
        $this->showPsychomotorModal = false;
    }

    public function setAllTraitsForSelected(string $value): void
    {
        foreach ($this->traitMap() as $slug => $label) {
            $this->psychomotorScores[$slug] = $value;
        }
        $this->dispatch('alert', message: "All traits set to {$value} for this student!", type: 'info');
    }

    public function setAllBulkTraits(string $value): void
    {
        foreach ($this->students as $student) {
            foreach ($this->traitMap() as $slug => $label) {
                $this->bulkPsychomotorScores[$student->id][$slug] = $value;
            }
        }
        $this->dispatch('alert', message: "Set all traits for all students to {$value}!", type: 'info');
    }

    public function applyTraitToAll(string $slug, string $value): void
    {
        foreach ($this->students as $student) {
            $this->bulkPsychomotorScores[$student->id][$slug] = $value;
        }
        $label = $this->traitMap()[$slug] ?? $slug;
        $this->dispatch('alert', message: "Set {$label} to {$value} for all students!", type: 'info');
    }

    #[Computed]
    public function submissions()
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'admin') {
            return collect();
        }

        return ScoreSubmission::query()
            ->with([
                'teacher:id,name',
                'schoolClass:id,name',
                'subject:id,name',
            ])
            ->orderByRaw("CASE status WHEN 'submitted' THEN 0 WHEN 'rejected' THEN 1 WHEN 'approved' THEN 2 ELSE 3 END")
            ->orderByDesc('submitted_at')
            ->limit(30)
            ->get();
    }

    public function updatedClassId($value): void
    {
        $this->classId = $value !== '' && $value !== null ? (int) $value : null;
        $this->subjectId = null;
        $this->scores = [];
        $this->validationErrors = [];
        // Bust Livewire 3 computed property cache
        unset($this->subjects, $this->students, $this->isPublished, $this->submission, $this->submissions);
    }

    public function updatedSubjectId($value): void
    {
        $this->subjectId = $value !== '' && $value !== null ? (int) $value : null;
        $this->validationErrors = [];
        $this->loadExistingScores();
        // Bust Livewire 3 computed property cache
        unset($this->students, $this->isPublished, $this->submission, $this->submissions);
    }

    public function updatedTerm($value): void
    {
        $this->term = $value !== '' && $value !== null ? (int) $value : null;
        $this->validationErrors = [];
        $this->loadExistingScores();
        unset($this->isPublished, $this->submission, $this->submissions);
    }

    public function updatedSession(): void
    {
        $this->validationErrors = [];
        $this->loadExistingScores();
        unset($this->isPublished, $this->submission, $this->submissions);
    }

    public function setTab(string $tab): void
    {
        try {
            $this->activeTab = $tab;
            $this->loadExistingScores();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ScoreEntry::setTab failed', ['tab' => $tab, 'error' => $e->getMessage()]);
            $this->dispatch('alert', message: 'Could not switch tab. Please try again.', type: 'error');
        }
    }

    public function updatedScores(mixed $value, ?string $name = null): void
    {
        if ($name === null || $name === '') {
            return;
        }

        $parts = explode('.', $name);
        if (count($parts) < 2) {
            return;
        }

        if (! is_numeric($parts[0]) && isset($parts[1]) && is_numeric($parts[1])) {
            array_shift($parts);
        }

        if (! isset($parts[0])) {
            return;
        }

        $studentId = (int) $parts[0];
        $field = (string) end($parts);
        $errorKey = "{$studentId}.{$field}";

        $maxMarks = $this->maxMarks();
        if (! array_key_exists($field, $maxMarks)) {
            return;
        }

        if ($value === '' || $value === null) {
            $value = 0;
        }

        $score = (int) $value;
        $max = $maxMarks[$field];

        // Clear any existing error for this field
        unset($this->validationErrors[$errorKey]);

        if ($score < 0) {
            $this->scores[$studentId][$field] = 0;
            $this->validationErrors[$errorKey] = 'negative';
            $this->dispatch('alert', message: 'Scores cannot be negative.', type: 'warning');
            $this->dispatch('shake-row', studentId: $studentId, field: $field);
            return;
        }

        if ($score > $max) {
            $label = strtoupper($field);
            $this->scores[$studentId][$field] = $max;
            $this->validationErrors[$errorKey] = 'exceeded';
            $this->dispatch('alert', message: "{$label} max is {$max}.", type: 'warning');
            $this->dispatch('shake-row', studentId: $studentId, field: $field);
        }
    }

    public function save(): void
    {
        if (! $this->classId || ! $this->subjectId) {
            throw ValidationException::withMessages([
                'classId' => 'Select a class and subject.',
            ]);
        }

        $user = auth()->user();
        abort_unless($user && $user->hasPermission('results.entry'), 403);

        if ($this->isPublished) {
            throw ValidationException::withMessages([
                'scores' => 'Results have been published and are locked. Ask admin to unpublish to make changes.',
            ]);
        }

        if ($user?->role === 'teacher') {
            $submission = $this->submission;
            if ($submission && in_array($submission->status, ['submitted', 'approved'], true)) {
                throw ValidationException::withMessages([
                    'scores' => 'This score entry has already been submitted to admin.',
                ]);
            }
        }

        if ($user?->role !== 'admin') {
            $allowed = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->where('class_id', $this->classId)
                ->where('subject_id', $this->subjectId)
                ->exists();

            abort_unless($allowed, 403);
        }

        $this->validate([
            'term' => ['required', 'integer', 'between:1,3'],
            'session' => ['required', 'string', 'max:9'],
        ]);

        $maxMarks = $this->maxMarks();
        $maxTotal = $maxMarks['ca1'] + $maxMarks['ca2'] + $maxMarks['exam'];
        if ($maxTotal <= 0) {
            throw ValidationException::withMessages([
                'scores' => 'Invalid scoring settings. Please update max marks in Settings.',
            ]);
        }

        DB::transaction(function () use ($maxMarks) {
            foreach ($this->students as $student) {
                $row = $this->scores[$student->id] ?? [];

                $ca1 = (int) ($row['ca1'] ?? 0);
                $ca2 = (int) ($row['ca2'] ?? 0);
                $exam = (int) ($row['exam'] ?? 0);

                if (
                    $ca1 < 0 || $ca1 > $maxMarks['ca1']
                    || $ca2 < 0 || $ca2 > $maxMarks['ca2']
                    || $exam < 0 || $exam > $maxMarks['exam']
                ) {
                    throw ValidationException::withMessages([
                        'scores' => "Invalid score range for {$student->full_name}.",
                    ]);
                }

                Score::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $this->subjectId,
                        'class_id' => $this->classId,
                        'term' => $this->term,
                        'session' => $this->session,
                    ],
                    [
                        'ca1' => $ca1,
                        'ca2' => $ca2,
                        'exam' => $exam,
                    ]
                );
            }
        });

        $this->dispatch('alert', message: 'Scores saved.', type: 'success');
        $this->validationErrors = []; // Clear all validation errors after successful save
        $this->loadExistingScores();
    }

    public function submitScores(): void
    {
        $this->save();

        $user = auth()->user();
        abort_unless($user?->role === 'teacher' && $user->hasPermission('results.entry'), 403);

        $submission = ScoreSubmission::query()->updateOrCreate(
            [
                'teacher_id' => $user->id,
                'class_id' => $this->classId,
                'subject_id' => $this->subjectId,
                'term' => $this->term,
                'session' => $this->session,
            ],
            [
                'status' => 'submitted',
                'note' => null,
                'submitted_at' => now(),
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        Audit::log('results.submission_created', $submission, [
            'teacher_id' => $submission->teacher_id,
            'class_id' => $submission->class_id,
            'subject_id' => $submission->subject_id,
            'term' => $submission->term,
            'session' => $submission->session,
        ]);

        $adminIds = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->pluck('id');

        $className = SchoolClass::query()->whereKey($this->classId)->value('name') ?? 'Class';
        $subjectName = Subject::query()->whereKey($this->subjectId)->value('name') ?? 'Subject';

        foreach ($adminIds as $adminId) {
            InAppNotification::query()->create([
                'user_id' => $adminId,
                'title' => 'Score submission',
                'body' => "{$user->name} submitted scores for {$className} / {$subjectName} ({$this->session} T{$this->term}).",
                'link' => route('results.entry', [
                    'class' => $submission->class_id,
                    'subject' => $submission->subject_id,
                    'term' => $submission->term,
                    'session' => $submission->session,
                ]),
            ]);
        }

        $this->dispatch('alert', message: 'Submitted to admin.', type: 'success');
        $this->dispatch('$refresh');
    }

    public function approveSubmission(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('results.review'), 403);

        $submission = ScoreSubmission::query()->with('teacher:id,name')->findOrFail($id);

        if ($submission->status !== 'submitted') {
            $this->dispatch('alert', message: 'This submission has already been reviewed.', type: 'warning');
            return;
        }

        $submission->status = 'approved';
        $submission->note = null;
        $submission->reviewed_by = $user->id;
        $submission->reviewed_at = now();
        $submission->save();

        Audit::log('results.submission_approved', $submission, [
            'reviewed_by' => $submission->reviewed_by,
        ]);

        InAppNotification::query()->create([
            'user_id' => $submission->teacher_id,
            'title' => 'Score submission approved',
            'body' => "Your score submission was approved ({$submission->session} T{$submission->term}).",
            'link' => route('results.entry', [
                'class' => $submission->class_id,
                'subject' => $submission->subject_id,
                'term' => $submission->term,
                'session' => $submission->session,
            ]),
        ]);

        if ($this->rejectingId === $id) {
            $this->rejectingId = null;
            $this->rejectNote = '';
            $this->resetValidation();
        }

        unset($this->submissions);
        $this->dispatch('alert', message: 'Submission approved.', type: 'success');
        $this->dispatch('$refresh');
    }

    public function startReject(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('results.review'), 403);

        $submission = ScoreSubmission::query()->findOrFail($id);
        if ($submission->status !== 'submitted') {
            $this->dispatch('alert', message: 'Only submitted items can be rejected.', type: 'warning');
            return;
        }

        $this->rejectingId = $id;
        $this->rejectNote = '';
        $this->resetValidation();
    }

    public function cancelReject(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('results.review'), 403);

        $this->rejectingId = null;
        $this->rejectNote = '';
        $this->resetValidation();
    }

    public function confirmReject(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('results.review'), 403);

        $id = (int) ($this->rejectingId ?? 0);
        if ($id <= 0) {
            return;
        }

        $this->validate([
            'rejectNote' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $submission = ScoreSubmission::query()->findOrFail($id);
        if ($submission->status !== 'submitted') {
            $this->dispatch('alert', message: 'This submission has already been reviewed.', type: 'warning');
            $this->cancelReject();
            return;
        }

        $submission->status = 'rejected';
        $submission->note = trim($this->rejectNote);
        $submission->reviewed_by = $user->id;
        $submission->reviewed_at = now();
        $submission->save();

        Audit::log('results.submission_rejected', $submission, [
            'reviewed_by' => $submission->reviewed_by,
            'note' => $submission->note,
        ]);

        InAppNotification::query()->create([
            'user_id' => $submission->teacher_id,
            'title' => 'Score submission rejected',
            'body' => "Your score submission was rejected: {$submission->note}",
            'link' => route('results.entry', [
                'class' => $submission->class_id,
                'subject' => $submission->subject_id,
                'term' => $submission->term,
                'session' => $submission->session,
            ]),
        ]);

        $this->rejectingId = null;
        $this->rejectNote = '';
        $this->resetValidation();
        $this->dispatch('alert', message: 'Submission rejected.', type: 'success');
        $this->dispatch('$refresh');
    }

    private function loadExistingScores(): void
    {
        try {
            $this->scores = [];
            $this->bulkPsychomotorScores = [];

            if (! $this->classId || ! $this->session) {
                return;
            }

            if ($this->activeTab === 'scores' && $this->subjectId) {
                $existing = Score::query()
                    ->where('class_id', $this->classId)
                    ->where('subject_id', $this->subjectId)
                    ->where('term', $this->term)
                    ->where('session', $this->session)
                    ->get()
                    ->keyBy('student_id');

                foreach ($this->students as $student) {
                    $score = $existing->get($student->id);
                    $this->scores[$student->id] = [
                        'ca1'  => $score?->ca1  ?? 0,
                        'ca2'  => $score?->ca2  ?? 0,
                        'exam' => $score?->exam ?? 0,
                    ];
                }
            }

            if ($this->activeTab === 'psychomotor') {
                $existing = \App\Models\PsychomotorScore::where('class_id', $this->classId)
                    ->where('term', $this->term)
                    ->where('session', $this->session)
                    ->get()
                    ->keyBy('student_id');

                foreach ($this->students as $student) {
                    $ps     = $existing->get($student->id);
                    $traits = $ps?->traits ?? [];

                    $mapped = [];
                    foreach ($this->traitMap() as $slug => $label) {
                        $mapped[$slug] = $traits[$label] ?? $traits[$slug] ?? ($label === 'Games / Sports' || $label === 'Musical Skills' ? 'Average' : ($label === 'Punctuality' || $label === 'Politeness / Courtesy' || $label === 'Honesty' || $label === 'Relationship with Others' ? 'Excellent' : 'Good'));
                    }
                    $this->bulkPsychomotorScores[$student->id] = $mapped;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ScoreEntry::loadExistingScores failed', ['error' => $e->getMessage()]);
            $this->dispatch('alert', message: 'Could not load scores. Please refresh the page.', type: 'error');
        }
    }

    public function saveBulkPsychomotor(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('results.entry'), 403);

        if ($this->isPublished) {
            $this->dispatch('alert', message: 'Results are published and locked.', type: 'error');
            return;
        }

        DB::transaction(function () {
            foreach ($this->bulkPsychomotorScores as $studentId => $mappedTraits) {
                $dbTraits = [];
                foreach ($this->traitMap() as $slug => $label) {
                    $dbTraits[$label] = $mappedTraits[$slug] ?? 'Good';
                }
                \App\Models\PsychomotorScore::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_id' => $this->classId,
                        'term' => $this->term,
                        'session' => $this->session,
                    ],
                    [
                        'traits' => $dbTraits,
                    ]
                );
            }
        });

        $this->dispatch('alert', message: 'All psychomotor scores saved!', type: 'success');
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



    public function render()
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasPermission('results.entry') || $user->hasPermission('results.review')), 403);

        return view('livewire.results.entry');
    }
}
