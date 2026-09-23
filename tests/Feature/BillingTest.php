<?php

namespace Tests\Feature;

use App\Livewire\Billing\Index as BillingIndex;
use App\Models\MarketplaceComponent;
use App\Models\Tenant;
use App\Models\TenantPluginBill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private function createTenant(array $overrides = []): Tenant
    {
        $tenant = Tenant::query()->create(array_merge([
            'name' => 'Custom Academy',
            'slug' => 'custom-academy',
            'plan' => 'pro',
            'status' => 'active',
            'max_students' => 100,
            'max_teachers' => 10,
        ], $overrides));

        app()->instance('currentTenant', $tenant);

        return $tenant;
    }

    public function test_user_can_view_plugin_bills_tab(): void
    {
        $tenant = $this->createTenant();

        $bursar = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bursar',
            'is_active' => true,
            'permissions' => ['billing.transactions'],
        ]);

        $component = MarketplaceComponent::create([
            'name' => 'CBT Plugin',
            'slug' => 'cbt',
            'is_active' => true,
            'setup_fee' => 5000,
            'usage_fee_per_student' => 50,
        ]);

        $bill = TenantPluginBill::create([
            'tenant_id' => $tenant->id,
            'marketplace_component_id' => $component->id,
            'bill_type' => 'setup',
            'setup_fee' => 5000,
            'total_due' => 5000,
            'status' => 'unpaid',
        ]);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->set('tab', 'plugin-bills')
            ->assertSee('CBT Plugin')
            ->assertSee('Setup Fee')
            ->assertSee('₦5,000.00')
            ->assertSee('Unpaid');
    }

    public function test_user_can_initialize_paystack_payment_for_plugin_bill(): void
    {
        $tenant = $this->createTenant();

        $bursar = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bursar',
            'is_active' => true,
            'permissions' => ['billing.transactions'],
        ]);

        $component = MarketplaceComponent::create([
            'name' => 'CBT Plugin',
            'slug' => 'cbt',
            'is_active' => true,
            'setup_fee' => 5000,
            'usage_fee_per_student' => 50,
        ]);

        $bill = TenantPluginBill::create([
            'tenant_id' => $tenant->id,
            'marketplace_component_id' => $component->id,
            'bill_type' => 'setup',
            'setup_fee' => 5000,
            'total_due' => 5000,
            'status' => 'unpaid',
        ]);

        // Fake Paystack initialize transaction response
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/mock-checkout-url-plugin-bill',
                    'reference' => 'BILL_' . $bill->id . '_abc123',
                ],
            ], 200),
        ]);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->set('tab', 'plugin-bills')
            ->call('payPluginBill', $bill->id)
            ->assertRedirect('https://checkout.paystack.com/mock-checkout-url-plugin-bill');
    }

    public function test_user_can_verify_paystack_payment_successfully(): void
    {
        $tenant = $this->createTenant();

        $bursar = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bursar',
            'is_active' => true,
            'permissions' => ['billing.transactions'],
        ]);

        $component = MarketplaceComponent::create([
            'name' => 'CBT Plugin',
            'slug' => 'cbt',
            'is_active' => true,
            'setup_fee' => 5000,
            'usage_fee_per_student' => 50,
        ]);

        $bill = TenantPluginBill::create([
            'tenant_id' => $tenant->id,
            'marketplace_component_id' => $component->id,
            'bill_type' => 'setup',
            'setup_fee' => 5000,
            'total_due' => 5000,
            'status' => 'unpaid',
        ]);

        $reference = 'BILL_' . $bill->id . '_abc123';

        // Fake Paystack API response
        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $reference,
                    'amount' => 500000,
                ],
            ], 200),
        ]);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->set('tab', 'plugin-bills')
            ->call('verifyPluginBillPayment', $reference)
            ->assertDispatched('alert', message: 'Payment successful! Plugin bill marked as paid.', type: 'success');

        $bill->refresh();
        $this->assertSame('paid', $bill->status);
        $this->assertNotNull($bill->paid_at);
    }

    public function test_failed_paystack_verification_keeps_bill_unpaid(): void
    {
        $tenant = $this->createTenant();

        $bursar = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bursar',
            'is_active' => true,
            'permissions' => ['billing.transactions'],
        ]);

        $component = MarketplaceComponent::create([
            'name' => 'CBT Plugin',
            'slug' => 'cbt',
            'is_active' => true,
            'setup_fee' => 5000,
            'usage_fee_per_student' => 50,
        ]);

        $bill = TenantPluginBill::create([
            'tenant_id' => $tenant->id,
            'marketplace_component_id' => $component->id,
            'bill_type' => 'setup',
            'setup_fee' => 5000,
            'total_due' => 5000,
            'status' => 'unpaid',
        ]);

        $reference = 'BILL_' . $bill->id . '_failed_reference';

        // Fake failed verification response
        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => false,
                'message' => 'Transaction not found',
            ], 404),
        ]);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->set('tab', 'plugin-bills')
            ->call('verifyPluginBillPayment', $reference)
            ->assertDispatched('alert', message: 'Payment verification failed. Please try again.', type: 'error');

        $bill->refresh();
        $this->assertSame('unpaid', $bill->status);
        $this->assertNull($bill->paid_at);
    }

    public function test_cashier_terminal_can_select_payment_methods_and_record_cash_payment(): void
    {
        $tenant = $this->createTenant();

        $bursar = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bursar',
            'is_active' => true,
            'permissions' => ['billing.transactions'],
        ]);

        $class = \App\Models\SchoolClass::create([
            'tenant_id' => $tenant->id,
            'name' => 'JSS 1',
            'level' => 1,
        ]);

        $section = \App\Models\Section::create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'name' => 'A',
        ]);

        $student = \App\Models\Student::create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'admission_number' => 'ADM-001',
            'first_name' => 'Fatima',
            'last_name' => 'Bello',
            'gender' => 'Female',
            'date_of_birth' => '2010-01-01',
            'status' => 'Active',
        ]);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->assertSee('Cashier & Fee Payment Terminal')
            ->assertSee('Cash')
            ->assertSee('Transfer')
            ->assertSee('POS')
            ->call('setPaymentMethod', 'Cash')
            ->assertSet('paymentMethod', 'Cash')
            ->set('studentId', $student->id)
            ->set('category', 'Tuition')
            ->set('amountPaid', '25000')
            ->set('date', now()->toDateString())
            ->call('saveTransaction')
            ->assertDispatched('alert', message: 'Transaction saved successfully!', type: 'success');

        $this->assertDatabaseHas('transactions', [
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'type' => 'Income',
            'payment_method' => 'Cash',
            'amount_paid' => 25000,
        ]);
    }

    public function test_cashier_terminal_quick_amounts_and_clear(): void
    {
        $tenant = $this->createTenant();

        $bursar = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bursar',
            'is_active' => true,
            'permissions' => ['billing.transactions'],
        ]);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->set('amountPaid', '')
            ->call('addQuickAmount', 5000)
            ->assertSet('amountPaid', '5000')
            ->call('addQuickAmount', 10000)
            ->assertSet('amountPaid', '15000')
            ->call('clearAmount')
            ->assertSet('amountPaid', '');
    }
}

