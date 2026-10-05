<?php

namespace Tests\Feature;

use App\Livewire\Biometrics\Index;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\TeacherAttendanceMark;
use App\Models\TeacherAttendanceSheet;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GatePunchLogsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_gate_punch_logs_display_student_and_staff_scans(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $student = Student::firstOrFail();
        $teacher = User::where('role', 'teacher')->firstOrFail();

        // Initially no gate scans today
        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertSee("Gate Punch Logs")
            ->assertSee("No Biometric Scans Recorded Today")
            ->call('triggerTestPopup')
            ->assertSee($student->full_name)
            ->assertSee('Student')
            ->call('triggerStaffScanTest')
            ->assertSee($teacher->name)
            ->set('punchTypeFilter', 'students')
            ->assertSee($student->full_name)
            ->set('punchTypeFilter', 'staff')
            ->assertSee($teacher->name)
            ->set('punchTypeFilter', 'all')
            ->call('triggerDepartureTest')
            ->assertSee('Departed')
            ->call('triggerStaffScanTest')
            ->set('punchTypeFilter', 'staff')
            ->assertSee('Out');
    }
}
