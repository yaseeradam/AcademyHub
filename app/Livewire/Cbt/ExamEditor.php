<?php

namespace App\Livewire\Cbt;

use App\Models\CbtExam;
use App\Models\CbtAnswer;
use App\Models\CbtOption;
use App\Models\CbtQuestion;
use App\Models\CbtAttempt;
use App\Models\InAppNotification;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentNotification;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\Score;
use App\Models\User;
use App\Support\Audit;
use App\Support\CbtQuestionImporter;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('CBT Exam')]
class ExamEditor extends Component
{
    use WithFileUploads;

    public int $examId;

    public string $title = '';
    public string $description = '';
    public string $examType = 'academic';
    public ?int $classId = null;
    public ?int $subjectId = null;
    public int $durationMinutes = 30;
    public ?int $term = null;
    public string $session = '';

    public ?int $editingQuestionId = null;
    public string $questionPrompt = '';
    public int $questionMarks = 1;
    public string $questionType = 'mcq';

    /** @var array<int,string> */
    public array $optionLabels = ['', '', '', ''];

    public int $correctIndex = 0;

    public bool $showRejectForm = false;
    public string $reviewNote = '';

    public array $theoryComments = [];

    public ?int $editingAttemptIpId = null;
    public string $allowedIp = '';

    public bool $showQuestions = false;

    public string $startsAt = '';
    public string $endsAt = '';
    public string $pin = '';
    public int $graceMinutes = 0;
    public string $allowedCidrs = '';
    public bool $showScore = false;
    public ?int $reviewAttemptId = null;
    /** @var array<int, int|string|null> */
    public array $theoryMarks = [];
    public bool $showDeleteModal = false;
    public bool $showForwardModal = false;
    public ?int $forwardAttemptId = null;
    public ?int $forwardTeacherId = null;
    public string $tab = 'details';
    public bool $shuffleQuestions = false;

    // AI generation
    public bool $showAiPanel = false;
    public string $aiTopic = '';
    public int $aiCount = 5;
    public string $aiType = 'mcq';
    public int $aiMarks = 1;
    public bool $aiLoading = false;
    /** @var array<int, array<string,mixed>> */
    public array $aiPreview = [];

    // File import
    public bool $showImportPanel = false;
    public $importFile = null;
    /** @var array<int, array<string,mixed>> */
    public array $importPreview = [];

    public function mount(CbtExam $exam): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        if ($user->role === 'teacher') {
            $canAccess = (int) $exam->created_by === (int) $user->id
                || (int) ($exam->assigned_teacher_id ?? 0) === (int) $user->id;
            abort_unless($canAccess, 403);
        }

        $this->examId = (int) $exam->id;
        $this->tab = (string) request('tab', 'details');

        $this->fillFromExam($exam);
    }

    private function fillFromExam(CbtExam $exam): void
    {
        $this->title = (string) $exam->title;
        $this->description = (string) ($exam->description ?? '');
        $this->examType = (string) ($exam->exam_type ?? 'academic');
        $this->classId = $exam->class_id ? (int) $exam->class_id : null;
        $this->subjectId = (int) $exam->subject_id;
        $this->durationMinutes = (int) ($exam->duration_minutes ?? 30);
        $this->term = (int) ($exam->term ?? 1);
        $this->session = (string) ($exam->session ?? '');

        $this->startsAt = $exam->starts_at ? $exam->starts_at->format('Y-m-d\TH:i') : '';
        $this->endsAt = $exam->ends_at ? $exam->ends_at->format('Y-m-d\TH:i') : '';

        $this->pin = (string) ($exam->pin ?? '');
        $this->graceMinutes = (int) ($exam->grace_minutes ?? 0);
        $this->allowedCidrs = (string) ($exam->allowed_cidrs ?? '');
        $this->showScore = (bool) ($exam->show_score ?? false);
        $this->shuffleQuestions = (bool) ($exam->shuffle_questions ?? false);
    }

    #[Computed]
    public function exam(): CbtExam
    {
        return CbtExam::query()
            ->with([
                'schoolClass:id,name',
                'subject:id,name',
                'creator:id,name',
                'assignedTeacher:id,name',
                'requester:id,name',
                'questions.options',
                'attempts' => fn ($q) => $q
                    ->with(['student:id,admission_number,first_name,last_name,passport_photo'])
                    ->orderByDesc('submitted_at')
                    ->orderByDesc('id'),
            ])
            ->findOrFail($this->examId);
    }

    #[Computed]
    public function canEdit(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $exam = $this->exam;

        // Prevent editing if exam has any attempts (students have started)
        if ($exam->attempts()->exists()) {
            return false;
        }

        if ($user->role === 'admin') {
            return true; // Admin can always edit (questions/details) unless attempts exist
        }

        if ($user->role !== 'teacher') {
            return false;
        }

        $isAssignee = (int) $exam->created_by === (int) $user->id
            || (int) ($exam->assigned_teacher_id ?? 0) === (int) $user->id;

        return $isAssignee && $exam->status === 'draft';
    }

    #[Computed]
    public function classes()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $tenant = app()->bound('currentTenant') ? app('currentTenant') : $user->tenant;
        $allowedClassIds = [];
        if ($tenant) {
            $plugin = $tenant->activeMarketplaceComponents()->where('slug', 'cbt')->first();
            if ($plugin && $plugin->pivot) {
                $ids = $plugin->pivot->allowed_class_ids ?? [];
                if (is_string($ids)) {
                    $allowedClassIds = json_decode($ids, true) ?: [];
                } else {
                    $allowedClassIds = is_array($ids) ? $ids : [];
                }
            }
        }

        if ($user->role === 'admin') {
            return SchoolClass::query()
                ->whereIn('id', $allowedClassIds)
                ->orderBy('level')
                ->get();
        }

        return SchoolClass::query()
            ->whereIn('id', $allowedClassIds)
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

        if ($user->role === 'admin' || $user->is_super_admin) {
            // Admins see ALL subjects — not limited by teacher allocations or defaults
            return Subject::query()->orderBy('name')->get();
        }

        $ids = SubjectAllocation::query()
            ->where('class_id', $this->classId)
            ->where('teacher_id', $user->id)
            ->pluck('subject_id')
            ->unique();

        return Subject::query()->whereIn('id', $ids)->orderBy('name')->get();
    }

    #[Computed]
    public function availableTeachers()
    {
        $exam = $this->exam;
        
        // If exam doesn't have class and subject set, return all active teachers
        if (!$exam->class_id || !$exam->subject_id) {
            return User::query()
                ->where('role', 'teacher')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        return User::query()
            ->where('role', 'teacher')
            ->where('is_active', true)
            ->whereIn('id', SubjectAllocation::query()
                ->where('class_id', $exam->class_id)
                ->where('subject_id', $exam->subject_id)
                ->pluck('teacher_id'))
            ->orderBy('name')
            ->get();
    }

    public function updatedClassId(): void
    {
        if (! $this->canEdit) {
            return;
        }

        $this->subjectId = null;
        $this->dispatch('refresh');
        $this->resetValidation();
    }

    public function saveDetails(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->role === 'teacher') {
            abort_unless($this->canEdit, 403);
        }

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'examType' => ['required', 'in:academic,aptitude'],
            'description' => ['nullable', 'string', 'max:5000'],
            'classId' => $this->examType === 'academic' ? ['required', 'integer', 'exists:classes,id'] : ['nullable', 'integer'],
            'subjectId' => $this->examType === 'academic' ? ['required', 'integer', 'exists:subjects,id'] : ['nullable', 'integer'],
            'session' => ['nullable', 'string', 'max:9'],
            'term' => ['required', 'integer', 'min:1', 'max:3'],
            'durationMinutes' => ['required', 'integer', 'min:1', 'max:300'],
        ];

        if ($user->role === 'admin') {
            $rules['startsAt'] = ['nullable', 'string', 'max:20'];
            $rules['endsAt'] = ['nullable', 'string', 'max:20'];
            $rules['pin'] = ['nullable', 'string', 'max:20'];
            $rules['graceMinutes'] = ['required', 'integer', 'min:0', 'max:120'];
            $rules['allowedCidrs'] = ['nullable', 'string', 'max:2000'];
        }

        $data = $this->validate($rules);

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

        $exam = $this->exam;

        $attrs = [
            'exam_type' => $this->examType,
            'title' => trim($data['title']),
            'description' => trim((string) ($data['description'] ?? '')) !== '' ? trim((string) $data['description']) : null,
            'class_id' => $this->examType === 'academic' ? (int) $data['classId'] : null,
            'subject_id' => $this->examType === 'academic' ? (int) $data['subjectId'] : null,
            'term' => (int) $data['term'],
            'session' => trim((string) ($data['session'] ?? '')) !== '' ? trim((string) $data['session']) : null,
            'duration_minutes' => (int) $data['durationMinutes'],
        ];

        if ($user->role === 'admin') {
            $startsAt = trim((string) ($data['startsAt'] ?? ''));
            $endsAt = trim((string) ($data['endsAt'] ?? ''));

            $starts = $startsAt !== '' ? Carbon::createFromFormat('Y-m-d\TH:i', $startsAt) : null;
            $ends = $endsAt !== '' ? Carbon::createFromFormat('Y-m-d\TH:i', $endsAt) : null;

            if ($starts && $ends && $ends->lessThanOrEqualTo($starts)) {
                throw ValidationException::withMessages([
                    'endsAt' => 'End time must be after start time.',
                ]);
            }

            $attrs['starts_at'] = $starts;
            $attrs['ends_at'] = $ends;

            $pin = trim((string) ($data['pin'] ?? ''));
            $attrs['pin'] = $pin !== '' ? $pin : null;
            $attrs['grace_minutes'] = (int) ($data['graceMinutes'] ?? 0);

            $allowed = trim((string) ($data['allowedCidrs'] ?? ''));
            $attrs['allowed_cidrs'] = $allowed !== '' ? $allowed : null;
            $attrs['show_score'] = (bool) ($this->showScore ?? false);
            $attrs['shuffle_questions'] = (bool) ($this->shuffleQuestions ?? false);
        }

        $exam->forceFill($attrs)->save();

        Audit::log('cbt.exam_updated', $exam);

        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Exam details saved.', type: 'success');
    }

    public function editQuestion(int $id): void
    {
        abort_unless($this->canEdit, 403);

        $q = CbtQuestion::query()
            ->where('exam_id', $this->examId)
            ->with(['options' => fn ($q) => $q->orderBy('position')])
            ->findOrFail($id);

        $this->editingQuestionId = $q->id;
        $this->questionPrompt = (string) $q->prompt;
        $this->questionMarks = (int) $q->marks;
        $this->questionType = (string) ($q->type ?: 'mcq');

        $labels = [];
        $correctIndex = 0;
        if ($this->questionType === 'mcq') {
            foreach ($q->options as $idx => $opt) {
                $labels[$idx] = (string) $opt->label;
                if ($opt->is_correct) {
                    $correctIndex = (int) $idx;
                }
            }
        }

        $this->optionLabels = array_pad($labels, 4, '');
        $this->correctIndex = $correctIndex;

        $this->resetValidation();
        $this->dispatch('questionTypeUpdated', $this->questionType);
        $this->dispatch('scrollToForm');
    }

    public function startNewQuestion(): void
    {
        abort_unless($this->canEdit, 403);

        $this->editingQuestionId = null;
        $this->questionPrompt = '';
        $this->questionMarks = 1;
        $this->questionType = 'mcq';
        $this->optionLabels = ['', '', '', ''];
        $this->correctIndex = 0;
        $this->resetValidation();
    }

    public function saveQuestion(): void
    {
        abort_unless($this->canEdit, 403);

        $data = $this->validate([
            'questionPrompt' => ['required', 'string', 'max:5000'],
            'questionMarks' => ['required', 'integer', 'min:1', 'max:100'],
            'questionType' => ['required', 'string', 'in:mcq,theory'],
            'optionLabels' => [$this->questionType === 'mcq' ? 'required' : 'nullable', 'array', 'size:4'],
            'optionLabels.*' => [$this->questionType === 'mcq' ? 'required' : 'nullable', 'string', 'max:1000'],
            'correctIndex' => [$this->questionType === 'mcq' ? 'required' : 'nullable', 'integer', 'min:0', 'max:3'],
        ]);

        $prompt = trim($data['questionPrompt']);
        $labels = array_map(fn ($v) => trim((string) $v), (array) ($data['optionLabels'] ?? []));

        DB::transaction(function () use ($prompt, $labels, $data) {
            if ($this->editingQuestionId) {
                $question = CbtQuestion::query()
                    ->where('exam_id', $this->examId)
                    ->findOrFail($this->editingQuestionId);
            } else {
                $nextPos = (int) CbtQuestion::query()->where('exam_id', $this->examId)->max('position') + 1;
                $question = CbtQuestion::query()->create([
                    'exam_id' => $this->examId,
                    'type' => (string) $data['questionType'],
                    'prompt' => $prompt,
                    'marks' => (int) $data['questionMarks'],
                    'position' => max(1, $nextPos),
                ]);
            }

            $question->forceFill([
                'type' => (string) $data['questionType'],
                'prompt' => $prompt,
                'marks' => (int) $data['questionMarks'],
            ])->save();

            if ($data['questionType'] === 'mcq') {
                foreach ($labels as $idx => $label) {
                    if ($label === '') {
                        throw ValidationException::withMessages(["optionLabels.{$idx}" => 'Option is required.']);
                    }
                }

                $existing = CbtOption::query()
                    ->where('question_id', $question->id)
                    ->orderBy('position')
                    ->get();

                for ($i = 0; $i < 4; $i++) {
                    $opt = $existing->get($i);
                    $attrs = [
                        'label' => $labels[$i],
                        'is_correct' => $i === (int) $data['correctIndex'],
                        'position' => $i + 1,
                    ];

                    if ($opt) {
                        $opt->forceFill($attrs)->save();
                    } else {
                        CbtOption::query()->create(array_merge($attrs, [
                            'question_id' => $question->id,
                        ]));
                    }
                }

                if ($existing->count() > 4) {
                    $ids = $existing->slice(4)->pluck('id')->all();
                    CbtOption::query()->whereIn('id', $ids)->delete();
                }
            } else {
                CbtOption::query()->where('question_id', $question->id)->delete();
            }
        });

        Audit::log($this->editingQuestionId ? 'cbt.question_updated' : 'cbt.question_created', $this->exam);

        $this->startNewQuestion();
        unset($this->exam);
        $this->dispatch('alert', message: 'Question saved.', type: 'success');
    }

    public function deleteQuestion(int $id): void
    {
        abort_unless($this->canEdit, 403);

        $q = CbtQuestion::query()
            ->where('exam_id', $this->examId)
            ->findOrFail($id);

        $q->delete();

        Audit::log('cbt.question_deleted', $this->exam, ['question_id' => $id]);

        if ($this->editingQuestionId === $id) {
            $this->startNewQuestion();
        }

        unset($this->exam);
        $this->dispatch('alert', message: 'Question deleted.', type: 'success');
    }

    public function resetAttempt(int $attemptId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $attempt = CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->with(['student:id,admission_number,first_name,last_name'])
            ->findOrFail($attemptId);

        DB::transaction(function () use ($attempt) {
            $attempt->answers()->delete();
            $attempt->delete();
        });

        Audit::log('cbt.attempt_reset', $this->exam, [
            'attempt_id' => $attemptId,
            'student_id' => $attempt->student_id,
        ]);

        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Attempt reset. Student can retake.', type: 'success');
    }

    #[Computed]
    public function roster()
    {
        $user = auth()->user();
        if (! $user || ! in_array($user->role, ['admin', 'teacher'], true)) {
            return collect();
        }

        $exam = $this->exam;
        $totalQuestions = (int) $exam->questions->count();

        // For aptitude exams with no class, build roster from attempts only
        if (! $exam->class_id) {
            $attempts = CbtAttempt::query()
                ->where('exam_id', $exam->id)
                ->with(['student:id,admission_number,first_name,last_name,passport_photo', 'answers'])
                ->get();

            $attemptIds = $attempts->pluck('id')->filter()->values();
            $answeredCounts = $attemptIds->isNotEmpty()
                ? CbtAnswer::query()
                    ->selectRaw('attempt_id, count(*) as answered')
                    ->whereIn('attempt_id', $attemptIds)
                    ->where(function ($q) {
                        $q->whereNotNull('option_id')->orWhereNotNull('text_answer');
                    })
                    ->groupBy('attempt_id')
                    ->pluck('answered', 'attempt_id')
                : collect();

            return $attempts->map(function ($attempt) use ($answeredCounts, $totalQuestions, $exam) {
                $state = 'not_started';
                if ($attempt->terminated_at) $state = 'terminated';
                elseif ($attempt->submitted_at) $state = 'submitted';
                elseif ($attempt->started_at) $state = 'in_progress';

                // Build student object from attempt's candidate_name if no student record
                $student = $attempt->student;
                if (! $student && $attempt->candidate_name) {
                    $student = new \stdClass();
                    $parts = explode(' ', $attempt->candidate_name, 2);
                    $student->first_name = $parts[0] ?? 'Candidate';
                    $student->last_name = $parts[1] ?? '';
                    $student->full_name = $attempt->candidate_name;
                    $student->admission_number = 'APT-' . strtoupper(substr(md5($attempt->candidate_name), 0, 6));
                    $student->passport_photo_url = null;
                }

                $objScore = null;
                $theoryScore = null;
                if ($attempt->submitted_at || $attempt->terminated_at) {
                    $objScore = 0;
                    $theoryScore = 0;
                    foreach ($attempt->answers as $ans) {
                        $q = $exam->questions->firstWhere('id', $ans->question_id);
                        if ($q) {
                            if ($q->type === 'theory') {
                                $theoryScore += (float) $ans->awarded_marks;
                            } else {
                                $objScore += (float) $ans->awarded_marks;
                            }
                        }
                    }
                }

                return [
                    'student' => $student,
                    'attempt' => $attempt,
                    'state' => $state,
                    'answered' => (int) ($answeredCounts[$attempt->id] ?? 0),
                    'remaining' => max(0, $totalQuestions - (int) ($answeredCounts[$attempt->id] ?? 0)),
                    'objScore' => $objScore,
                    'theoryScore' => $theoryScore,
                ];
            })->values();
        }

        $students = Student::query()
            ->where('class_id', $exam->class_id)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'admission_number', 'first_name', 'last_name', 'passport_photo', 'status']);

        $attempts = CbtAttempt::query()
            ->where('exam_id', $exam->id)
            ->with('answers')
            ->get()
            ->keyBy('student_id');

        $attemptIds = $attempts->pluck('id')->filter()->values();
        $answeredCounts = $attemptIds->isNotEmpty()
            ? CbtAnswer::query()
                ->selectRaw('attempt_id, count(*) as answered')
                ->whereIn('attempt_id', $attemptIds)
                ->where(function ($q) {
                    $q->whereNotNull('option_id')
                        ->orWhereNotNull('text_answer');
                })
                ->groupBy('attempt_id')
                ->pluck('answered', 'attempt_id')
            : collect();

        return $students->map(function (Student $student) use ($attempts, $answeredCounts, $totalQuestions, $exam) {
            /** @var ?CbtAttempt $attempt */
            $attempt = $attempts->get($student->id);

            $state = 'not_started';
            if ($attempt) {
                if ($attempt->terminated_at) {
                    $state = 'terminated';
                } elseif ($attempt->submitted_at) {
                    $state = 'submitted';
                } elseif ($attempt->started_at) {
                    $state = 'in_progress';
                }
            }

            $answered = $attempt ? (int) ($answeredCounts[$attempt->id] ?? 0) : 0;

            $objScore = null;
            $theoryScore = null;
            if ($attempt && ($attempt->submitted_at || $attempt->terminated_at)) {
                $objScore = 0;
                $theoryScore = 0;
                foreach ($attempt->answers as $ans) {
                    $q = $exam->questions->firstWhere('id', $ans->question_id);
                    if ($q) {
                        if ($q->type === 'theory') {
                            $theoryScore += (float) $ans->awarded_marks;
                        } else {
                            $objScore += (float) $ans->awarded_marks;
                        }
                    }
                }
            }

            return [
                'student' => $student,
                'attempt' => $attempt,
                'state' => $state,
                'answered' => $answered,
                'remaining' => $attempt ? max(0, $totalQuestions - $answered) : $totalQuestions,
                'objScore' => $objScore,
                'theoryScore' => $theoryScore,
            ];
        });
    }

    #[Computed]
    public function reviewAttempt(): ?CbtAttempt
    {
        if (! $this->reviewAttemptId) {
            return null;
        }

        return CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->with(['student:id,admission_number,first_name,last_name', 'answers'])
            ->find($this->reviewAttemptId);
    }

    public function startReview(int $attemptId): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        if ($user->role === 'teacher') {
            $exam = $this->exam;
            $canAccess = (int) $exam->created_by === (int) $user->id
                || (int) ($exam->assigned_teacher_id ?? 0) === (int) $user->id;
            abort_unless($canAccess, 403);
        }

        $attempt = CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->with(['answers', 'student:id,admission_number,first_name,last_name'])
            ->findOrFail($attemptId);

        if (! $attempt->submitted_at && ! $attempt->terminated_at) {
            $this->dispatch('alert', message: 'Only submitted attempts can be reviewed.', type: 'warning');
            return;
        }

        $exam = $this->exam;
        if (! $exam->questions->contains('type', 'theory')) {
            $this->dispatch('alert', message: 'This exam has no theory questions.', type: 'warning');
            return;
        }

        $this->reviewAttemptId = $attempt->id;
        $this->theoryMarks = [];
        $this->theoryComments = [];

        foreach ($exam->questions->where('type', 'theory') as $question) {
            $answer = $attempt->answers->firstWhere('question_id', $question->id);
            $this->theoryMarks[$question->id] = $answer?->awarded_marks;
            $this->theoryComments[$question->id] = $answer?->teacher_comment;
        }
    }

    public function cancelReview(): void
    {
        $this->reviewAttemptId = null;
        $this->theoryMarks = [];
        $this->theoryComments = [];
        $this->resetValidation();
    }

    public function saveTheoryMarks(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);
        abort_unless($this->reviewAttemptId, 422);

        if ($user->role === 'teacher') {
            $exam = $this->exam;
            $canAccess = (int) $exam->created_by === (int) $user->id
                || (int) ($exam->assigned_teacher_id ?? 0) === (int) $user->id;
            abort_unless($canAccess, 403);
        }

        $attempt = CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->with(['exam.questions.options', 'answers', 'student'])
            ->findOrFail((int) $this->reviewAttemptId);

        $questions = $attempt->exam->questions->where('type', 'theory');
        $errors = [];

        foreach ($questions as $question) {
            $raw = $this->theoryMarks[$question->id] ?? null;
            if ($raw === '' || $raw === null) {
                continue;
            }

            if (! is_numeric($raw)) {
                $errors["theoryMarks.{$question->id}"] = 'Marks must be a number.';
                continue;
            }

            $value = (int) $raw;
            $maxMark = (int) ($question->marks ?? 0);
            if ($value < 0 || $value > $maxMark) {
                $errors["theoryMarks.{$question->id}"] = "Marks must be between 0 and {$maxMark}.";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $answers = $attempt->answers->keyBy('question_id');

        foreach ($questions as $question) {
            $raw = $this->theoryMarks[$question->id] ?? null;
            $value = ($raw === '' || $raw === null) ? null : (int) $raw;
            $maxMark = (int) ($question->marks ?? 0);
            if ($value !== null) {
                $value = max(0, min($value, $maxMark));
            }

            $textAnswer = trim((string) ($answers->get($question->id)?->text_answer ?? ''));

            CbtAnswer::query()->updateOrCreate(
                [
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ],
                [
                    'option_id' => null,
                    'text_answer' => $textAnswer !== '' ? $textAnswer : null,
                    'awarded_marks' => $value,
                    'teacher_comment' => $this->theoryComments[$question->id] ?? null,
                    'is_correct' => null,
                ]
            );
        }

        $attempt->forceFill([
            'theory_status' => 'marked',
            'marked_at' => now(),
        ])->save();

        $attempt->refresh();
        $this->recalculateAttemptScore($attempt);

        Audit::log('cbt.theory_marked', $this->exam, [
            'attempt_id' => $attempt->id,
            'student_id' => $attempt->student_id,
        ]);

        $this->cancelReview();
        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Theory marks saved.', type: 'success');
    }

    private function recalculateAttemptScore(CbtAttempt $attempt): void
    {
        $attempt->loadMissing(['exam.questions.options', 'answers']);
        $answers = $attempt->answers->keyBy('question_id');
        $maxScore = 0;
        $score = 0;

        foreach ($attempt->exam->questions as $question) {
            $questionType = $question->type ?? 'mcq';
            $maxScore += (int) ($question->marks ?? 0);

            if ($questionType === 'theory') {
                $awarded = $answers->get($question->id)?->awarded_marks;
                if (is_numeric($awarded)) {
                    $score += max(0, min((int) $awarded, (int) ($question->marks ?? 0)));
                }
                continue;
            }

            $correctOptionId = (int) ($question->options->firstWhere('is_correct', true)?->id ?? 0);
            $selectedOptionId = (int) ($answers->get($question->id)?->option_id ?? 0);
            if ($selectedOptionId > 0 && ! $question->options->contains('id', $selectedOptionId)) {
                $selectedOptionId = 0;
            }

            $isCorrect = $selectedOptionId > 0 && $selectedOptionId === $correctOptionId;
            if ($isCorrect) {
                $score += (int) ($question->marks ?? 0);
            }
        }

        $percent = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0;
        $attempt->forceFill([
            'score' => $score,
            'max_score' => $maxScore,
            'percent' => $percent,
        ])->save();

        $this->dispatch('refresh');
    }

    public function startIpOverride(int $attemptId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $attempt = CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->findOrFail($attemptId);

        $this->editingAttemptIpId = (int) $attempt->id;
        $this->allowedIp = (string) ($attempt->allowed_ip ?? '');
        $this->resetValidation();
    }

    public function cancelIpOverride(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $this->editingAttemptIpId = null;
        $this->allowedIp = '';
        $this->resetValidation();
    }

    public function saveIpOverride(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);
        abort_unless($this->editingAttemptIpId, 422);

        $data = $this->validate([
            'allowedIp' => ['nullable', 'string', 'max:45'],
        ]);

        $attempt = CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->findOrFail((int) $this->editingAttemptIpId);

        $value = trim((string) ($data['allowedIp'] ?? ''));

        $attempt->forceFill([
            'allowed_ip' => $value !== '' ? $value : null,
        ])->save();

        Audit::log('cbt.attempt_allowed_ip_updated', $this->exam, [
            'attempt_id' => $attempt->id,
            'allowed_ip' => $attempt->allowed_ip,
        ]);

        $this->cancelIpOverride();
        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Allowed IP updated.', type: 'success');
    }

    public function clearIpLock(int $attemptId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $attempt = CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->findOrFail($attemptId);

        $attempt->forceFill([
            'ip_address' => null,
            'allowed_ip' => null,
        ])->save();

        Audit::log('cbt.attempt_ip_cleared', $this->exam, [
            'attempt_id' => $attempt->id,
        ]);

        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'IP lock cleared.', type: 'success');
    }

    public function terminateAttempt(int $attemptId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $attempt = CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->with(['exam.questions.options', 'answers'])
            ->findOrFail($attemptId);

        if ($attempt->submitted_at || $attempt->terminated_at) {
            $this->dispatch('alert', message: 'Attempt already ended.', type: 'warning');
            return;
        }

        DB::transaction(function () use ($attempt, $user) {
            $attempt->refresh();
            if ($attempt->submitted_at || $attempt->terminated_at) {
                return;
            }

            $attempt->loadMissing(['exam.questions.options', 'answers']);

            $answers = $attempt->answers->keyBy('question_id');

            $maxScore = 0;
            $score = 0;

            foreach ($attempt->exam->questions as $question) {
                $questionType = $question->type ?? 'mcq';
                $maxScore += (int) ($question->marks ?? 0);

                if ($questionType === 'theory') {
                    $textAnswer = trim((string) ($answers->get($question->id)?->text_answer ?? ''));
                    $existingAwarded = $answers->get($question->id)?->awarded_marks;
                    $maxMark = (int) ($question->marks ?? 0);
                    $awardedScore = is_numeric($existingAwarded)
                        ? max(0, min((int) $existingAwarded, $maxMark))
                        : 0;

                    if (is_numeric($existingAwarded)) {
                        $score += $awardedScore;
                    }

                    CbtAnswer::query()->updateOrCreate(
                        [
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                        ],
                        [
                            'option_id' => null,
                            'text_answer' => $textAnswer !== '' ? $textAnswer : null,
                            'awarded_marks' => is_numeric($existingAwarded) ? $awardedScore : null,
                            'is_correct' => null,
                        ]
                    );

                    continue;
                }

                $correctOptionId = (int) ($question->options->firstWhere('is_correct', true)?->id ?? 0);
                $selectedOptionId = (int) ($answers->get($question->id)?->option_id ?? 0);
                if ($selectedOptionId > 0 && ! $question->options->contains('id', $selectedOptionId)) {
                    $selectedOptionId = 0;
                }

                $isCorrect = $selectedOptionId > 0 && $selectedOptionId === $correctOptionId;
                if ($isCorrect) {
                    $score += (int) ($question->marks ?? 0);
                }

                CbtAnswer::query()->updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'option_id' => $selectedOptionId > 0 ? $selectedOptionId : null,
                        'text_answer' => null,
                        'awarded_marks' => null,
                        'is_correct' => $isCorrect,
                    ]
                );
            }

            $percent = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0;

            $attempt->forceFill([
                'score' => $score,
                'max_score' => $maxScore,
                'percent' => $percent,
                'submitted_at' => now(),
                'terminated_at' => now(),
                'terminated_by' => $user->id,
            ])->save();
        });

        Audit::log('cbt.attempt_terminated', $this->exam, [
            'attempt_id' => $attemptId,
        ]);

        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Attempt terminated.', type: 'success');
    }

    public function forceSubmitAttempt(int $attemptId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $attempt = CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->with(['exam.questions.options', 'answers'])
            ->findOrFail($attemptId);

        if ($attempt->submitted_at || $attempt->terminated_at) {
            $this->dispatch('alert', message: 'Attempt already ended.', type: 'warning');
            return;
        }

        DB::transaction(function () use ($attempt) {
            $attempt->refresh();
            if ($attempt->submitted_at || $attempt->terminated_at) {
                return;
            }

            $attempt->loadMissing(['exam.questions.options', 'answers']);

            $answers = $attempt->answers->keyBy('question_id');

            $maxScore = 0;
            $score = 0;

            foreach ($attempt->exam->questions as $question) {
                $questionType = $question->type ?? 'mcq';
                $maxScore += (int) ($question->marks ?? 0);

                if ($questionType === 'theory') {
                    $textAnswer = trim((string) ($answers->get($question->id)?->text_answer ?? ''));
                    $existingAwarded = $answers->get($question->id)?->awarded_marks;
                    $maxMark = (int) ($question->marks ?? 0);
                    $awardedScore = is_numeric($existingAwarded)
                        ? max(0, min((int) $existingAwarded, $maxMark))
                        : 0;

                    if (is_numeric($existingAwarded)) {
                        $score += $awardedScore;
                    }

                    CbtAnswer::query()->updateOrCreate(
                        [
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                        ],
                        [
                            'option_id' => null,
                            'text_answer' => $textAnswer !== '' ? $textAnswer : null,
                            'awarded_marks' => is_numeric($existingAwarded) ? $awardedScore : null,
                            'is_correct' => null,
                        ]
                    );

                    continue;
                }

                $correctOptionId = (int) ($question->options->firstWhere('is_correct', true)?->id ?? 0);
                $selectedOptionId = (int) ($answers->get($question->id)?->option_id ?? 0);
                if ($selectedOptionId > 0 && ! $question->options->contains('id', $selectedOptionId)) {
                    $selectedOptionId = 0;
                }

                $isCorrect = $selectedOptionId > 0 && $selectedOptionId === $correctOptionId;
                if ($isCorrect) {
                    $score += (int) ($question->marks ?? 0);
                }

                CbtAnswer::query()->updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'option_id' => $selectedOptionId > 0 ? $selectedOptionId : null,
                        'text_answer' => null,
                        'awarded_marks' => null,
                        'is_correct' => $isCorrect,
                    ]
                );
            }

            $percent = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0;

            $attempt->forceFill([
                'score' => $score,
                'max_score' => $maxScore,
                'percent' => $percent,
                'submitted_at' => now(),
            ])->save();
        });

        Audit::log('cbt.attempt_force_submitted', $this->exam, [
            'attempt_id' => $attemptId,
        ]);

        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Attempt submitted.', type: 'success');
    }

    public function duplicateExam()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        $source = $this->exam;
        if ($user->role === 'teacher') {
            $canAccess = (int) $source->created_by === (int) $user->id
                || (int) ($source->assigned_teacher_id ?? 0) === (int) $user->id;
            abort_unless($canAccess, 403);
        }

        $newExam = DB::transaction(function () use ($user, $source) {
            $copy = CbtExam::query()->create([
                'title' => $source->title.' (Copy)',
                'description' => $source->description,
                'class_id' => $source->class_id,
                'subject_id' => $source->subject_id,
                'term' => $source->term,
                'session' => $source->session,
                'duration_minutes' => $source->duration_minutes,
                'status' => 'draft',
                'created_by' => $user->id,
                'assigned_teacher_id' => $user->role === 'teacher' ? $user->id : $source->assigned_teacher_id,
            ]);

            $source->loadMissing(['questions.options']);

            foreach ($source->questions as $q) {
                $newQ = CbtQuestion::query()->create([
                    'exam_id' => $copy->id,
                    'type' => $q->type,
                    'prompt' => $q->prompt,
                    'marks' => $q->marks,
                    'position' => $q->position,
                ]);

                foreach ($q->options as $opt) {
                    CbtOption::query()->create([
                        'question_id' => $newQ->id,
                        'label' => $opt->label,
                        'is_correct' => (bool) $opt->is_correct,
                        'position' => $opt->position,
                    ]);
                }
            }

            return $copy;
        });

        Audit::log('cbt.exam_duplicated', $newExam, [
            'source_exam_id' => $source->id,
        ]);

        return redirect()->route('cbt.exams.edit', $newExam);
    }

    // ── Lifecycle Management ───────────────────────────────────────────────

    public function requestApproval(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'teacher', 403);
        abort_unless($this->canEdit, 403);

        $exam = $this->exam;

        if ($exam->questions->isEmpty()) {
            $this->dispatch('alert', message: 'Add at least one question before requesting approval.', type: 'warning');
            return;
        }

        $exam->forceFill([
            'status' => 'pending_approval',
            'requested_by' => $user->id,
            'requested_at' => now(),
        ])->save();

        Audit::log('cbt.exam_approval_requested', $exam, ['user_id' => $user->id]);

        // Notify all admin users
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            \App\Models\InAppNotification::query()->create([
                'user_id' => $admin->id,
                'title' => 'CBT Approval Requested',
                'body' => "Teacher {$user->name} requested approval for CBT exam: {$exam->title}.",
                'link' => '/cbt/exams/' . $exam->id,
            ]);
        }

        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Exam submitted for admin approval.', type: 'success');
    }

    public function goLive(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'admin', 403);

        $exam = $this->exam;

        if ($exam->questions->isEmpty()) {
            $this->dispatch('alert', message: 'Add at least one question before going live.', type: 'warning');
            return;
        }

        $code = $exam->access_code ?: $this->generateAccessCode();

        $exam->forceFill([
            'status' => 'live',
            'access_code' => $code,
            'published_at' => now(),
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ])->save();

        Audit::log('cbt.exam_live', $exam, ['user_id' => $user->id]);

        // Notify all active students in the exam's class
        $subject = $exam->subject?->name ?? 'Unknown Subject';
        Student::where('class_id', $exam->class_id)
            ->where('status', 'Active')
            ->pluck('id')
            ->each(fn ($studentId) => StudentNotification::send(
                $studentId,
                'Exam Available: ' . $exam->title,
                "Your {$subject} exam is now live. Access code: {$code}",
                'exam',
                route('student.exams')
            ));

        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Exam is now live. Students can take it.', type: 'success');
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

    public function toggleShowScore(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        $exam = $this->exam;

        if ($user->role === 'teacher') {
            $canAccess = (int) $exam->created_by === (int) $user->id
                || (int) ($exam->assigned_teacher_id ?? 0) === (int) $user->id;
            abort_unless($canAccess, 403);
        }

        $exam->forceFill(['show_score' => ! $exam->show_score])->save();
        $this->showScore = (bool) $exam->show_score;

        Audit::log('cbt.show_score_toggled', $exam, ['show_score' => $exam->show_score]);
        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Show Score is now ' . ($exam->show_score ? 'ON' : 'OFF') . '.', type: 'info');
    }

    public function releaseResults(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        $exam = $this->exam;

        if ($user->role === 'teacher') {
            $canAccess = (int) $exam->created_by === (int) $user->id
                || (int) ($exam->assigned_teacher_id ?? 0) === (int) $user->id;
            abort_unless($canAccess, 403);
        }

        if ($exam->results_released_at) {
            $this->dispatch('alert', message: 'Results have already been released.', type: 'warning');
            return;
        }

        // Block if the exam is not ended yet or has active attempts in progress
        if ($exam->status !== 'ended') {
            $inProgressCount = CbtAttempt::where('exam_id', $exam->id)
                ->whereNotNull('started_at')
                ->whereNull('submitted_at')
                ->whereNull('terminated_at')
                ->count();

            if ($inProgressCount > 0) {
                $this->dispatch('alert', message: "{$inProgressCount} student(s) are still actively taking the exam. End the exam first or wait for them to submit.", type: 'warning');
                return;
            }

            $this->dispatch('alert', message: "Please end the exam first before releasing results.", type: 'warning');
            return;
        }

        // Block if exam has theory and not all attempts are marked
        $hasTheory = $exam->questions->contains('type', 'theory');
        if ($hasTheory) {
            $unmarked = CbtAttempt::where('exam_id', $exam->id)
                ->whereNotNull('submitted_at')
                ->where(fn ($q) => $q->whereNull('theory_status')->orWhere('theory_status', '!=', 'marked'))
                ->count();

            if ($unmarked > 0) {
                $this->dispatch('alert', message: "{$unmarked} attempt(s) still have unmarked theory questions. Mark all theory answers before releasing results.", type: 'warning');
                return;
            }
        }

        $exam->forceFill(['results_released_at' => now()])->save();

        // Notify all students in the class
        Student::where('class_id', $exam->class_id)
            ->where('status', 'Active')
            ->pluck('id')
            ->each(fn ($studentId) => StudentNotification::send(
                $studentId,
                'Results Released: ' . $exam->title,
                'Your results for ' . $exam->title . ' are now available. Check your Exams page to view your score.',
                'exam',
                route('student.exams')
            ));

        Audit::log('cbt.results_released', $exam, ['released_by' => $user->id]);

        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Results released to students.', type: 'success');
    }


    public function endAllExams(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        $exam = $this->exam;
        
        if ($user->role === 'teacher') {
            $canAccess = (int) $exam->created_by === (int) $user->id
                || (int) ($exam->assigned_teacher_id ?? 0) === (int) $user->id;
            abort_unless($canAccess, 403);
        }
        $inProgressAttempts = CbtAttempt::query()
            ->where('exam_id', $exam->id)
            ->whereNotNull('started_at')
            ->whereNull('submitted_at')
            ->whereNull('terminated_at')
            ->with(['exam.questions.options', 'answers'])
            ->get();

        if ($inProgressAttempts->isEmpty()) {
            $exam->forceFill(['status' => 'ended'])->save();
            \App\Models\Audit::log('cbt.all_exams_ended', $exam, ['count' => 0]);
            $this->dispatch('alert', message: 'Exam ended successfully (no active attempts were running).', type: 'success');
            $this->dispatch('refresh');
            return;
        }

        $count = 0;
        foreach ($inProgressAttempts as $attempt) {
            DB::transaction(function () use ($attempt) {
                $attempt->loadMissing(['exam.questions.options', 'answers']);
                $answers = $attempt->answers->keyBy('question_id');
                $maxScore = 0;
                $score = 0;

                foreach ($attempt->exam->questions as $question) {
                    $questionType = $question->type ?? 'mcq';
                    $maxScore += (int) ($question->marks ?? 0);

                    if ($questionType === 'theory') {
                        $textAnswer = trim((string) ($answers->get($question->id)?->text_answer ?? ''));
                        $existingAwarded = $answers->get($question->id)?->awarded_marks;
                        $maxMark = (int) ($question->marks ?? 0);
                        $awardedScore = is_numeric($existingAwarded)
                            ? max(0, min((int) $existingAwarded, $maxMark))
                            : 0;

                        if (is_numeric($existingAwarded)) {
                            $score += $awardedScore;
                        }

                        CbtAnswer::query()->updateOrCreate(
                            ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                            [
                                'option_id' => null,
                                'text_answer' => $textAnswer !== '' ? $textAnswer : null,
                                'awarded_marks' => is_numeric($existingAwarded) ? $awardedScore : null,
                                'is_correct' => null,
                            ]
                        );
                        continue;
                    }
                    $correctOptionId = (int) ($question->options->firstWhere('is_correct', true)?->id ?? 0);
                    $selectedOptionId = (int) ($answers->get($question->id)?->option_id ?? 0);
                    if ($selectedOptionId > 0 && ! $question->options->contains('id', $selectedOptionId)) {
                        $selectedOptionId = 0;
                    }
                    $isCorrect = $selectedOptionId > 0 && $selectedOptionId === $correctOptionId;
                    if ($isCorrect) {
                        $score += (int) ($question->marks ?? 0);
                    }
                    CbtAnswer::query()->updateOrCreate(
                        ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                        [
                            'option_id' => $selectedOptionId > 0 ? $selectedOptionId : null,
                            'text_answer' => null,
                            'awarded_marks' => null,
                            'is_correct' => $isCorrect,
                        ]
                    );
                }

                $percent = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0;
                $attempt->forceFill([
                    'score' => $score,
                    'max_score' => $maxScore,
                    'percent' => $percent,
                    'submitted_at' => now(),
                ])->save();
            });
            $count++;
        }

        $exam->forceFill(['status' => 'ended'])->save();

        Audit::log('cbt.all_exams_ended', $exam, ['count' => $count]);
        $this->dispatch('refresh');
        $this->dispatch('alert', message: "Exam ended. {$count} active attempt(s) forcibly submitted.", type: 'success');
    }

    public function transferToResults(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        if (! Schema::hasTable('scores')) {
            $this->dispatch('alert', message: 'Scores table not found. Run migrations or disable CBT-to-results transfer.', type: 'warning');
            return;
        }

        $exam = $this->exam;
        // Allow transfer when exam is in any post-live state (approved, ended, or live).
        abort_unless(in_array($exam->status, ['approved', 'ended', 'live'], true), 403);

        if (! $exam->subject_id || ! $exam->class_id || ! $exam->term || ! $exam->session) {
            $this->dispatch('alert', message: 'This exam is missing subject, class, term, or session — cannot transfer to results.', type: 'warning');
            return;
        }

        $attempts = CbtAttempt::query()
            ->where('exam_id', $exam->id)
            ->whereNotNull('submitted_at')
            ->with('student')
            ->get();

        if ($attempts->isEmpty()) {
            $this->dispatch('alert', message: 'No submitted attempts to transfer.', type: 'warning');
            return;
        }

        // Scale CBT raw score to the results exam max (e.g. CBT max 25 → results max 60)
        $cbtMax = (int) ($attempts->max('max_score') ?: 0);
        $resultsExamMax = max(1, (int) config('academyhub.results_exam_max', 60));

        $count = 0;
        DB::transaction(function () use ($exam, $attempts, $cbtMax, $resultsExamMax, &$count) {
            foreach ($attempts as $attempt) {
                if (! $attempt->student) {
                    continue;
                }

                // Scale: if CBT max != results exam max, proportionally convert
                $rawScore = (int) ($attempt->score ?? 0);
                $scaledExamScore = $cbtMax > 0
                    ? (int) round(($rawScore / $cbtMax) * $resultsExamMax)
                    : $rawScore;
                $scaledExamScore = max(0, min($scaledExamScore, $resultsExamMax));

                // Use Eloquent so the booted() observer sets total, grade, and tenant_id automatically
                $score = Score::firstOrNew([
                    'student_id' => $attempt->student_id,
                    'subject_id' => $exam->subject_id,
                    'class_id'   => $exam->class_id,
                    'term'       => $exam->term,
                    'session'    => $exam->session,
                ]);

                $score->exam = $scaledExamScore;
                // Preserve existing CA scores if already entered manually
                $score->ca1 = $score->ca1 ?? 0;
                $score->ca2 = $score->ca2 ?? 0;
                $score->save();

                $count++;
            }
        });

        Audit::log('cbt.scores_transferred', $exam, ['count' => $count]);
        $this->dispatch('alert', message: "Transferred {$count} CBT score(s) to the results scoresheet.", type: 'success');
    }

    public function startForward(int $attemptId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $attempt = CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->findOrFail($attemptId);

        if (!$attempt->submitted_at && !$attempt->terminated_at) {
            $this->dispatch('alert', message: 'Only submitted attempts can be forwarded.', type: 'warning');
            return;
        }

        $this->forwardAttemptId = $attempt->id;
        $this->forwardTeacherId = null;
        $this->showForwardModal = true;
        $this->resetValidation();
    }

    public function cancelForward(): void
    {
        $this->showForwardModal = false;
        $this->forwardAttemptId = null;
        $this->forwardTeacherId = null;
        $this->resetValidation();
    }

    public function confirmForward(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);
        abort_unless($this->forwardAttemptId, 422);

        $this->validate([
            'forwardTeacherId' => ['required', 'integer', 'exists:users,id'],
        ]);

        $attempt = CbtAttempt::query()
            ->where('exam_id', $this->examId)
            ->with(['student', 'exam'])
            ->findOrFail($this->forwardAttemptId);

        $attempt->forceFill([
            'theory_status' => 'forwarded',
            'assigned_teacher_id' => (int) $this->forwardTeacherId,
            'forwarded_at' => now(),
        ])->save();

        Audit::log('cbt.theory_forwarded', $this->exam, [
            'attempt_id' => $attempt->id,
            'teacher_id' => $this->forwardTeacherId,
        ]);

        InAppNotification::query()->create([
            'user_id' => (int) $this->forwardTeacherId,
            'title' => 'Theory marking assigned',
            'body' => "Mark theory questions for {$attempt->student?->full_name} in {$attempt->exam->title}.",
            'link' => '/cbt/exams/' . $this->exam->id,
        ]);

        $this->dispatch('browser-notification', 
            title: 'Theory marking assigned',
            message: "Mark theory for {$attempt->student?->full_name}",
            url: '/cbt/exams/' . $this->exam->id
        );

        $this->cancelForward();
        $this->dispatch('refresh');
        $this->dispatch('alert', message: 'Forwarded to teacher.', type: 'success');
    }

    // ── AI Generation ──────────────────────────────────────────────────────


    public function saveShuffle(): void
    {
        $this->exam->forceFill(['shuffle_questions' => (bool) $this->shuffleQuestions])->save();
        $this->dispatch('alert', message: $this->shuffleQuestions ? 'Shuffle enabled.' : 'Shuffle disabled.', type: 'success');
    }

    public function openAiPanel(): void
    {
        abort_unless($this->canEdit, 403);
        $this->showAiPanel = true;
        $this->showImportPanel = false;
        $this->aiPreview = [];
        $this->resetValidation();
    }

    public function closeAiPanel(): void
    {
        $this->showAiPanel = false;
        $this->aiPreview = [];
    }

    public function generateAiQuestions(): void
    {
        abort_unless($this->canEdit, 403);

        $this->validate([
            'aiTopic' => ['required', 'string', 'max:200'],
            'aiCount' => ['required', 'integer', 'min:1', 'max:20'],
            'aiType'  => ['required', 'in:mcq,theory,mixed'],
            'aiMarks' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $exam    = $this->exam;
        $subject = $exam->subject?->name ?? 'General';

        try {
            if ($this->aiType === 'mixed') {
                $half         = (int) ceil($this->aiCount / 2);
                $mcq          = \App\Support\CbtQuestionImporter::fromAi($this->aiTopic, $subject, $half, 'mcq', $this->aiMarks);
                $theory       = \App\Support\CbtQuestionImporter::fromAi($this->aiTopic, $subject, $this->aiCount - $half, 'theory', $this->aiMarks);
                $this->aiPreview = array_merge($mcq, $theory);
            } else {
                $this->aiPreview = \App\Support\CbtQuestionImporter::fromAi($this->aiTopic, $subject, $this->aiCount, $this->aiType, $this->aiMarks);
            }
        } catch (\Throwable $e) {
            $this->dispatch('alert', message: $e->getMessage(), type: 'error');
        }
    }

    public function removeAiPreviewItem(int $index): void
    {
        unset($this->aiPreview[$index]);
        $this->aiPreview = array_values($this->aiPreview);
    }

    public function insertAiQuestions(): void
    {
        abort_unless($this->canEdit, 403);

        if (empty($this->aiPreview)) {
            $this->dispatch('alert', message: 'No questions to insert.', type: 'warning');
            return;
        }

        $count = count($this->aiPreview);
        $this->bulkInsertQuestions($this->aiPreview);
        $this->aiPreview = [];
        $this->showAiPanel = false;
        unset($this->exam);
        $this->dispatch('alert', message: "{$count} AI questions added.", type: 'success');
    }

    public function openImportPanel(): void
    {
        abort_unless($this->canEdit, 403);
        $this->showImportPanel = true;
        $this->showAiPanel = false;
        $this->importPreview = [];
        $this->importFile = null;
        $this->resetValidation();
    }

    public function closeImportPanel(): void
    {
        $this->showImportPanel = false;
        $this->importPreview = [];
        $this->importFile = null;
    }

    public function parseImportFile(): void
    {
        abort_unless($this->canEdit, 403);

        $this->validate([
            'importFile' => ['required', 'file', 'max:2048', 'mimes:txt,text'],
        ]);

        try {
            $this->importPreview = \App\Support\CbtQuestionImporter::fromFile($this->importFile);
        } catch (\Throwable $e) {
            $this->dispatch('alert', message: $e->getMessage(), type: 'error');
        }
    }

    public function removeImportPreviewItem(int $index): void
    {
        unset($this->importPreview[$index]);
        $this->importPreview = array_values($this->importPreview);
    }

    public function insertImportQuestions(): void
    {
        abort_unless($this->canEdit, 403);

        if (empty($this->importPreview)) {
            $this->dispatch('alert', message: 'No questions to insert.', type: 'warning');
            return;
        }

        $count = count($this->importPreview);
        $this->bulkInsertQuestions($this->importPreview);
        $this->importPreview = [];
        $this->showImportPanel = false;
        unset($this->exam);
        $this->dispatch('alert', message: "{$count} questions imported from file.", type: 'success');
    }

    private function bulkInsertQuestions(array $questions): void
    {
        DB::transaction(function () use ($questions) {
            $nextPos = (int) CbtQuestion::query()->where('exam_id', $this->examId)->max('position');

            foreach ($questions as $q) {
                $type    = $q['type'] ?? 'mcq';
                $nextPos++;

                $question = CbtQuestion::query()->create([
                    'exam_id'  => $this->examId,
                    'type'     => $type,
                    'prompt'   => trim((string) $q['prompt']),
                    'marks'    => max(1, (int) ($q['marks'] ?? 1)),
                    'position' => $nextPos,
                ]);

                if ($type === 'mcq' && ! empty($q['options'])) {
                    foreach ($q['options'] as $i => $label) {
                        if (trim((string) $label) === '') continue;
                        CbtOption::query()->create([
                            'question_id' => $question->id,
                            'label'       => trim((string) $label),
                            'is_correct'  => $i === (int) ($q['correct'] ?? 0),
                            'position'    => $i + 1,
                        ]);
                    }
                }
            }
        });

        Audit::log('cbt.questions_bulk_inserted', $this->exam, ['count' => count($questions)]);
    }

    public function deleteExam()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $exam = $this->exam;

        DB::transaction(function () use ($exam) {
            $exam->delete();
        });

        Audit::log('cbt.exam_deleted', null, ['exam_id' => $exam->id, 'title' => $exam->title]);

        $this->showDeleteModal = false;
        $this->dispatch('alert', message: 'Exam deleted successfully.', type: 'success');
        return redirect()->route('cbt.index');
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);

        $exam = $this->exam;

        if ($user->role === 'teacher') {
            $canAccess = (int) $exam->created_by === (int) $user->id
                || (int) ($exam->assigned_teacher_id ?? 0) === (int) $user->id;
            abort_unless($canAccess, 403);
        }

        return view('livewire.cbt.exam-editor', [
            'me' => $user,
            'exam' => $exam,
        ]);
    }
}