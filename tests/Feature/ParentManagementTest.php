<?php

namespace Tests\Feature;

use App\Livewire\Parents\Management as ParentsManagement;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\FeeStructure;
use App\Models\Transaction;
use App\Models\AcademicTerm;
use App\Models\AcademicSession;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ParentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_parent_management_and_edit_parent_profile(): void
    {
        $tenant = Tenant::query()->create([
            'slug' => 'test-school',
            'name' => 'Test School',
            'plan' => 'pro',
            'status' => 'active',
            'max_students' => 500,
            'max_teachers' => 50,
        ]);

        app()->instance('currentTenant', $tenant);

        $admin = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $parent = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'John Doe',
            'email' => 'john.doe@test.local',
            'password' => Hash::make('password123'),
            'role' => 'parent',
            'is_active' => true,
        ]);

        // Access page
        $this->actingAs($admin)
            ->get('/parents')
            ->assertStatus(200);

        // Livewire test
        Livewire::actingAs($admin)
            ->test(ParentsManagement::class)
            ->call('openEditModal', $parent->id)
            ->assertSet('editParentId', $parent->id)
            ->assertSet('editName', 'John Doe')
            ->assertSet('editEmail', 'john.doe@test.local')
            ->set('editName', 'John Updated')
            ->set('editEmail', 'john.updated@test.local')
            ->set('editPhone', '08012345678')
            ->set('editPassword', 'newsecurepassword')
            ->call('updateParent')
            ->assertHasNoErrors();

        // Check DB update
        $parent->refresh();
        $this->assertEquals('John Updated', $parent->name);
        $this->assertEquals('john.updated@test.local', $parent->email);
        $this->assertEquals('08012345678', $parent->custom_fields['phone'] ?? null);
        $this->assertTrue(Hash::check('newsecurepassword', $parent->password));
    }

    public function test_parent_outstanding_balance_calculation(): void
    {
        $tenant = Tenant::query()->create([
            'slug' => 'test-school',
            'name' => 'Test School',
            'plan' => 'pro',
            'status' => 'active',
            'max_students' => 500,
            'max_teachers' => 50,
        ]);

        app()->instance('currentTenant', $tenant);

        $admin = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $parent = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Jane Parent',
            'email' => 'jane.parent@test.local',
            'password' => Hash::make('password123'),
            'role' => 'parent',
            'is_active' => true,
        ]);

        $class = SchoolClass::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Grade 1',
            'level' => 1,
        ]);

        $section = Section::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'name' => 'A',
        ]);

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-BAL-0001',
            'first_name' => 'Child',
            'last_name' => 'One',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Female',
            'status' => 'Active',
        ]);

        $parent->students()->attach($student->id);

        $academicSession = AcademicSession::query()->create([
            'tenant_id' => $tenant->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $academicTerm = AcademicTerm::query()->create([
            'tenant_id' => $tenant->id,
            'academic_session_id' => $academicSession->id,
            'name' => 'First Term',
            'term_number' => 1,
            'is_active' => true,
        ]);

        // Set fee structure for child's class
        FeeStructure::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'category' => 'Tuition',
            'term' => 1,
            'session' => '2026/2027',
            'amount_due' => 50000.00,
        ]);

        // 1. Initial State - Owe 50000
        Livewire::actingAs($admin)
            ->test(ParentsManagement::class)
            ->assertSet('parentBalances.' . $parent->id, 50000.00);

        // 2. Partial Payment - 20000
        Transaction::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'type' => 'Income',
            'category' => 'Tuition',
            'term' => 1,
            'session' => '2026/2027',
            'amount_paid' => 20000.00,
            'date' => now()->toDateString(),
            'is_void' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(ParentsManagement::class)
            ->assertSet('parentBalances.' . $parent->id, 30000.00);

        // 3. Clear balance
        Transaction::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'type' => 'Income',
            'category' => 'Tuition',
            'term' => 1,
            'session' => '2026/2027',
            'amount_paid' => 30000.00,
            'date' => now()->toDateString(),
            'is_void' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(ParentsManagement::class)
            ->assertSet('parentBalances.' . $parent->id, 0.00);
    }

    public function test_parent_can_access_parent_pay_route(): void
    {
        $tenant = Tenant::query()->create([
            'slug' => 'test-school',
            'name' => 'Test School',
            'plan' => 'pro',
            'status' => 'active',
            'max_students' => 500,
            'max_teachers' => 50,
        ]);

        app()->instance('currentTenant', $tenant);

        // Seed the payment-gateway component
        $gatewayComponent = \App\Models\MarketplaceComponent::query()->create([
            'name' => 'Payment Gateway',
            'slug' => 'payment-gateway',
            'price' => 20000.00,
            'pricing_model' => 'flat',
            'setup_fee' => 10000.00,
            'usage_fee_per_student' => 0.00,
            'short_description' => 'Test payment gateway',
            'description' => 'Test',
            'category' => 'Finance',
            'icon' => 'finance',
            'is_active' => true,
        ]);

        // Attach it to the tenant to active it
        $tenant->marketplaceComponents()->attach($gatewayComponent->id, [
            'installed_at' => now(),
            'status' => 'active',
            'price_paid' => 20000.00,
            'setup_fee' => 10000.00,
            'usage_fee_per_student' => 0.00,
        ]);

        $parent = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Jane Parent',
            'email' => 'jane.parent@test.local',
            'password' => Hash::make('password123'),
            'role' => 'parent',
            'is_active' => true,
        ]);

        $class = SchoolClass::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Grade 1',
            'level' => 1,
        ]);

        $section = Section::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'name' => 'A',
        ]);

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-PAY-0001',
            'first_name' => 'Child',
            'last_name' => 'One',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Female',
            'status' => 'Active',
        ]);
        $parent->students()->attach($student->id);

        FeeStructure::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'category' => 'Tuition',
            'term' => 2,
            'session' => '2026/2027',
            'amount_due' => 60000.00,
        ]);

        // Access the /parent/pay route as parent
        $this->actingAs($parent)
            ->get('http://test-school.localhost/parent/pay')
            ->assertStatus(200);

        // Test Livewire component interactions for term selection
        Livewire::actingAs($parent)
            ->test(\App\Livewire\PaymentGateway\ParentPay::class)
            ->assertSet('selectedStudentId', $student->id)
            ->set('selectedSession', '2026/2027')
            ->set('selectedTerm', 2)
            ->assertSet('amount_due', 60000.00)
            ->assertSet('outstanding_balance', 60000.00);
    }

    public function test_parent_checkout_requires_approved_subaccount_status(): void
    {
        $tenant = Tenant::query()->create([
            'slug' => 'test-school-checkout',
            'name' => 'Test School Checkout',
            'plan' => 'pro',
            'status' => 'active',
            'max_students' => 500,
            'max_teachers' => 50,
            'settings' => [
                'payment_gateway' => [
                    'subaccount_status' => 'pending' // pending approval
                ]
            ]
        ]);

        app()->instance('currentTenant', $tenant);

        $parent = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Jane Parent',
            'email' => 'jane.parent2@test.local',
            'password' => Hash::make('password123'),
            'role' => 'parent',
            'is_active' => true,
        ]);

        $class = SchoolClass::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Grade 1',
            'level' => 1,
        ]);

        $section = Section::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'name' => 'A',
        ]);

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-PAY-0002',
            'first_name' => 'Child',
            'last_name' => 'Two',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Female',
            'status' => 'Active',
        ]);
        $parent->students()->attach($student->id);

        FeeStructure::query()->create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'category' => 'Tuition',
            'term' => 1,
            'session' => '2026/2027',
            'amount_due' => 15000.00,
        ]);

        // Fake Paystack initialize transaction response
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/mock-checkout-url-tuition',
                    'reference' => 'TUI_mock_ref_123',
                ],
            ], 200),
        ]);

        // When subaccount is pending, parent checkout page is disabled
        Livewire::actingAs($parent)
            ->test(\App\Livewire\PaymentGateway\ParentPay::class)
            ->assertSet('isGatewayApproved', false)
            ->call('processCardPayment')
            ->assertSet('paymentSuccess', false);

        // Now set subaccount_status to approved and check
        $settings = $tenant->settings;
        $settings['payment_gateway']['subaccount_status'] = 'approved';
        $tenant->update(['settings' => $settings]);

        Livewire::actingAs($parent)
            ->test(\App\Livewire\PaymentGateway\ParentPay::class)
            ->assertSet('isGatewayApproved', true)
            ->call('processCardPayment')
            ->assertRedirect('https://checkout.paystack.com/mock-checkout-url-tuition');
    }

    public function test_superadmin_can_approve_and_reject_subaccount_settlements(): void
    {
        $tenant = Tenant::query()->create([
            'slug' => 'test-school-sa-action',
            'name' => 'Test School SA Action',
            'plan' => 'pro',
            'status' => 'active',
            'max_students' => 500,
            'max_teachers' => 50,
            'settings' => [
                'payment_gateway' => [
                    'bank_name' => 'Zenith Bank',
                    'account_number' => '1234567890',
                    'account_name' => 'Greenwood LLC',
                    'collection_timing' => 'Daily',
                    'subaccount_status' => 'pending'
                ]
            ]
        ]);

        $superadmin = new User([
            'name' => 'Superadmin',
            'email' => 'superadmin@test.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $superadmin->is_super_admin = true;
        $superadmin->save();

        // Fake Paystack subaccount response
        Http::fake([
            'https://api.paystack.co/subaccount' => Http::response([
                'status' => true,
                'message' => 'Subaccount created',
                'data' => [
                    'subaccount_code' => 'ACCT_test_payout_123',
                ]
            ], 200)
        ]);

        // Action as superadmin approving subaccount
        $this->actingAs($superadmin)
            ->post(route('superadmin.tenants.approve-subaccount', $tenant))
            ->assertRedirect();

        $tenant->refresh();
        $this->assertEquals('approved', $tenant->settings['payment_gateway']['subaccount_status']);
        $this->assertEquals('ACCT_test_payout_123', $tenant->settings['payment_gateway']['subaccount_code']);

        // Reject/reset action
        $this->actingAs($superadmin)
            ->post(route('superadmin.tenants.reject-subaccount', $tenant))
            ->assertRedirect();

        $tenant->refresh();
        $this->assertEquals('not_submitted', $tenant->settings['payment_gateway']['subaccount_status']);
    }
}
