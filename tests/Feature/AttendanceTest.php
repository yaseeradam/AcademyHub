<?php

namespace Tests\Feature;

use App\Livewire\Attendance\Index as AttendanceIndex;
use App\Livewire\Attendance\Teachers as StaffAttendance;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\TeacherAttendanceMark;
use App\Models\TeacherAttendanceSheet;
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

    public function test_students_default_to_unmarked_and_only_marked_students_are_persisted(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $class = SchoolClass::query()->where('name', 'JSS 2')->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->where('name', 'A')->firstOrFail();
        $students = Student::query()
            ->where('class_id', $class->id)
            ->where('section_id', $section->id)
            ->where('status', 'Active')
            ->get();

        $this->assertGreaterThan(1, $students->count());
        $student1 = $students->first();
        $student2 = $students->skip(1)->first();

        $test = Livewire::actingAs($admin)
            ->test(AttendanceIndex::class)
            ->set('classId', $class->id)
            ->set('sectionId', $section->id)
            ->set('date', '2026-02-07')
            ->set('term', 1)
            ->set('session', '2026/2027')
            ->call('start');

        // Check that initial status is Unmarked (not Present)
        $test->assertSet("marks.{$student1->id}.status", 'Unmarked');
        $test->assertSet("marks.{$student2->id}.status", 'Unmarked');

        // Explicitly mark only student1 as Present
        $test->call('setMark', $student1->id, 'Present');
        $test->assertSet("marks.{$student1->id}.status", 'Present');
        $test->assertSet("marks.{$student2->id}.status", 'Unmarked');

        // Save
        $test->call('save');

        $sheet = AttendanceSheet::query()
            ->where('class_id', $class->id)
            ->where('section_id', $section->id)
            ->where('date', '2026-02-07')
            ->firstOrFail();

        // student1 should have a mark in DB
        $this->assertDatabaseHas('attendance_marks', [
            'sheet_id'   => $sheet->id,
            'student_id' => $student1->id,
            'status'     => 'Present',
        ]);

        // student2 was left Unmarked, so no record should exist in DB
        $this->assertDatabaseMissing('attendance_marks', [
            'sheet_id'   => $sheet->id,
            'student_id' => $student2->id,
        ]);

        // Clicking Present again toggles back to Unmarked and removes from DB
        $test->call('setMark', $student1->id, 'Present');
        $test->assertSet("marks.{$student1->id}.status", 'Unmarked');

        $this->assertDatabaseMissing('attendance_marks', [
            'sheet_id'   => $sheet->id,
            'student_id' => $student1->id,
        ]);
    }

    public function test_staff_attendance_defaults_to_unmarked_and_only_marked_staff_are_persisted(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $teachers = User::query()
            ->whereIn('role', ['teacher', 'admin', 'bursar'])
            ->where('is_active', true)
            ->get();

        $this->assertGreaterThan(1, $teachers->count());
        $teacher1 = $teachers->first();
        $teacher2 = $teachers->skip(1)->first();

        $test = Livewire::actingAs($admin)
            ->test(StaffAttendance::class)
            ->set('date', '2026-02-07')
            ->set('term', 1)
            ->set('session', '2026/2027')
            ->call('start');

        // Check that initial status is Unmarked (not Present)
        $test->assertSet("marks.{$teacher1->id}.status", 'Unmarked');
        $test->assertSet("marks.{$teacher2->id}.status", 'Unmarked');

        // Mark only teacher1 as Present
        $test->call('setMark', $teacher1->id, 'Present');
        $test->assertSet("marks.{$teacher1->id}.status", 'Present');
        $test->assertSet("marks.{$teacher2->id}.status", 'Unmarked');

        // Save
        $test->call('save');

        $sheet = TeacherAttendanceSheet::query()
            ->where('date', '2026-02-07')
            ->where('term', 1)
            ->where('session', '2026/2027')
            ->firstOrFail();

        // teacher1 must exist in DB
        $this->assertDatabaseHas('teacher_attendance_marks', [
            'sheet_id'   => $sheet->id,
            'teacher_id' => $teacher1->id,
            'status'     => 'Present',
        ]);

        // teacher2 was left Unmarked, so must NOT exist in DB
        $this->assertDatabaseMissing('teacher_attendance_marks', [
            'sheet_id'   => $sheet->id,
            'teacher_id' => $teacher2->id,
        ]);

        // Tapping Present again on teacher1 unmarks them and removes from DB
        $test->call('setMark', $teacher1->id, 'Present');
        $test->assertSet("marks.{$teacher1->id}.status", 'Unmarked');

        $this->assertDatabaseMissing('teacher_attendance_marks', [
            'sheet_id'   => $sheet->id,
            'teacher_id' => $teacher1->id,
        ]);
    }
}

