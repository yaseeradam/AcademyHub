<?php

namespace Tests\Feature;

use App\Livewire\Attendance\StaffTimesheet;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\TeacherAttendanceMark;
use App\Models\TeacherAttendanceSheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class K40BiometricsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_k40_dual_shift_rules_and_signout_recording(): void
    {
        // 1. Create a Western teacher and an Islamic teacher
        $westernTeacher = User::factory()->create([
            'role' => 'teacher',
            'is_active' => true,
            'custom_fields' => [
                'shift' => 'Western',
                'k40_uid' => 101,
            ],
        ]);

        $islamicTeacher = User::factory()->create([
            'role' => 'teacher',
            'is_active' => true,
            'custom_fields' => [
                'shift' => 'Islamic',
                'k40_uid' => 102,
            ],
        ]);

        $this->assertSame('Western', $westernTeacher->getShift());
        $this->assertSame('Islamic', $islamicTeacher->getShift());

        $dateStr = '2026-09-22';

        // 2. Punch in Western Teacher at 08:10:00 (Before 08:15 AM Western cutoff -> Present)
        $payload1 = "101\t{$dateStr} 08:10:00\t0\t1\n";
        $response1 = $this->call('POST', '/iclock/cdata?table=ATTLOG&SN=FDED260401004', [], [], [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload1);

        $response1->assertOk();

        $sheet = TeacherAttendanceSheet::where('date', $dateStr)->first();
        $this->assertNotNull($sheet);

        $westernMark = TeacherAttendanceMark::where('sheet_id', $sheet->id)
            ->where('teacher_id', $westernTeacher->id)
            ->first();
        $this->assertNotNull($westernMark);
        $this->assertSame('Present', $westernMark->status);
        $this->assertSame('08:10:00', $westernMark->punch_in_time);
        $this->assertNull($westernMark->punch_out_time);

        // 3. Western Teacher punches OUT at 14:30:00 (Check Sign-out logic)
        $payload2 = "101\t{$dateStr} 14:30:00\t0\t1\n";
        $response2 = $this->call('POST', '/iclock/cdata?table=ATTLOG&SN=FDED260401004', [], [], [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload2);

        $response2->assertOk();

        $westernMark->refresh();
        $this->assertSame('Present', $westernMark->status); // Status preserved!
        $this->assertSame('08:10:00', $westernMark->punch_in_time);
        $this->assertSame('14:30:00', $westernMark->punch_out_time); // Sign-out recorded!
        $this->assertStringContainsString('In: 8:10 AM | Out: 2:30 PM', $westernMark->note);

        // 4. Punch in Islamic Teacher at 12:55:00 (After 12:45 PM Islamic cutoff -> Late)
        $payload3 = "102\t{$dateStr} 12:55:00\t0\t1\n";
        $response3 = $this->call('POST', '/iclock/cdata?table=ATTLOG&SN=FDED260401009', [], [], [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload3);

        $response3->assertOk();

        $islamicMark = TeacherAttendanceMark::where('sheet_id', $sheet->id)
            ->where('teacher_id', $islamicTeacher->id)
            ->first();
        $this->assertNotNull($islamicMark);
        $this->assertSame('Late', $islamicMark->status);
        $this->assertSame('12:55:00', $islamicMark->punch_in_time);

        // 5. Islamic Teacher punches OUT at 17:00:00
        $payload4 = "102\t{$dateStr} 17:00:00\t0\t1\n";
        $response4 = $this->call('POST', '/iclock/cdata?table=ATTLOG&SN=FDED260401009', [], [], [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload4);

        $response4->assertOk();

        $islamicMark->refresh();
        $this->assertSame('Late', $islamicMark->status);
        $this->assertSame('12:55:00', $islamicMark->punch_in_time);
        $this->assertSame('17:00:00', $islamicMark->punch_out_time);
        $this->assertStringContainsString('In: 12:55 PM | Out: 5:00 PM', $islamicMark->note);

        // 6. Verify StaffTimesheet component handles the marks properly
        $admin = User::where('email', 'admin@academyhub.local')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(StaffTimesheet::class)
            ->set('selectedMonth', '2026-09')
            ->call('openBreakdown', $westernTeacher->id)
            ->assertSet('showBreakdownModal', true)
            ->assertSee('8:10 AM')
            ->assertSee('2:30 PM');
    }
}
