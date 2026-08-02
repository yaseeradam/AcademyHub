<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\MarketplaceComponent;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Create/Update Marketplace Components
            $this->call(MarketplaceComponentSeeder::class);

            // 2. Create or Update Tenant for demo.academyhub.com.ng
            $tenant = Tenant::query()->where('slug', 'demo')
                ->orWhere('domain', 'demo.academyhub.com.ng')
                ->first();

            if (! $tenant) {
                $tenant = Tenant::query()->create([
                    'name'          => 'Demo Academy',
                    'slug'          => 'demo',
                    'domain'        => 'demo.academyhub.com.ng',
                    'plan'          => 'pro',
                    'status'        => 'active',
                    'max_students'  => 500,
                    'max_teachers'  => 50,
                    'contact_email' => 'admin@demo.academyhub.com.ng',
                    'contact_phone' => '08000000000',
                ]);
            } else {
                $tenant->update([
                    'name'   => 'Demo Academy',
                    'slug'   => 'demo',
                    'domain' => 'demo.academyhub.com.ng',
                    'status' => 'active',
                ]);
            }

            // 3. Provision Tenant Settings & Academic Calendar
            /** @var TenantProvisioner $provisioner */
            $provisioner = app(TenantProvisioner::class);
            $provisioner->provision($tenant);

            // 4. Install all Marketplace Components for Demo Tenant
            $components = MarketplaceComponent::all();
            foreach ($components as $component) {
                $tenant->marketplaceComponents()->syncWithoutDetaching([
                    $component->id => [
                        'installed_at'            => now(),
                        'uninstalled_at'          => null,
                        'status'                  => 'active',
                        'price_paid'              => 0,
                        'setup_fee'               => 0,
                        'usage_fee_per_student'   => 0,
                        'student_count_at_install'=> 0,
                        'allowed_class_ids'       => null,
                    ],
                ]);
            }

            // 5. Create default Class & Section for demo tenant
            $class = SchoolClass::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'JSS 1'],
                ['code' => 'JSS1', 'description' => 'Junior Secondary School 1']
            );

            $section = Section::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'class_id' => $class->id, 'name' => 'Gold']
            );

            $passwordHash = Hash::make('password');

            // 6. Create Staff / Parent Accounts
            $users = [
                [
                    'email'    => 'admin@demo.academyhub.com.ng',
                    'name'     => 'Demo Admin',
                    'role'     => 'admin',
                    'is_admin' => true,
                ],
                [
                    'email'    => 'teacher@demo.academyhub.com.ng',
                    'name'     => 'Demo Teacher',
                    'role'     => 'teacher',
                    'is_admin' => false,
                ],
                [
                    'email'    => 'bursar@demo.academyhub.com.ng',
                    'name'     => 'Demo Bursar',
                    'role'     => 'bursar',
                    'is_admin' => false,
                ],
                [
                    'email'    => 'parent@demo.academyhub.com.ng',
                    'name'     => 'Demo Parent',
                    'role'     => 'parent',
                    'is_admin' => false,
                ],
            ];

            $createdUsers = [];

            foreach ($users as $userData) {
                $user = User::query()->updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name'           => $userData['name'],
                        'password'       => $passwordHash,
                        'role'           => $userData['role'],
                        'is_active'      => true,
                        'tenant_id'      => $tenant->id,
                        'is_super_admin' => false,
                    ]
                );
                $createdUsers[$userData['role']] = $user;
            }

            // 7. Create Student Account
            $student = Student::query()->updateOrCreate(
                ['admission_number' => 'STU001', 'tenant_id' => $tenant->id],
                [
                    'first_name'      => 'Demo',
                    'last_name'       => 'Student',
                    'class_id'        => $class->id,
                    'section_id'      => $section->id,
                    'gender'          => 'Male',
                    'dob'             => '2012-01-15',
                    'guardian_name'   => 'Demo Parent',
                    'guardian_phone'  => '08000000000',
                    'guardian_address'=> '123 Demo Street',
                    'status'          => 'Active',
                    'password'        => $passwordHash,
                ]
            );

            // Link parent to student
            if (isset($createdUsers['parent'])) {
                $createdUsers['parent']->students()->syncWithoutDetaching([$student->id]);
            }
        });
    }
}
