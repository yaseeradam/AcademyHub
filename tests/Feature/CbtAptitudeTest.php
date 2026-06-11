<?php

namespace Tests\Feature;

use App\Livewire\Cbt\Portal\Start as CbtPortalStart;
use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CbtAptitudeTest extends TestCase
{
    use RefreshDatabase;

    public function test_aptitude_exam_can_be_started_with_candidate_name_and_no_student_registered(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        $tenant = \App\Models\Tenant::first();
        app()->instance('currentTenant', $tenant);

        $exam = CbtExam::query()->create([
            'tenant_id' => 1,
            'title' => 'Aptitude Test',
            'exam_type' => 'aptitude',
            'access_code' => 'APT123',
            'status' => 'live',
            'published_at' => now(),
            'starts_at' => now()->subHour(),
            'created_by' => $admin->id,
        ]);

        $studentCountBefore = Student::count();

        // Start the exam with a candidate name
        Livewire::test(CbtPortalStart::class)
            ->set('examCode', 'APT123')
            ->set('admissionNumber', 'John Doe')
            ->call('start')
            ->assertRedirect();

        // Verify that no student record was registered/created
        $this->assertEquals($studentCountBefore, Student::count());

        // Verify that a CbtAttempt was created with candidate_name John Doe and student_id is null
        $this->assertDatabaseHas('cbt_attempts', [
            'exam_id' => $exam->id,
            'candidate_name' => 'John Doe',
            'student_id' => null,
        ]);
    }

    public function test_school_staff_can_download_result_pdf_but_candidate_cannot(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        $tenant = \App\Models\Tenant::first();
        app()->instance('currentTenant', $tenant);

        $exam = CbtExam::query()->create([
            'tenant_id' => 1,
            'title' => 'Aptitude Test',
            'exam_type' => 'aptitude',
            'access_code' => 'APT123',
            'status' => 'live',
            'published_at' => now(),
            'starts_at' => now()->subHour(),
            'created_by' => $admin->id,
        ]);

        // Create a submitted attempt
        $attempt = CbtAttempt::query()->create([
            'tenant_id' => 1,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'exam_id' => $exam->id,
            'student_id' => null,
            'candidate_name' => 'John Doe',
            'started_at' => now()->subHour(),
            'submitted_at' => now(),
        ]);

        // 1. Staff (Admin) can download PDF
        $response = $this->actingAs($admin)
            ->get(route('cbt.attempt.export-pdf', ['attempt' => $attempt->uuid]));

        $response->assertStatus(200);

        // 2. Candidate / Guest cannot download PDF (auth middleware on the route stops them since they are not logged in)
        $this->app['auth']->logout();

        $response = $this->get(route('cbt.attempt.export-pdf', ['attempt' => $attempt->uuid]));
        $response->assertRedirect(route('login'));
    }

    public function test_aptitude_candidate_can_access_take_exam_even_if_student_dashboard_plugin_is_not_installed(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        $tenant = \App\Models\Tenant::first();
        app()->instance('currentTenant', $tenant);

        // Deactivate student-dashboard plugin for this tenant
        $tenant->activeMarketplaceComponents()->where('slug', 'student-dashboard')->delete();

        $exam = CbtExam::query()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Aptitude Test',
            'exam_type' => 'aptitude',
            'access_code' => 'APT123',
            'status' => 'live',
            'published_at' => now(),
            'starts_at' => now()->subHour(),
            'created_by' => $admin->id,
        ]);

        $attempt = CbtAttempt::query()->create([
            'tenant_id' => $tenant->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'exam_id' => $exam->id,
            'student_id' => null,
            'candidate_name' => 'John Doe',
            'started_at' => now(),
        ]);

        // Access route as candidate (mock session keys)
        $response = $this->withSession([
            'tenant_id'         => $tenant->id,
            'student_id'        => $attempt->id,
            'student_name'      => 'John Doe',
            'student_admission' => 'APT-123456',
            'student_class'     => 'Aptitude',
            'login_type'        => 'aptitude',
            'aptitude_attempt_id' => $attempt->id,
        ])->get(route('cbt.student.take', ['attempt' => $attempt->uuid]));

        $response->assertStatus(200);
    }

    public function test_staff_login_clears_old_student_session_data(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        $tenant = \App\Models\Tenant::first();
        app()->instance('currentTenant', $tenant);

        $admin->password = bcrypt('password');
        $admin->tenant_id = $tenant->id;
        $admin->is_super_admin = false;
        $admin->save();

        $response = $this->withSession([
            'student_id'        => 999,
            'login_type'        => 'aptitude',
            'aptitude_attempt_id' => 999,
        ])->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
            'login_type' => 'staff',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertNull(session('student_id'));
        $this->assertNull(session('login_type'));
        $this->assertNull(session('aptitude_attempt_id'));
    }
}
