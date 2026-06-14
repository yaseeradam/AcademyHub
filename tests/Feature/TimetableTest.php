<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Timetable\Index;
use Tests\TestCase;

class TimetableTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_timetable_page()
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertStatus(200);
    }

    public function test_admin_can_save_regular_timetable_entry()
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $class = SchoolClass::query()->firstOrFail();
        $subject = Subject::query()->firstOrFail();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('classId', $class->id)
            ->set('entryDay', 1)
            ->set('startsAt', '08:00')
            ->set('endsAt', '09:00')
            ->set('isBreak', false)
            ->set('subjectId', $subject->id)
            ->set('color', 'blue')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('timetable_entries', [
            'class_id' => $class->id,
            'day_of_week' => 1,
            'starts_at' => '08:00',
            'ends_at' => '09:00',
            'is_break' => false,
            'subject_id' => $subject->id,
            'color' => 'blue',
        ]);
    }

    public function test_admin_can_save_break_timetable_entry()
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $class = SchoolClass::query()->firstOrFail();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('classId', $class->id)
            ->set('entryDay', 2)
            ->set('startsAt', '10:40')
            ->set('endsAt', '11:10')
            ->set('isBreak', true)
            ->set('breakText', 'ZUHR - BREAK')
            ->set('color', 'amber')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('timetable_entries', [
            'class_id' => $class->id,
            'day_of_week' => 2,
            'starts_at' => '10:40',
            'ends_at' => '11:10',
            'is_break' => true,
            'break_text' => 'ZUHR - BREAK',
            'color' => 'amber',
        ]);
    }

    public function test_admin_can_bulk_apply_break_to_all_days()
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $class = SchoolClass::query()->firstOrFail();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('classId', $class->id)
            ->set('entryDay', 1)
            ->set('startsAt', '10:40')
            ->set('endsAt', '11:10')
            ->set('isBreak', true)
            ->set('breakText', 'BREAK')
            ->set('color', 'slate')
            ->set('applyToAllDays', true)
            ->call('save')
            ->assertHasNoErrors();

        for ($day = 1; $day <= 5; $day++) {
            $this->assertDatabaseHas('timetable_entries', [
                'class_id' => $class->id,
                'day_of_week' => $day,
                'starts_at' => '10:40',
                'ends_at' => '11:10',
                'is_break' => true,
                'break_text' => 'BREAK',
                'color' => 'slate',
            ]);
        }
    }
}
