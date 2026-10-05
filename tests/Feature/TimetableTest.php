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

    public function test_user_can_download_timetable_pdf()
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $class = SchoolClass::query()->firstOrFail();

        $response = $this->actingAs($admin)->get(route('timetable.pdf', ['class_id' => $class->id]));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_parent_views_timetable_directly_without_download_button()
    {
        $this->seed();
        $class = SchoolClass::query()->firstOrFail();
        $section = \App\Models\Section::query()->where('class_id', $class->id)->firstOrFail();

        $student = \App\Models\Student::query()->create([
            'admission_number' => 'ADM-PARENT-TIME1',
            'first_name' => 'Timetable',
            'last_name' => 'Child',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        $parent = User::query()->create([
            'name' => 'Parent Timetable',
            'email' => 'parent-timetable@academyhub.local',
            'password' => 'password',
            'role' => 'parent',
            'is_active' => true,
        ]);
        $parent->students()->attach($student->id);

        $tenant = \App\Models\Tenant::query()->first();
        app()->instance('currentTenant', $tenant);

        $student->forceFill(['tenant_id' => $tenant->id])->saveQuietly();
        $parent->forceFill(['tenant_id' => $tenant->id])->saveQuietly();
        $parent->refresh();

        $component = \App\Models\MarketplaceComponent::query()->where('slug', 'student-dashboard')->first();
        if ($component) {
            $tenant->marketplaceComponents()->syncWithoutDetaching([
                $component->id => [
                    'installed_at' => now(),
                    'uninstalled_at' => null,
                    'status' => 'active',
                    'allowed_class_ids' => [$class->id],
                ],
            ]);
        }

        // On the dedicated Timetable page, parent sees schedule directly but not PDF download
        Livewire::actingAs($parent)
            ->test(Index::class)
            ->assertStatus(200)
            ->assertDontSeeHtml(route('timetable.pdf', ['class_id' => $class->id]));

        // Admin still sees the PDF download button
        $admin = User::query()->where('email', 'admin@demo.academyhub.com.ng')->first()
            ?? User::query()->where('role', 'admin')->first();
        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('classId', $class->id)
            ->assertStatus(200)
            ->assertSeeHtml(route('timetable.pdf', ['class_id' => $class->id]));

        // On the Parent Dashboard timetable tab, parent sees schedule directly but not PDF download button
        Livewire::actingAs($parent)
            ->test(\App\Livewire\Parents\Dashboard::class)
            ->set('selectedChildId', $student->id)
            ->set('activeTab', 'timetable')
            ->assertStatus(200)
            ->assertSee('Weekly Timetable Schedule')
            ->assertDontSee('Download PDF Timetable');
    }
}
