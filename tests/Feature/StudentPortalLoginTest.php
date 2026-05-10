<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
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

        $student = Student::query()->create([
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
        ])->assertRedirect('/student/dashboard');

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
        ])->assertRedirect('/student/dashboard');
    }
}

