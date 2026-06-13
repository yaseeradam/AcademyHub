<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\FeeStructure;
use App\Models\MarketplaceComponent;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\TenantPluginBill;
use App\Models\Transaction;
use App\Models\User;
use App\Support\TenantProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PaymentFlowTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Payment Flow Test Seeder...');

        // 1. Seed or find the active Tenant for model.myacademy.com.ng
        $tenant = Tenant::where('domain', 'model.myacademy.com.ng')->first();
        if (!$tenant) {
            $tenant = Tenant::where('slug', 'demo')->first();
        }
        if (!$tenant) {
            $tenant = Tenant::query()->create([
                'slug' => 'demo',
                'name' => 'Demo School',
                'domain' => 'model.myacademy.com.ng',
                'plan' => 'pro',
                'status' => 'active',
                'max_students' => 500,
                'max_teachers' => 50,
                'activated_at' => now(),
                'expires_at' => now()->addYear(), // Active — no payment due
            ]);
        } else {
            // Default: subscription is healthy (1 year out).
            // To test the expiring/expired flow, temporarily change addYear() to addDays(5) or subDays(1).
            $tenant->update([
                'plan'       => 'pro',
                'status'     => 'active',
                'expires_at' => now()->addYear(), // Change to addDays(5) to test expiring-soon flow
            ]);
        }

        // 2. Configure payment gateway settings for the tenant
        $settings = $tenant->settings ?? [];
        $settings['payment_gateway'] = [
            'subaccount_status' => 'approved',
            'subaccount_code' => null, // Set to null for local/dev testing so it processes via the main account without "Invalid Subaccount" error
            'bank_name' => 'Access Bank',
            'account_number' => '0123456789',
        ];
        $tenant->update(['settings' => $settings]);

        // 3. Provision the tenant default structures
        $provisioner = app(TenantProvisioner::class);
        $provisioner->provision($tenant);

        // 4. Seed Marketplace Components
        $this->call(MarketplaceComponentSeeder::class);

        // 5. Sync active components but detach a few paid ones so they can be bought
        $allowedSlugs = ['cbt', 'homework', 'e-learning', 'student-dashboard', 'parent-portal', 'payment-gateway', 'messages'];
        $components = MarketplaceComponent::whereIn('slug', $allowedSlugs)->get();
        
        // Detect existing class for this tenant (used for billing targets)
        $seedClass = SchoolClass::where('tenant_id', $tenant->id)->first();
        $seedStudentCount = $seedClass
            ? Student::where('class_id', $seedClass->id)->where('status', 'Active')->count()
            : 0;

        $syncData = [];
        foreach ($components as $component) {
            $syncData[$component->id] = [
                'installed_at'             => now(),
                'uninstalled_at'           => null,
                'status'                   => 'active',
                'setup_fee'                => $component->setup_fee,
                'usage_fee_per_student'    => $component->usage_fee_per_student,
                'price_paid'               => $component->setup_fee,
                'student_count_at_install' => $seedStudentCount,
                // Assign all available classes so billing shows realistic numbers
                'allowed_class_ids'        => $seedClass ? [$seedClass->id] : [],
            ];
        }
        $tenant->marketplaceComponents()->sync($syncData);

        // Detach 'whatsapp-bot' and 'savings-loan' so they can be purchased/installed via the Marketplace flow
        $whatsappBot = MarketplaceComponent::where('slug', 'whatsapp-bot')->first();
        if ($whatsappBot) {
            $tenant->marketplaceComponents()->detach($whatsappBot->id);
        }
        $savingsLoan = MarketplaceComponent::where('slug', 'savings-loan')->first();
        if ($savingsLoan) {
            $tenant->marketplaceComponents()->detach($savingsLoan->id);
        }

        // 6. Seed Accounts (School Admin, Bursar, Parent, Teacher)
        
        // Tenant Admin (School Admin)
        $schoolAdmin = User::query()->updateOrCreate(
            ['email' => 'schooladmin@academyhub.local'],
            [
                'name' => 'School Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'is_super_admin' => false,
                'tenant_id' => $tenant->id,
            ]
        );

        // Bursar
        $bursar = User::query()->updateOrCreate(
            ['email' => 'bursar@academyhub.local'],
            [
                'name' => 'Bursar User',
                'password' => Hash::make('password'),
                'role' => 'bursar',
                'is_active' => true,
                'is_super_admin' => false,
                'tenant_id' => $tenant->id,
            ]
        );

        // Parent
        $parent = User::query()->updateOrCreate(
            ['email' => 'parent1@academyhub.local'],
            [
                'name' => 'John Doe (Parent)',
                'password' => Hash::make('password'),
                'role' => 'parent',
                'is_active' => true,
                'tenant_id' => $tenant->id,
                'custom_fields' => ['phone' => '+2348012345678'],
            ]
        );

        // 7. Ensure students exist and are linked to parent
        $class = SchoolClass::where('tenant_id', $tenant->id)->first();
        $section = Section::where('class_id', $class->id)->first();

        $student = Student::query()->updateOrCreate(
            ['admission_number' => 'ADM-2026-0001', 'tenant_id' => $tenant->id],
            [
                'first_name' => 'Amina',
                'last_name' => 'Yusuf',
                'class_id' => $class->id,
                'section_id' => $section->id,
                'gender' => 'Female',
                'guardian_name' => 'John Doe',
                'guardian_phone' => '+2348012345678',
                'status' => 'Active',
            ]
        );

        $parent->students()->sync([$student->id]);

        // 8. Seed Specific Tuition Fee Structure for parent checkout
        $session = AcademicSession::where('tenant_id', $tenant->id)->where('is_active', true)->first();
        $sessionName = $session ? $session->name : '2026/2027';
        $activeTerm = AcademicTerm::where('tenant_id', $tenant->id)->where('is_active', true)->first();
        $termNumber = $activeTerm ? $activeTerm->term_number : 1;

        FeeStructure::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'class_id' => $class->id,
                'category' => 'Tuition',
                'term' => $termNumber,
                'session' => $sessionName,
            ],
            [
                'amount_due' => 45000.00,
                // Enabled plans representation in DB? In the model, it falls back or reads from config/json.
                // Let's check how enabled plans is stored. It's stored in metadata/settings.
            ]
        );

        // Let's ensure any existing transactions for this term are cleared or clean for parent tuition testing
        // to show full outstanding balance.
        Transaction::query()
            ->where('tenant_id', $tenant->id)
            ->where('student_id', $student->id)
            ->where('category', 'Tuition')
            ->where('term', $termNumber)
            ->where('session', $sessionName)
            ->delete();

        // 9. Seed pending plugin bills for testing Bursar/Admin plugin bill payment
        $cbtComponent = MarketplaceComponent::where('slug', 'cbt')->first();
        if ($cbtComponent) {
            TenantPluginBill::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'marketplace_component_id' => $cbtComponent->id,
                    'bill_type' => 'usage',
                    'term_name' => 'First Term',
                    'session_name' => $sessionName,
                    'status' => 'pending',
                ],
                [
                    'student_count' => 100,
                    'setup_fee' => 0.00,
                    'usage_fee_per_student' => 150.00,
                    'total_due' => 15000.00,
                ]
            );
        }

        $this->command->info('Seeding completed successfully!');
        $this->command->info('----------------------------------------------');
        $this->command->info('Test Accounts Prepared:');
        $this->command->info('1. School Admin (Plugin/Subscription Renewal checkout)');
        $this->command->info('   Email: schooladmin@academyhub.local | Password: password');
        $this->command->info('2. Bursar User (Plugin Billing/ledger)');
        $this->command->info('   Email: bursar@academyhub.local | Password: password');
        $this->command->info('3. Parent User (Tuition checkouts)');
        $this->command->info('   Email: parent1@academyhub.local | Password: password');
        $this->command->info('----------------------------------------------');
    }
}
