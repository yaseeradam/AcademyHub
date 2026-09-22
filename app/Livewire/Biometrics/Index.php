<?php

namespace App\Livewire\Biometrics;

use App\Jobs\SendWhatsAppAttendanceAlert;
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
                'name'     => 'K40 Terminal 1 (Main Entrance)',
                'ip'       => '192.168.0.201',
                'port'     => 4370,
                'location' => 'Main Gate (Gate 1)',
                'device'   => 'ZKTeco K40 Fingerprint & RFID',
            ],
            [
                'id'       => 2,
                'name'     => 'K40 Terminal 2 (Western Section / Gate 2)',
                'ip'       => '192.168.0.202',
                'port'     => 4370,
                'location' => 'Mosque / Staff Gate (Gate 2)',
                'device'   => 'ZKTeco K40 Standard',
            ],
        ];

        $this->terminals = Cache::get("tenant_{$tenantId}_k40_terminals", $defaultTerminals);
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

    public function triggerTestPopup(): void
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $student = Student::where('tenant_id', $tenantId)->first() ?? Student::first();

        $scanPayload = [
            'scan_id'           => \Illuminate\Support\Str::uuid()->toString(),
            'type'              => 'student',
            'user_id'           => $student?->id ?? 1,
            'name'              => $student?->full_name ?? 'Musa Lawal',
            'email'             => $student?->admission_number ?? 'ADM-2026-001',
            'photo_url'         => $student?->passport_photo_url ?? '/avatars/student_blue.png',
            'role'              => 'student',
            'class_name'        => $student?->schoolClass?->name ?? 'Nursery 1 Gold',
            'section_name'      => $student?->section?->name ?? 'A',
            'admission_number'  => $student?->admission_number ?? 'ADM-2026-001',
            'status'            => 'Present',
            'time'              => now()->format('g:i A'),
            'date'              => now()->format('Y-m-d'),
            'scanned_at'        => now()->toISOString(),
            'scanned_timestamp' => now()->timestamp,
            'tenant_id'         => $tenantId,
        ];

        Cache::put('latest_biometric_scan_tenant_' . $tenantId, $scanPayload, 120);
        Cache::put('latest_biometric_scan_global', $scanPayload, 120);

        event(new \App\Events\BiometricScanDetected($scanPayload));

        if ($student) {
            SendWhatsAppAttendanceAlert::dispatch($student, now()->toISOString(), 'Present', 'checkin');
        }

        $this->dispatch('alert', message: 'Biometric scan broadcast dispatched! Modal popup fired & parent alert queued.', type: 'success');
    }

    public function triggerDepartureTest(): void
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $student = Student::where('tenant_id', $tenantId)->first() ?? Student::first();

        $scanPayload = [
            'scan_id'           => \Illuminate\Support\Str::uuid()->toString(),
            'type'              => 'student',
            'user_id'           => $student?->id ?? 1,
            'name'              => $student?->full_name ?? 'Musa Lawal',
            'email'             => $student?->admission_number ?? 'ADM-2026-001',
            'photo_url'         => $student?->passport_photo_url ?? '/avatars/student_blue.png',
            'role'              => 'student',
            'class_name'        => $student?->schoolClass?->name ?? 'Nursery 1 Gold',
            'section_name'      => $student?->section?->name ?? 'A',
            'admission_number'  => $student?->admission_number ?? 'ADM-2026-001',
            'status'            => 'Departed',
            'time'              => now()->format('g:i A'),
            'date'              => now()->format('Y-m-d'),
            'scanned_at'        => now()->toISOString(),
            'scanned_timestamp' => now()->timestamp,
            'tenant_id'         => $tenantId,
        ];

        Cache::put('latest_biometric_scan_tenant_' . $tenantId, $scanPayload, 120);
        Cache::put('latest_biometric_scan_global', $scanPayload, 120);

        event(new \App\Events\BiometricScanDetected($scanPayload));

        if ($student) {
            SendWhatsAppAttendanceAlert::dispatch($student, now()->toISOString(), 'Departed', 'checkout');
        }

        $this->dispatch('alert', message: 'Departure scan simulated! Parent closing alert queued.', type: 'success');
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
            ->whereNotNull('note')
            ->where('note', 'like', '%Biometric%')
            ->with(['student.schoolClass', 'student.section'])
            ->latest('updated_at')
            ->take(20)
            ->get();

        // Today's staff biometric marks
        $staffSheets = TeacherAttendanceSheet::where('tenant_id', $tenantId)
            ->whereDate('date', $today)
            ->pluck('id');

        $staffMarks = TeacherAttendanceMark::whereIn('sheet_id', $staffSheets)
            ->whereNotNull('note')
            ->where(function ($q) {
                $q->where('note', 'like', '%Biometric%')
                  ->orWhere('note', 'like', '%In:%')
                  ->orWhere('note', 'like', '%Out:%');
            })
            ->with(['teacher'])
            ->latest('updated_at')
            ->take(20)
            ->get();

        $totalPunchesToday = $studentMarks->count() + $staffMarks->count();

        return view('livewire.biometrics.index', [
            'totalStudents'     => $totalStudents,
            'totalStaff'        => $totalStaff,
            'totalPunchesToday' => $totalPunchesToday,
            'studentMarks'      => $studentMarks,
            'staffMarks'        => $staffMarks,
            'terminals'         => $this->terminals,
        ]);
    }
}
