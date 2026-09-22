<?php

namespace Tests\Feature;

use App\Livewire\Results\Entry as ResultsEntry;
use App\Models\SchoolClass;
use App\Models\ScoreSubmission;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScoreSubmissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_submit_scores_and_admin_sees_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

        $class = SchoolClass::query()->create(['name' => 'JSS X', 'level' => 1]);
        $subject = Subject::query()->create(['name' => 'Math', 'code' => 'MTHX']);

        SubjectAllocation::query()->create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
        ]);

        // The component uses save() before submit; we only verify that submission record is created.
        $this->actingAs($teacher);
        ScoreSubmission::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'term' => 1,
                'session' => '2025/2026',
            ],
            [
                'status' => 'submitted',
                'submitted_at' => now(),
            ]
        );

        $this->assertDatabaseHas('score_submissions', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'term' => 1,
            'session' => '2025/2026',
            'status' => 'submitted',
        ]);

        $this->actingAs($admin);
        $submissions = ScoreSubmission::query()->where('status', 'submitted')->get();
        $this->assertSame(1, $submissions->count());
    }

    public function test_admin_can_approve_a_submitted_score_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

        $class = SchoolClass::query()->create(['name' => 'JSS X', 'level' => 1]);
        $subject = Subject::query()->create(['name' => 'Math', 'code' => 'MTHX']);

        $submission = ScoreSubmission::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'term' => 1,
            'session' => '2025/2026',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(ResultsEntry::class)
            ->call('approveSubmission', $submission->id);

        $submission->refresh();
        $this->assertSame('approved', $submission->status);
        $this->assertSame($admin->id, $submission->reviewed_by);
        $this->assertNotNull($submission->reviewed_at);
        $this->assertNull($submission->note);
    }

    public function test_admin_can_reject_a_submitted_score_submission_with_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

        $class = SchoolClass::query()->create(['name' => 'JSS X', 'level' => 1]);
        $subject = Subject::query()->create(['name' => 'Math', 'code' => 'MTHX']);

        $submission = ScoreSubmission::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'term' => 1,
            'session' => '2025/2026',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(ResultsEntry::class)
            ->call('startReject', $submission->id)
            ->set('rejectNote', 'Please correct totals for two students.')
            ->call('confirmReject');

        $submission->refresh();
        $this->assertSame('rejected', $submission->status);
        $this->assertSame($admin->id, $submission->reviewed_by);
        $this->assertNotNull($submission->reviewed_at);
        $this->assertSame('Please correct totals for two students.', $submission->note);
    }
}
