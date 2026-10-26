<?php

namespace App\Support;

use App\Events\BiometricScanDetected;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\Student;
use App\Models\TeacherAttendanceMark;
use App\Models\TeacherAttendanceSheet;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BiometricSyncService
{
    /**
     * Process a single biometric punch for either a Student or Staff member.
     * Returns array with result details or null if skipped.
     */
    public static function processPunch(string|int $userId, Carbon $punchTime, ?int $tenantId = null): ?array
    {
        $userId = trim((string) $userId);
        if (empty($userId)) {
            return null;
        }

        $tenantId = $tenantId ?: (app()->bound('currentTenant') && app('currentTenant') ? app('currentTenant')->id : 1);

        $dateStr = $punchTime->format('Y-m-d');
        $timeStr = $punchTime->format('H:i:s');

        $activeTerm = AcademicTerm::active();
        $termNumber = $activeTerm?->term_number ?? 1;
        $sessionName = AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        $endTime = config('academyhub.attendance_end_time', '18:00:00');
        $lateThreshold = config('academyhub.late_threshold_time', '08:15:00');

        $systemUserId = Cache::remember('zkteco_system_user_id', 3600, function () use ($tenantId) {
            return User::where('role', 'admin')->where('tenant_id', $tenantId)->value('id')
                ?? User::where('role', 'admin')->value('id')
                ?? 1;
        });

        // ── 1. Check if punch belongs to a Teacher/Staff ──────────────────
        // Priority 1: Match by hardware K40 UID (custom_fields->k40_uid)
        $teacher = User::withoutGlobalScopes()
            ->whereIn('role', ['teacher', 'admin', 'bursar'])
            ->where('is_active', true)
            ->where(function ($q) use ($userId) {
                $q->where('custom_fields->k40_uid', (int) $userId)
                  ->orWhere('custom_fields->k40_uid', (string) $userId);
            })
            ->orderByRaw("CASE WHEN role = 'teacher' THEN 0 ELSE 1 END")
            ->first();

        // Priority 2: Staff database ID or email prefix (only if no student owns this k40_uid)
        if (!$teacher) {
            $hasStudentUid = Student::where('k40_uid', (int) $userId)->exists();
            if (!$hasStudentUid) {
                $teacher = User::withoutGlobalScopes()
                    ->whereIn('role', ['teacher', 'admin', 'bursar'])
                    ->where('is_active', true)
                    ->where(function ($q) use ($userId) {
                        $q->where('id', $userId)
                          ->orWhere('email', 'like', $userId . '@%');
                    })
                    ->first();
            }
        }

        if ($teacher) {
            $staffTenantId = $teacher->tenant_id ?? $tenantId;
            $shift = method_exists($teacher, 'getShift') ? $teacher->getShift() : 'Western';
            $calculatedStatus = AttendanceShiftConfig::evaluateStatus($timeStr, $shift, $staffTenantId);

            $staffSheet = TeacherAttendanceSheet::withoutGlobalScopes()->firstOrCreate(
                [
                    'date'    => $dateStr,
                    'term'    => $termNumber,
                    'session' => $sessionName,
                ],
                [
                    'tenant_id' => $staffTenantId,
                    'taken_by'  => $systemUserId,
                ]
            );

            $existingMark = TeacherAttendanceMark::withoutGlobalScopes()
                ->where('sheet_id', $staffSheet->id)
                ->where('teacher_id', $teacher->id)
                ->first();

            $isDeparture = false;

            if ($existingMark) {
                $isDeparture = true;
                // Departure / sign-out: never downgrade Present to Late
                $finalStatus = ($existingMark->status === 'Present') ? 'Present' : $calculatedStatus;
                $inDisplay = $existingMark->punch_in_time
                    ? Carbon::parse($existingMark->punch_in_time)->format('g:i A')
                    : '–';
                $outDisplay = $punchTime->format('g:i A');

                $existingMark->update([
                    'status'         => $finalStatus,
                    'punch_out_time' => $timeStr,
                    'note'           => "In: {$inDisplay} | Out: {$outDisplay}",
                ]);
            } else {
                $finalStatus = $calculatedStatus;
                TeacherAttendanceMark::create([
                    'tenant_id'      => $staffTenantId,
                    'sheet_id'       => $staffSheet->id,
                    'teacher_id'     => $teacher->id,
                    'status'         => $finalStatus,
                    'punch_in_time'  => $timeStr,
                    'punch_out_time' => null,
                    'note'           => 'In: ' . $punchTime->format('g:i A') . " ({$finalStatus})",
                ]);
            }

            // Real-time broadcast
            $scanPayload = [
                'scan_id'           => Str::uuid()->toString(),
                'type'              => 'staff',
                'user_id'           => $teacher->id,
                'name'              => $teacher->name,
                'email'             => $teacher->email,
                'photo_url'         => $teacher->profile_photo_url,
                'role'              => $teacher->role,
                'section'           => method_exists($teacher, 'getShiftLabel') ? $teacher->getShiftLabel() : $shift,
                'status'            => $finalStatus,
                'time'              => $punchTime->format('g:i A'),
                'date'              => $dateStr,
                'scanned_at'        => now()->toISOString(),
                'scanned_timestamp' => now()->timestamp,
                'tenant_id'         => $staffTenantId,
            ];

            Cache::put('latest_biometric_scan_tenant_' . $staffTenantId, $scanPayload, 120);
            Cache::put('latest_biometric_scan_global', $scanPayload, 120);

            try {
                event(new BiometricScanDetected($scanPayload));
            } catch (\Throwable $e) {}

            return [
                'type'   => 'staff',
                'name'   => $teacher->name,
                'status' => $finalStatus,
                'action' => $isDeparture ? 'checkout' : 'checkin',
                'time'   => $punchTime->format('g:i A'),
            ];
        }

        // ── 2. Check if punch belongs to a Student ────────────────────────
        $student = Student::with(['schoolClass', 'section'])
            ->where(function ($q) use ($userId) {
                $q->where('k40_uid', (int) $userId)
                  ->orWhere('admission_number', $userId);
                if (is_numeric($userId) && (int) $userId >= 200) {
                    $q->orWhere('id', (int) $userId);
                }
            })
            ->first();

        if (!$student) {
            return null;
        }

        $studentTenantId = $student->tenant_id ?? $tenantId;
        $studentShift = method_exists($student, 'getShift') ? $student->getShift() : 'Western';
        $studentStatus = AttendanceShiftConfig::evaluateStatus($timeStr, $studentShift, $studentTenantId);

        $sheet = AttendanceSheet::withoutGlobalScopes()->firstOrCreate(
            [
                'class_id'   => $student->class_id,
                'section_id' => $student->section_id,
                'date'       => $dateStr,
                'term'       => $termNumber,
                'session'    => $sessionName,
            ],
            [
                'tenant_id' => $studentTenantId,
                'taken_by'  => $systemUserId,
            ]
        );

        $existingStudentMark = AttendanceMark::withoutGlobalScopes()->where([
            'sheet_id'   => $sheet->id,
            'student_id' => $student->id,
        ])->first();

        $isDeparture = false;

        if ($existingStudentMark && !empty($existingStudentMark->arrived_at)) {
            // Second scan = departure
            $isDeparture = true;
            $finalStatus = ($existingStudentMark->status === 'Present') ? 'Present' : $studentStatus;
            $inDisplay = Carbon::parse($existingStudentMark->arrived_at)->format('g:i A');
            $outDisplay = $punchTime->format('g:i A');

            $existingStudentMark->update([
                'status'      => $finalStatus,
                'departed_at' => $timeStr,
                'note'        => "Arrived: {$inDisplay} | Departed: {$outDisplay} [Biometric]",
            ]);
            $studentStatus = $finalStatus;
        } else {
            // First scan = arrival
            AttendanceMark::updateOrCreate(
                [
                    'sheet_id'   => $sheet->id,
                    'student_id' => $student->id,
                ],
                [
                    'tenant_id'   => $studentTenantId,
                    'status'      => $studentStatus,
                    'arrived_at'  => $timeStr,
                    'departed_at' => null,
                    'note'        => 'Arrived: ' . $punchTime->format('g:i A') . " ({$studentStatus}) [Biometric]",
                ]
            );
        }

        // WhatsApp notification to parent
        if (!empty($student->guardian_phone)) {
            $actionType = $isDeparture ? 'checkout' : 'checkin';
            $alertCacheKey = "zk_wa_alert_{$actionType}_{$student->id}_{$dateStr}";
            if (!Cache::has($alertCacheKey)) {
                Cache::put($alertCacheKey, true, now()->endOfDay());
                try {
                    \App\Jobs\SendWhatsAppAttendanceAlert::dispatch(
                        $student,
                        $punchTime->toISOString(),
                        $studentStatus,
                        $actionType
                    );
                } catch (\Throwable $e) {}
            }
        }

        // Real-time broadcast
        $photoUrl = null;
        if (!empty($student->passport_photo)) {
            $photoUrl = asset('uploads/passports/' . $student->passport_photo);
        }

        $scanPayload = [
            'scan_id'           => Str::uuid()->toString(),
            'type'              => 'student',
            'user_id'           => $student->id,
            'name'              => $student->full_name,
            'admission_number'  => $student->admission_number,
            'photo_url'         => $photoUrl,
            'class_name'        => $student->schoolClass?->name ?? 'Unassigned',
            'section_name'      => $student->section?->name ?? 'A',
            'section'           => method_exists($student, 'getShiftLabel') ? $student->getShiftLabel() : $studentShift,
            'status'            => $studentStatus,
            'time'              => $punchTime->format('g:i A'),
            'date'              => $dateStr,
            'is_departure'      => $isDeparture,
            'scanned_at'        => now()->toISOString(),
            'scanned_timestamp' => now()->timestamp,
            'tenant_id'         => $studentTenantId,
        ];

        Cache::put('latest_biometric_scan_tenant_' . $studentTenantId, $scanPayload, 120);
        Cache::put('latest_biometric_scan_global', $scanPayload, 120);

        try {
            event(new BiometricScanDetected($scanPayload));
        } catch (\Throwable $e) {}

        return [
            'type'   => 'student',
            'name'   => $student->full_name,
            'status' => $studentStatus,
            'action' => $isDeparture ? 'checkout' : 'checkin',
            'time'   => $punchTime->format('g:i A'),
        ];
    }

    /**
     * Process multi-line raw log text (from USB attlog.dat, .txt, or ADMS push).
     */
    public static function processRawLogText(string $rawContent, ?int $tenantId = null): array
    {
        $lines = explode("\n", str_replace("\r", "", trim($rawContent)));
        $processed = 0;
        $studentCount = 0;
        $staffCount = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, 'ATTLOG')) {
                continue;
            }

            // Standard ZKTeco format: USERID \t TIMESTAMP \t STATUS ...
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 2) {
                continue;
            }

            $userId = trim($parts[0]);
            $datePart = $parts[1] ?? '';
            $timePart = $parts[2] ?? '';

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datePart)) {
                $skipped++;
                continue;
            }

            try {
                $punchTime = Carbon::parse("{$datePart} {$timePart}");
                $res = self::processPunch($userId, $punchTime, $tenantId);
                if ($res) {
                    $processed++;
                    if ($res['type'] === 'student') {
                        $studentCount++;
                    } else {
                        $staffCount++;
                    }
                } else {
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $skipped++;
            }
        }

        return [
            'total_lines' => count($lines),
            'processed'   => $processed,
            'students'    => $studentCount,
            'staff'       => $staffCount,
            'skipped'     => $skipped,
        ];
    }

    /**
     * Connect to a ZKTeco terminal over LAN and pull punch logs.
     */
    public static function syncFromTerminal(string $ip = '192.168.1.201', int $port = 4370, int $days = 0, ?int $tenantId = null): array
    {
        // 1. Check if class exists
        if (!class_exists('\Rats\Zkteco\Lib\Zkteco')) {
            return [
                'success' => false,
                'message' => "ZKTeco socket library not loaded. Use ADMS push or USB file upload.",
                'count'   => 0,
            ];
        }

        try {
            $zk = new \Rats\Zkteco\Lib\Zkteco($ip, $port);
            if (!$zk->connect()) {
                return [
                    'success' => false,
                    'message' => "Could not connect to terminal at {$ip}:{$port}. Ensure terminal is powered on and reachable.",
                    'count'   => 0,
                ];
            }

            try {
                $zk->setTime(date('Y-m-d H:i:s'));
            } catch (\Throwable $e) {}

            $logs = $zk->getAttendance();
            $zk->disconnect();

            if (empty($logs)) {
                return [
                    'success' => true,
                    'message' => "Connected successfully. No new attendance logs found on device.",
                    'count'   => 0,
                ];
            }

            $cutoff = Carbon::today()->subDays($days)->startOfDay();
            $synced = 0;

            foreach ($logs as $log) {
                $uid = trim((string) ($log['id'] ?? $log['user_id'] ?? ''));
                if (empty($uid)) continue;

                $punchTime = Carbon::parse($log['timestamp']);
                if ($punchTime->lt($cutoff)) {
                    continue;
                }

                $res = self::processPunch($uid, $punchTime, $tenantId);
                if ($res) {
                    $synced++;
                }
            }

            return [
                'success' => true,
                'message' => "Successfully synced {$synced} attendance logs from terminal ({$ip})!",
                'count'   => $synced,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => "Terminal connection error: " . $e->getMessage(),
                'count'   => 0,
            ];
        }
    }
}
