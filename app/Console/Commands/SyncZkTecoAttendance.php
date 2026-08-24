<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\User;
use App\Models\AttendanceSheet;
use App\Models\AttendanceMark;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncZkTecoAttendance extends Command
{
    /**
     * The name and signature of the console command.
     * Usage: php artisan zkteco:sync 192.168.1.201 4370
     */
    protected $signature = 'zkteco:sync {ip=192.168.1.201} {port=4370}';

    protected $description = 'Sync biometric attendance records from ZKTeco K40 hardware device over LAN';

    public function handle()
    {
        $ip = $this->argument('ip');
        $port = (int) $this->argument('port');

        $this->info("Connecting to ZKTeco K40 at {$ip}:{$port}...");

        if (!class_exists('\Rats\Zkteco\Lib\Zkteco')) {
            $this->error("Package 'rats/zkteco' is not installed. Please run: composer require rats/zkteco");
            return 1;
        }

        $zk = new \Rats\Zkteco\Lib\Zkteco($ip, $port);

        if (!$zk->connect()) {
            $this->error("ERROR: Could not connect to ZKTeco device at {$ip}:{$port}. Check Ethernet connection & IP.");
            return 1;
        }

        // Try syncing device clock with server time
        try {
            $zk->setTime(date('Y-m-d H:i:s'));
        } catch (\Throwable $e) {
            // Ignore if prohibited by device firmware
        }

        // Retrieve log array from device
        $attendanceLogs = $zk->getAttendance();
        $zk->disconnect();

        $this->info("Retrieved " . count($attendanceLogs) . " total log entries from device memory.");

        if (empty($attendanceLogs)) {
            $this->info("No logs found on device.");
            return 0;
        }

        $activeTerm = AcademicTerm::active();
        $termNumber = $activeTerm?->term_number ?? 1;
        $sessionName = AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);
        $lateThreshold = config('academyhub.late_threshold_time', '08:15:00');

        // Find a valid system user for 'taken_by'
        $systemUserId = Cache::remember('zkteco_system_user_id', 3600, function () {
            return User::where('role', 'admin')->orderBy('id')->value('id') ?? User::orderBy('id')->value('id');
        });

        $syncedCount = 0;

        foreach ($attendanceLogs as $log) {
            $userId = trim((string) ($log['id'] ?? $log['user_id'] ?? ''));
            if (empty($userId)) continue;

            $punchTime = Carbon::parse($log['timestamp']);

            // Only process today's records
            if (!$punchTime->isToday()) {
                continue;
            }

            // Find matching student by Admission Number or Database ID
            $student = Student::where('admission_number', $userId)
                ->orWhere('id', $userId)
                ->first();

            if (!$student) {
                $this->warn("Skipping User ID {$userId}: No matching student found in database.");
                continue;
            }

            $dateStr = $punchTime->format('Y-m-d');
            $timeStr = $punchTime->format('H:i:s');
            $status  = ($timeStr > $lateThreshold) ? 'Late' : 'Present';

            // 1. Create or retrieve daily attendance sheet for student's class
            $sheet = AttendanceSheet::firstOrCreate(
                [
                    'tenant_id'  => $student->tenant_id,
                    'class_id'   => $student->class_id,
                    'section_id' => $student->section_id,
                    'date'       => $dateStr,
                    'term'       => $termNumber,
                    'session'    => $sessionName,
                ],
                [
                    'taken_by'   => $systemUserId,
                ]
            );

            // 2. Record or update student's attendance mark
            AttendanceMark::updateOrCreate(
                [
                    'tenant_id'  => $student->tenant_id,
                    'sheet_id'   => $sheet->id,
                    'student_id' => $student->id,
                ],
                [
                    'status'     => $status,
                    'note'       => "Biometric scan on K40 at {$punchTime->format('g:i A')}",
                ]
            );

            $syncedCount++;

            // 3. Trigger WhatsApp Notification to Parent (with duplicate prevention)
            if (!empty($student->guardian_phone)) {
                $alertCacheKey = "zk_wa_alert_{$student->id}_{$dateStr}";
                if (!Cache::has($alertCacheKey)) {
                    Cache::put($alertCacheKey, true, now()->endOfDay());
                    $this->sendWhatsAppAttendanceAlert($student, $punchTime, $status);
                }
            }
        }

        $this->info("Successfully synced {$syncedCount} student attendance records into AcademyHub!");
        return 0;
    }

    private function sendWhatsAppAttendanceAlert(Student $student, Carbon $punchTime, string $status)
    {
        $schoolName = config('academyhub.school_name') ?: config('app.name', 'AcademyHub');
        $phone = preg_replace('/\D/', '', $student->guardian_phone);

        if (empty($phone)) return;

        $timeFormatted = $punchTime->format('g:i A');
        $dateFormatted = $punchTime->format('M j, Y');

        $statusEmoji = ($status === 'Late') ? '⏰' : '✅';
        $guardianName = $student->guardian_name ?: 'Parent/Guardian';

        $message = "{$statusEmoji} *ATTENDANCE ALERT - {$schoolName}*\n\n" .
                   "Dear *{$guardianName}*,\n" .
                   "Your child *{$student->full_name}* has arrived at school and checked in via Biometric Attendance.\n\n" .
                   "📅 *Date:* {$dateFormatted}\n" .
                   "🕒 *Time:* {$timeFormatted}\n" .
                   "📌 *Status:* {$status}\n\n" .
                   "_Thank you for choosing {$schoolName}!_";

        try {
            $token = config('services.whatsapp.token');
            $phoneId = config('services.whatsapp.phone_number_id');

            if (!empty($token) && !empty($phoneId)) {
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ])->timeout(3)->connectTimeout(2)->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $phone,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("WhatsApp Biometric Command Alert Failed: " . $e->getMessage());
        }
    }
}
