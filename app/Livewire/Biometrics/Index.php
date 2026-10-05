<?php

namespace App\Livewire\Biometrics;

use App\Jobs\SendWhatsAppAttendanceAlert;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\Student;
use App\Models\TeacherAttendanceMark;
use App\Models\TeacherAttendanceSheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('ZKTeco K40 Biometrics & Hardware Gate Monitor')]
class Index extends Component
{
    public string $todayDate;
    public array $terminals = [];
    public ?string $lastPingAt = null;
    public string $punchTypeFilter = 'all'; // 'all', 'students', 'staff'

    // WhatsApp Configuration & Test
    public bool $showWhatsAppModal = false;
    public bool $whatsappEnabled = true;
    public bool $notifyCheckin = true;
    public bool $notifyCheckout = true;
    public string $testPhoneNumber = '';
    public ?string $testAlertStatus = null;

    // Terminal Configuration
    public bool $showTerminalModal = false;
    public string $editingTerminalName = '';
    public string $editingTerminalIp = '';
    public int $editingTerminalPort = 4370;
    public string $editingTerminalLocation = 'Main Gate';

    public function mount(): void
    {
        $this->todayDate = Carbon::today()->format('Y-m-d');
        $this->loadSettings();
        $this->refreshGateStatus();
    }

    private function loadSettings(): void
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;

        // Load WhatsApp settings
        $wa = Cache::get("tenant_{$tenantId}_whatsapp_attendance_settings", [
            'enabled'         => true,
            'notify_checkin'  => true,
            'notify_checkout' => true,
        ]);
        $this->whatsappEnabled = (bool) ($wa['enabled'] ?? true);
        $this->notifyCheckin = (bool) ($wa['notify_checkin'] ?? true);
        $this->notifyCheckout = (bool) ($wa['notify_checkout'] ?? true);

        // Load terminals list
        $defaultTerminals = [
            [
                'id'       => 1,
                'name'     => 'K40 Terminal (Main Gate)',
                'ip'       => '192.168.0.201',
                'port'     => 4370,
                'location' => 'Main Gate',
                'device'   => 'ZKTeco K40 Fingerprint & RFID',
            ],
        ];

        $terminals = Cache::get("tenant_{$tenantId}_k40_terminals", $defaultTerminals);

        // If the legacy 2-terminal default was stored in cache, keep only 1 terminal
        if (is_array($terminals) && count($terminals) === 2 && isset($terminals[1]['name']) && (str_contains($terminals[1]['name'], 'K40 Terminal 2') || str_contains($terminals[1]['name'], 'Western Section'))) {
            $terminals = array_slice($terminals, 0, 1);
            Cache::put("tenant_{$tenantId}_k40_terminals", $terminals, now()->addDays(30));
        }

        $this->terminals = $terminals;
    }

    public function refreshGateStatus(): void
    {
        $this->pingTerminals();
        $this->lastPingAt = now()->format('g:i:s A');
    }

    public function pingTerminals(): void
    {
        $updated = [];

        foreach ($this->terminals as $term) {
            $ip = $term['ip'];
            $port = (int) ($term['port'] ?? 4370);

            $start = microtime(true);
            $errno = 0;
            $errstr = '';

            // Fast TCP connection check (timeout 1.0s to avoid blocking UI)
            $socket = @fsockopen($ip, $port, $errno, $errstr, 1.0);
            $end = microtime(true);

            if ($socket) {
                fclose($socket);
                $latency = round(($end - $start) * 1000);
                $term['status'] = 'Online';
                $term['latency'] = "{$latency}ms";
                $term['error'] = null;
            } else {
                $term['status'] = 'Offline';
                $term['latency'] = null;
                $term['error'] = $errstr ?: 'Unreachable / Port closed';
            }

            $term['last_checked'] = now()->format('g:i A');
            $updated[] = $term;
        }

        $this->terminals = $updated;
        $tenantId = auth()->user()?->tenant_id ?? 1;
        Cache::put("tenant_{$tenantId}_k40_terminals", $this->terminals, now()->addDays(30));
    }

    public function addTerminal(): void
    {
        $this->validate([
            'editingTerminalName'     => 'required|string|min:2|max:100',
            'editingTerminalIp'       => 'required|ip',
            'editingTerminalPort'     => 'required|integer|between:1,65535',
            'editingTerminalLocation' => 'required|string|max:100',
        ]);

        $this->terminals[] = [
            'id'       => count($this->terminals) + 1,
            'name'     => trim($this->editingTerminalName),
            'ip'       => trim($this->editingTerminalIp),
            'port'     => (int) $this->editingTerminalPort,
            'location' => trim($this->editingTerminalLocation),
            'device'   => 'ZKTeco K40 Device',
            'status'   => 'Pending',
        ];

        $tenantId = auth()->user()?->tenant_id ?? 1;
        Cache::put("tenant_{$tenantId}_k40_terminals", $this->terminals, now()->addDays(30));

        $this->reset(['editingTerminalName', 'editingTerminalIp', 'editingTerminalLocation']);
        $this->editingTerminalPort = 4370;
        $this->showTerminalModal = false;

        $this->refreshGateStatus();
        $this->dispatch('alert', message: 'Hardware terminal added successfully!', type: 'success');
    }

    public function removeTerminal(int $index): void
    {
        if (isset($this->terminals[$index])) {
            unset($this->terminals[$index]);
            $this->terminals = array_values($this->terminals);

            $tenantId = auth()->user()?->tenant_id ?? 1;
            Cache::put("tenant_{$tenantId}_k40_terminals", $this->terminals, now()->addDays(30));

            $this->dispatch('alert', message: 'Hardware terminal removed.', type: 'info');
        }
    }

    public function saveWhatsAppSettings(): void
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        Cache::put("tenant_{$tenantId}_whatsapp_attendance_settings", [
            'enabled'         => $this->whatsappEnabled,
            'notify_checkin'  => $this->notifyCheckin,
            'notify_checkout' => $this->notifyCheckout,
        ], now()->addYears(1));

        $this->showWhatsAppModal = false;
        $this->dispatch('alert', message: 'WhatsApp notification settings saved successfully!', type: 'success');
    }

    public function sendTestAlert(): void
    {
        $this->validate([
            'testPhoneNumber' => 'required|string|min:10',
        ]);

        $schoolName = config('academyhub.school_name', 'Greenwood School');
        $msg = "✅ *TEST ATTENDANCE ALERT - {$schoolName}*\n\n" .
               "Assalamu Alaikum! This is a test biometric gate scan notification sent from your AcademyHub ZKTeco K40 hardware monitor.\n\n" .
               "🕒 *Time:* " . now()->format('g:i A') . "\n" .
               "📌 *Gate:* Gate 1 Main Entrance\n\n" .
               "_System status: Online & Ready._";

        $res = SendWhatsAppAttendanceAlert::sendDirectMessage($this->testPhoneNumber, $msg);

        if ($res['status'] === 'success' || $res['status'] === 'simulated') {
            $this->testAlertStatus = $res['message'];
            $this->dispatch('alert', message: $res['message'], type: 'success');
        } else {
            $this->testAlertStatus = 'Error: ' . $res['message'];
            $this->dispatch('alert', message: $res['message'], type: 'error');
        }
    }

    public function setPunchFilter(string $filter): void
    {
        $this->punchTypeFilter = in_array($filter, ['all', 'students', 'staff']) ? $filter : 'all';
    }

    public function triggerTestPopup(): void
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $student = Student::where('tenant_id', $tenantId)->with(['schoolClass', 'section'])->first()
            ?? Student::with(['schoolClass', 'section'])->first();

        if (!$student) {
            $this->dispatch('alert', message: 'No student found to simulate scan.', type: 'error');
            return;
        }

        $activeTerm = AcademicTerm::active();
        $termNumber = $activeTerm?->term_number ?? 1;
        $sessionName = AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);
        $systemUserId = auth()->id() ?? User::where('tenant_id', $tenantId)->where('role', 'admin')->value('id') ?? 1;
        $dateStr = now()->format('Y-m-d');
        $punchTime = now();
        $timeStr = $punchTime->format('H:i:s');
        $shift = $student->getShift();
        $status = \App\Support\AttendanceShiftConfig::evaluateStatus($timeStr, $shift, $student->tenant_id ?? $tenantId);

        // Ensure daily attendance sheet exists for student's class
        $sheet = AttendanceSheet::firstOrCreate(
            [
                'tenant_id'  => $student->tenant_id ?? $tenantId,
                'class_id'   => $student->class_id ?? 1,
                'section_id' => $student->section_id,
                'date'       => $dateStr,
                'term'       => $termNumber,
                'session'    => $sessionName,
            ],
            [
                'taken_by'   => $systemUserId,
            ]
        );

        // Record or update attendance mark
        $existingMark = AttendanceMark::where([
            'tenant_id'  => $student->tenant_id ?? $tenantId,
            'sheet_id'   => $sheet->id,
            'student_id' => $student->id,
        ])->first();

        $inDisplay = $punchTime->format('g:i A');

        if ($existingMark) {
            $existingMark->update([
                'status'      => $status,
                'arrived_at'  => $timeStr,
                'note'        => 'Arrived: ' . $inDisplay . " ({$status}) [Biometric]",
            ]);
        } else {
            AttendanceMark::create([
                'tenant_id'   => $student->tenant_id ?? $tenantId,
                'sheet_id'    => $sheet->id,
                'student_id'  => $student->id,
                'status'      => $status,
                'arrived_at'  => $timeStr,
                'departed_at' => null,
                'note'        => 'Arrived: ' . $inDisplay . " ({$status}) [Biometric]",
            ]);
        }

        $scanPayload = [
            'scan_id'           => \Illuminate\Support\Str::uuid()->toString(),
            'type'              => 'student',
            'user_id'           => $student->id,
            'name'              => $student->full_name,
            'email'             => $student->admission_number ?? 'ADM-2026-001',
            'photo_url'         => $student->passport_photo_url ?? '/avatars/student_blue.png',
            'role'              => 'student',
            'class_name'        => $student->schoolClass?->name ?? 'Nursery 1 Gold',
            'section_name'      => $student->section?->name ?? 'A',
            'admission_number'  => $student->admission_number ?? 'ADM-2026-001',
            'status'            => $status,
            'time'              => $inDisplay,
            'date'              => $dateStr,
            'scanned_at'        => now()->toISOString(),
            'scanned_timestamp' => now()->timestamp,
            'tenant_id'         => $student->tenant_id ?? $tenantId,
        ];

        Cache::put('latest_biometric_scan_tenant_' . ($student->tenant_id ?? $tenantId), $scanPayload, 120);
        Cache::put('latest_biometric_scan_global', $scanPayload, 120);

        event(new \App\Events\BiometricScanDetected($scanPayload));

        SendWhatsAppAttendanceAlert::dispatch($student, now()->toISOString(), $status, 'checkin');

        $this->dispatch('alert', message: "Student arrival punch recorded for {$student->full_name}! Modal popup & parent alert dispatched.", type: 'success');
    }

    public function triggerDepartureTest(): void
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $student = Student::where('tenant_id', $tenantId)->with(['schoolClass', 'section'])->first()
            ?? Student::with(['schoolClass', 'section'])->first();

        if (!$student) {
            $this->dispatch('alert', message: 'No student found to simulate departure.', type: 'error');
            return;
        }

        $activeTerm = AcademicTerm::active();
        $termNumber = $activeTerm?->term_number ?? 1;
        $sessionName = AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);
        $systemUserId = auth()->id() ?? User::where('tenant_id', $tenantId)->where('role', 'admin')->value('id') ?? 1;
        $dateStr = now()->format('Y-m-d');
        $punchTime = now();
        $timeStr = $punchTime->format('H:i:s');
        $outDisplay = $punchTime->format('g:i A');

        $sheet = AttendanceSheet::firstOrCreate(
            [
                'tenant_id'  => $student->tenant_id ?? $tenantId,
                'class_id'   => $student->class_id ?? 1,
                'section_id' => $student->section_id,
                'date'       => $dateStr,
                'term'       => $termNumber,
                'session'    => $sessionName,
            ],
            [
                'taken_by'   => $systemUserId,
            ]
        );

        $existingMark = AttendanceMark::where([
            'tenant_id'  => $student->tenant_id ?? $tenantId,
            'sheet_id'   => $sheet->id,
            'student_id' => $student->id,
        ])->first();

        if ($existingMark) {
            $inDisplay = !empty($existingMark->arrived_at) ? Carbon::parse($existingMark->arrived_at)->format('g:i A') : '–';
            $existingMark->update([
                'departed_at' => $timeStr,
                'note'        => "Arrived: {$inDisplay} | Departed: {$outDisplay} [Biometric]",
            ]);
        } else {
            AttendanceMark::create([
                'tenant_id'   => $student->tenant_id ?? $tenantId,
                'sheet_id'    => $sheet->id,
                'student_id'  => $student->id,
                'status'      => 'Present',
                'arrived_at'  => null,
                'departed_at' => $timeStr,
                'note'        => "Departed: {$outDisplay} [Biometric]",
            ]);
        }

        $scanPayload = [
            'scan_id'           => \Illuminate\Support\Str::uuid()->toString(),
            'type'              => 'student',
            'user_id'           => $student->id,
            'name'              => $student->full_name,
            'email'             => $student->admission_number ?? 'ADM-2026-001',
            'photo_url'         => $student->passport_photo_url ?? '/avatars/student_blue.png',
            'role'              => 'student',
            'class_name'        => $student->schoolClass?->name ?? 'Nursery 1 Gold',
            'section_name'      => $student->section?->name ?? 'A',
            'admission_number'  => $student->admission_number ?? 'ADM-2026-001',
            'status'            => 'Departed',
            'time'              => $outDisplay,
            'date'              => $dateStr,
            'scanned_at'        => now()->toISOString(),
            'scanned_timestamp' => now()->timestamp,
            'tenant_id'         => $student->tenant_id ?? $tenantId,
        ];

        Cache::put('latest_biometric_scan_tenant_' . ($student->tenant_id ?? $tenantId), $scanPayload, 120);
        Cache::put('latest_biometric_scan_global', $scanPayload, 120);

        event(new \App\Events\BiometricScanDetected($scanPayload));

        SendWhatsAppAttendanceAlert::dispatch($student, now()->toISOString(), 'Departed', 'checkout');

        $this->dispatch('alert', message: "Student departure recorded for {$student->full_name}! Parent closing alert queued.", type: 'success');
    }

    public function triggerStaffScanTest(): void
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $teacher = User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('role', 'teacher')
            ->where('is_active', true)
            ->first()
            ?? User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereIn('role', ['teacher', 'admin', 'bursar'])
            ->where('is_active', true)
            ->first();

        if (!$teacher) {
            $this->dispatch('alert', message: 'No staff member found to simulate scan.', type: 'error');
            return;
        }

        $activeTerm = AcademicTerm::active();
        $termNumber = $activeTerm?->term_number ?? 1;
        $sessionName = AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);
        $systemUserId = auth()->id() ?? User::where('tenant_id', $tenantId)->where('role', 'admin')->value('id') ?? 1;
        $dateStr = now()->format('Y-m-d');
        $punchTime = now();
        $timeStr = $punchTime->format('H:i:s');
        $shift = $teacher->getShift();
        $calculatedStatus = \App\Support\AttendanceShiftConfig::evaluateStatus($timeStr, $shift, $teacher->tenant_id ?? $tenantId);

        $staffSheet = TeacherAttendanceSheet::firstOrCreate(
            [
                'tenant_id' => $teacher->tenant_id ?? $tenantId,
                'date'      => $dateStr,
                'term'      => $termNumber,
                'session'   => $sessionName,
            ],
            [
                'taken_by'  => $systemUserId,
            ]
        );

        $existingMark = TeacherAttendanceMark::where('sheet_id', $staffSheet->id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if ($existingMark && !empty($existingMark->punch_in_time) && empty($existingMark->punch_out_time)) {
            // Staff Sign-Out
            $finalStatus = ($existingMark->status === 'Present') ? 'Present' : $calculatedStatus;
            $inDisplay = Carbon::parse($existingMark->punch_in_time)->format('g:i A');
            $outDisplay = $punchTime->format('g:i A');

            $existingMark->update([
                'status'         => $finalStatus,
                'punch_out_time' => $timeStr,
                'note'           => "In: {$inDisplay} | Out: {$outDisplay}",
            ]);

            $action = 'Sign-Out';
            $resolvedStatus = 'Departed';
            $displayTime = $outDisplay;
        } else {
            // Staff Sign-In
            $inDisplay = $punchTime->format('g:i A');
            if ($existingMark) {
                $existingMark->update([
                    'status'         => $calculatedStatus,
                    'punch_in_time'  => $timeStr,
                    'punch_out_time' => null,
                    'note'           => "In: {$inDisplay} ({$calculatedStatus})",
                ]);
            } else {
                TeacherAttendanceMark::create([
                    'tenant_id'      => $teacher->tenant_id ?? $tenantId,
                    'sheet_id'       => $staffSheet->id,
                    'teacher_id'     => $teacher->id,
                    'status'         => $calculatedStatus,
                    'punch_in_time'  => $timeStr,
                    'punch_out_time' => null,
                    'note'           => "In: {$inDisplay} ({$calculatedStatus})",
                ]);
            }

            $action = 'Sign-In';
            $resolvedStatus = $calculatedStatus;
            $displayTime = $inDisplay;
        }

        $scanPayload = [
            'scan_id'           => \Illuminate\Support\Str::uuid()->toString(),
            'type'              => 'staff',
            'user_id'           => $teacher->id,
            'name'              => $teacher->name,
            'email'             => $teacher->email,
            'photo_url'         => $teacher->profile_photo_url,
            'role'              => $teacher->role,
            'section'           => $teacher->getShiftLabel(),
            'status'            => $resolvedStatus,
            'time'              => $displayTime,
            'date'              => $dateStr,
            'scanned_at'        => now()->toISOString(),
            'scanned_timestamp' => now()->timestamp,
            'tenant_id'         => $teacher->tenant_id ?? $tenantId,
        ];

        Cache::put('latest_biometric_scan_tenant_' . ($teacher->tenant_id ?? $tenantId), $scanPayload, 120);
        Cache::put('latest_biometric_scan_global', $scanPayload, 120);

        event(new \App\Events\BiometricScanDetected($scanPayload));

        $this->dispatch('alert', message: "Staff {$action} recorded for {$teacher->name}! Gate scan broadcast dispatched.", type: 'success');
    }

    public function render()
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $today = Carbon::today()->format('Y-m-d');

        // Total registered students & staff
        $totalStudents = Student::where('tenant_id', $tenantId)
            ->where(function($q) {
                $q->whereNull('status')->orWhere('status', 'Active')->orWhere('status', 'active');
            })->count();

        $totalStaff = User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereIn('role', ['teacher', 'admin', 'bursar'])
            ->where('is_active', true)
            ->count();

        // Today's student biometric marks
        $studentSheets = AttendanceSheet::where('tenant_id', $tenantId)
            ->whereDate('date', $today)
            ->pluck('id');

        $studentMarks = AttendanceMark::whereIn('sheet_id', $studentSheets)
            ->where(function ($q) {
                $q->whereNotNull('arrived_at')
                  ->orWhereNotNull('departed_at')
                  ->orWhere('note', 'like', '%Arrived%')
                  ->orWhere('note', 'like', '%Departed%')
                  ->orWhere('note', 'like', '%Biometric%');
            })
            ->with(['student.schoolClass', 'student.section'])
            ->latest('updated_at')
            ->take(50)
            ->get();

        // Today's staff biometric marks
        $staffSheets = TeacherAttendanceSheet::where('tenant_id', $tenantId)
            ->whereDate('date', $today)
            ->pluck('id');

        $staffMarks = TeacherAttendanceMark::whereIn('sheet_id', $staffSheets)
            ->where(function ($q) {
                $q->whereNotNull('punch_in_time')
                  ->orWhereNotNull('punch_out_time')
                  ->orWhere('note', 'like', '%In:%')
                  ->orWhere('note', 'like', '%Out:%')
                  ->orWhere('note', 'like', '%Biometric%');
            })
            ->with(['teacher'])
            ->latest('updated_at')
            ->take(50)
            ->get();

        // Transform into unified log records
        $studentLogs = $studentMarks->map(function ($mark) {
            $inTime = $mark->arrived_at ? Carbon::parse($mark->arrived_at)->format('g:i A') : null;
            $outTime = $mark->departed_at ? Carbon::parse($mark->departed_at)->format('g:i A') : null;
            $displayTime = $outTime ?? $inTime ?? $mark->updated_at->format('g:i A');
            $action = $mark->departed_at ? 'Departure' : 'Arrival';
            $status = $mark->departed_at && !$mark->arrived_at ? 'Departed' : ($mark->status ?? 'Present');

            return (object) [
                'id'          => 'student_' . $mark->id,
                'type'        => 'student',
                'type_label'  => 'Student',
                'name'        => $mark->student?->full_name ?? 'Unknown Student',
                'subtext'     => ($mark->student?->schoolClass?->name ?? 'Class') . ($mark->student?->section ? ' (' . $mark->student->section->name . ')' : '') . ' • ' . ($mark->student?->admission_number ?? ''),
                'photo_url'   => $mark->student?->passport_photo_url,
                'avatar_text' => substr($mark->student?->full_name ?? 'S', 0, 1),
                'status'      => $status,
                'action'      => $action,
                'time'        => $displayTime,
                'arrived_at'  => $inTime,
                'departed_at' => $outTime,
                'note'        => $mark->note,
                'timestamp'   => $mark->updated_at?->timestamp ?? 0,
            ];
        });

        $staffLogs = $staffMarks->map(function ($mark) {
            $inTime = $mark->punch_in_time ? Carbon::parse($mark->punch_in_time)->format('g:i A') : null;
            $outTime = $mark->punch_out_time ? Carbon::parse($mark->punch_out_time)->format('g:i A') : null;
            $displayTime = $outTime ?? $inTime ?? $mark->updated_at->format('g:i A');
            $action = $mark->punch_out_time ? 'Sign-Out' : 'Sign-In';
            $status = $mark->punch_out_time && !$mark->punch_in_time ? 'Departed' : ($mark->status ?? 'Present');

            return (object) [
                'id'          => 'staff_' . $mark->id,
                'type'        => 'staff',
                'type_label'  => ucfirst($mark->teacher?->role ?? 'Staff'),
                'name'        => $mark->teacher?->name ?? 'Unknown Staff',
                'subtext'     => ucfirst($mark->teacher?->role ?? 'Staff') . ' • ' . ($mark->teacher?->email ?? ''),
                'photo_url'   => $mark->teacher?->profile_photo_url,
                'avatar_text' => substr($mark->teacher?->name ?? 'T', 0, 1),
                'status'      => $status,
                'action'      => $action,
                'time'        => $displayTime,
                'arrived_at'  => $inTime,
                'departed_at' => $outTime,
                'note'        => $mark->note,
                'timestamp'   => $mark->updated_at?->timestamp ?? 0,
            ];
        });

        $allLogs = $studentLogs->concat($staffLogs)->sortByDesc('timestamp')->values();

        $filteredLogs = match ($this->punchTypeFilter) {
            'students' => $studentLogs->sortByDesc('timestamp')->values(),
            'staff'    => $staffLogs->sortByDesc('timestamp')->values(),
            default    => $allLogs,
        };

        $totalPunchesToday = $allLogs->count();

        return view('livewire.biometrics.index', [
            'totalStudents'     => $totalStudents,
            'totalStaff'        => $totalStaff,
            'totalPunchesToday' => $totalPunchesToday,
            'studentMarks'      => $studentMarks,
            'staffMarks'        => $staffMarks,
            'terminals'         => $this->terminals,
            'punchLogs'         => $filteredLogs,
            'totalStudentLogs'  => $studentLogs->count(),
            'totalStaffLogs'    => $staffLogs->count(),
            'totalAllLogs'      => $allLogs->count(),
        ]);
    }
}
