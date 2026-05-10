<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\AttendanceMark;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeacherApiAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_attendance_api_requires_section_id_when_multiple_sections_exist(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher@myacademy.local')->firstOrFail();
        Sanctum::actingAs($teacher);

        $class = SchoolClass::query()->where('name', 'JSS 2')->firstOrFail();
        $session = AcademicSession::query()->firstOrFail()->name;

        $res = $this->postJson('/api/teacher/attendance', [
            'class_id' => $class->id,
            'date' => now()->toDateString(),
            'term' => 1,
            'session' => $session,
            'marks' => [
                ['student_id' => 1, 'status' => 'present'],
            ],
        ]);

        $res->assertStatus(422);
    }

    public function test_teacher_can_save_attendance_with_lowercase_status_and_it_is_normalized(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher@myacademy.local')->firstOrFail();
        Sanctum::actingAs($teacher);

        $class = SchoolClass::query()->where('name', 'JSS 2')->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->where('name', 'A')->firstOrFail();
        $session = AcademicSession::query()->firstOrFail()->name;

        $student = Student::query()->create([
            'admission_number' => 'ADM-API-0001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $this->postJson('/api/teacher/attendance', [
            'class_id' => $class->id,
            'section_id' => $section->id,
            'date' => now()->toDateString(),
            'term' => 1,
            'session' => $session,
            'marks' => [
                ['student_id' => $student->id, 'status' => 'present'],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('attendance_marks', [
            'student_id' => $student->id,
            'status' => 'Present',
        ]);

        $this->assertSame(
            1,
            AttendanceMark::query()->where('student_id', $student->id)->where('status', 'Present')->count()
        );
    }
}

