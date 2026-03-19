<?php

namespace App\Livewire\Attendance;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\TeacherAttendanceMark;
use App\Models\TeacherAttendanceSheet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Teacher Attendance')]
class Teachers extends Component
{
    public string $date = '';
    public ?int $term = null;
    public string $session = '';

    public ?int $sheetId = null;

    public string $tool = 'Absent';
    public string $search = '';
    public bool $onlyExceptions = false;

    /**
     * @var array<int, array{status:string, note:string|null}>
     */
    public array $marks = [];

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->session = $this->session ?: $this->defaultSession();
        $this->term = $this->term ?: $this->defaultTerm();

        $this->syncSheetFromSelection();
    }

    #[Computed]
    public function teachers()
    {
        return User::query()
            ->where('role', 'teacher')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    #[Computed]
    public function visibleTeachers()
    {
        $teachers = $this->teachers;

        $query = trim($this->search);
        if ($query !== '') {
            $q = mb_strtolower($query);
            $teachers = $teachers->filter(function (User $teacher) use ($q, $query) {
                $name = mb_strtolower((string) $teacher->name);
                $email = mb_strtolower((string) $teacher->email);

                return str_contains($name, $q) || str_contains($email, mb_strtolower($query));
            });
        }

        if ($this->onlyExceptions) {
            $teachers = $teachers->filter(function (User $teacher) {
                $status = (string) ($this->marks[$teacher->id]['status'] ?? 'Present');

                return $status !== 'Present';
            });
        }

        return $teachers->values();
    }

    #[Computed]
    public function markCounts(): array
    {
        $counts = [
            'Present' => 0,
            'Absent' => 0,
            'Late' => 0,
            'Excused' => 0,
        ];

        foreach ($this->teachers as $teacher) {
            $status = (string) ($this->marks[$teacher->id]['status'] ?? 'Present');
            if (!array_key_exists($status, $counts)) {
                $status = 'Present';
            }

            $counts[$status]++;
        }

        return $counts;
    }

    public function updatedDate(): void
    {
        unset($this->teachers);
        $this->syncSheetFromSelection();
    }

    public function updatedTerm(): void
    {
        unset($this->teachers);
        $this->syncSheetFromSelection();
    }

    public function updatedSession(): void
    {
        unset($this->teachers);
        $this->syncSheetFromSelection();
    }

    public function updatedSearch(): void
    {
        unset($this->visibleTeachers);
    }

    public function updatedOnlyExceptions(): void
    {
        unset($this->visibleTeachers);
    }

    public function setTool(string $tool): void
    {
        if (!in_array($tool, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            return;
        }

        $this->tool = $tool;
    }

    public function applyTool(int $teacherId): void
    {
        $tool = $this->tool;
        if (!in_array($tool, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            $tool = 'Absent';
        }

        $current = (string) ($this->marks[$teacherId]['status'] ?? 'Present');

        if ($tool === 'Present') {
            $this->marks[$teacherId]['status'] = 'Present';
            return;
        }

        $this->marks[$teacherId]['status'] = $current === $tool ? 'Present' : $tool;
    }

    public function start(): void
    {
        $this->validateSelection();

        $sheet = TeacherAttendanceSheet::query()->firstOrCreate(
            [
                'date' => $this->date,
                'term' => $this->term,
                'session' => $this->session,
            ],
            [
                'taken_by' => auth()->id(),
            ],
        );

        $this->sheetId = $sheet->id;
        $this->loadMarks($sheet);
    }

    public function save(): void
    {
        $this->validateSelection();

        $sheet = TeacherAttendanceSheet::query()->firstOrCreate(
            [
                'date' => $this->date,
                'term' => $this->term,
                'session' => $this->session,
            ],
            [
                'taken_by' => auth()->id(),
            ],
        );

        $this->sheetId = $sheet->id;

        DB::transaction(function () use ($sheet) {
            foreach ($this->teachers as $teacher) {
                $row = $this->marks[$teacher->id] ?? [];
                $status = (string) ($row['status'] ?? 'Present');
                $note = $row['note'] ?? null;

                if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
                    throw ValidationException::withMessages([
                        'marks' => "Invalid attendance status for {$teacher->name}.",
                    ]);
                }

                TeacherAttendanceMark::query()->updateOrCreate(
                    [
                        'sheet_id' => $sheet->id,
                        'teacher_id' => $teacher->id,
                    ],
                    [
                        'status' => $status,
                        'note' => $note ? (string) $note : null,
                    ],
                );
            }
        });

        $this->dispatch('alert', message: 'Teacher attendance saved successfully.', type: 'success');
    }

    public function markAll(string $status): void
    {
        if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            return;
        }

        foreach ($this->teachers as $teacher) {
            $this->marks[$teacher->id]['status'] = $status;
        }
    }

    public function cycleStatus(int $teacherId): void
    {
        $order = ['Present', 'Absent', 'Late', 'Excused'];
        $current = (string) ($this->marks[$teacherId]['status'] ?? 'Present');
        $index = array_search($current, $order, true);
        $next = $order[$index === false ? 0 : ($index + 1) % count($order)];

        $this->marks[$teacherId]['status'] = $next;
    }

    private function loadMarks(TeacherAttendanceSheet $sheet): void
    {
        $this->marks = [];

        $existing = TeacherAttendanceMark::query()
            ->where('sheet_id', $sheet->id)
            ->get()
            ->keyBy('teacher_id');

        foreach ($this->teachers as $teacher) {
            $mark = $existing->get($teacher->id);
            $this->marks[$teacher->id] = [
                'status' => $mark?->status ?? 'Present',
                'note' => $mark?->note,
            ];
        }
    }

    private function validateSelection(): void
    {
        $this->validate([
            'date' => ['required', 'date'],
            'term' => ['required', 'integer', 'between:1,3'],
            'session' => ['required', 'string', 'max:9', 'regex:/^\\d{4}\\/\\d{4}$/'],
        ]);
    }

    private function syncSheetFromSelection(): void
    {
        $this->sheetId = null;
        $this->marks = [];

        if (!$this->date || !$this->term || !$this->session) {
            return;
        }

        $sheet = TeacherAttendanceSheet::query()
            ->where('date', $this->date)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->first();

        if (!$sheet) {
            return;
        }

        $this->sheetId = $sheet->id;
        $this->loadMarks($sheet);
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

    private function defaultTerm(): int
    {
        return AcademicTerm::activeTermNumber();
    }

    public function render()
    {
        abort_unless(auth()->user()?->role === 'admin' || auth()->user()?->role === 'teacher', 403);

        return view('livewire.attendance.teachers');
    }
}
