<?php

namespace Tests\Feature;

use App\Livewire\DataCollection\Submissions as DataCollectionSubmissions;
use App\Livewire\DataCollection\Weekly as DataCollectionWeekly;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use App\Models\WeeklyDataCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DataCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_submit_weekly_data_collection(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher@academyhub.local')->firstOrFail();
        $class = SchoolClass::query()->where('name', 'JSS 2')->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->where('name', 'A')->firstOrFail();

        Livewire::actingAs($teacher)
            ->test(DataCollectionWeekly::class)
            ->set('classId', $class->id)
            ->set('sectionId', $section->id)
            ->set('term', 1)
            ->set('session', '2025/2026')
            ->set('weekStart', '2026-02-10')
            ->set('boysPresent', 10)
            ->set('boysAbsent', 2)
            ->set('girlsPresent', 12)
            ->set('girlsAbsent', 1)
            ->set('schoolDays', 5)
            ->set('note', 'Weekly summary')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('weekly_data_collections', [
            'class_id' => $class->id,
            'section_id' => $section->id,
            'term' => 1,
            'session' => '2025/2026',
            'week_start' => '2026-02-10',
            'boys_present' => 10,
            'boys_absent' => 2,
            'girls_present' => 12,
            'girls_absent' => 1,
            'school_days' => 5,
            'status' => 'submitted',
        ]);
    }

    public function test_admin_can_approve_weekly_data_collection(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher@academyhub.local')->firstOrFail();
        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $class = SchoolClass::query()->where('name', 'JSS 2')->firstOrFail();
        $section = Section::query()->where('class_id', $class->id)->where('name', 'A')->firstOrFail();

        $row = WeeklyDataCollection::query()->create([
            'class_id' => $class->id,
            'section_id' => $section->id,
            'teacher_id' => $teacher->id,
            'term' => 1,
            'session' => '2025/2026',
            'week_start' => '2026-02-10',
            'week_end' => '2026-02-16',
            'boys_present' => 10,
            'boys_absent' => 2,
            'girls_present' => 12,
            'girls_absent' => 1,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(DataCollectionSubmissions::class)
            ->set('term', 1)
            ->set('session', '2025/2026')
            ->call('approve', $row->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('weekly_data_collections', [
            'id' => $row->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);
    }
}

