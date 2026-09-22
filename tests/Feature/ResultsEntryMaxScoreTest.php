<?php

namespace Tests\Feature;

use App\Livewire\Results\Entry as ResultsEntry;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResultsEntryMaxScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_scores_are_clamped_to_max_marks(): void
    {
        $this->seed();

        config([
            'academyhub.results_ca1_max' => 10,
            'academyhub.results_ca2_max' => 15,
            'academyhub.results_exam_max' => 50,
        ]);

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $class = SchoolClass::query()->where('name', 'JSS 2')->firstOrFail();
        $subject = Subject::query()->where('code', 'MTH')->firstOrFail();
        $student = Student::query()->where('class_id', $class->id)->firstOrFail();

        Livewire::actingAs($admin)
            ->test(ResultsEntry::class)
            ->set('classId', $class->id)
            ->set('subjectId', $subject->id)
            ->set('term', 1)
            ->set('session', '2026/2027')
            ->set("scores.{$student->id}.ca1", 25)
            ->assertSet("scores.{$student->id}.ca1", 10)
            ->set("scores.{$student->id}.ca2", 99)
            ->assertSet("scores.{$student->id}.ca2", 15)
            ->set("scores.{$student->id}.exam", 1000)
            ->assertSet("scores.{$student->id}.exam", 50)
            ->call('save');

        $score = Score::query()
            ->where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->where('term', 1)
            ->where('session', '2026/2027')
            ->firstOrFail();

        $this->assertSame(10, (int) $score->ca1);
        $this->assertSame(15, (int) $score->ca2);
        $this->assertSame(50, (int) $score->exam);
    }

    public function test_scores_are_saved_automatically_on_updating_scores(): void
    {
        $this->seed();

        $admin = User::query()->where('email', env('ACADEMYHUB_ADMIN_EMAIL', 'admin@academyhub.local'))->firstOrFail();
        $class = SchoolClass::query()->where('name', 'JSS 2')->firstOrFail();
        $subject = Subject::query()->where('code', 'MTH')->firstOrFail();
        $student = Student::query()->where('class_id', $class->id)->firstOrFail();

        Livewire::actingAs($admin)
            ->test(ResultsEntry::class)
            ->set('classId', $class->id)
            ->set('subjectId', $subject->id)
            ->set('term', 1)
            ->set('session', '2026/2027')
            ->set("scores.{$student->id}.ca1", 8)
            ->set("scores.{$student->id}.ca2", 12)
            ->set("scores.{$student->id}.exam", 45);

        $score = Score::query()
            ->where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->where('term', 1)
            ->where('session', '2026/2027')
            ->firstOrFail();

        $this->assertSame(8, (int) $score->ca1);
        $this->assertSame(12, (int) $score->ca2);
        $this->assertSame(45, (int) $score->exam);
    }
}

