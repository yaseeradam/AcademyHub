<?php

namespace App\Jobs;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends a WhatsApp attendance alert to a student's guardian.
 * Supports both arrival (check-in) and departure (check-out) alerts.
 * Dispatched from ZkTecoController or Biometrics index.
 */
class SendWhatsAppAttendanceAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public Student $student,
        public string $punchTimeStr,
        public string $status,
        public string $action = 'checkin', // 'checkin' or 'checkout'
    ) {}

    public function handle(): void
    {
        $tenantId = $this->student->tenant_id ?? 1;
        $settings = Cache::get("tenant_{$tenantId}_whatsapp_attendance_settings", [
            'enabled'         => true,
            'notify_checkin'  => true,
            'notify_checkout' => true,
        ]);

        if (!($settings['enabled'] ?? true)) {
            Log::info("WhatsApp alert skipped: disabled for tenant {$tenantId}");
            return;
        }

        if ($this->action === 'checkout' && !($settings['notify_checkout'] ?? true)) {
            Log::info("WhatsApp checkout alert skipped: checkout notifications disabled for tenant {$tenantId}");
            return;
        }

        if ($this->action === 'checkin' && !($settings['notify_checkin'] ?? true)) {
            Log::info("WhatsApp checkin alert skipped: checkin notifications disabled for tenant {$tenantId}");
            return;
        }

        $phone = preg_replace('/\D/', '', $this->student->guardian_phone ?? '');

        if (empty($phone)) {
            return;
        }

        // Normalize Nigerian phone numbers (e.g. 080... to 23480...)
        if (str_starts_with($phone, '0') && strlen($phone) === 11) {
            $phone = '234' . substr($phone, 1);
        }

        $punchTime    = Carbon::parse($this->punchTimeStr);
        $schoolName   = config('academyhub.school_name') ?: config('app.name', 'AcademyHub');
        $guardianName = $this->student->guardian_name ?: 'Parent/Guardian';

        if ($this->action === 'checkout') {
            $message = "👋 *DEPARTURE ALERT - {$schoolName}*\n\n" .
                       "Dear *{$guardianName}*,\n" .
                       "Your child *{$this->student->full_name}* has completed the school day and departed via the school biometric gate.\n\n" .
                       "📅 *Date:* {$punchTime->format('M j, Y')}\n" .
                       "🕒 *Departure Time:* {$punchTime->format('g:i A')}\n\n" .
                       "_We wish your child a safe journey home!_";
        } else {
            $statusEmoji = ($this->status === 'Late') ? '⏰' : '✅';
            $message = "{$statusEmoji} *ATTENDANCE ALERT - {$schoolName}*\n\n" .
                       "Dear *{$guardianName}*,\n" .
                       "Your child *{$this->student->full_name}* has arrived at school and checked in via Biometric Attendance.\n\n" .
                       "📅 *Date:* {$punchTime->format('M j, Y')}\n" .
                       "🕒 *Arrival Time:* {$punchTime->format('g:i A')}\n" .
                       "📌 *Status:* {$this->status}\n\n" .
                       "_Thank you for choosing {$schoolName}!_";
        }

        $token   = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_number_id');

        if (empty($token) || empty($phoneId)) {
            Log::info("WhatsApp Attendance Alert [SIMULATION] to {$phone} for {$this->student->full_name}:\n{$message}");
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ])->timeout(10)->connectTimeout(5)->post(
                "https://graph.facebook.com/v19.0/{$phoneId}/messages",
                [
                    'messaging_product' => 'whatsapp',
                    'to'                => $phone,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ]
            );

            if ($response->successful()) {
                Log::info("WhatsApp alert sent to guardian of student {$this->student->id} ({$phone})");
            } else {
                Log::warning("WhatsApp alert failed for student {$this->student->id}: {$response->status()} - {$response->body()}");
                $this->release(30);
            }
        } catch (\Throwable $e) {
            Log::error("WhatsApp alert exception for student {$this->student->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Send direct message for instant testing.
     */
    public static function sendDirectMessage(string $phone, string $message): array
    {
        $cleanPhone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($cleanPhone, '0') && strlen($cleanPhone) === 11) {
            $cleanPhone = '234' . substr($cleanPhone, 1);
        }

        $token   = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_number_id');

        if (empty($token) || empty($phoneId)) {
            Log::info("WhatsApp Test Direct Message [SIMULATION] to {$cleanPhone}:\n{$message}");
            return [
                'status'  => 'simulated',
                'message' => "WhatsApp API credentials are in simulation mode. Alert logged successfully to {$cleanPhone}.",
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ])->timeout(10)->connectTimeout(5)->post(
                "https://graph.facebook.com/v19.0/{$phoneId}/messages",
                [
                    'messaging_product' => 'whatsapp',
                    'to'                => $cleanPhone,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ]
            );

            if ($response->successful()) {
                return [
                    'status'  => 'success',
                    'message' => "WhatsApp message successfully delivered to {$cleanPhone}!",
                ];
            }

            return [
                'status'  => 'error',
                'message' => "Meta API error (HTTP {$response->status()}): " . $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'status'  => 'error',
                'message' => "Connection exception: " . $e->getMessage(),
            ];
        }
    }
}
