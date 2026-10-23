<?php

namespace App\Livewire\Attendance;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\TeacherAttendanceMark;
use App\Models\TeacherAttendanceSheet;
use App\Models\User;
use App\Traits\DispatchesModals;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
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
    public ?int $tenantId = null;

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

        $this->tenantId = (int) (auth()->user()?->tenant_id ?? 1);
        $this->date = $this->date ?: now()->toDateString();
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
            ->get(['id', 'name', 'email', 'role', 'profile_photo', 'shift', 'custom_fields']);
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
                $status = (string) ($this->marks[$teacher->id]['status'] ?? 'Unmarked');

                return !in_array($status, ['Present', 'Unmarked'], true);
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
            'Unmarked' => 0,
        ];

        foreach ($this->teachers as $teacher) {
            $status = (string) ($this->marks[$teacher->id]['status'] ?? 'Unmarked');
            if (!array_key_exists($status, $counts)) {
                $status = 'Unmarked';
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

    /**
     * Retrieve or create the staff attendance sheet for the selected date/term/session.
     */
    private function getOrCreateSheet(): ?TeacherAttendanceSheet
    {
        if (!$this->date || !$this->term || !$this->session) {
            return null;
        }

        $tenantId = auth()->user()?->tenant_id;

        $sheet = TeacherAttendanceSheet::query()->firstOrCreate(
            [
                'date'    => $this->date,
                'term'    => (int) $this->term,
                'session' => $this->session,
            ],
            [
                'tenant_id' => $tenantId,
                'taken_by'  => auth()->id(),
            ]
        );

        $this->sheetId = $sheet->id;
        return $sheet;
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
        if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused', 'Unmarked'], true)) {
            return;
        }

        $current = (string) ($this->marks[$teacherId]['status'] ?? 'Unmarked');
        if ($current === $status) {
            $status = 'Unmarked';
        }

        $this->marks[$teacherId]['status'] = $status;
        unset($this->markCounts);
        unset($this->teacherSummaries);

        if ($status === 'Unmarked') {
            if ($this->sheetId) {
                TeacherAttendanceMark::query()
                    ->where('sheet_id', $this->sheetId)
                    ->where('teacher_id', $teacherId)
                    ->delete();
            }
            $this->marks[$teacherId]['note'] = null;

            $teacher = User::find($teacherId);
            $name = $teacher ? $teacher->name : "Teacher #{$teacherId}";
            $this->dispatch('alert', message: "{$name} unmarked", type: 'info');
            return;
        }

        $sheet = $this->getOrCreateSheet();
        if (!$sheet) {
            return;
        }

        $note = $this->marks[$teacherId]['note'] ?? null;

        TeacherAttendanceMark::query()->updateOrCreate(
            [
                'sheet_id'   => $sheet->id,
                'teacher_id' => $teacherId,
            ],
            [
                'tenant_id' => $sheet->tenant_id ?? auth()->user()?->tenant_id,
                'status'    => $status,
                'note'      => $note ? (string) $note : null,
            ]
        );

        $teacher = User::find($teacherId);
        $name = $teacher ? $teacher->name : "Teacher #{$teacherId}";
        $this->dispatch('alert', message: "{$name} marked {$status}", type: 'success');
    }

    public function applyTool(int $teacherId): void
    {
        $tool = $this->tool;
        if (!in_array($tool, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            $tool = 'Absent';
        }

        $this->setMark($teacherId, $tool);
    }

    public function updatedMarks($value, $key): void
    {
        unset($this->markCounts);

        // Check if a note was updated: e.g. "12.note"
        if (str_ends_with((string) $key, '.note')) {
            $parts = explode('.', (string) $key);
            $teacherId = (int) ($parts[0] ?? 0);

            if ($teacherId > 0 && $this->sheetId) {
                TeacherAttendanceMark::query()
                    ->where('sheet_id', $this->sheetId)
                    ->where('teacher_id', $teacherId)
                    ->update(['note' => $value ? (string) $value : null]);
            }
        }
    }

    public function start(): void
    {
        $this->validateSelection();

        $sheet = $this->getOrCreateSheet();
        if ($sheet) {
            $this->loadMarks($sheet);
        }
    }

    public function save(): void
    {
        $this->validateSelection();

        $sheet = $this->getOrCreateSheet();
        if (!$sheet) {
            return;
        }

        DB::transaction(function () use ($sheet) {
            foreach ($this->teachers as $teacher) {
                $row = $this->marks[$teacher->id] ?? [];
                $status = (string) ($row['status'] ?? 'Unmarked');
                $note = $row['note'] ?? null;

                // Skip Unmarked teachers — only save explicitly marked ones
                if ($status === 'Unmarked') {
                    TeacherAttendanceMark::query()
                        ->where('sheet_id', $sheet->id)
                        ->where('teacher_id', $teacher->id)
                        ->delete();
                    continue;
                }

                if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
                    throw ValidationException::withMessages([
                        'marks' => "Invalid attendance status for {$teacher->name}.",
                    ]);
                }

                TeacherAttendanceMark::query()->updateOrCreate(
                    [
                        'sheet_id'   => $sheet->id,
                        'teacher_id' => $teacher->id,
                    ],
                    [
                        'tenant_id' => $sheet->tenant_id ?? auth()->user()?->tenant_id,
                        'status'    => $status,
                        'note'      => $note ? (string) $note : null,
                    ],
                );
            }
        });

        unset($this->markCounts);
        unset($this->teacherSummaries);

        $this->dispatch('alert', message: 'Staff attendance saved successfully.', type: 'success');
        $this->dispatchSuccessModal('Staff Attendance Saved', 'Staff attendance sheet for ' . $this->date . ' has been recorded successfully.');
    }

    public function markAll(string $status): void
    {
        if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused'], true)) {
            return;
        }

        $sheet = $this->getOrCreateSheet();
        if (!$sheet) {
            return;
        }

        DB::transaction(function () use ($sheet, $status) {
            foreach ($this->teachers as $teacher) {
                $existing = TeacherAttendanceMark::withoutGlobalScopes()
                    ->where('sheet_id', $sheet->id)
                    ->where('teacher_id', $teacher->id)
                    ->first();

                // If marking all Absent, protect staff who physically scanned biometric at the gate!
                if ($status === 'Absent' && $existing && !empty($existing->punch_in_time) && in_array($existing->status, ['Present', 'Late'])) {
                    continue; // Skip - do not wipe verified biometric punches!
                }

                $this->marks[$teacher->id]['status'] = $status;
                $note = $this->marks[$teacher->id]['note'] ?? $existing?->note;

                TeacherAttendanceMark::withoutGlobalScopes()->updateOrCreate(
                    [
                        'sheet_id'   => $sheet->id,
                        'teacher_id' => $teacher->id,
                    ],
                    [
                        'tenant_id' => $sheet->tenant_id ?? auth()->user()?->tenant_id,
                        'status'    => $status,
                        'note'      => $note ? (string) $note : null,
                    ]
                );
            }
        });

        unset($this->markCounts);
        unset($this->teacherSummaries);

        $this->dispatch('alert', message: "All staff marked as {$status}.", type: 'success');
    }

    public function cycleStatus(int $teacherId): void
    {
        $order = ['Unmarked', 'Present', 'Absent', 'Late', 'Excused'];
        $current = (string) ($this->marks[$teacherId]['status'] ?? 'Unmarked');
        $index = array_search($current, $order, true);
        $next = $order[$index === false ? 0 : ($index + 1) % count($order)];

        $this->setMark($teacherId, $next);
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

                $status = $mark['status'] ?? ($dbMark?->status ?? 'Unmarked');
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

    public function downloadWeeklyPdf(): ?StreamedResponse
    {
        $this->validateSelection();

        $referenceDate = $this->date ? Carbon::parse($this->date) : now();
        $startOfWeek = $referenceDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $startOfWeek->copy()->addDays(4); // Friday

        $weekRangeLabel = 'Week of ' . $startOfWeek->format('D, d M') . ' – ' . $endOfWeek->format('D, d M, Y');

        $weekDays = [];
        for ($i = 0; $i < 5; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $weekDays[] = [
                'date' => $dayDate->toDateString(),
                'name' => $dayDate->format('D'),
                'formatted' => $dayDate->format('d M'),
            ];
        }

        $tenantId = auth()->user()?->tenant_id;
        $sheetsQuery = TeacherAttendanceSheet::withoutGlobalScopes()
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->where('term', (int) $this->term)
            ->where('session', $this->session);

        if ($tenantId) {
            $sheetsQuery->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        $sheets = $sheetsQuery->with(['marks.teacher'])->get();
        $sheetsByDate = $sheets->keyBy(fn($s) => Carbon::parse($s->date)->toDateString());
        $activeDaysCount = $sheets->count() > 0 ? $sheets->count() : 5;

        // Collect staff members based on shift filter if applicable
        $staffMembers = $this->visibleTeachers;
        $scopeLabel = match(strtolower($this->sectionFilter)) {
            'western' => 'Western Shift Staff',
            'islamic' => 'Islamic Shift Staff',
            default => 'All School Staff (Academic & Administrative)',
        };

        $staffRows = [];
        $totalPresentAll = 0;
        $totalPossibleAll = 0;
        $punctualityStarsAchievers = [];

        foreach ($staffMembers as $staff) {
            $daysData = [];
            $daysPresent = 0;
            $daysLate = 0;

            foreach ($weekDays as $day) {
                $sheet = $sheetsByDate->get($day['date']);
                if ($sheet) {
                    $mark = $sheet->marks->firstWhere('teacher_id', $staff->id);
                    $status = $mark ? $mark->status : 'Unmarked';
                    $punchDisplay = null;
                    if ($mark) {
                        $pTime = $this->formatPunchTime($mark->status, $mark->note, $mark->punch_in_time ?? $mark->created_at);
                        $punchDisplay = ($pTime !== '-') ? $pTime : null;
                    }

                    if ($status === 'Present') {
                        $daysPresent++;
                    } elseif ($status === 'Late') {
                        $daysPresent++;
                        $daysLate++;
                    }
                } else {
                    $status = '-';
                    $punchDisplay = null;
                }

                $daysData[] = [
                    'status' => $status,
                    'punch_display' => $punchDisplay,
                ];
            }

            $rate = $activeDaysCount > 0 ? (int) round(($daysPresent / $activeDaysCount) * 100) : 0;
            if ($rate > 100) {
                $rate = 100;
            }

            $stars = 0;
            if ($activeDaysCount > 0 && $daysPresent > 0) {
                if ($daysPresent === $activeDaysCount && $daysLate === 0) {
                    $stars = 5;
                } elseif ($daysPresent === $activeDaysCount && $daysLate <= 1) {
                    $stars = 4;
                } elseif ($daysPresent >= ($activeDaysCount * 0.8)) {
                    $stars = max(3, 4 - $daysLate);
                } elseif ($daysPresent >= ($activeDaysCount * 0.6)) {
                    $stars = 3;
                } elseif ($daysPresent >= ($activeDaysCount * 0.4)) {
                    $stars = 2;
                } else {
                    $stars = 1;
                }
            }

            if ($stars === 5) {
                $punctualityStarsAchievers[] = [
                    'name' => $staff->name,
                    'role' => ucfirst($staff->role),
                ];
            }

            $totalPresentAll += $daysPresent;
            $totalPossibleAll += $activeDaysCount;

            $staffRows[] = [
                'name' => $staff->name,
                'role' => $staff->role,
                'shift' => $staff->getShiftLabel(),
                'days' => $daysData,
                'days_present' => $daysPresent,
                'days_late' => $daysLate,
                'rate' => $rate,
                'stars' => $stars,
            ];
        }

        $weeklyAttendanceRate = $totalPossibleAll > 0 
            ? (int) round(($totalPresentAll / $totalPossibleAll) * 100) 
            : 0;

        $schoolName = config('academyhub.school_name', config('app.name', 'AI INTEGRATED ACADEMY ARGUNGU'));
        $schoolMotto = config('academyhub.school_motto', 'LEARNING TODAY LEADING TOMORROW');
        $schoolAddress = config('academyhub.school_address', '');
        $schoolPhone = config('academyhub.school_phone', '');
        $schoolEmail = config('academyhub.school_email', '');
        $logoPath = config('academyhub.school_logo');

        $logoBase64 = null;
        if ($logoPath) {
            $candidates = [
                storage_path('app/public/' . $logoPath),
                public_path('uploads/' . $logoPath),
                public_path($logoPath),
            ];
            foreach ($candidates as $fullPath) {
                if (file_exists($fullPath) && !is_dir($fullPath)) {
                    $mime = mime_content_type($fullPath) ?: 'image/png';
                    $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
                    break;
                }
            }
        }

        $termLabel = match((int) $this->term) {
            1 => 'First Term',
            2 => 'Second Term',
            3 => 'Third Term',
            default => 'Term ' . ($this->term ?? '1'),
        };
        $sessionLabel = $this->session ?: (AcademicSession::activeName() ?? now()->format('Y') . '/' . (now()->year + 1));

        $pdf = Pdf::loadView('pdf.weekly-staff-attendance', [
            'staffMembers' => $staffMembers,
            'staffRows' => $staffRows,
            'weekDays' => $weekDays,
            'activeDaysCount' => $activeDaysCount,
            'weeklyAttendanceRate' => $weeklyAttendanceRate,
            'punctualityStarsAchievers' => $punctualityStarsAchievers,
            'scopeLabel' => $scopeLabel,
            'schoolName' => $schoolName,
            'schoolMotto' => $schoolMotto,
            'schoolAddress' => $schoolAddress,
            'schoolPhone' => $schoolPhone,
            'schoolEmail' => $schoolEmail,
            'logoBase64' => $logoBase64,
            'termLabel' => $termLabel,
            'sessionLabel' => $sessionLabel,
            'weekRangeLabel' => $weekRangeLabel,
        ])->setPaper('a4', 'landscape');

        $weekCode = $startOfWeek->format('Y_m_d');
        $filename = "Weekly_Staff_Attendance_{$weekCode}.pdf";

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    private function loadMarks(TeacherAttendanceSheet $sheet): void
    {
        $this->marks = [];

        $existing = TeacherAttendanceMark::withoutGlobalScopes()
            ->where('sheet_id', $sheet->id)
            ->get()
            ->keyBy('teacher_id');

        foreach ($this->teachers as $teacher) {
            $mark = $existing->get($teacher->id);
            $hasIn = !empty($mark?->punch_in_time);
            $isBio = $hasIn || (!empty($mark?->note) && (str_contains((string) $mark->note, 'In:') || str_contains((string) $mark->note, 'Biometric')));

            $punchTime = null;
            if ($mark) {
                $punchTime = $this->formatPunchTime($mark->status, $mark->note, $mark->punch_in_time ?? $mark->created_at);
            }

            $this->marks[$teacher->id] = [
                'status'         => $mark?->status ?? 'Unmarked',
                'note'           => $mark?->note,
                'punch_in_time'  => $mark?->punch_in_time,
                'punch_out_time' => $mark?->punch_out_time,
                'punch_display'  => ($punchTime !== '-') ? $punchTime : null,
                'is_biometric'   => $isBio,
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

        $normalizedDate = Carbon::parse($this->date)->toDateString();

        $sheet = TeacherAttendanceSheet::withoutGlobalScopes()
            ->whereDate('date', $normalizedDate)
            ->where('term', (int) $this->term)
            ->where('session', $this->session)
            ->where(function ($q) {
                if ($this->tenantId) {
                    $q->where('tenant_id', $this->tenantId)->orWhereNull('tenant_id');
                }
            })
            ->first();

        if (!$sheet) {
            foreach ($this->teachers as $teacher) {
                $this->marks[$teacher->id] = [
                    'status'         => 'Unmarked',
                    'note'           => null,
                    'punch_in_time'  => null,
                    'punch_out_time' => null,
                    'punch_display'  => null,
                    'is_biometric'   => false,
                ];
            }
            return;
        }

        $this->sheetId = $sheet->id;
        $this->loadMarks($sheet);
    }

    /**
     * Real-time sync: on each poll or render, if a staff member punched the K40
     * or a sheet was created in the DB, pull the latest marks into memory.
     */
    private function syncFromDatabaseIfSheetExists(): void
    {
        if (!$this->date || !$this->term || !$this->session) {
            return;
        }

        $normalizedDate = Carbon::parse($this->date)->toDateString();

        if (!$this->sheetId) {
            $sheet = TeacherAttendanceSheet::withoutGlobalScopes()
                ->whereDate('date', $normalizedDate)
                ->where('term', (int) $this->term)
                ->where('session', $this->session)
                ->where(function ($q) {
                    if ($this->tenantId) {
                        $q->where('tenant_id', $this->tenantId)->orWhereNull('tenant_id');
                    }
                })
                ->first();

            if ($sheet) {
                $this->sheetId = $sheet->id;
                $this->loadMarks($sheet);
            }
            return;
        }

        $sheet = TeacherAttendanceSheet::withoutGlobalScopes()->find($this->sheetId);
        if (!$sheet || Carbon::parse($sheet->date)->toDateString() !== $normalizedDate) {
            $this->syncSheetFromSelection();
            return;
        }

        $this->loadMarks($sheet);
    }

    #[On('echo-private:biometric.scans.{tenantId},.BiometricScanDetected')]
    #[On('echo-private:biometric.scans.{tenantId},BiometricScanDetected')]
    #[On('echo-private:tenant.{tenantId},.BiometricScanDetected')]
    #[On('biometric-punch-received')]
    public function handleBiometricScan($event = null): void
    {
        $scan = is_array($event) ? ($event['scan'] ?? $event['event'] ?? $event) : null;
        if ($scan && in_array($scan['type'] ?? '', ['staff', 'teacher'], true) && !empty($scan['user_id'])) {
            $teacherId = (int) $scan['user_id'];
            $newStatus = $scan['status'] ?? 'Present';
            $time = $scan['time'] ?? now()->format('g:i A');
            $name = $scan['name'] ?? "Staff #{$teacherId}";

            if (isset($this->marks[$teacherId])) {
                $this->marks[$teacherId]['status'] = $newStatus;
                $this->marks[$teacherId]['is_biometric'] = true;
                $this->marks[$teacherId]['punch_display'] = $time;
                $this->dispatch('alert', message: "⚡ {$name} ({$newStatus}) clocked in via biometric at {$time}", type: 'success');
            }
        }

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

    private function getLatestExistingSheet(): ?TeacherAttendanceSheet
    {
        return TeacherAttendanceSheet::withoutGlobalScopes()
            ->withCount([
                'marks as present_count' => function ($query) {
                    $query->whereIn('status', ['Present', 'Late']);
                },
                'marks as total_count',
            ])
            ->where('term', (int) $this->term)
            ->where('session', $this->session)
            ->where(function ($q) {
                if ($this->tenantId) {
                    $q->where('tenant_id', $this->tenantId)->orWhereNull('tenant_id');
                }
            })
            ->orderBy('date', 'desc')
            ->first();
    }

    public function render()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $this->syncFromDatabaseIfSheetExists();

        return view('livewire.attendance.teachers', [
            'teachers' => $this->teachers,
            'visibleTeachers' => $this->visibleTeachers,
            'markCounts' => $this->markCounts,
            'teacherSummaries' => $this->teacherSummaries,
            'marks' => $this->marks,
            'date' => $this->date,
            'sheetId' => $this->sheetId,
            'latestSheet' => $this->getLatestExistingSheet(),
        ]);
    }
}
