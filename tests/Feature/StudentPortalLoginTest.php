<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\CbtExam;
use App\Models\CbtAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_login_with_default_password_and_then_with_custom_password(): void
    {
        $this->seed();

        $class = SchoolClass::query()->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->firstOrFail();

        $tenant = Tenant::firstOrFail();
        app()->instance('currentTenant', $tenant);

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-2026-1234',
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Female',
            'status' => 'Active',
        ]);

        $defaultPassword = 'amina1234';

        $this->post('/login', [
            'login_type' => 'student',
            'admission_number' => $student->admission_number,
            'password' => $defaultPassword,
        ])->assertRedirect(route('student.exams'));

        $this->assertEquals($student->id, session('student_id'));

        $student->password = 'NewPass123';
        $student->save();

        $this->from('/login')->post('/login', [
            'login_type' => 'student',
            'admission_number' => $student->admission_number,
            'password' => $defaultPassword,
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('password');

        $this->post('/login', [
            'login_type' => 'student',
            'admission_number' => $student->admission_number,
            'password' => 'NewPass123',
        ])->assertRedirect(route('student.exams'));
    }

    public function test_student_login_honors_intended_url(): void
    {
        $this->seed();

        $class = SchoolClass::query()->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->firstOrFail();
        $tenant = Tenant::firstOrFail();
        app()->instance('currentTenant', $tenant);

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-2026-INTENDED',
            'first_name' => 'Intended',
            'last_name' => 'Student',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $defaultPassword = 'intendedNDED';

        session(['url.intended' => route('student.results')]);

        $this->post('/login', [
            'login_type' => 'student',
            'admission_number' => $student->admission_number,
            'password' => $defaultPassword,
        ])->assertRedirect(route('student.results'));
    }

    public function test_student_cannot_login_if_student_dashboard_plugin_is_not_installed(): void
    {
        $this->seed();

        $class = SchoolClass::query()->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->firstOrFail();
        $tenant = Tenant::firstOrFail();
        app()->instance('currentTenant', $tenant);

        // Deactivate student-dashboard plugin for the tenant
        $component = \App\Models\MarketplaceComponent::where('slug', 'student-dashboard')->firstOrFail();
        $tenant->marketplaceComponents()->updateExistingPivot($component->id, [
            'uninstalled_at' => now(),
        ]);

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-2026-9999',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $defaultPassword = 'test9999';

        // 1. Attempt login -> Should fail
        $this->post('/login', [
            'login_type' => 'student',
            'admission_number' => $student->admission_number,
            'password' => $defaultPassword,
        ])->assertSessionHasErrors('admission_number');

        // 2. Mock logged-in session, attempt dashboard access -> Should redirect to login
        session([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'student_admission' => $student->admission_number,
            'student_class' => $class->name,
            'login_type' => 'student',
        ]);

        $this->get('/student/dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHas('warning');
    }

    public function test_student_cannot_login_if_class_is_not_allowed_for_student_dashboard(): void
    {
        $this->seed();

        $class1 = SchoolClass::query()->firstOrFail();
        $class2 = SchoolClass::query()->create([
            'tenant_id' => $class1->tenant_id,
            'name' => 'Restricted Class',
            'level' => 1,
        ]);
        $section2 = Section::query()->create([
            'tenant_id' => $class1->tenant_id,
            'class_id' => $class2->id,
            'name' => 'A',
        ]);

        $tenant = Tenant::firstOrFail();
        app()->instance('currentTenant', $tenant);

        // Update pivot table for student-dashboard to only allow class1
        $component = \App\Models\MarketplaceComponent::where('slug', 'student-dashboard')->firstOrFail();
        $tenant->marketplaceComponents()->updateExistingPivot($component->id, [
            'allowed_class_ids' => [$class1->id],
        ]);

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-2026-8888',
            'first_name' => 'Banned',
            'last_name' => 'Student',
            'class_id' => $class2->id, // class2 is not allowed
            'section_id' => $section2->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $defaultPassword = 'banned8888';

        $this->post('/login', [
            'login_type' => 'student',
            'admission_number' => $student->admission_number,
            'password' => $defaultPassword,
        ])->assertSessionHasErrors('admission_number');

        $this->assertNull(session('student_id'));
    }

    public function test_student_cannot_login_if_tenant_subscription_is_expired(): void
    {
        $this->seed();

        $class = SchoolClass::query()->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->firstOrFail();
        $tenant = Tenant::firstOrFail();
        app()->instance('currentTenant', $tenant);

        // Make subscription expired by setting expires_at to past
        $tenant->update([
            'expires_at' => now()->subDay(),
        ]);
        // Save tenant to trigger the booted boot method update for settings.json and settingsCacheKey
        $tenant->save();

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-2026-7777',
            'first_name' => 'Expired',
            'last_name' => 'Student',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $defaultPassword = 'expired7777';

        $this->post('/login', [
            'login_type' => 'student',
            'admission_number' => $student->admission_number,
            'password' => $defaultPassword,
        ])->assertSessionHasErrors('admission_number');

        $this->assertNull(session('student_id'));
    }

    public function test_student_can_access_cbt_routes_even_if_student_dashboard_is_disabled(): void
    {
        $this->seed();

        $class = SchoolClass::query()->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->firstOrFail();
        $tenant = Tenant::firstOrFail();
        app()->instance('currentTenant', $tenant);

        // Deactivate student-dashboard plugin for the tenant
        $component = \App\Models\MarketplaceComponent::where('slug', 'student-dashboard')->firstOrFail();
        $tenant->marketplaceComponents()->updateExistingPivot($component->id, [
            'uninstalled_at' => now(),
        ]);

        // Ensure cbt plugin is active
        $cbtComponent = \App\Models\MarketplaceComponent::where('slug', 'cbt')->firstOrFail();
        $tenant->marketplaceComponents()->updateExistingPivot($cbtComponent->id, [
            'uninstalled_at' => null,
        ]);

        $student = Student::query()->create([
            'tenant_id' => $tenant->id,
            'admission_number' => 'ADM-2026-8888',
            'first_name' => 'Cbt',
            'last_name' => 'Test',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        // Create exam and attempt
        $subject = \App\Models\Subject::query()->create(['tenant_id' => $tenant->id, 'name' => 'Physics', 'code' => 'PHY']);
        $admin = User::withoutGlobalScope('tenant')->where('email', 'admin@academyhub.local')->firstOrFail();
        $exam = CbtExam::query()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Physics Test',
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'duration_minutes' => 60,
            'access_code' => 'PHY101',
            'status' => 'live',
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);

        $attempt = CbtAttempt::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'last_activity_at' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        // Mock student session
        session([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'student_admission' => $student->admission_number,
            'student_class' => $class->name,
            'login_type' => 'student',
        ]);

        // Access the cbt student take page -> Should be OK (not redirect to login)
        $response = $this->get(route('cbt.student.take', ['attempt' => $attempt, 'code' => 'PHY101']));
        $response->assertOk();
    }
}

