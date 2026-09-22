<?php

namespace App\Livewire\Marketplace;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\MarketplaceComponent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

#[Layout('layouts.app')]
#[Title('Marketplace')]
class Index extends Component
{
    public string $errorMessage = '';
    /**
     * Free plugin — install immediately.
     * ID comes from Alpine, price is always fetched from DB (not trusted from frontend).
     */
    public function confirmInstall(int $componentId): void
    {
        $this->errorMessage = '';
        $user = auth()->user();
        abort_unless($user?->role === 'admin' || $user?->is_super_admin, 403);

        $component = MarketplaceComponent::findOrFail($componentId);

        // Re-validate it really is free from DB — never trust frontend price
        abort_unless($component->price == 0, 403, 'This plugin requires payment.');

        $setupFee = (float) $component->setup_fee;
        $usageFee = (float) $component->usage_fee_per_student;

        $user->tenant->marketplaceComponents()->syncWithoutDetaching([
            $component->id => [
                'installed_at'          => now(),
                'uninstalled_at'        => null,
                'status'                => 'active',
                'setup_fee'             => $setupFee,
                'usage_fee_per_student' => $usageFee,
                'price_paid'            => $setupFee,
            ]
        ]);

        $user->tenant->marketplaceComponents()->updateExistingPivot($component->id, [
            'installed_at'          => now(),
            'uninstalled_at'        => null,
            'status'                => 'active',
            'setup_fee'             => $setupFee,
            'usage_fee_per_student' => $usageFee,
            'price_paid'            => $setupFee,
        ]);

        $component->increment('installs');

        $this->dispatch('alert', message: 'Plugin installed successfully!', type: 'success');

        $this->redirect(route('marketplace'), navigate: false);
    }

    public function startPayment(int $componentId)
    {
        $this->errorMessage = '';
        $user = auth()->user();
        abort_unless($user?->role === 'admin' || $user?->is_super_admin, 403);

        $component = MarketplaceComponent::findOrFail($componentId);
        abort_unless($component->price > 0, 403, 'This plugin is free, use confirmInstall.');

        $reference = 'PLG-' . strtoupper(Str::random(12));
        $amountInKobo = (int) ($component->price * 100);

        $secretKey = config('services.paystack.secret_key');

        $response = Http::withToken($secretKey)
            ->withOptions(['verify' => false])
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => str_replace('.local', '.com', $user->email),
                'amount' => $amountInKobo,
                'reference' => $reference,
                'callback_url' => route('paystack.callback'),
                'metadata' => [
                    'payment_type' => 'marketplace',
                    'component_id' => $component->id,
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                ]
            ]);

        if (!$response->successful() || !$response->json('status')) {
            $msg = $response->json('message') ?? 'Unable to initialize payment transaction.';
            $this->errorMessage = 'Payment initialization failed: ' . $msg;
            $this->dispatch('alert', message: $this->errorMessage, type: 'error');
            return;
        }

        $authorizationUrl = $response->json('data.authorization_url');
        return redirect()->away($authorizationUrl);
    }

    /**
     * Verify Paystack payment server-side and install the plugin if successful.
     */
    public function verifyPayment(string $reference): void
    {
        $this->errorMessage = '';
        $user = auth()->user();
        abort_unless($user?->role === 'admin' || $user?->is_super_admin, 403);

        $response = Http::withToken(config('services.paystack.secret_key'))
            ->withOptions(['verify' => false])
            ->timeout(10)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (! $response->successful() || $response->json('data.status') !== 'success') {
            $this->errorMessage = 'Payment verification failed. Please contact support.';
            $this->dispatch('alert', message: $this->errorMessage, type: 'error');
            return;
        }

        $data        = $response->json('data');
        $componentId = $data['metadata']['custom_fields'][0]['value'] ?? null;

        if (! $componentId) {
            $this->errorMessage = 'Invalid payment metadata.';
            $this->dispatch('alert', message: $this->errorMessage, type: 'error');
            return;
        }

        $component = MarketplaceComponent::find((int) $componentId);

        if (! $component) {
            $this->errorMessage = 'Plugin not found.';
            $this->dispatch('alert', message: $this->errorMessage, type: 'error');
            return;
        }

        // Double-check the amount paid matches DB price (in kobo)
        $amountPaidKobo = (int) ($data['amount'] ?? 0);
        $expectedKobo   = (int) ($component->price * 100);

        if ($amountPaidKobo < $expectedKobo) {
            $this->errorMessage = 'Payment amount mismatch. Please contact support.';
            $this->dispatch('alert', message: $this->errorMessage, type: 'error');
            return;
        }

        $setupFee = (float) $component->setup_fee;
        $usageFee = (float) $component->usage_fee_per_student;

        $user->tenant->marketplaceComponents()->syncWithoutDetaching([
            $component->id => [
                'installed_at'          => now(),
                'uninstalled_at'        => null,
                'status'                => 'active',
                'setup_fee'             => $setupFee,
                'usage_fee_per_student' => $usageFee,
                'price_paid'            => $setupFee,
            ]
        ]);

        $user->tenant->marketplaceComponents()->updateExistingPivot($component->id, [
            'installed_at'          => now(),
            'uninstalled_at'        => null,
            'status'                => 'active',
            'setup_fee'             => $setupFee,
            'usage_fee_per_student' => $usageFee,
            'price_paid'            => $setupFee,
        ]);

        $component->increment('installs');

        if ($setupFee > 0) {
            \App\Models\TenantPluginBill::create([
                'tenant_id'                => $user->tenant->id,
                'marketplace_component_id' => $component->id,
                'bill_type'                => 'setup',
                'term_name'                => null,
                'session_name'             => null,
                'student_count'            => null,
                'setup_fee'                => $setupFee,
                'usage_fee_per_student'    => 0,
                'total_due'                => $setupFee,
                'status'                   => 'paid',
                'paid_at'                  => now(),
            ]);
        }

        $this->dispatch('alert', message: 'Payment successful! Plugin is now active in your sidebar.', type: 'success');

        $this->redirect(route('marketplace'), navigate: false);
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin' || $user?->is_super_admin, 403);

        $query = MarketplaceComponent::query();
        if (!$user->is_super_admin) {
            $query->where('is_active', true);
        }
        $components = $query->get();

        $installed = [];
        if ($user->tenant) {
            $installed  = $user->tenant->marketplaceComponents()
                ->wherePivotNotNull('installed_at')
                ->wherePivotNull('uninstalled_at')
                ->pluck('marketplace_components.id')
                ->toArray();
        }

        return view('livewire.marketplace.index', compact('components', 'installed'));
    }
}