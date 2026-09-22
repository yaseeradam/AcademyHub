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

            // 5. Create default Classes, Sections & Subject for demo tenant
            $class = SchoolClass::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'JSS 1'],
                ['level' => 1]
            );

            $section = Section::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'class_id' => $class->id, 'name' => 'Gold']
            );

            $class2 = SchoolClass::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'JSS 2'],
                ['level' => 2]
            );

            $section2A = Section::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'class_id' => $class2->id, 'name' => 'A']
            );

            $section2B = Section::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'class_id' => $class2->id, 'name' => 'B']
            );

            $subject = \App\Models\Subject::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'MTH'],
                ['name' => 'Mathematics']
            );

            $academicSession = AcademicSession::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => '2025/2026'],
                ['is_active' => true]
            );

            AcademicTerm::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'academic_session_id' => $academicSession->id, 'term_number' => 1],
                ['name' => 'First Term', 'is_active' => true]
            );

            foreach ($components as $component) {
                $tenant->marketplaceComponents()->updateExistingPivot($component->id, [
                    'allowed_class_ids' => [$class->id, $class2->id],
                ]);
            }

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
                    'email'    => 'teacher@academyhub.local',
                    'name'     => 'Demo Teacher Local',
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
                    'email'    => 'bursar@academyhub.local',
                    'name'     => 'Demo Bursar Local',
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
                $createdUsers[] = $user;
            }

            // Allocate all teachers to demo classes with default subject
            foreach ($createdUsers as $u) {
                if ($u->role === 'teacher') {
                    \App\Models\SubjectAllocation::query()->firstOrCreate([
                        'tenant_id'  => $tenant->id,
                        'teacher_id' => $u->id,
                        'subject_id' => $subject->id,
                        'class_id'   => $class->id,
                    ]);

                    \App\Models\SubjectAllocation::query()->firstOrCreate([
                        'tenant_id'  => $tenant->id,
                        'teacher_id' => $u->id,
                        'subject_id' => $subject->id,
                        'class_id'   => $class2->id,
                    ]);
                }
            }

            // 7. Create Student Accounts (for Class 1 and Class 2)
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

            $student2 = Student::query()->updateOrCreate(
                ['admission_number' => 'STU002', 'tenant_id' => $tenant->id],
                [
                    'first_name'      => 'Jane',
                    'last_name'       => 'Student',
                    'class_id'        => $class2->id,
                    'section_id'      => $section2A->id,
                    'gender'          => 'Female',
                    'dob'             => '2011-05-10',
                    'guardian_name'   => 'Demo Parent',
                    'guardian_phone'  => '08000000000',
                    'guardian_address'=> '123 Demo Street',
                    'status'          => 'Active',
                    'password'        => $passwordHash,
                ]
            );

            // Link parent to students
            $parentUser = collect($createdUsers)->firstWhere('role', 'parent');
            if ($parentUser) {
                $parentUser->students()->syncWithoutDetaching([$student->id, $student2->id]);
            }
        });
    }
}
