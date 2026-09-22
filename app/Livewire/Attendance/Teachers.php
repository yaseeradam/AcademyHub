<?php

namespace App\Livewire\Attendance;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\TeacherAttendanceMark;
use App\Models\TeacherAttendanceSheet;
use App\Models\User;
use App\Traits\DispatchesModals;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Staff Attendance')]
class Teachers extends Component
{
    use DispatchesModals;

    public string $date = '';
    public ?int $term = null;
    public string $session = '';

    public ?int $sheetId = null;

    public string $tool = 'Absent';
    public string $search = '';
    public bool $onlyExceptions = false;
    public string $sectionFilter = 'all'; // 'all', 'Western', 'Islamic'
    public string $viewMode = 'sheet'; // 'sheet' or 'summary'

    /**
     * @var array<int, array{status:string, note:string|null}>
     */
    public array $marks = [];

    public function boot(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Only administrators can take staff attendance.');
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Only administrators can take staff attendance.');

        $this->date = now()->toDateString();
        $this->session = $this->session ?: $this->defaultSession();
        $this->term = $this->term ?: $this->defaultTerm();

        $this->syncSheetFromSelection();
    }

    public function __get($property)
    {
        if ($property === 'teachers') {
            return $this->teachers();
        }
        if ($property === 'visibleTeachers') {
            return $this->visibleTeachers();
        }
        if ($property === 'markCounts') {
            return $this->markCounts();
        }
        if ($property === 'teacherSummaries') {
            return $this->teacherSummaries();
        }

        return parent::__get($property);
    }

    #[Computed]
    public function teachers()
    {
        return User::query()
            ->whereIn('role', ['teacher', 'admin', 'bursar'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'custom_fields']);
    }

    #[Computed]
    public function visibleTeachers()
    {
        $teachers = $this->teachers;

        if ($this->sectionFilter !== 'all') {
            $filter = strtolower($this->sectionFilter);
            $teachers = $teachers->filter(function (User $teacher) use ($filter) {
                return strtolower($teacher->getShift()) === $filter;
            });
        }

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

    #[Computed]
    public function teacherSummaries(): array
    {
        if (!$this->session || !$this->term) {
            return [];
        }

        $sheets = TeacherAttendanceSheet::query()
            ->where('session', $this->session)
            ->where('term', $this->term)
            ->pluck('id');

        $totalSheets = $sheets->count();

        if ($totalSheets === 0) {
            return [];
        }

        $marks = TeacherAttendanceMark::query()
            ->whereIn('sheet_id', $sheets)
            ->get()
            ->groupBy('teacher_id');

        $summaries = [];

        foreach ($this->teachers as $teacher) {
            $teacherMarks = $marks->get($teacher->id, collect());

            $present = $teacherMarks->where('status', 'Present')->count();
            $absent = $teacherMarks->where('status', 'Absent')->count();
            $late = $teacherMarks->where('status', 'Late')->count();
            $excused = $teacherMarks->where('status', 'Excused')->count();
            $markedDays = $teacherMarks->count();

            // Calculate attendance rate (% present or late out of total sheets)
            $rate = $totalSheets > 0 ? round((($present + $late) / $totalSheets) * 100, 1) : 0;

            $summaries[$teacher->id] = [
                'teacher' => $teacher,
                'total_sheets' => $totalSheets,
                'marked_days' => $markedDays,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'attendance_rate' => $rate,
            ];
        }

        return $summaries;
    }

    public function updatedDate(): void
    {
        $this->syncSheetFromSelection();
        $this->dispatch('$refresh');
    }

    public function updatedTerm(): void
    {
        $this->syncSheetFromSelection();
        $this->dispatch('$refresh');
    }

    public function updatedSession(): void
    {
        $this->syncSheetFromSelection();
        $this->dispatch('$refresh');
    }

    public function updatedSearch(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedOnlyExceptions(): void
    {
        $this->dispatch('$refresh');
    }

    public function setTool(string $tool): void
    {
        if (!in_array($tool, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            return;
        }

        $this->tool = $tool;
    }

    public function setMark(int $teacherId, string $status): void
    {
        if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            return;
        }

        $this->marks[$teacherId]['status'] = $status;
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

        $this->dispatch('alert', message: 'Staff attendance saved successfully.', type: 'success');
        $this->dispatchSuccessModal('Staff Attendance Saved', 'Staff attendance sheet for ' . $this->date . ' has been recorded successfully.');
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

    public function exportCsv(): StreamedResponse
    {
        $this->validateSelection();

        $cleanSession = str_replace(['/', '\\'], '-', (string) $this->session);
        $filename = "staff_attendance_{$this->date}_{$cleanSession}_term{$this->term}.csv";

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'wb');

            fputcsv($out, [
                'Teacher Name',
                'Email',
                'Section / Shift',
                'Date',
                'Punch Time',
                'Attendance Status',
                'Session',
                'Term',
                'Verification Note',
                'Recorded By'
            ]);

            $takenByName = auth()->user()?->name ?? 'Admin';

            $existing = [];
            if ($this->sheetId) {
                $existing = TeacherAttendanceMark::query()
                    ->where('sheet_id', $this->sheetId)
                    ->get()
                    ->keyBy('teacher_id');
            }

            foreach ($this->teachers() as $teacher) {
                $mark = $this->marks[$teacher->id] ?? [];
                $dbMark = $existing[$teacher->id] ?? null;

                $status = $mark['status'] ?? ($dbMark?->status ?? 'Present');
                $note = $mark['note'] ?? ($dbMark?->note ?? '');
                $timestamp = $dbMark?->updated_at ?? ($dbMark?->created_at ?? null);
                $punchTime = $this->formatPunchTime($status, $note, $timestamp);

                fputcsv($out, [
                    $teacher->name,
                    $teacher->email,
                    $teacher->getShiftLabel(),
                    $this->date,
                    $punchTime,
                    $status,
                    $this->session,
                    $this->term,
                    $note,
                    $takenByName,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
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
            foreach ($this->teachers as $teacher) {
                $this->marks[$teacher->id] = [
                    'status' => 'Present',
                    'note' => null,
                ];
            }
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

    private function formatPunchTime(?string $status, ?string $note, mixed $timestamp): string
    {
        if (strcasecmp($status ?? '', 'Absent') === 0 && empty($note)) {
            return '-';
        }

        if (!empty($note)) {
            if (preg_match('/In:\s*(\d{1,2}:\d{2}\s*(?:AM|PM))\s*\|\s*Out:\s*(\d{1,2}:\d{2}\s*(?:AM|PM))/i', $note, $m)) {
                return strtoupper("{$m[1]} (Out: {$m[2]})");
            }
            if (preg_match('/\b(\d{1,2}:\d{2}\s*(?:AM|PM))\b/i', $note, $matches)) {
                return strtoupper($matches[1]);
            }
        }

        if ($timestamp) {
            $tz = config('app.timezone', 'Africa/Lagos');
            return \Carbon\Carbon::parse($timestamp)->timezone($tz)->format('g:i A');
        }

        return '-';
    }

    public function render()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        return view('livewire.attendance.teachers');
    }
}
