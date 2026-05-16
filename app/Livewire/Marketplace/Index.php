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
    /**
     * Free plugin — install immediately.
     * ID comes from Alpine, price is always fetched from DB (not trusted from frontend).
     */
    public function confirmInstall(int $componentId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $component = MarketplaceComponent::findOrFail($componentId);

        // Re-validate it really is free from DB — never trust frontend price
        abort_unless($component->price == 0, 403, 'This plugin requires payment.');

        $user->tenant->marketplaceComponents()->syncWithoutDetaching([
            $component->id => ['installed_at' => now()]
        ]);

        $this->dispatch('alert', message: 'Plugin installed successfully!', type: 'success');

        $this->redirect(route('marketplace'), navigate: false);
    }

    /**
     * Paid plugin — generate a reference and open the Paystack popup via a browser event.
     * Amount is fetched from DB only — never from the frontend.
     */
    public function startPayment(int $componentId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $component = MarketplaceComponent::findOrFail($componentId);
        abort_unless($component->price > 0, 403, 'This plugin is free, use confirmInstall.');

        $reference = 'PLG-' . strtoupper(Str::random(12));

        $this->dispatch('open-paystack', [
            'key'          => config('paystack.public_key', env('PAYSTACK_PUBLIC_KEY')),
            'email'        => $user->email,
            'amount'       => (int) ($component->price * 100), // Kobo — from DB, not frontend
            'ref'          => $reference,
            'component_id' => $component->id,
        ]);
    }

    /**
     * Verify Paystack payment server-side and install the plugin if successful.
     */
    public function verifyPayment(string $reference): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $response = Http::withToken(config('paystack.secret_key', env('PAYSTACK_SECRET_KEY')))
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (! $response->successful() || $response->json('data.status') !== 'success') {
            $this->dispatch('alert', message: 'Payment verification failed. Please contact support.', type: 'error');
            return;
        }

        $data        = $response->json('data');
        $componentId = $data['metadata']['custom_fields'][0]['value'] ?? null;

        if (! $componentId) {
            $this->dispatch('alert', message: 'Invalid payment metadata.', type: 'error');
            return;
        }

        $component = MarketplaceComponent::find((int) $componentId);

        if (! $component) {
            $this->dispatch('alert', message: 'Plugin not found.', type: 'error');
            return;
        }

        // Double-check the amount paid matches DB price (in kobo)
        $amountPaidKobo = (int) ($data['amount'] ?? 0);
        $expectedKobo   = (int) ($component->price * 100);

        if ($amountPaidKobo < $expectedKobo) {
            $this->dispatch('alert', message: 'Payment amount mismatch. Please contact support.', type: 'error');
            return;
        }

        $user->tenant->marketplaceComponents()->syncWithoutDetaching([
            $component->id => ['installed_at' => now()]
        ]);

        $this->dispatch('alert', message: 'Payment successful! Plugin is now active in your sidebar.', type: 'success');

        $this->redirect(route('marketplace'), navigate: false);
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $components = MarketplaceComponent::where('is_active', true)->get();
        $installed  = $user->tenant->marketplaceComponents()
            ->wherePivotNotNull('installed_at')
            ->pluck('marketplace_components.id')
            ->toArray();

        return view('livewire.marketplace.index', compact('components', 'installed'));
    }
}