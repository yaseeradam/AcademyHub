<?php

namespace App\Livewire\Admin;

use App\Models\Student;
use App\Support\TenantSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Subscription & Billing')]
class SubscriptionBilling extends Component
{
    public int $studentCount = 0;
    public string $errorMessage = '';

    public bool $whatsapp = false;
    public bool $cbt = false;
    public bool $parent_app = false;

    public function mount()
    {
        $this->studentCount = Student::count();

        $tenant = auth()->user()?->tenant;
        if ($tenant) {
            $activeSlugs = $tenant->activeMarketplaceComponents()->pluck('slug')->toArray();
            $this->whatsapp = in_array('whatsapp-bot', $activeSlugs, true);
            $this->cbt = in_array('cbt', $activeSlugs, true);
            $this->parent_app = in_array('parent-portal', $activeSlugs, true);
        }
    }

    /** True when subscription expires within the next 30 days (including already expired) */
    public function getIsExpiringSoonProperty(): bool
    {
        $tenant = auth()->user()?->tenant;
        if (!$tenant || !$tenant->expires_at) {
            return true; // No expiry set — treat as needing renewal
        }
        return $tenant->expires_at->diffInDays(now(), false) >= -30;
    }

    /** True when subscription is already past its expiry date */
    public function getIsExpiredProperty(): bool
    {
        $tenant = auth()->user()?->tenant;
        if (!$tenant || !$tenant->expires_at) {
            return false;
        }
        return $tenant->expires_at->isPast();
    }

    public function getCoreCostProperty()
    {
        return $this->studentCount * \App\Support\PlatformSettings::getStudentTermlyFee();
    }

    public function getActivePluginsProperty()
    {
        $tenant = auth()->user()?->tenant;
        if (!$tenant) {
            return collect();
        }
        return $tenant->activeMarketplaceComponents()->get();
    }

    public function getPluginTermlyCost($plugin)
    {
        // Get allowed classes from pivot
        $rawClasses = $plugin->pivot->allowed_class_ids ?? [];
        if (is_string($rawClasses)) {
            $rawClasses = json_decode($rawClasses, true);
        }
        if (is_string($rawClasses)) {
            $rawClasses = json_decode($rawClasses, true);
        }
        $classes = is_array($rawClasses) ? $rawClasses : [];

        // Calculate student count for these classes
        if (!empty($classes)) {
            $studentCount = Student::whereIn('class_id', $classes)
                ->where('status', 'Active')
                ->count();
        } else {
            // Fallback to studentCount at install or total if empty
            $studentCount = $plugin->pivot->student_count_at_install ?? 0;
        }

        $usageFee = (float) ($plugin->pivot->usage_fee_per_student ?? $plugin->usage_fee_per_student ?? 0);
        
        // Termly usage fee (for 4 months / 1 term)
        return $usageFee * $studentCount;
    }

    public function getAddonsCostProperty()
    {
        $cost = 0;
        foreach ($this->activePlugins as $plugin) {
            $cost += $this->getPluginTermlyCost($plugin);
        }
        return $cost;
    }

    public function getTotalCostProperty()
    {
        return $this->coreCost + $this->addonsCost;
    }

    public function payNow()
    {
        $this->errorMessage = '';

        // Block payment when subscription is healthy (more than 30 days remaining)
        if (!$this->isExpiringSoon) {
            $this->errorMessage = 'Your subscription is still active with more than 30 days remaining. No payment is due yet.';
            return;
        }

        if ($this->totalCost <= 0) {
            $this->errorMessage = 'Nothing to pay — your subscription cost is ₦0. Please configure your plugins with target classes first.';
            return;
        }

        $amountInKobo = $this->totalCost * 100;
        $email = str_replace('.local', '.com', auth()->user()->email ?? 'admin@school.com');
        $reference = 'SUB_' . uniqid() . '_' . time();

        $secretKey = config('services.paystack.secret_key');

        $response = Http::withToken($secretKey)
            ->withOptions(['verify' => false])
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $email,
                'amount' => $amountInKobo,
                'reference' => $reference,
                'callback_url' => route('paystack.callback'),
                'metadata' => [
                    'payment_type' => 'subscription',
                    'tenant_id' => TenantSettings::tenantId(),
                ]
            ]);

        if (!$response->successful() || !$response->json('status')) {
            Log::error("Paystack Subscription Payment initialization failed", [
                'response' => $response->json()
            ]);
            $msg = $response->json('message') ?? 'Unable to connect to Paystack gateway.';
            $this->errorMessage = 'Payment initialization failed: ' . $msg;
            $this->dispatch('alert', message: $this->errorMessage, type: 'error');
            return;
        }

        $authorizationUrl = $response->json('data.authorization_url');
        return redirect()->away($authorizationUrl);
    }

    private function settingsPath(): string
    {
        return TenantSettings::settingsPath();
    }

    private function readSettings(): array
    {
        $path = $this->settingsPath();
        return file_exists($path) ? (json_decode(file_get_contents($path), true) ?? []) : [];
    }

    private function writeSettings(array $data): void
    {
        $path = $this->settingsPath();
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        // Merge with existing settings so we don't overwrite other keys
        $existing = $this->readSettings();
        file_put_contents($path, json_encode(array_merge($existing, $data), JSON_PRETTY_PRINT));
    }

    public function verifyPayment($reference)
    {
        $response = \Illuminate\Support\Facades\Http::withToken(config('services.paystack.secret_key'))
            ->withOptions(['verify' => false])
            ->timeout(10)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if ($response->successful() && $response->json('data.status') === 'success') {
            $newExpiry = now()->addYear();

            // Extend subscription by 1 year and persist to JSON settings file
            $this->writeSettings([
                'subscription_due_date' => $newExpiry->toDateString(),
            ]);

            // Update database to keep in sync
            $tenantId = TenantSettings::tenantId();
            if ($tenantId) {
                $tenant = \App\Models\Tenant::find($tenantId);
                if ($tenant) {
                    $tenant->update([
                        'expires_at' => $newExpiry,
                    ]);
                }
            }

            // Flush settings cache so renewal takes effect instantly
            \Illuminate\Support\Facades\Cache::forget(TenantSettings::settingsCacheKey());

            session()->flash('success', 'Payment successful! Your subscription has been securely renewed.');
            $this->dispatch('payment-successful');
        } else {
            session()->flash('error', 'Payment verification failed. Please contact support.');
            $this->dispatch('payment-failed');
        }
    }

    public function render()
    {
        return view('livewire.admin.subscription-billing');
    }
}
