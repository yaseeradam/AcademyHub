<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Dispatch an instant WhatsApp message through our bot webhook.
     *
     * @param string $phone
     * @param string $message
     * @param string|null $mediaUrl
     * @param string|null $filename
     * @param string|null $caption
     * @return bool
     */
    public static function sendMessage(string $phone, string $message, ?string $mediaUrl = null, ?string $filename = null, ?string $caption = null): bool
    {
        if (app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
            if ($tenant) {
                $tenantActive = ($tenant->status === 'active') && (!$tenant->expires_at || !$tenant->expires_at->isPast());
                $botActive = $tenant->activeMarketplaceComponents()->where('slug', 'whatsapp-bot')->exists();

                if (!$tenantActive || !$botActive) {
                    Log::warning("WhatsAppService: Blocked sending message to {$phone} because tenant '{$tenant->name}' (ID: {$tenant->id}) has active status = " . ($tenantActive ? 'yes' : 'no') . " and bot active = " . ($botActive ? 'yes' : 'no'));
                    return false;
                }
            }
        }

        try {
            $token = config('services.whatsapp.token') ?: env('WHATSAPP_TOKEN');
            $phoneNumberId = config('services.whatsapp.phone_number_id') ?: env('WHATSAPP_PHONE_NUMBER_ID');

            if (empty($token) || empty($phoneNumberId)) {
                Log::warning('WhatsAppService (Meta Cloud API): Token or Phone Number ID not configured.');
                return false;
            }

            $url = "https://graph.facebook.com/v19.0/{$phoneNumberId}/messages";
            $toPhone = preg_replace('/\D/', '', $phone);

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $toPhone,
            ];

            if ($mediaUrl) {
                $payload['type'] = 'document';
                $payload['document'] = [
                    'link' => $mediaUrl,
                    'filename' => $filename ?: 'document.pdf',
                    'caption' => $message ?: $caption
                ];
            } else {
                $payload['type'] = 'text';
                $payload['text'] = [
                    'preview_url' => false,
                    'body' => $message
                ];
            }

            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])
                ->post($url, $payload);

            if ($response->failed()) {
                Log::error('WhatsAppService (Meta Cloud API): Failed to send message', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('WhatsAppService (Meta Cloud API): Exception during message send', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
