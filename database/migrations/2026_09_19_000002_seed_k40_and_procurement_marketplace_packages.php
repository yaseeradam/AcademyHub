<?php

use App\Models\MarketplaceComponent;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('marketplace_components')) {
            return;
        }

        // 1. ZKTeco K40 Biometrics Package
        $k40 = MarketplaceComponent::updateOrCreate(
            ['slug' => 'k40-biometrics'],
            [
                'name'                  => 'ZKTeco K40 Biometrics',
                'route_name'            => 'biometrics.index',
                'price'                 => 0.00,
                'pricing_model'         => 'flat',
                'setup_fee'             => 0.00,
                'usage_fee_per_student' => 0.00,
                'short_description'     => 'Biometric hardware synchronization for student and staff attendance.',
                'description'           => 'Connect your school\'s ZKTeco K40 fingerprint devices directly to AcademyHub. Features include real-time live punch notifications with audio alerts, automated parent WhatsApp arrival broadcasts, dual-shift Western/Islamic timetable tracking, hardware sync monitoring, and instant attendance logs.',
                'category'              => 'Hardware',
                'icon'                  => 'fingerprint',
                'is_active'             => true,
                'rating_avg'            => 4.95,
                'rating_count'          => 18,
                'installs'              => 24,
            ]
        );

        // 2. School Procurement & Expense Records Package
        $procurement = MarketplaceComponent::updateOrCreate(
            ['slug' => 'procurement-records'],
            [
                'name'                  => 'Procurement & Expense Records',
                'route_name'            => 'procurement.index',
                'price'                 => 0.00,
                'pricing_model'         => 'flat',
                'setup_fee'             => 0.00,
                'usage_fee_per_student' => 0.00,
                'short_description'     => 'Track school purchases, equipment costs, inventory acquisitions, vendors, and receipts.',
                'description'           => 'Complete procurement, asset, and expense record-keeping module for schools. Record every purchase made for the school—including what was bought, purchase date, cost, purchaser, supplier/vendor, payment method, and uploaded receipt/invoice attachments.',
                'category'              => 'Finance',
                'icon'                  => 'cart',
                'is_active'             => true,
                'rating_avg'            => 4.90,
                'rating_count'          => 15,
                'installs'              => 32,
            ]
        );

        // Ensure Tenant 1 (current school) has k40-biometrics active so existing hardware setup keeps working seamlessly
        $tenant1 = Tenant::find(1);
        if ($tenant1 && Schema::hasTable('tenant_marketplace_components')) {
            $tenant1->marketplaceComponents()->syncWithoutDetaching([
                $k40->id => [
                    'installed_at'          => now(),
                    'uninstalled_at'        => null,
                    'status'                => 'active',
                    'setup_fee'             => 0.00,
                    'usage_fee_per_student' => 0.00,
                    'price_paid'            => 0.00,
                ],
                $procurement->id => [
                    'installed_at'          => now(),
                    'uninstalled_at'        => null,
                    'status'                => 'active',
                    'setup_fee'             => 0.00,
                    'usage_fee_per_student' => 0.00,
                    'price_paid'            => 0.00,
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reverse
    }
};
