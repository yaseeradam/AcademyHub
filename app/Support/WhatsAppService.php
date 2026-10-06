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
     * @param array|null $buttons
     * @return bool
     */
    /**
     * Resolve the Evolution API instance name for a specific tenant.
     * Tenant 1 uses 'academyhub' to preserve existing connected sessions.
     * Other tenants use 'school_{id}' or a custom name configured in tenant settings.
     */
    public static function getInstanceName(?\App\Models\Tenant $tenant = null): string
    {
        $tenant = $tenant ?? (app()->bound('currentTenant') ? app('currentTenant') : null);

        if (!$tenant && auth()->check() && auth()->user()->tenant_id) {
            $tenant = auth()->user()->tenant;
        }

        if ($tenant) {
            $custom = config('academyhub.whatsapp_instance');
            if (!empty($custom)) {
                return preg_replace('/[^a-zA-Z0-9_-]/', '', $custom);
            }

            // Tenant 1 preserves 'academyhub' to keep existing active connections
            if ((int) $tenant->id === 1) {
                return config('services.whatsapp.evolution_instance', 'academyhub');
            }

            return 'school_' . $tenant->id;
        }

        return config('services.whatsapp.evolution_instance', 'academyhub');
    }

    public static function sendMessage(string $phone, string $message, ?string $mediaUrl = null, ?string $filename = null, ?string $caption = null, ?array $buttons = null, ?\App\Models\Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? (app()->bound('currentTenant') ? app('currentTenant') : null);
        if ($tenant) {
            $tenantActive = ($tenant->status === 'active') && (!$tenant->expires_at || !$tenant->expires_at->isPast());
            $botActive = $tenant->activeMarketplaceComponents()->where('slug', 'whatsapp-bot')->exists();

            if (!$tenantActive || !$botActive) {
                Log::warning("WhatsAppService: Blocked sending message to {$phone} because tenant '{$tenant->name}' (ID: {$tenant->id}) has active status = " . ($tenantActive ? 'yes' : 'no') . " and bot active = " . ($botActive ? 'yes' : 'no'));
                return false;
            }
        }

        $provider = config('services.whatsapp.provider', 'evolution');

        if ($provider === 'evolution') {
            return self::sendEvolutionMessage($phone, $message, $mediaUrl, $filename, $caption, $buttons, $tenant);
        }

        return self::sendMetaMessage($phone, $message, $mediaUrl, $filename, $caption, $buttons);
    }

    /**
     * Send via Free Multi-Device Gateway (Evolution API / Baileys).
     */
    public static function sendEvolutionMessage(string $phone, string $message, ?string $mediaUrl = null, ?string $filename = null, ?string $caption = null, ?array $buttons = null, ?\App\Models\Tenant $tenant = null): bool
    {
        try {
            $baseUrl  = rtrim(config('services.whatsapp.evolution_url', 'http://whatsapp:8080'), '/');
            $apiKey   = config('services.whatsapp.evolution_api_key', 'academyhub-wa-secret-key');
            $instance = self::getInstanceName($tenant);

            $toPhone = preg_replace('/\D/', '', $phone);

            // Guard: reject empty phone numbers
            if (empty($toPhone)) {
                Log::warning('WhatsAppService (Evolution API): Empty phone number after sanitization', ['original' => $phone]);
                return false;
            }

            // Format message with buttons as text bullets if provided
            $textToSend = $message;
            if ($buttons && is_array($buttons)) {
                $buttonLines = [];
                foreach ($buttons as $btn) {
                    $title = $btn['title'] ?? $btn['id'] ?? '';
                    if (!empty($title)) {
                        $buttonLines[] = "• Reply *{$title}*";
                    }
                }
                if (!empty($buttonLines)) {
                    $textToSend .= "\n\n" . implode("\n", $buttonLines);
                }
            }

            if (!empty($mediaUrl)) {
                // Auto-detect media type from URL/filename extension
                $ext = strtolower(pathinfo($mediaUrl, PATHINFO_EXTENSION));
                if (empty($ext) && $filename) {
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                }
                $mediaType = match (true) {
                    in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) => 'image',
                    in_array($ext, ['mp4', 'avi', 'mov', 'mkv', '3gp'])  => 'video',
                    in_array($ext, ['mp3', 'ogg', 'wav', 'aac', 'opus']) => 'audio',
                    default => 'document',
                };

                $url = "{$baseUrl}/message/sendMedia/{$instance}";
                $payload = [
                    'number'    => $toPhone,
                    'media'     => $mediaUrl,
                    'mediatype' => $mediaType,
                    'caption'   => $textToSend ?: ($caption ?: ''),
                    'fileName'  => $filename ?: 'document.pdf',
                ];
            } else {
                // Guard: reject empty text messages when no media
                if (empty(trim($textToSend))) {
                    Log::warning('WhatsAppService (Evolution API): Empty message text and no media', ['phone' => $toPhone]);
                    return false;
                }

                $url = "{$baseUrl}/message/sendText/{$instance}";
                $payload = [
                    'number' => $toPhone,
                    'text'   => $textToSend,
                ];
            }

            $response = Http::withOptions(['verify' => false, 'timeout' => 15])
                ->withHeaders([
                    'apikey'       => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            if ($response->failed()) {
                Log::error('WhatsAppService (Evolution API): Failed to send message', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('WhatsAppService (Evolution API): Exception during message send', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send via Official Meta Cloud API (legacy/paid).
     */
    public static function sendMetaMessage(string $phone, string $message, ?string $mediaUrl = null, ?string $filename = null, ?string $caption = null, ?array $buttons = null): bool
    {
        try {
            $token = config('services.whatsapp.token');
            $phoneNumberId = config('services.whatsapp.phone_number_id');

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
                'type' => 'text',
            ];

            if ($buttons) {
                $payload['type'] = 'interactive';
                $payload['interactive'] = [
                    'type' => 'button',
                    'body' => [
                        'text' => $message
                    ],
                    'action' => [
                        'buttons' => array_map(fn($btn) => [
                            'type' => 'reply',
                            'reply' => [
                                'id' => $btn['id'],
                                'title' => substr($btn['title'], 0, 20)
                            ]
                        ], $buttons)
                    ]
                ];
            } elseif ($mediaUrl) {
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

    /**
     * Check Evolution API connection state.
     * Returns ['connected' => bool, 'state' => string, 'phone' => ?string]
     */
    public static function getEvolutionStatus(?\App\Models\Tenant $tenant = null): array
    {
        try {
            $baseUrl  = rtrim(config('services.whatsapp.evolution_url', 'http://whatsapp:8080'), '/');
            $apiKey   = config('services.whatsapp.evolution_api_key', 'academyhub-wa-secret-key');
            $instance = self::getInstanceName($tenant);

            $response = Http::withOptions(['verify' => false, 'timeout' => 5])
                ->withHeaders(['apikey' => $apiKey])
                ->get("{$baseUrl}/instance/connectionState/{$instance}");

            if ($response->successful()) {
                $data = $response->json();
                $state = $data['instance']['state'] ?? 'close';
                $isConnected = ($state === 'open');
                $phone = null;

                // connectionState in v2 does NOT return ownerJid.
                // Fetch it from fetchInstances when connected.
                if ($isConnected) {
                    try {
                        $infoRes = Http::withOptions(['verify' => false, 'timeout' => 5])
                            ->withHeaders(['apikey' => $apiKey])
                            ->get("{$baseUrl}/instance/fetchInstances", ['instanceName' => $instance]);

                        if ($infoRes->successful()) {
                            $instances = $infoRes->json();
                            // Response is an array of instance objects
                            $inst = is_array($instances) ? ($instances[0] ?? null) : null;
                            $phone = $inst['instance']['owner'] ?? $inst['instance']['ownerJid'] ?? null;
                        }
                    } catch (\Exception $e) {
                        // Non-critical: phone display will show 'Active Instance' fallback
                    }
                }

                return [
                    'connected' => $isConnected,
                    'state'     => $state,
                    'phone'     => $phone,
                ];
            }

            return ['connected' => false, 'state' => 'unreachable', 'phone' => null];
        } catch (\Exception $e) {
            return ['connected' => false, 'state' => 'offline', 'phone' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get or create Evolution API QR Code for pairing.
     */
    public static function getEvolutionQr(?\App\Models\Tenant $tenant = null): ?string
    {
        try {
            $baseUrl  = rtrim(config('services.whatsapp.evolution_url', 'http://whatsapp:8080'), '/');
            $apiKey   = config('services.whatsapp.evolution_api_key', 'academyhub-wa-secret-key');
            $instance = self::getInstanceName($tenant);

            // 1. Try to connect to existing instance to fetch QR
            $response = Http::withOptions(['verify' => false, 'timeout' => 8])
                ->withHeaders(['apikey' => $apiKey])
                ->get("{$baseUrl}/instance/connect/{$instance}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['base64'] ?? $data['qrcode']['base64'] ?? null;
            }

            // 2. If instance doesn't exist, create it first (with webhook config)
            if ($response->status() === 404) {
                $createRes = Http::withOptions(['verify' => false, 'timeout' => 8])
                    ->withHeaders(['apikey' => $apiKey])
                    ->post("{$baseUrl}/instance/create", [
                        'instanceName' => $instance,
                        'token'        => $apiKey,
                        'qrcode'       => true,
                        'integration'  => 'WHATSAPP-BAILEYS',
                        'webhook' => [
                            'url'      => rtrim(config('app.url', 'http://app'), '/') . '/api/whatsapp/webhook',
                            'byEvents' => false,
                            'enabled'  => true,
                            'events'   => ['MESSAGES_UPSERT'],
                        ],
                    ]);

                if ($createRes->successful()) {
                    $createData = $createRes->json();
                    return $createData['base64'] ?? $createData['qrcode']['base64'] ?? null;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('WhatsAppService: Failed to get Evolution QR code', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Disconnect/logout the Evolution API instance.
     */
    public static function logoutEvolution(?\App\Models\Tenant $tenant = null): bool
    {
        try {
            $baseUrl  = rtrim(config('services.whatsapp.evolution_url', 'http://whatsapp:8080'), '/');
            $apiKey   = config('services.whatsapp.evolution_api_key', 'academyhub-wa-secret-key');
            $instance = self::getInstanceName($tenant);

            $res = Http::withOptions(['verify' => false, 'timeout' => 8])
                ->withHeaders(['apikey' => $apiKey])
                ->delete("{$baseUrl}/instance/logout/{$instance}");

            return $res->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
