<?php

namespace App\Livewire\Admin;

use App\Models\Student;
use App\Support\TenantSettings;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Subscription & Billing')]
class SubscriptionBilling extends Component
{
    public int $studentCount = 0;
    
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

    public function getCoreCostProperty()
    {
        return $this->studentCount * 1000;
    }

    public function getActivePluginsProperty()
    {
        $tenant = auth()->user()?->tenant;
        if (!$tenant) {
            return collect();
        }
        return $tenant->activeMarketplaceComponents()->get();
    }

    public function getPluginYearlyCost($plugin)
    {
        // Get allowed classes from pivot
        $rawClasses = $plugin->pivot->allowed_class_ids ?? [];
        $classes = is_string($rawClasses)
            ? (json_decode($rawClasses, true) ?: [])
            : (is_array($rawClasses) ? $rawClasses : []);

        // Calculate student count for these classes
        if (!empty($classes)) {
            $studentCount = Student::whereIn('class_id', $classes)
                ->where('status', 'active')
                ->count();
        } else {
            // Fallback to studentCount at install or total if empty
            $studentCount = $plugin->pivot->student_count_at_install ?? 0;
        }

        $usageFee = (float) ($plugin->pivot->usage_fee_per_student ?? $plugin->usage_fee_per_student ?? 0);
        
        // Termly usage fee * 3 terms
        return $usageFee * $studentCount * 3;
    }

    public function getAddonsCostProperty()
    {
        $cost = 0;
        foreach ($this->activePlugins as $plugin) {
            $cost += $this->getPluginYearlyCost($plugin);
        }
        return $cost;
    }

    public function getTotalCostProperty()
    {
        return $this->coreCost + $this->addonsCost;
    }

    public function payNow()
    {
        $amountInKobo = $this->totalCost * 100;
        $email = auth()->user()->email ?? 'admin@school.com';
        $reference = 'SUB_' . uniqid() . '_' . time();

        $this->dispatch('initialize-paystack', [
            'amount' => $amountInKobo,
            'email' => $email,
            'ref' => $reference
        ]);
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
        $response = \Illuminate\Support\Facades\Http::withToken(config('services.paystack.secret_key', env('PAYSTACK_SECRET_KEY')))
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
