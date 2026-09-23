<?php

namespace App\Livewire\Attendance;

use App\Models\TeacherAttendanceMark;
use App\Models\TeacherAttendanceSheet;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Staff Timesheet & Payroll Attendance')]
class StaffTimesheet extends Component
{
    public string $selectedMonth;
    public string $sectionFilter = 'all'; // 'all', 'Western', 'Islamic'
    public string $search = '';
    public ?int $selectedTeacherId = null;
    public bool $showBreakdownModal = false;

    // Payroll deduction configuration
    public float $deductionPerMinute = 50.00; // ₦50 per late minute
    public float $deductionPerAbsent = 2000.00; // ₦2,000 per unexcused day absent

    public function boot(): void
    {
        abort_unless(
            in_array(auth()->user()?->role, ['admin', 'bursar', 'proprietor'], true) || auth()->user()?->is_super_admin,
            403,
            'Only administrators, bursars, and proprietors have permission to access the payroll timesheet.'
        );
    }

    public function mount(): void
    {
        $this->selectedMonth = Carbon::now()->format('Y-m');
    }

    public function __get($property)
    {
        if ($property === 'workingDaysCount') {
            return $this->workingDaysCount();
        }
        if ($property === 'staffTimesheets') {
            return $this->staffTimesheets();
        }
        if ($property === 'selectedTeacherBreakdown') {
            return $this->selectedTeacherBreakdown();
        }

        return parent::__get($property);
    }

    #[Computed]
    public function workingDaysCount(): int
    {
        $tenantId = auth()->user()?->tenant_id;
        $startOfMonth = Carbon::parse($this->selectedMonth . '-01')->startOfMonth();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();
        $capDate = $endOfMonth->isFuture() ? Carbon::today() : $endOfMonth;

        // Count distinct attendance sheets recorded in this month
        $sheetQuery = TeacherAttendanceSheet::query();
        if ($tenantId) {
            $sheetQuery->where('tenant_id', $tenantId);
        }
        $recordedSheetsCount = $sheetQuery
            ->whereBetween('date', [$startOfMonth->toDateString(), $capDate->toDateString()])
            ->count();

        if ($recordedSheetsCount > 0) {
            return $recordedSheetsCount;
        }

        // Fallback: count weekdays (Mon-Fri) up to capDate
        $weekdays = 0;
        $period = CarbonPeriod::create($startOfMonth, $capDate);
        foreach ($period as $dt) {
            if (!$dt->isWeekend()) {
                $weekdays++;
            }
        }

        return max(1, $weekdays);
    }

    #[Computed]
    public function staffTimesheets(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        $startOfMonth = Carbon::parse($this->selectedMonth . '-01')->startOfMonth()->toDateString();
        $endOfMonth = Carbon::parse($this->selectedMonth . '-01')->endOfMonth()->toDateString();
        $totalWorkDays = $this->workingDaysCount;

        $staffQuery = User::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('role', ['teacher', 'admin', 'bursar'])
            ->where('is_active', true)
            ->orderBy('name');

        if ($this->search) {
            $staffQuery->where('name', 'like', '%' . trim($this->search) . '%');
        }

        $staffMembers = $staffQuery->get();

        // Eager-load all marks for the month
        $sheetQuery = TeacherAttendanceSheet::query();
        if ($tenantId) {
            $sheetQuery->where('tenant_id', $tenantId);
        }
        $sheetIds = $sheetQuery
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->pluck('id');

        $allMarks = TeacherAttendanceMark::whereIn('sheet_id', $sheetIds)
            ->with('sheet')
            ->get()
            ->groupBy('teacher_id');

        $results = [];

        $westernLateThreshold = \App\Support\AttendanceShiftConfig::getLateThreshold(\App\Support\AttendanceShiftConfig::SHIFT_WESTERN, $tenantId);
        $islamicLateThreshold = \App\Support\AttendanceShiftConfig::getLateThreshold(\App\Support\AttendanceShiftConfig::SHIFT_ISLAMIC, $tenantId);

        foreach ($staffMembers as $staff) {
            $shift = $staff->getShift(); // 'Islamic' or 'Western'

            if ($this->sectionFilter !== 'all' && strtolower($shift) !== strtolower($this->sectionFilter)) {
                continue;
            }

            $marks = $allMarks->get($staff->id, collect());

            $presentCount = 0;
            $lateCount = 0;
            $absentCount = 0;
            $excusedCount = 0;
            $totalLateMinutes = 0;

            foreach ($marks as $mark) {
                $status = strtolower($mark->status ?? 'present');

                if ($status === 'present') {
                    $presentCount++;
                } elseif ($status === 'late') {
                    $presentCount++; // Attended, but arrived late
                    $lateCount++;

                    // Calculate late minutes from dedicated punch_in_time column (falls back to note parsing for old records)
                    $lateMinutes = $this->extractLateMinutes($mark, $shift, $westernLateThreshold, $islamicLateThreshold);
                    $totalLateMinutes += $lateMinutes;
                } elseif ($status === 'absent') {
                    $absentCount++;
                } elseif ($status === 'excused') {
                    $excusedCount++;
                }
            }

            // Unrecorded working days default to absent if in the past
            $recordedDays = $presentCount + $absentCount + $excusedCount;
            if ($recordedDays < $totalWorkDays) {
                $unaccounted = $totalWorkDays - $recordedDays;
                $absentCount += $unaccounted;
            }

            $attendanceRate = $totalWorkDays > 0 ? round(($presentCount / $totalWorkDays) * 100, 1) : 0;
            $punctualityRate = $presentCount > 0 ? round((($presentCount - $lateCount) / $presentCount) * 100, 1) : 100;

            $lateDeduction = $totalLateMinutes * $this->deductionPerMinute;
            $absentDeduction = $absentCount * $this->deductionPerAbsent;
            $totalDeduction = $lateDeduction + $absentDeduction;

            $results[] = [
                'teacher'           => $staff,
                'id'                => $staff->id,
                'name'              => $staff->name,
                'email'             => $staff->email,
                'role'              => ucfirst($staff->role),
                'shift'             => $shift,
                'shift_label'       => $staff->getShiftLabel(),
                'k40_uid'           => $staff->custom_fields['k40_uid'] ?? null,
                'work_days'         => $totalWorkDays,
                'present_days'      => $presentCount,
                'late_days'         => $lateCount,
                'absent_days'       => $absentCount,
                'excused_days'      => $excusedCount,
                'total_late_minutes'=> $totalLateMinutes,
                'attendance_rate'   => $attendanceRate,
                'punctuality_rate'  => $punctualityRate,
                'late_deduction'    => $lateDeduction,
                'absent_deduction'  => $absentDeduction,
                'total_deduction'   => $totalDeduction,
            ];
        }

        return $results;
    }

    private function extractLateMinutes(TeacherAttendanceMark $mark, string $shift, string $westernThreshold, string $islamicThreshold): int
    {
        // Prefer the dedicated punch_in_time column (available for all new punches)
        $punchInRaw = $mark->punch_in_time;

        // Legacy fallback: parse the note text for older records that pre-date the column
        if (!$punchInRaw && $mark->note) {
            if (preg_match('/(?:In:\s*|scan at\s*)(\d{1,2}:\d{2}(?::\d{2})?\s*(?:AM|PM)?)/i', $mark->note, $matches)) {
                $punchInRaw = trim($matches[1]);
            }
        }

        if (!$punchInRaw) {
            return 15; // default 15 mins if we can't determine exact time
        }

        try {
            $punch     = Carbon::parse($punchInRaw);
            $threshold = Carbon::parse(($shift === 'Islamic') ? $islamicThreshold : $westernThreshold);

            return $punch->greaterThan($threshold)
                ? max(0, (int) $threshold->diffInMinutes($punch))
                : 0;
        } catch (\Throwable) {
            return 15;
        }
    }

    public function openBreakdown(int $teacherId): void
    {
        $this->selectedTeacherId = $teacherId;
        $this->showBreakdownModal = true;
    }

    public function closeBreakdown(): void
    {
        $this->showBreakdownModal = false;
        $this->selectedTeacherId = null;
    }

    #[Computed]
    public function selectedTeacherBreakdown(): array
    {
        if (!$this->selectedTeacherId) {
            return [];
        }

        $tenantId = auth()->user()?->tenant_id;
        $startOfMonth = Carbon::parse($this->selectedMonth . '-01')->startOfMonth()->toDateString();
        $endOfMonth = Carbon::parse($this->selectedMonth . '-01')->endOfMonth()->toDateString();

        $teacherQuery = User::withoutGlobalScopes();
        if ($tenantId) {
            $teacherQuery->where('tenant_id', $tenantId);
        }
        $teacher = $teacherQuery->find($this->selectedTeacherId);
        if (!$teacher) {
            return [];
        }

        $sheetQuery = TeacherAttendanceSheet::query();
        if ($tenantId) {
            $sheetQuery->where('tenant_id', $tenantId);
        }
        $sheetIds = $sheetQuery
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->pluck('id', 'date');

        $marks = TeacherAttendanceMark::whereIn('sheet_id', $sheetIds->values())
            ->where('teacher_id', $teacher->id)
            ->with('sheet')
            ->get()
            ->keyBy(fn($m) => $m->sheet->date->format('Y-m-d'));

        $logs = [];
        $start = Carbon::parse($startOfMonth);
        $end = Carbon::parse($endOfMonth);
        $cap = $end->isFuture() ? Carbon::today() : $end;

        $period = CarbonPeriod::create($start, $cap);

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            $dateStr = $date->format('Y-m-d');
            $mark = $marks->get($dateStr);

            $logs[] = [
                'date'      => $date->format('D, M j, Y'),
                'status'    => $mark?->status ?? 'Absent',
                'punch_in'  => $mark?->punch_in_time  ? \Carbon\Carbon::parse($mark->punch_in_time)->format('g:i A')  : null,
                'punch_out' => $mark?->punch_out_time ? \Carbon\Carbon::parse($mark->punch_out_time)->format('g:i A') : null,
                'note'      => $mark?->note ?? 'No punch record recorded',
                'updated'   => $mark?->updated_at ? $mark->updated_at->format('g:i A') : '-',
            ];
        }

        return [
            'teacher' => $teacher,
            'logs'    => array_reverse($logs),
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $monthLabel = Carbon::parse($this->selectedMonth . '-01')->format('F Y');
        $filename = "Staff_Timesheet_Payroll_{$this->selectedMonth}.csv";
        $data = $this->staffTimesheets;

        return response()->streamDownload(function () use ($data, $monthLabel) {
            $output = fopen('php://output', 'w');

            // Header info
            fputcsv($output, ["Staff Monthly Timesheet & Payroll Attendance Summary - {$monthLabel}"]);
            fputcsv($output, ["Generated At: " . now()->format('Y-m-d H:i:s')]);
            fputcsv($output, []);

            // Column Headings
            fputcsv($output, [
                'Staff ID',
                'Name',
                'Email',
                'Role',
                'Section / Shift',
                'K40 Hardware UID',
                'Working Days',
                'Days Present',
                'Days Late',
                'Days Absent',
                'Days Excused',
                'Total Late (Mins)',
                'Attendance Rate (%)',
                'Punctuality Rate (%)',
                'Late Deduction (NGN)',
                'Absent Deduction (NGN)',
                'Total Suggested Deduction (NGN)',
            ]);

            foreach ($data as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['email'],
                    $row['role'],
                    $row['shift_label'],
                    $row['k40_uid'] ?? '-',
                    $row['work_days'],
                    $row['present_days'],
                    $row['late_days'],
                    $row['absent_days'],
                    $row['excused_days'],
                    $row['total_late_minutes'],
                    $row['attendance_rate'] . '%',
                    $row['punctuality_rate'] . '%',
                    number_format($row['late_deduction'], 2, '.', ''),
                    number_format($row['absent_deduction'], 2, '.', ''),
                    number_format($row['total_deduction'], 2, '.', ''),
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function render()
    {
        return view('livewire.attendance.staff-timesheet', [
            'timesheets' => $this->staffTimesheets,
        ]);
    }
}
