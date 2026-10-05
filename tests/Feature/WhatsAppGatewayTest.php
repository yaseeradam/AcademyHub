<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.whatsapp.provider' => 'evolution',
            'services.whatsapp.evolution_url' => 'http://whatsapp:8080',
            'services.whatsapp.evolution_api_key' => 'test-api-key',
            'services.whatsapp.evolution_instance' => 'academyhub-test',
        ]);
    }

    public function test_send_evolution_message_formats_and_dispatches_http_request(): void
    {
        Http::fake([
            'http://whatsapp:8080/message/sendText/academyhub-test' => Http::response([
                'key' => ['id' => 'MSG12345'],
                'message' => ['conversation' => 'Hello World'],
            ], 200),
        ]);

        $result = WhatsAppService::sendEvolutionMessage('2348012345678', 'Hello World');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://whatsapp:8080/message/sendText/academyhub-test'
                && $request->hasHeader('apikey', 'test-api-key')
                && $request['number'] === '2348012345678'
                && $request['text'] === 'Hello World';
        });
    }

    public function test_send_evolution_message_with_media(): void
    {
        Http::fake([
            'http://whatsapp:8080/message/sendMedia/academyhub-test' => Http::response([
                'key' => ['id' => 'MEDIA123'],
            ], 200),
        ]);

        $result = WhatsAppService::sendEvolutionMessage(
            phone: '2348012345678',
            message: 'Your report card is attached',
            mediaUrl: 'https://school.test/report.pdf',
            filename: 'Term1_Report.pdf'
        );

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://whatsapp:8080/message/sendMedia/academyhub-test'
                && $request['mediatype'] === 'document'
                && $request['media'] === 'https://school.test/report.pdf'
                && $request['fileName'] === 'Term1_Report.pdf';
        });
    }

    public function test_send_evolution_image_detects_mediatype(): void
    {
        Http::fake([
            'http://whatsapp:8080/message/sendMedia/academyhub-test' => Http::response([
                'key' => ['id' => 'IMG123'],
            ], 200),
        ]);

        $result = WhatsAppService::sendEvolutionMessage(
            phone: '2348012345678',
            message: 'Check this out',
            mediaUrl: 'https://school.test/photo.jpg',
            filename: 'photo.jpg'
        );

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request['mediatype'] === 'image';
        });
    }

    public function test_send_evolution_message_rejects_empty_phone(): void
    {
        Http::fake();

        $result = WhatsAppService::sendEvolutionMessage('', 'Hello');

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    public function test_send_evolution_message_rejects_empty_text_without_media(): void
    {
        Http::fake();

        $result = WhatsAppService::sendEvolutionMessage('2348012345678', '');

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    public function test_get_evolution_status_returns_connected_state(): void
    {
        Http::fake([
            // connectionState endpoint (v2 does NOT return ownerJid)
            'http://whatsapp:8080/instance/connectionState/academyhub-test' => Http::response([
                'instance' => [
                    'instanceName' => 'academyhub-test',
                    'state' => 'open',
                ],
            ], 200),
            // fetchInstances endpoint (v2 returns owner/ownerJid here)
            'http://whatsapp:8080/instance/fetchInstances*' => Http::response([
                [
                    'instance' => [
                        'instanceName' => 'academyhub-test',
                        'owner' => '2348012345678@s.whatsapp.net',
                    ],
                ],
            ], 200),
        ]);

        $status = WhatsAppService::getEvolutionStatus();

        $this->assertTrue($status['connected']);
        $this->assertSame('open', $status['state']);
        $this->assertSame('2348012345678@s.whatsapp.net', $status['phone']);
    }

    public function test_get_evolution_status_returns_disconnected_without_fetching_instances(): void
    {
        Http::fake([
            'http://whatsapp:8080/instance/connectionState/academyhub-test' => Http::response([
                'instance' => [
                    'instanceName' => 'academyhub-test',
                    'state' => 'close',
                ],
            ], 200),
        ]);

        $status = WhatsAppService::getEvolutionStatus();

        $this->assertFalse($status['connected']);
        $this->assertSame('close', $status['state']);
        $this->assertNull($status['phone']);

        // Should NOT call fetchInstances when disconnected
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'fetchInstances');
        });
    }

    public function test_get_evolution_qr_fetches_base64_string(): void
    {
        Http::fake([
            'http://whatsapp:8080/instance/connect/academyhub-test' => Http::response([
                'base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...',
            ], 200),
        ]);

        $qr = WhatsAppService::getEvolutionQr();

        $this->assertNotNull($qr);
        $this->assertStringContainsString('data:image/png;base64,', $qr);
    }

    public function test_get_evolution_qr_handles_nested_v2_response(): void
    {
        Http::fake([
            'http://whatsapp:8080/instance/connect/academyhub-test' => Http::response([
                'qrcode' => [
                    'base64' => 'data:image/png;base64,NESTED_QR_DATA...',
                ],
            ], 200),
        ]);

        $qr = WhatsAppService::getEvolutionQr();

        $this->assertNotNull($qr);
        $this->assertStringContainsString('data:image/png;base64,', $qr);
    }

    public function test_webhook_handles_evolution_api_incoming_message(): void
    {
        // Fake outbound Evolution API calls that the bot reply triggers
        Http::fake([
            'http://whatsapp:8080/message/sendText/academyhub-test' => Http::response([
                'key' => ['id' => 'REPLY_001'],
            ], 200),
            'http://whatsapp:8080/message/sendMedia/academyhub-test' => Http::response([
                'key' => ['id' => 'REPLY_002'],
            ], 200),
        ]);

        $payload = [
            'event' => 'messages.upsert',
            'instance' => 'academyhub-test',
            'data' => [
                'key' => [
                    'remoteJid' => '2348098765432@s.whatsapp.net',
                    'fromMe' => false,
                    'id' => 'EVOMSG_001',
                ],
                'message' => [
                    'conversation' => 'hi',
                ],
            ],
        ];

        $response = $this->postJson('/api/whatsapp/webhook', $payload);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
    }

    public function test_webhook_ignores_evolution_outgoing_messages(): void
    {
        $payload = [
            'event' => 'messages.upsert',
            'instance' => 'academyhub-test',
            'data' => [
                'key' => [
                    'remoteJid' => '2348098765432@s.whatsapp.net',
                    'fromMe' => true, // Outgoing message from bot itself
                    'id' => 'BOT_MSG_001',
                ],
                'message' => [
                    'conversation' => 'Welcome to our school!',
                ],
            ],
        ];

        $response = $this->postJson('/api/whatsapp/webhook', $payload);

        $response->assertOk();
        $response->assertJson(['status' => 'ignored_self']);
    }

    public function test_webhook_ignores_group_and_broadcast_messages(): void
    {
        $payload = [
            'event' => 'messages.upsert',
            'instance' => 'academyhub-test',
            'data' => [
                'key' => [
                    'remoteJid' => '120363012345678@g.us', // Group JID
                    'fromMe' => false,
                    'id' => 'GROUP_MSG_001',
                ],
                'message' => [
                    'conversation' => 'group message',
                ],
            ],
        ];

        $response = $this->postJson('/api/whatsapp/webhook', $payload);

        $response->assertOk();
        $response->assertJson(['status' => 'ignored_non_individual']);
    }

    public function test_webhook_deduplicates_evolution_messages(): void
    {
        // Fake outbound calls for the first request
        Http::fake([
            'http://whatsapp:8080/message/sendText/academyhub-test' => Http::response([
                'key' => ['id' => 'REPLY_001'],
            ], 200),
        ]);

        $payload = [
            'event' => 'messages.upsert',
            'instance' => 'academyhub-test',
            'data' => [
                'key' => [
                    'remoteJid' => '2348098765432@s.whatsapp.net',
                    'fromMe' => false,
                    'id' => 'DUPE_MSG_001',
                ],
                'message' => [
                    'conversation' => 'hello',
                ],
            ],
        ];

        // First request should process normally
        $response1 = $this->postJson('/api/whatsapp/webhook', $payload);
        $response1->assertOk();
        $response1->assertJson(['status' => 'success']);

        // Second identical request should be deduplicated
        $response2 = $this->postJson('/api/whatsapp/webhook', $payload);
        $response2->assertOk();
        $response2->assertJson(['status' => 'duplicate_skipped']);
    }

    public function test_settings_whatsapp_page_is_accessible_to_admin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Http::fake([
            'http://whatsapp:8080/instance/connectionState/academyhub-test' => Http::response([
                'instance' => [
                    'state' => 'open',
                ],
            ], 200),
            'http://whatsapp:8080/instance/fetchInstances*' => Http::response([
                [
                    'instance' => [
                        'instanceName' => 'academyhub-test',
                        'owner' => '2348012345678@s.whatsapp.net',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->get('/settings/whatsapp');

        $response->assertOk();
        $response->assertSee('WhatsApp Gateway');
        $response->assertSee('Connected');
    }
}
