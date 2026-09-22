<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_access_parent_dashboard_and_performance_pages(): void
    {
        $this->seed();

        $class = SchoolClass::query()->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->firstOrFail();

        $student = Student::query()->create([
            'admission_number' => 'ADM-PARENT-0001',
            'first_name' => 'Child',
            'last_name' => 'One',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $parent = User::query()->create([
            'name' => 'Parent One',
            'email' => 'parent-test@academyhub.local',
            'password' => 'password',
            'role' => 'parent',
            'is_active' => true,
        ]);

        $parent->students()->attach($student->id);

        $this->actingAs($parent)
            ->get('/parents/dashboard')
            ->assertStatus(200);

        $this->actingAs($parent)
            ->get('/parents/performance')
            ->assertStatus(200);
    }
}

