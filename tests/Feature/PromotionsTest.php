<?php

namespace Tests\Feature;

use App\Livewire\Promotions\Index as PromotionsIndex;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PromotionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotion_auto_selects_destination_section_if_only_one_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $fromClass = SchoolClass::query()->create(['name' => 'JSS 1', 'level' => 1]);
        $toClass = SchoolClass::query()->create(['name' => 'JSS 2', 'level' => 2]);

        $fromSection = Section::query()->create(['class_id' => $fromClass->id, 'name' => 'A']);
        $onlyToSection = Section::query()->create(['class_id' => $toClass->id, 'name' => 'A']);

        $student = Student::query()->create([
            'admission_number' => 'ADM-1',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'class_id' => $fromClass->id,
            'section_id' => $fromSection->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        Livewire::actingAs($admin)
            ->test(PromotionsIndex::class)
            ->set('fromClassId', $fromClass->id)
            ->set('toClassId', $toClass->id)
            ->set('selected', [$student->id])
            ->call('promoteSelected')
            ->assertHasNoErrors();

        $student->refresh();
        $this->assertSame($toClass->id, $student->class_id);
        $this->assertSame($onlyToSection->id, $student->section_id);
    }

    public function test_promotion_maps_destination_section_by_name_when_not_selected(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $fromClass = SchoolClass::query()->create(['name' => 'SSS 1', 'level' => 3]);
        $toClass = SchoolClass::query()->create(['name' => 'SSS 2', 'level' => 4]);

        $fromSection = Section::query()->create(['class_id' => $fromClass->id, 'name' => 'Blue']);
        $toSectionBlue = Section::query()->create(['class_id' => $toClass->id, 'name' => 'Blue']);
        Section::query()->create(['class_id' => $toClass->id, 'name' => 'Red']);

        $student = Student::query()->create([
            'admission_number' => 'ADM-2',
            'first_name' => 'Jane',
            'last_name' => 'Roe',
            'class_id' => $fromClass->id,
            'section_id' => $fromSection->id,
            'gender' => 'Female',
            'status' => 'Active',
        ]);

        Livewire::actingAs($admin)
            ->test(PromotionsIndex::class)
            ->set('fromClassId', $fromClass->id)
            ->set('toClassId', $toClass->id)
            ->set('selected', [$student->id])
            ->call('promoteSelected');

        $student->refresh();
        $this->assertSame($toClass->id, $student->class_id);
        $this->assertSame($toSectionBlue->id, $student->section_id);
    }

    public function test_promotion_requires_destination_section_when_multiple_exist_and_no_mapping_available(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $fromClass = SchoolClass::query()->create(['name' => 'Primary 1', 'level' => 1]);
        $toClass = SchoolClass::query()->create(['name' => 'Primary 2', 'level' => 2]);

        $fromSection = Section::query()->create(['class_id' => $fromClass->id, 'name' => 'A']);
        Section::query()->create(['class_id' => $toClass->id, 'name' => 'B']);
        Section::query()->create(['class_id' => $toClass->id, 'name' => 'C']);

        $student = Student::query()->create([
            'admission_number' => 'ADM-3',
            'first_name' => 'Sam',
            'last_name' => 'Smith',
            'class_id' => $fromClass->id,
            'section_id' => $fromSection->id,
            'gender' => 'Male',
            'status' => 'Active',
        ]);

        Livewire::actingAs($admin)
            ->test(PromotionsIndex::class)
            ->set('fromClassId', $fromClass->id)
            ->set('toClassId', $toClass->id)
            ->set('selected', [$student->id])
            ->call('promoteSelected')
            ->assertHasErrors(['toSectionId']);
    }
}
