<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\TeacherAttendanceMark;
use App\Models\TeacherAttendanceSheet;
use App\Models\User;
use App\Support\AttendanceShiftConfig;
use App\Support\TenantSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAssignedShiftAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_view_and_customize_shift_parameters_and_section_allocations(): void
    {
        $admin = User::where('email', 'admin@academyhub.local')->firstOrFail();

        $class = SchoolClass::firstOrCreate(
            ['name' => 'JSS 1', 'tenant_id' => $admin->tenant_id ?? 1],
            ['level' => 1]
        );

        $section1 = Section::create([
            'class_id'  => $class->id,
            'name'      => 'A',
            'shift'     => 'Western',
            'tenant_id' => $admin->tenant_id ?? 1,
        ]);

        $section2 = Section::create([
            'class_id'  => $class->id,
            'name'      => 'B',
            'shift'     => 'Western',
            'tenant_id' => $admin->tenant_id ?? 1,
        ]);

        // 1. Visit settings page
        $response = $this->actingAs($admin)->get(route('settings.attendance'));
        $response->assertOk();
        $response->assertSee('Attendance & Shift Settings', false);
        $response->assertSee('Western Shift (Morning)');
        $response->assertSee('Islamic Shift (Afternoon)');
        $response->assertSee('Section A');
        $response->assertSee('Section B');

        // 2. Submit customized shift parameters
        $postData = [
            'western_start_time'     => '07:30',
            'western_late_threshold' => '08:45',
            'western_end_time'       => '13:15',
            'islamic_start_time'     => '12:15',
            'islamic_late_threshold' => '13:30',
            'islamic_end_time'       => '17:45',
            'section_shifts'         => [
                $section1->id => 'Western',
                $section2->id => 'Islamic',
            ],
        ];

        $updateResponse = $this->actingAs($admin)->post(route('settings.update-attendance'), $postData);
        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('status');

        // 3. Verify settings were persisted and loaded
        TenantSettings::loadToConfig();

        $westernConfig = AttendanceShiftConfig::getShiftConfig(AttendanceShiftConfig::SHIFT_WESTERN);
        $this->assertSame('07:30:00', $westernConfig['start_time']);
        $this->assertSame('08:45:00', $westernConfig['late_threshold']);
        $this->assertSame('13:15:00', $westernConfig['end_time']);

        $islamicConfig = AttendanceShiftConfig::getShiftConfig(AttendanceShiftConfig::SHIFT_ISLAMIC);
        $this->assertSame('12:15:00', $islamicConfig['start_time']);
        $this->assertSame('13:30:00', $islamicConfig['late_threshold']);
        $this->assertSame('17:45:00', $islamicConfig['end_time']);

        // 4. Verify section shifts updated
        $this->assertSame('Western', $section1->fresh()->getShift());
        $this->assertSame('Islamic', $section2->fresh()->getShift());
    }

    public function test_student_inherits_shift_from_assigned_section(): void
    {
        $class = SchoolClass::firstOrCreate(['name' => 'Primary 1'], ['level' => 1]);

        $westernSection = Section::create([
            'class_id' => $class->id,
            'name'     => 'Morning Class',
            'shift'    => 'Western',
        ]);

        $islamicSection = Section::create([
            'class_id' => $class->id,
            'name'     => 'Tahfeez Class',
            'shift'    => 'Islamic',
        ]);

        $westernStudent = Student::create([
            'admission_number' => 'ADM-WEST-01',
            'first_name'       => 'Bilal',
            'last_name'        => 'West',
            'gender'           => 'Male',
            'class_id'         => $class->id,
            'section_id'       => $westernSection->id,
            'status'           => 'Active',
        ]);

        $islamicStudent = Student::create([
            'admission_number' => 'ADM-ISL-01',
            'first_name'       => 'Aisha',
            'last_name'        => 'Noor',
            'gender'           => 'Female',
            'class_id'         => $class->id,
            'section_id'       => $islamicSection->id,
            'status'           => 'Active',
        ]);

        $this->assertSame('Western', $westernStudent->getShift());
        $this->assertSame('Western Section', $westernStudent->getShiftLabel());

        $this->assertSame('Islamic', $islamicStudent->getShift());
        $this->assertSame('Islamic Section', $islamicStudent->getShiftLabel());
    }

    public function test_punch_evaluation_respects_user_defined_custom_shift_thresholds(): void
    {
        $admin = User::where('email', 'admin@academyhub.local')->firstOrFail();
        $tenantId = $admin->tenant_id ?? 1;

        // Admin customizes Western late cutoff to 08:45 AM (instead of default 08:15 AM)
        // and Islamic late cutoff to 13:30 PM (instead of default 12:45 PM)
        $this->actingAs($admin)->post(route('settings.update-attendance'), [
            'western_start_time'     => '07:00',
            'western_late_threshold' => '08:45',
            'western_end_time'       => '13:00',
            'islamic_start_time'     => '12:00',
            'islamic_late_threshold' => '13:30',
            'islamic_end_time'       => '17:30',
        ]);

        TenantSettings::loadToConfig();

        // 1. Check evaluateStatus directly with tenant id
        // Under old 08:15 cutoff, 08:30 was Late; under 08:45 cutoff, it is Present!
        $this->assertSame('Present', AttendanceShiftConfig::evaluateStatus('08:30:00', 'Western', $tenantId));
        $this->assertSame('Late', AttendanceShiftConfig::evaluateStatus('08:50:00', 'Western', $tenantId));

        // Under old 12:45 cutoff, 13:10 was Late; under 13:30 cutoff, it is Present!
        $this->assertSame('Present', AttendanceShiftConfig::evaluateStatus('13:10:00', 'Islamic', $tenantId));
        $this->assertSame('Late', AttendanceShiftConfig::evaluateStatus('13:40:00', 'Islamic', $tenantId));

        // 2. Hardware biometric punch check
        $westernTeacher = User::factory()->create([
            'role'          => 'teacher',
            'is_active'     => true,
            'tenant_id'     => $tenantId,
            'custom_fields' => ['shift' => 'Western', 'k40_uid' => 881],
        ]);

        $islamicTeacher = User::factory()->create([
            'role'          => 'teacher',
            'is_active'     => true,
            'tenant_id'     => $tenantId,
            'custom_fields' => ['shift' => 'Islamic', 'k40_uid' => 882],
        ]);

        $dateStr = '2026-09-23';

        // Punch Western teacher at 08:30:00 -> should be Present because late cutoff is 08:45:00
        $payload = "881\t{$dateStr} 08:30:00\t0\t1\n";
        $response = $this->call('POST', '/iclock/cdata?table=ATTLOG&SN=FDED260401004', [], [], [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload);
        $response->assertOk();

        $sheet = TeacherAttendanceSheet::where('date', $dateStr)->first();
        $this->assertNotNull($sheet);

        $westernMark = TeacherAttendanceMark::where('sheet_id', $sheet->id)
            ->where('teacher_id', $westernTeacher->id)
            ->first();
        $this->assertNotNull($westernMark);
        $this->assertSame('Present', $westernMark->status);

        // Punch Islamic teacher at 13:15:00 -> should be Present because late cutoff is 13:30:00
        $payload2 = "882\t{$dateStr} 13:15:00\t0\t1\n";
        $response2 = $this->call('POST', '/iclock/cdata?table=ATTLOG&SN=FDED260401009', [], [], [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload2);
        $response2->assertOk();

        $islamicMark = TeacherAttendanceMark::where('sheet_id', $sheet->id)
            ->where('teacher_id', $islamicTeacher->id)
            ->first();
        $this->assertNotNull($islamicMark);
        $this->assertSame('Present', $islamicMark->status);
    }

    public function test_section_controller_handles_shift_assignment_and_quick_toggles(): void
    {
        $admin = User::where('email', 'admin@academyhub.local')->firstOrFail();
        $class = SchoolClass::firstOrCreate(['name' => 'SS 2'], ['level' => 11]);

        // 1. Create section with explicit shift
        $response = $this->actingAs($admin)->post(route('sections.store', $class), [
            'name'  => 'Science',
            'shift' => 'Islamic',
        ]);
        $response->assertRedirect();

        $section = Section::where('class_id', $class->id)->where('name', 'SCIENCE')->firstOrFail();
        $this->assertSame('Islamic', $section->getShift());

        // 2. Toggle shift to Western
        $updateResponse = $this->actingAs($admin)->patch(route('sections.update', ['class' => $class, 'section' => $section]), [
            'shift' => 'Western',
        ]);
        $updateResponse->assertRedirect();
        $this->assertSame('Western', $section->fresh()->getShift());
    }

    protected function tearDown(): void
    {
        $globalPath = storage_path('app/academyhub/settings.json');
        if (file_exists($globalPath)) {
            @unlink($globalPath);
        }
        $tenantPath = storage_path('app/academyhub/tenants/1/settings.json');
        if (file_exists($tenantPath)) {
            @unlink($tenantPath);
        }

        TenantSettings::clearCache();
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Artisan::call('config:clear');

        // Reset runtime config to defaults
        config(['academyhub.western_start_time' => null]);
        config(['academyhub.western_late_threshold' => null]);
        config(['academyhub.western_end_time' => null]);
        config(['academyhub.islamic_start_time' => null]);
        config(['academyhub.islamic_late_threshold' => null]);
        config(['academyhub.islamic_end_time' => null]);
        config(['myacademy.western_start_time' => null]);
        config(['myacademy.western_late_threshold' => null]);
        config(['myacademy.western_end_time' => null]);
        config(['myacademy.islamic_start_time' => null]);
        config(['myacademy.islamic_late_threshold' => null]);
        config(['myacademy.islamic_end_time' => null]);

        parent::tearDown();
    }
}
