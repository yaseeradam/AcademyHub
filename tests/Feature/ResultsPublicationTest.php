<?php

namespace Tests\Feature;

use App\Livewire\Results\Entry as ResultsEntry;
use App\Models\ResultPublication;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResultsPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_report_card_requires_results_published(): void
    {
        config(['academyhub.report_card_template' => 'compact']);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

        $class = SchoolClass::query()->create(['name' => 'JSS PUB', 'level' => 1]);
        $section = Section::query()->create(['class_id' => $class->id, 'name' => 'A']);

        $student = Student::query()->create([
            'admission_number' => 'ADM-PUB-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $subject = Subject::query()->create(['name' => 'Mathematics', 'code' => 'MTHP']);
        SubjectAllocation::query()->create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
        ]);

        $url = route('results.report-card', $student).'?term=1&session=2025/2026';

        $this->actingAs($teacher)
            ->get($url)
            ->assertStatus(403);

        ResultPublication::query()->create([
            'class_id' => $class->id,
            'term' => 1,
            'session' => '2025/2026',
            'published_at' => now(),
            'published_by' => $admin->id,
        ]);

        $resp = $this->actingAs($teacher)->get($url);
        $resp->assertOk();
        $resp->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', (string) $resp->getContent());
    }

    public function test_published_results_lock_teacher_score_save(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

        $class = SchoolClass::query()->create(['name' => 'JSS LOCK', 'level' => 1]);
        $subject = Subject::query()->create(['name' => 'English', 'code' => 'ENGP']);

        ResultPublication::query()->create([
            'class_id' => $class->id,
            'term' => 1,
            'session' => '2025/2026',
            'published_at' => now(),
        ]);

        Livewire::actingAs($teacher)
            ->test(ResultsEntry::class)
            ->set('classId', $class->id)
            ->set('subjectId', $subject->id)
            ->set('term', 1)
            ->set('session', '2025/2026')
            ->call('save')
            ->assertHasErrors(['scores']);
    }

    public function test_broadsheet_class_selection_and_publication_workflow(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $class = SchoolClass::query()->create(['name' => 'JSS 3 Broadsheet', 'level' => 3]);
        $section = Section::query()->create(['class_id' => $class->id, 'name' => 'A']);

        $student1 = Student::query()->create([
            'admission_number' => 'ADM-BS-001',
            'first_name' => 'Ali',
            'last_name' => 'Bello',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $student2 = Student::query()->create([
            'admission_number' => 'ADM-BS-002',
            'first_name' => 'Zainab',
            'last_name' => 'Ahmed',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Female',
            'status' => 'Active',
        ]);

        $subject = Subject::query()->create(['name' => 'Mathematics', 'code' => 'MTH-BS']);
        \App\Models\SubjectAllocation::create([
            'teacher_id' => $admin->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
        ]);

        \App\Models\Score::create([
            'student_id' => $student1->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'term' => 1,
            'session' => '2026/2027',
            'ca1' => 15,
            'ca2' => 15,
            'exam' => 50,
            'total' => 80,
        ]);

        \App\Models\Score::create([
            'student_id' => $student2->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'term' => 1,
            'session' => '2026/2027',
            'ca1' => 18,
            'ca2' => 18,
            'exam' => 58,
            'total' => 94,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Results\Broadsheet::class)
            ->set('classId', $class->id)
            ->set('term', 1)
            ->set('session', '2026/2027')
            ->assertSee('JSS 3 Broadsheet')
            ->assertSee('Zainab Ahmed')
            ->assertSee('Ali Bello')
            ->assertSee('94')
            ->assertSee('80')
            ->call('publish')
            ->assertDispatched('alert', message: 'Results published successfully.', type: 'success');

        $this->assertDatabaseHas('result_publications', [
            'class_id' => $class->id,
            'term' => 1,
            'session' => '2026/2027',
        ]);
    }
}

