<?php

namespace Tests\Feature;

use App\Livewire\Users\Index as UsersIndex;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProprietorRoleTest extends TestCase
{
    use RefreshDatabase;

    private function setupTenantAndProprietor(): array
    {
        $tenant = Tenant::create([
            'name'   => 'Excellence Academy',
            'slug'   => 'excellence',
            'domain' => 'excellence.local',
        ]);

        app()->instance('currentTenant', $tenant);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@excellence.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'tenant_id' => $tenant->id,
        ]);

        $proprietor = User::create([
            'name' => 'Alhaji Proprietor',
            'email' => 'owner@excellence.local',
            'password' => bcrypt('password'),
            'role' => 'proprietor',
            'is_active' => true,
            'tenant_id' => $tenant->id,
        ]);

        return [$tenant, $admin, $proprietor];
    }

    public function test_admin_can_create_user_with_proprietor_role(): void
    {
        [$tenant, $admin] = $this->setupTenantAndProprietor();

        Livewire::actingAs($admin)
            ->test(UsersIndex::class)
            ->set('name', 'Hajiya Owner')
            ->set('email', 'proprietress@excellence.local')
            ->set('role', 'proprietor')
            ->set('isActive', true)
            ->set('password', 'password123')
            ->call('createUser');

        $this->assertDatabaseHas('users', [
            'email' => 'proprietress@excellence.local',
            'role' => 'proprietor',
            'is_active' => 1,
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_proprietor_lands_on_executive_dashboard(): void
    {
        [$tenant, $admin, $proprietor] = $this->setupTenantAndProprietor();

        $response = $this->actingAs($proprietor)->get('/dashboard');

        $response->assertOk();
        $response->assertViewIs('pages.dashboard-proprietor');
        $response->assertViewHasAll([
            'totalStudents',
            'totalTeachers',
            'totalCollected',
            'outstandingDebt',
            'starStudents',
            'watchlistStudents',
            'classRankings',
        ]);
    }

    public function test_proprietor_dashboard_calculates_best_and_low_performers(): void
    {
        [$tenant, $admin, $proprietor] = $this->setupTenantAndProprietor();

        $class = SchoolClass::create([
            'name' => 'JSS 1 Gold',
            'level' => 1,
            'tenant_id' => $tenant->id,
        ]);

        $section = Section::create([
            'class_id' => $class->id,
            'name' => 'A',
            'tenant_id' => $tenant->id,
        ]);

        $subject = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MTH',
            'tenant_id' => $tenant->id,
        ]);

        $starStudent = Student::create([
            'first_name' => 'Zainab',
            'last_name' => 'Bello',
            'admission_number' => 'STU-001',
            'gender' => 'Female',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'tenant_id' => $tenant->id,
        ]);

        $lowStudent = Student::create([
            'first_name' => 'Musa',
            'last_name' => 'Aliyu',
            'admission_number' => 'STU-002',
            'gender' => 'Male',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'tenant_id' => $tenant->id,
        ]);

        // Zainab scores 92
        Score::create([
            'student_id' => $starStudent->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'term' => 1,
            'session' => '2026/2027',
            'ca1' => 18,
            'ca2' => 18,
            'exam' => 56,
            'total' => 92,
            'tenant_id' => $tenant->id,
        ]);

        // Musa scores 28
        Score::create([
            'student_id' => $lowStudent->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'term' => 1,
            'session' => '2026/2027',
            'ca1' => 5,
            'ca2' => 5,
            'exam' => 18,
            'total' => 28,
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($proprietor)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Zainab Bello');
        $response->assertSee('Musa Aliyu');
        $response->assertSee('92%');
        $response->assertSee('28%');
    }

    public function test_proprietor_is_prevented_from_mutating_data(): void
    {
        [$tenant, $admin, $proprietor] = $this->setupTenantAndProprietor();

        // Attempt to create a teacher via POST
        $response = $this->actingAs($proprietor)->post('/teachers', [
            'name' => 'Rogue Teacher',
            'email' => 'rogue@excellence.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_proprietor_can_access_read_only_reports(): void
    {
        [$tenant, $admin, $proprietor] = $this->setupTenantAndProprietor();

        // Staff Timesheet
        $responseTimesheet = $this->actingAs($proprietor)->get('/attendance/staff-timesheet');
        $responseTimesheet->assertOk();

        // Academic Broadsheet
        $responseBroadsheet = $this->actingAs($proprietor)->get('/results/broadsheet');
        $responseBroadsheet->assertOk();

        // Tuition & Debtors (both with and without tab parameter)
        $responseBilling = $this->actingAs($proprietor)->get('/billing?tab=debtors');
        $responseBilling->assertOk();

        $responseBillingRoot = $this->actingAs($proprietor)->get('/billing');
        $responseBillingRoot->assertOk();

        // My Profile
        $responseProfile = $this->actingAs($proprietor)->get('/profile');
        $responseProfile->assertOk();
    }

    public function test_proprietor_sees_all_classes_in_broadsheet(): void
    {
        [$tenant, $admin, $proprietor] = $this->setupTenantAndProprietor();

        $class = SchoolClass::create([
            'name' => 'Primary 5 Diamonds',
            'level' => 5,
            'tenant_id' => $tenant->id,
        ]);

        Livewire::actingAs($proprietor)
            ->test(\App\Livewire\Results\Broadsheet::class)
            ->assertSee('Primary 5 Diamonds');
    }

    public function test_proprietor_can_view_cbt_and_savings_loan(): void
    {
        [$tenant, $admin, $proprietor] = $this->setupTenantAndProprietor();

        $responseSavings = $this->actingAs($proprietor)->get('/savings-loan');
        $responseSavings->assertOk();

        $responseEvents = $this->actingAs($proprietor)->get('/events');
        $responseEvents->assertOk();
    }

    public function test_proprietor_api_token_cannot_mutate_data(): void
    {
        [$tenant, $admin, $proprietor] = $this->setupTenantAndProprietor();

        $token = $proprietor->createToken('test-token')->plainTextToken;

        // Attempt mutating API POST endpoint with token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/homework', [
                'title' => 'Rogue Homework',
            ]);

        $response->assertStatus(403);
    }

    public function test_proprietor_can_export_financial_analytics(): void
    {
        [$tenant, $admin, $proprietor] = $this->setupTenantAndProprietor();

        $response = $this->actingAs($proprietor)->get('/analytics/export/financial');
        $response->assertOk();
    }
}
