<?php

namespace App\Livewire\Attendance;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\SubjectAllocation;
use App\Traits\DispatchesModals;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Attendance')]
class Index extends Component
{
    use DispatchesModals;

    #[Url]
    public ?int $classId = null;

    #[Url]
    public ?int $sectionId = null;

    #[Url]
    public string $date = '';

    public ?int $term = null;
    public string $session = '';

    public ?int $sheetId = null;

    public string $tool = 'Absent';
    public string $search = '';
    public bool $onlyExceptions = false;
    public bool $showModal = false;

    // View filter for live biometric scans: 'class' (only selected class) or 'all' (entire school)
    public string $biometricFeedScope = 'class';

    public ?int $tenantId = null;

    /**
     * @var array<int, array{status:string, note:string|null, arrived_at:string|null, departed_at:string|null, is_biometric:bool}>
     */
    public array $marks = [];

    public function mount(): void
    {
        $this->tenantId = (int) (auth()->user()?->tenant_id ?? 1);
        $this->date = $this->date ?: now()->toDateString();
        $this->session = $this->session ?: $this->defaultSession();
        $this->term = $this->term ?: $this->defaultTerm();

        $classes = $this->getClasses();

        // If classId was not passed in URL or is invalid, auto-select from today's sheet or first class
        if (!$this->classId || !$classes->contains('id', (int) $this->classId)) {
            $todaySheet = AttendanceSheet::where('date', $this->date)->latest()->first();
            if ($todaySheet && $classes->contains('id', (int) $todaySheet->class_id)) {
                $this->classId = (int) $todaySheet->class_id;
                $allowedSections = $this->getSections();
                if ($allowedSections->contains('id', (int) $todaySheet->section_id)) {
                    $this->sectionId = (int) $todaySheet->section_id;
                } else {
                    $this->sectionId = (int) ($allowedSections->first()?->id ?? 0);
                }
            } else {
                $firstClass = $classes->first();
                if ($firstClass) {
                    $this->classId = (int) $firstClass->id;
                    $this->sectionId = (int) ($this->getSections()->first()?->id ?? 0);
                }
            }
        } else {
            $this->classId = (int) $this->classId;
            $allowedSections = $this->getSections();
            if (!$this->sectionId || !$allowedSections->contains('id', (int) $this->sectionId)) {
                $this->sectionId = (int) ($allowedSections->first()?->id ?? 0);
            } else {
                $this->sectionId = (int) $this->sectionId;
            }
        }

        $this->syncSheetFromSelection();
    }

    public function setBiometricScope(string $scope): void
    {
        $this->biometricFeedScope = in_array($scope, ['class', 'all'], true) ? $scope : 'class';
    }

    public function exportCsv(): StreamedResponse
    {
        $records = $this->getDateRecords();
        $dateStr = $this->date;
        $filename = "attendance_records_{$dateStr}.csv";

        return response()->streamDownload(function () use ($records, $dateStr) {
            $out = fopen('php://output', 'wb');
            fputcsv($out, [
                'Student ID',
                'Full Name',
                'ADM No',
                'Class',
                'Section',
                'Date',
                'Punch Time',
                'Status',
                'Verification Note',
            ]);

            foreach ($records as $m) {
                $punchTime = $this->formatPunchTime($m->status, $m->note, $m->arrived_at ?? $m->updated_at ?? $m->created_at);
                fputcsv($out, [
                    $m->student_id,
                    $m->student?->full_name ?? 'Unknown Student',
                    $m->student?->admission_number ?? 'N/A',
                    $m->student?->schoolClass?->name ?? 'N/A',
                    $m->student?->section?->name ?? 'A',
                    $dateStr,
                    $punchTime,
                    $m->status,
                    $m->note ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    private function formatPunchTime(?string $status, ?string $note, mixed $timestamp): string
    {
        if (strcasecmp($status ?? '', 'Absent') === 0 && empty($note)) {
            return '-';
        }

        if (!empty($note) && preg_match('/\b(\d{1,2}:\d{2}\s*(?:AM|PM))\b/i', $note, $matches)) {
            return strtoupper($matches[1]);
        }

        if ($timestamp) {
            $tz = config('app.timezone', 'Africa/Lagos');
            try {
                return Carbon::parse($timestamp)->timezone($tz)->format('g:i A');
            } catch (\Throwable $e) {
                return (string) $timestamp;
            }
        }

        return '-';
    }

    private function getDateRecords()
    {
        $query = AttendanceMark::query()
            ->with(['student.schoolClass', 'student.section', 'student.user', 'sheet'])
            ->whereHas('sheet', fn($q) => $q->where('date', $this->date));

        // When scoped to 'class' and a class is selected, filter scans to the selected class
        if ($this->biometricFeedScope === 'class' && $this->classId) {
            $query->whereHas('sheet', fn($q) => $q->where('class_id', (int) $this->classId));
        }

        return $query->get()->sortBy(fn($m) => $m->student?->last_name);
    }

    private function getClasses()
    {
        $user = auth()->user();
        if ($user && $user->role === 'teacher') {
            $classIds = SubjectAllocation::where('teacher_id', $user->id)
                ->pluck('class_id')
                ->unique();
            if ($classIds->isNotEmpty()) {
                return SchoolClass::whereIn('id', $classIds)->orderBy('level')->get();
            }
            // Fall back to all classes for their school if the teacher has no specific subject allocations configured
        }
        return SchoolClass::query()->orderBy('level')->get();
    }

    private function getSections()
    {
        if (!$this->classId) {
            return collect();
        }

        $query = Section::query()
            ->where('class_id', (int) $this->classId);

        $user = auth()->user();
        if ($user && $user->role === 'teacher') {
            $scope = $user->teacherClassSectionScope();
            if (array_key_exists((int) $this->classId, $scope) && $scope[(int) $this->classId] !== null) {
                $query->whereIn('id', $scope[(int) $this->classId]);
            }
        }

        return $query->orderBy('name')->get();
    }

    private function getStudents()
    {
        if (!$this->classId || !$this->sectionId) {
            return collect();
        }
        return Student::query()
            ->where('class_id', (int) $this->classId)
            ->where('section_id', (int) $this->sectionId)
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
                return !in_array((string) ($this->marks[$student->id]['status'] ?? 'Unmarked'), ['Present', 'Unmarked'], true);
            });
        }
        return $students->values();
    }

    private function getMarkCounts($students): array
    {
        $counts = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Excused' => 0, 'Unmarked' => 0];
        foreach ($students as $student) {
            $status = (string) ($this->marks[$student->id]['status'] ?? 'Unmarked');
            if (!array_key_exists($status, $counts)) {
                $status = 'Unmarked';
            }
            $counts[$status]++;
        }
        return $counts;
    }

    public function updatedClassId(): void
    {
        $this->classId = $this->classId ? (int) $this->classId : null;
        $this->sectionId = null;
        if ($this->classId) {
            $firstSectionId = $this->getSections()->first()?->id;
            $this->sectionId = $firstSectionId ? (int) $firstSectionId : null;
        }
        $this->syncSheetFromSelection();
    }

    public function updatedSectionId(): void
    {
        $allowedSections = $this->getSections();
        if ($this->sectionId && !$allowedSections->contains('id', (int) $this->sectionId)) {
            $this->sectionId = (int) ($allowedSections->first()?->id ?? 0);
        } else {
            $this->sectionId = $this->sectionId ? (int) $this->sectionId : null;
        }
        $this->syncSheetFromSelection();
    }

    public function updatedDate(): void
    {
        $this->syncSheetFromSelection();
    }

    public function updatedTerm(): void
    {
        $this->term = $this->term ? (int) $this->term : null;
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

        $this->setMark($studentId, $tool);
    }

    /**
     * Retrieve or create the attendance sheet for the selected class/section/date.
     */
    private function getOrCreateSheet(): ?AttendanceSheet
    {
        if (!$this->classId || !$this->sectionId || !$this->date || !$this->term || !$this->session) {
            return null;
        }

        $tenantId = auth()->user()?->tenant_id;

        $sheet = AttendanceSheet::query()->firstOrCreate(
            [
                'class_id'   => (int) $this->classId,
                'section_id' => (int) $this->sectionId,
                'date'       => $this->date,
                'term'       => (int) $this->term,
                'session'    => $this->session,
            ],
            [
                'tenant_id' => $tenantId,
                'taken_by'  => auth()->id(),
            ]
        );

        $this->sheetId = $sheet->id;
        return $sheet;
    }

    public function start(): void
    {
        $sheet = $this->getOrCreateSheet();
        if ($sheet) {
            $this->loadMarks($sheet);
        }
    }

    /**
     * Immediately persist a mark when a teacher clicks Present, Absent, Late, or Excused.
     */
    public function setMark(int $studentId, string $status): void
    {
        if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused', 'Unmarked'], true)) {
            return;
        }

        $current = (string) ($this->marks[$studentId]['status'] ?? 'Unmarked');
        if ($current === $status) {
            $status = 'Unmarked';
        }

        // If resetting to Unmarked, delete any existing DB mark and reset in-memory
        if ($status === 'Unmarked') {
            if ($this->sheetId) {
                AttendanceMark::query()
                    ->where('sheet_id', $this->sheetId)
                    ->where('student_id', $studentId)
                    ->delete();
            }
            $this->marks[$studentId] = [
                'status'       => 'Unmarked',
                'note'         => null,
                'arrived_at'   => null,
                'departed_at'  => null,
                'is_biometric' => false,
            ];

            $student = Student::find($studentId);
            $studentName = $student ? $student->full_name : "Student #{$studentId}";
            $this->dispatch('alert', message: "{$studentName} unmarked", type: 'info');
            return;
        }

        $sheet = $this->getOrCreateSheet();
        if (!$sheet) {
            return;
        }

        $existingMark = AttendanceMark::query()
            ->where('sheet_id', $sheet->id)
            ->where('student_id', $studentId)
            ->first();

        $note = $this->marks[$studentId]['note'] ?? $existingMark?->note;
        $arrivedAt = $existingMark?->arrived_at;

        if ($status === 'Present' || $status === 'Late') {
            if (!$arrivedAt) {
                $arrivedAt = now()->format('H:i:s');
            }
        } elseif ($status === 'Absent') {
            $arrivedAt = null;
        }

        AttendanceMark::query()->updateOrCreate(
            [
                'sheet_id'   => $sheet->id,
                'student_id' => $studentId,
            ],
            [
                'tenant_id'  => $sheet->tenant_id ?? auth()->user()?->tenant_id,
                'status'     => $status,
                'note'       => $note,
                'arrived_at' => $arrivedAt,
            ]
        );

        $this->marks[$studentId] = [
            'status'       => $status,
            'note'         => $note,
            'arrived_at'   => $arrivedAt,
            'departed_at'  => $existingMark?->departed_at,
            'is_biometric' => !empty($existingMark?->arrived_at) && (str_contains((string) $note, 'Biometric') || str_contains((string) $note, 'Arrived:')),
        ];

        $student = Student::find($studentId);
        $studentName = $student ? $student->full_name : "Student #{$studentId}";
        $this->dispatch('alert', message: "{$studentName} marked {$status}", type: 'success');
    }

    /**
     * Mark all students in the class/section immediately in the database.
     */
    public function markAll(string $status): void
    {
        if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            return;
        }

        $sheet = $this->getOrCreateSheet();
        if (!$sheet) {
            return;
        }

        $students = $this->getStudents();
        $nowTime = now()->format('H:i:s');

        DB::transaction(function () use ($sheet, $students, $status, $nowTime) {
            foreach ($students as $student) {
                $existing = AttendanceMark::query()
                    ->where('sheet_id', $sheet->id)
                    ->where('student_id', $student->id)
                    ->first();

                $note = $this->marks[$student->id]['note'] ?? $existing?->note;
                $arrivedAt = $existing?->arrived_at;

                if ($status === 'Present' || $status === 'Late') {
                    if (!$arrivedAt) {
                        $arrivedAt = $nowTime;
                    }
                } elseif ($status === 'Absent') {
                    $arrivedAt = null;
                }

                AttendanceMark::query()->updateOrCreate(
                    [
                        'sheet_id'   => $sheet->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'tenant_id'  => $sheet->tenant_id ?? auth()->user()?->tenant_id,
                        'status'     => $status,
                        'note'       => $note,
                        'arrived_at' => $arrivedAt,
                    ]
                );

                $this->marks[$student->id] = [
                    'status'       => $status,
                    'note'         => $note,
                    'arrived_at'   => $arrivedAt,
                    'departed_at'  => $existing?->departed_at,
                    'is_biometric' => !empty($existing?->arrived_at) && (str_contains((string) $note, 'Biometric') || str_contains((string) $note, 'Arrived:')),
                ];
            }
        });

        $this->dispatch('alert', message: "All students marked as {$status}.", type: 'success');
    }

    public function cycleStatus(int $studentId): void
    {
        $order = ['Unmarked', 'Present', 'Absent', 'Late', 'Excused'];
        $current = (string) ($this->marks[$studentId]['status'] ?? 'Unmarked');
        $index = array_search($current, $order, true);
        $next = $order[$index === false ? 0 : ($index + 1) % count($order)];

        $this->setMark($studentId, $next);
    }

    public function updateNote(int $studentId, ?string $note): void
    {
        if (!isset($this->marks[$studentId])) {
            return;
        }

        $this->marks[$studentId]['note'] = $note;

        if ($this->sheetId) {
            AttendanceMark::query()
                ->where('sheet_id', $this->sheetId)
                ->where('student_id', $studentId)
                ->update(['note' => $note]);
        }
    }

    public function save(): void
    {
        $sheet = $this->getOrCreateSheet();
        if (!$sheet) {
            return;
        }

        DB::transaction(function () use ($sheet) {
            foreach ($this->getStudents() as $student) {
                $row = $this->marks[$student->id] ?? [];
                $status = (string) ($row['status'] ?? 'Unmarked');
                $note = $row['note'] ?? null;
                $arrivedAt = $row['arrived_at'] ?? null;

                // Skip unmarked students — don't persist them
                if ($status === 'Unmarked') {
                    continue;
                }

                if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
                    throw ValidationException::withMessages([
                        'marks' => "Invalid attendance status for {$student->full_name}.",
                    ]);
                }

                AttendanceMark::query()->updateOrCreate(
                    [
                        'sheet_id'   => $sheet->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'tenant_id'  => $sheet->tenant_id ?? auth()->user()?->tenant_id,
                        'status'     => $status,
                        'note'       => $note ? (string) $note : null,
                        'arrived_at' => $arrivedAt,
                    ],
                );
            }
        });

        $this->dispatch('alert', message: 'Attendance synchronized and saved.', type: 'success');
        $this->dispatchSuccessModal('Attendance Saved', 'The attendance sheet has been updated and synchronized successfully.');
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
                'status'       => $mark?->status ?? 'Unmarked',
                'note'         => $mark?->note,
                'arrived_at'   => $mark?->arrived_at,
                'departed_at'  => $mark?->departed_at,
                'is_biometric' => !empty($mark?->arrived_at) && (str_contains((string) $mark?->note, 'Biometric') || str_contains((string) $mark?->note, 'Arrived:')),
            ];
        }
    }

    /**
     * @return array{0: \App\Models\Section}
     */
    private function validateSelection(): array
    {
        $this->validate([
            'classId'   => [
                'required',
                'integer',
                Rule::exists('classes', 'id')->when(
                    auth()->user()?->tenant_id,
                    fn ($query, $tenantId) => $query->where('tenant_id', $tenantId)
                ),
            ],
            'sectionId' => ['required', 'integer', Rule::exists('sections', 'id')],
            'date'      => ['required', 'date'],
            'term'      => ['required', 'integer', 'between:1,3'],
            'session'   => ['required', 'string', 'max:9', 'regex:/^\d{4}\/\d{4}$/'],
        ]);

        $user = auth()->user();
        if ($user?->role === 'teacher') {
            $scope = $user->teacherClassSectionScope();
            if (!empty($scope) && !array_key_exists((int) $this->classId, $scope)) {
                throw ValidationException::withMessages([
                    'classId' => 'You are not authorized to record attendance for this class.',
                ]);
            }
            if (!empty($scope) && array_key_exists((int) $this->classId, $scope) && $scope[(int) $this->classId] !== null) {
                if (!in_array((int) $this->sectionId, $scope[(int) $this->classId], true)) {
                    throw ValidationException::withMessages([
                        'sectionId' => 'You are not authorized to record attendance for this arm / subclass.',
                    ]);
                }
            }
        }

        $section = Section::query()
            ->where('id', (int) $this->sectionId)
            ->where('class_id', (int) $this->classId)
            ->first();

        if (!$section) {
            throw ValidationException::withMessages([
                'sectionId' => 'Please select a valid section for the chosen class.',
            ]);
        }

        return [$section];
    }

    public function syncSheetFromSelection(): void
    {
        $this->sheetId = null;
        $this->marks = [];

        if (!$this->classId || !$this->sectionId || !$this->date || !$this->term || !$this->session) {
            return;
        }

        $sheet = AttendanceSheet::query()
            ->where('class_id', (int) $this->classId)
            ->where('section_id', (int) $this->sectionId)
            ->where('date', $this->date)
            ->where('term', (int) $this->term)
            ->where('session', $this->session)
            ->first();

        if (!$sheet) {
            // Pre-initialize default marks for active students to ensure secure binding
            $students = $this->getStudents();
            foreach ($students as $student) {
                $this->marks[$student->id] = [
                    'status'       => 'Unmarked',
                    'note'         => null,
                    'arrived_at'   => null,
                    'departed_at'  => null,
                    'is_biometric' => false,
                ];
            }
            return;
        }

        $this->sheetId = $sheet->id;
        $this->loadMarks($sheet);
    }

    /**
     * Real-time sync: on each poll or render, if a student punched the K40
     * or a sheet was created in the DB, pull the latest marks into memory.
     */
    private function syncFromDatabaseIfSheetExists(): void
    {
        if (!$this->classId || !$this->sectionId || !$this->date || !$this->term || !$this->session) {
            return;
        }

        if (!$this->sheetId) {
            $sheet = AttendanceSheet::query()
                ->where('class_id', (int) $this->classId)
                ->where('section_id', (int) $this->sectionId)
                ->where('date', $this->date)
                ->where('term', (int) $this->term)
                ->where('session', $this->session)
                ->first();

            if ($sheet) {
                $this->sheetId = $sheet->id;
                $this->loadMarks($sheet);
                return;
            }
        } else {
            $dbMarks = AttendanceMark::query()
                ->where('sheet_id', $this->sheetId)
                ->get()
                ->keyBy('student_id');

            foreach ($dbMarks as $studentId => $mark) {
                $this->marks[$studentId] = [
                    'status'       => $mark->status,
                    'note'         => $mark->note,
                    'arrived_at'   => $mark->arrived_at,
                    'departed_at'  => $mark->departed_at,
                    'is_biometric' => !empty($mark->arrived_at) && (str_contains((string) $mark->note, 'Biometric') || str_contains((string) $mark->note, 'Arrived:')),
                ];
            }
        }
    }

    #[On('echo-private:tenant.{tenantId},.BiometricScanDetected')]
    #[On('biometric-punch-received')]
    public function handleBiometricScan($event = null): void
    {
        $this->syncFromDatabaseIfSheetExists();
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
        $this->syncFromDatabaseIfSheetExists();

        $students        = $this->getStudents();
        $visibleStudents = $this->getVisibleStudents($students);
        $markCounts      = $this->getMarkCounts($students);
        $classes         = $this->getClasses();
        $sections        = $this->getSections();
        $dateRecords     = $this->getDateRecords();
        $selectedClass   = $classes->firstWhere('id', (int) $this->classId);

        return view('livewire.attendance.index', compact(
            'students', 'visibleStudents', 'markCounts', 'classes', 'sections', 'dateRecords', 'selectedClass'
        ));
    }
}
