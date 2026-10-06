<?php

namespace App\Livewire\Settings;

use App\Support\WhatsAppService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('WhatsApp Bot Gateway')]
class WhatsAppGateway extends Component
{
    #[Locked]
    public string $provider = 'evolution';

    #[Locked]
    public bool $isConnected = false;

    #[Locked]
    public ?string $connectedPhone = null;

    #[Locked]
    public ?string $qrCode = null;

    #[Locked]
    public ?string $errorMessage = null;

    #[Locked]
    public ?string $statusMessage = null;

    #[Locked]
    public string $instanceName = '';

    #[Locked]
    public string $schoolName = '';

    // Test message properties (user-editable)
    public string $testPhone = '';
    public string $testMessage = 'Hello from AcademyHub! Your WhatsApp gateway is active and working properly.';

    #[Locked]
    public ?bool $testSuccess = null;

    #[Locked]
    public ?string $testFeedback = null;

    public function mount()
    {
        $user = auth()->user();
        $role = $user?->role;
        abort_unless(
            in_array($role, ['admin', 'proprietor'], true) || !empty($user?->is_super_admin),
            403,
            'Unauthorized access to WhatsApp Gateway.'
        );

        $this->provider = config('services.whatsapp.provider', 'evolution');
        $this->instanceName = WhatsAppService::getInstanceName();
        $this->schoolName = app()->bound('currentTenant') && app('currentTenant')
            ? app('currentTenant')->name
            : (auth()->user()?->tenant?->name ?? 'AcademyHub');
        $this->checkStatus();
    }

    /**
     * Query gateway status and fetch QR code if disconnected.
     */
    public function checkStatus(): void
    {
        $this->errorMessage = null;

        if ($this->provider === 'evolution') {
            $status = WhatsAppService::getEvolutionStatus();
            $this->isConnected = $status['connected'] ?? false;
            $this->connectedPhone = $status['phone'] ?? null;

            // Show error when gateway is unreachable or returns an error
            if (!$this->isConnected) {
                if (isset($status['error'])) {
                    $this->errorMessage = 'Cannot reach WhatsApp gateway. Ensure the container is running (docker compose up -d whatsapp).';
                } elseif (($status['state'] ?? '') === 'unreachable') {
                    $this->errorMessage = 'WhatsApp gateway returned an unexpected response. The container may be restarting.';
                }

                $this->loadQr();
            } else {
                $this->qrCode = null;
                // Clear disconnect banner once reconnected
                $this->statusMessage = null;
            }
        } else {
            // Meta Cloud API mode
            $token = config('services.whatsapp.token');
            $phoneId = config('services.whatsapp.phone_number_id');
            $this->isConnected = !empty($token) && !empty($phoneId);
            $this->connectedPhone = $phoneId;
            $this->qrCode = null;
        }
    }

    /**
     * Load or regenerate QR code for pairing.
     */
    public function loadQr(): void
    {
        if ($this->provider !== 'evolution') {
            return;
        }

        $qr = WhatsAppService::getEvolutionQr();
        // Clear stale QR on failure so expired codes aren't displayed
        $this->qrCode = $qr ?: null;
    }

    /**
     * Disconnect WhatsApp session.
     */
    public function disconnect(): void
    {
        if ($this->provider === 'evolution') {
            $success = WhatsAppService::logoutEvolution();

            if ($success) {
                $this->statusMessage = 'WhatsApp session disconnected. Please scan the new QR code to reconnect.';
            } else {
                $this->statusMessage = 'Disconnect request failed. The gateway may be unreachable. Please try again.';
            }

            $this->isConnected = false;
            $this->connectedPhone = null;
            $this->checkStatus();
        }
    }

    /**
     * Send a test message through the active gateway.
     */
    public function sendTest(): void
    {
        // Reset feedback before validation so stale success messages don't persist
        $this->testFeedback = null;
        $this->testSuccess = null;

        $this->validate([
            'testPhone' => 'required|string|min:7',
            'testMessage' => 'required|string|max:500',
        ]);

        try {
            $sent = WhatsAppService::sendMessage(
                phone: $this->testPhone,
                message: $this->testMessage
            );

            if ($sent) {
                $this->testSuccess = true;
                $this->testFeedback = "Test message dispatched successfully to {$this->testPhone}!";
            } else {
                $this->testSuccess = false;
                $this->testFeedback = "Gateway failed to send message. Please ensure your device is connected and the number includes international format (e.g. 2348012345678).";
            }
        } catch (\Throwable $e) {
            $this->testSuccess = false;
            $this->testFeedback = "Gateway error: unable to deliver test message. Check that the WhatsApp container is running.";
        }
    }

    public function render()
    {
        return view('livewire.settings.whatsapp-gateway');
    }
}
