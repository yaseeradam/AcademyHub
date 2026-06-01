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

        $botUrl = env('WHATSAPP_BOT_WEBHOOK_URL', 'http://localhost:3000/webhook/send');
        $apiKey = config('services.whatsapp.api_key') ?: env('WHATSAPP_API_KEY', 'dev-local-whatsapp-key-change-in-production');

        try {
            $response = Http::withHeaders([
                'X-WhatsApp-Api-Key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(5)
            ->post($botUrl, [
                'phone' => preg_replace('/\D/', '', $phone),
                'message' => $message,
                'mediaUrl' => $mediaUrl,
                'filename' => $filename,
                'caption' => $caption,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('WhatsApp Webhook delivery failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook exception occurred', [
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }
}
