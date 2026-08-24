<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZkTecoController extends Controller
{
    /**
     * ADMS Handshake / Heartbeat (GET /api/iclock/cdata)
     * Device pings server to initialize push connection.
     */
    public function handshake(Request $request)
    {
        $sn = $request->input('SN', $request->query('sn', 'UNKNOWN'));

        Log::info("ZKTeco K40 ADMS Handshake received from SN: {$sn}");

        // Return device configuration parameters expected by ZKTeco ADMS firmware
        $response = "GET OPTION FROM: {$sn}\r\n" .
                    "Stamp=9999\r\n" .
                    "OpStamp=9999\r\n" .
                    "ErrorDelay=60\r\n" .
                    "Delay=10\r\n" .
                    "TransTimes=00:00;23:59\r\n" .
                    "TransInterval=1\r\n" .
                    "TransFlag=1111111111\r\n" .
                    "Realtime=1\r\n" .
                    "Encrypt=0";

        return response($response, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Unified ADMS handler for root-level /iclock/cdata route.
     * ZKTeco firmware sends both GET (handshake) and POST (punch data) to the same URL.
     */
    public function handleAdms(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->receivePunch($request);
        }
        return $this->handshake($request);
    }


    /**
     * ADMS Real-time Punch Log Receiver (POST /api/iclock/cdata)
     * Device POSTs data immediately when a student scans finger or card.
     *
     * Multi-Tenancy: If the global TenantDiscovery middleware has already resolved
     * a tenant (via domain or X-Tenant-Slug header), queries are automatically scoped.
     * For single-tenant deployments where no tenant resolves (e.g. device hits a
     * bare local IP), BelongsToTenant scope is transparently skipped and all records
     * are visible — which is correct for single-tenant.
     */
    public function receivePunch(Request $request)
    {
        $content = $request->getContent();

        if (empty($content)) {
            $content = $request->input('data', '');
        }

        if (empty($content)) {
            return response("OK", 200)->header('Content-Type', 'text/plain');
        }

        Log::info("ZKTeco K40 ADMS Raw Punch Data Received:\n" . $content);

        $lines = explode("\n", str_replace("\r", "", trim($content)));

        $activeTerm = AcademicTerm::active();
        $termNumber = $activeTerm?->term_number ?? 1;
        $sessionName = AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);
        $lateThreshold = config('academyhub.late_threshold_time', '08:15:00');

        // Find a valid system user for 'taken_by' (first admin user, or null-safe)
        $systemUserId = Cache::remember('zkteco_system_user_id', 3600, function () {
            return User::where('role', 'admin')->orderBy('id')->value('id') ?? User::orderBy('id')->value('id');
        });

        $processedCount = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, 'ATTLOG')) continue;

            // Standard ZKTeco ADMS format: USERID \t TIMESTAMP \t STATUS \t VERIFYTYPE ...
            // Example: 1001 \t 2026-08-20 07:45:12 \t 0 \t 1
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 2) continue;

            $userId = trim($parts[0]);

            // Reconstruct timestamp from space-separated parts
            $datePart = $parts[1] ?? '';
            $timePart = $parts[2] ?? '';

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datePart)) {
                continue;
            }

            try {
                $punchTime = Carbon::parse("{$datePart} {$timePart}");
            } catch (\Throwable $e) {
                continue;
            }

            // Find matching student by Admission Number or Database ID
            $student = Student::where('admission_number', $userId)
                ->orWhere('id', $userId)
                ->first();

            if (!$student) {
                Log::warning("ZKTeco ADMS Sync: No student found for User ID/Admission Number: {$userId}");
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

            // 2. Record or update Attendance Mark
            AttendanceMark::updateOrCreate(
                [
                    'tenant_id'  => $student->tenant_id,
                    'sheet_id'   => $sheet->id,
                    'student_id' => $student->id,
                ],
                [
                    'status'     => $status,
                    'note'       => 'Biometric scan at ' . $punchTime->format('g:i A'),
                ]
            );

            $processedCount++;

            // 3. Send WhatsApp notification — with duplicate prevention
            //    Cache key prevents re-sending for the same student on the same day
            if (!empty($student->guardian_phone)) {
                $alertCacheKey = "zk_wa_alert_{$student->id}_{$dateStr}";
                if (!Cache::has($alertCacheKey)) {
                    Cache::put($alertCacheKey, true, now()->endOfDay());
                    $this->sendWhatsAppAttendanceAlert($student, $punchTime, $status);
                }
            }
        }

        Log::info("ZKTeco ADMS Sync: Successfully processed {$processedCount} attendance records.");

        return response("OK", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Send WhatsApp Message to Guardian (non-blocking, short timeout).
     */
    private function sendWhatsAppAttendanceAlert(Student $student, Carbon $punchTime, string $status): void
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
                Log::info("WhatsApp Biometric Alert sent to guardian of student {$student->id} ({$phone})");
            }
        } catch (\Throwable $e) {
            // Non-blocking: log the error but don't disrupt attendance recording
            Log::error("WhatsApp Biometric Alert Failed for Student {$student->id}: " . $e->getMessage());
        }
    }
}
