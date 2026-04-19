<?php

namespace App\Livewire\Attendance;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Attendance')]
class Index extends Component
{
    public ?int $classId = null;
    public ?int $sectionId = null;
    public string $date = '';
    public ?int $term = null;
    public string $session = '';

    public ?int $sheetId = null;

    public string $tool = 'Absent';
    public string $search = '';
    public bool $onlyExceptions = false;
    public bool $showModal = false;

    /**
     * @var array<int, array{status:string, note:string|null}>
     */
    public array $marks = [];

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->session = $this->session ?: $this->defaultSession();
        $this->term = $this->term ?: $this->defaultTerm();
    }

    private function getDateRecords()
    {
        return AttendanceMark::query()
            ->with(['student.schoolClass', 'sheet'])
            ->whereHas('sheet', fn($q) => $q->where('date', $this->date))
            ->get()
            ->sortBy(fn($m) => $m->student?->last_name);
    }

    private function getClasses()
    {
        return SchoolClass::query()->orderBy('level')->get();
    }

    private function getSections()
    {
        if (!$this->classId) {
            return collect();
        }
        return Section::query()
            ->where('class_id', $this->classId)
            ->orderBy('name')
            ->get();
    }

    private function getStudents()
    {
        if (!$this->classId || !$this->sectionId) {
            return collect();
        }
        return Student::query()
            ->where('class_id', $this->classId)
            ->where('section_id', $this->sectionId)
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->get();
    }

    private function getVisibleStudents($students)
    {
        $query = trim($this->search);
        if ($query !== '') {
            $q = mb_strtolower($query);
            $students = $students->filter(function (Student $student) use ($q, $query) {
                $name = mb_strtolower($student->full_name);
                $adm  = (string) ($student->admission_number ?? '');
                return str_contains($name, $q) || str_contains($adm, $query);
            });
        }
        if ($this->onlyExceptions) {
            $students = $students->filter(function (Student $student) {
                return ((string) ($this->marks[$student->id]['status'] ?? 'Present')) !== 'Present';
            });
        }
        return $students->values();
    }

    private function getMarkCounts($students): array
    {
        $counts = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Excused' => 0];
        foreach ($students as $student) {
            $status = (string) ($this->marks[$student->id]['status'] ?? 'Present');
            if (!array_key_exists($status, $counts)) {
                $status = 'Present';
            }
            $counts[$status]++;
        }
        return $counts;
    }

    public function updatedClassId(): void
    {
        $this->sectionId = null;
        if ($this->classId) {
            $this->sectionId = Section::query()
                ->where('class_id', $this->classId)
                ->orderBy('name')
                ->value('id');
        }
        $this->syncSheetFromSelection();
    }

    public function updatedSectionId(): void
    {
        $this->syncSheetFromSelection();
    }

    public function updatedDate(): void
    {
        $this->syncSheetFromSelection();
    }

    public function updatedTerm(): void
    {
        $this->syncSheetFromSelection();
    }

    public function updatedSession(): void
    {
        $this->syncSheetFromSelection();
    }

    public function setTool(string $tool): void
    {
        if (!in_array($tool, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            return;
        }

        $this->tool = $tool;
    }

    public function applyTool(int $studentId): void
    {
        $tool = $this->tool;
        if (!in_array($tool, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            $tool = 'Absent';
        }

        $current = (string) ($this->marks[$studentId]['status'] ?? 'Present');

        if ($tool === 'Present') {
            $this->marks[$studentId]['status'] = 'Present';
            return;
        }

        $this->marks[$studentId]['status'] = $current === $tool ? 'Present' : $tool;
    }

    public function start(): void
    {
        [$section] = $this->validateSelection();

        if (!$section) {
            $this->dispatch('alert', message: 'Please select a valid section.', type: 'error');
            return;
        }

        $sheet = AttendanceSheet::query()->firstOrCreate(
            [
                'class_id' => $this->classId,
                'section_id' => $section->id,
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
        [$section] = $this->validateSelection();

        $sheet = AttendanceSheet::query()->firstOrCreate(
            [
                'class_id' => $this->classId,
                'section_id' => $section->id,
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
            foreach ($this->getStudents() as $student) {
                $row = $this->marks[$student->id] ?? [];
                $status = (string) ($row['status'] ?? 'Present');
                $note = $row['note'] ?? null;

                if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
                    throw ValidationException::withMessages([
                        'marks' => "Invalid attendance status for {$student->full_name}.",
                    ]);
                }

                AttendanceMark::query()->updateOrCreate(
                    [
                        'sheet_id' => $sheet->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'status' => $status,
                        'note' => $note ? (string) $note : null,
                    ],
                );
            }
        });

        $this->dispatch('alert', message: 'Attendance saved successfully.', type: 'success');
    }

    public function markAll(string $status): void
    {
        if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            return;
        }

        foreach ($this->getStudents() as $student) {
            $this->marks[$student->id]['status'] = $status;
        }
    }

    public function cycleStatus(int $studentId): void
    {
        $order = ['Present', 'Absent', 'Late', 'Excused'];
        $current = (string) ($this->marks[$studentId]['status'] ?? 'Present');
        $index = array_search($current, $order, true);
        $next = $order[$index === false ? 0 : ($index + 1) % count($order)];

        $this->marks[$studentId]['status'] = $next;
    }

    public function setMark(int $studentId, string $status): void
    {
        if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            return;
        }

        $this->marks[$studentId]['status'] = $status;
    }

    private function loadMarks(AttendanceSheet $sheet): void
    {
        $this->marks = [];
        $existing = AttendanceMark::query()
            ->where('sheet_id', $sheet->id)
            ->get()
            ->keyBy('student_id');
        $students = $this->getStudents();
        foreach ($students as $student) {
            $mark = $existing->get($student->id);
            $this->marks[$student->id] = [
                'status' => $mark?->status ?? 'Present',
                'note'   => $mark?->note,
            ];
        }
    }

    private function resetSheet(): void
    {
        $this->sheetId = null;
        $this->marks = [];
    }

    /**
     * @return array{0: \App\Models\Section}
     */
    private function validateSelection(): array
    {
        $this->validate([
            'classId' => ['required', 'integer', Rule::exists('classes', 'id')],
            'sectionId' => ['required', 'integer', Rule::exists('sections', 'id')],
            'date' => ['required', 'date'],
            'term' => ['required', 'integer', 'between:1,3'],
            'session' => ['required', 'string', 'max:9', 'regex:/^\\d{4}\\/\\d{4}$/'],
        ]);

        $section = Section::query()
            ->where('id', $this->sectionId)
            ->where('class_id', $this->classId)
            ->first();

        if (!$section) {
            throw ValidationException::withMessages([
                'sectionId' => 'Please select a valid section for the chosen class.',
            ]);
        }

        return [$section];
    }

    private function syncSheetFromSelection(): void
    {
        $this->sheetId = null;
        $this->marks = [];

        if (!$this->classId || !$this->sectionId || !$this->date || !$this->term || !$this->session) {
            return;
        }

        $sheet = AttendanceSheet::query()
            ->where('class_id', $this->classId)
            ->where('section_id', $this->sectionId)
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
        $students       = $this->getStudents();
        $visibleStudents = $this->getVisibleStudents($students);
        $markCounts     = $this->getMarkCounts($students);
        $classes        = $this->getClasses();
        $sections       = $this->getSections();
        $dateRecords    = $this->getDateRecords();

        return view('livewire.attendance.index', compact(
            'students', 'visibleStudents', 'markCounts', 'classes', 'sections', 'dateRecords'
        ));
    }
}
