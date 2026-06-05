<?php

namespace Tests\Feature;

use App\Livewire\Attendance\Index as AttendanceIndex;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_attendance_page(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        $this->actingAs($admin)
            ->get('/attendance')
            ->assertOk()
            ->assertSee('Attendance');
    }

    public function test_attendance_can_be_started_and_saved(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        $class = SchoolClass::query()->where('name', 'JSS 2')->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->where('name', 'A')->firstOrFail();
        $student = Student::query()->where('class_id', $class->id)->where('section_id', $section->id)->firstOrFail();

        Livewire::actingAs($admin)
            ->test(AttendanceIndex::class)
            ->set('classId', $class->id)
            ->set('sectionId', $section->id)
            ->set('date', '2026-02-07')
            ->set('term', 1)
            ->set('session', '2026/2027')
            ->call('start')
            ->set("marks.{$student->id}.status", 'Absent')
            ->set("marks.{$student->id}.note", 'Sick')
            ->call('save')
            ->assertSet('sheetId', fn ($id) => is_int($id) && $id > 0);

        $sheet = AttendanceSheet::query()->firstOrFail();
        $this->assertSame($admin->id, $sheet->taken_by);
        $this->assertSame($class->id, $sheet->class_id);
        $this->assertSame($section->id, $sheet->section_id);
        $this->assertSame('2026-02-07', $sheet->date->toDateString());

        $mark = AttendanceMark::query()->where('sheet_id', $sheet->id)->where('student_id', $student->id)->firstOrFail();
        $this->assertSame('Absent', $mark->status);
        $this->assertSame('Sick', $mark->note);
    }

    public function test_starting_same_sheet_twice_does_not_duplicate(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $class = SchoolClass::query()->where('name', 'JSS 2')->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->where('name', 'A')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(AttendanceIndex::class)
            ->set('classId', $class->id)
            ->set('sectionId', $section->id)
            ->set('date', '2026-02-07')
            ->set('term', 1)
            ->set('session', '2026/2027')
            ->call('start')
            ->call('start');

        $this->assertSame(1, AttendanceSheet::query()->count());
    }
}

