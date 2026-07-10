<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\Tenant;
use App\Models\MarketplaceComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CbtCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cbt_creation_from_livewire_component(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $tenant = Tenant::first();
        app()->instance('currentTenant', $tenant);

        // Ensure the CBT plugin is activated and allowed classes are set
        $cbtPlugin = MarketplaceComponent::where('slug', 'cbt')->firstOrFail();
        $class = SchoolClass::first();
        
        $tenant->marketplaceComponents()->syncWithoutDetaching([
            $cbtPlugin->id => [
                'status' => 'active',
                'allowed_class_ids' => json_encode([$class->id])
            ]
        ]);

        $subject = Subject::first();

        // Run the Livewire component test
        $response = Livewire::actingAs($admin)
            ->test(\App\Livewire\Cbt\Index::class)
            ->set('title', 'Test CBT Exam')
            ->set('examType', 'academic')
            ->set('classId', $class->id)
            ->set('subjectId', $subject->id)
            ->set('durationMinutes', 45)
            ->set('term', 1)
            ->set('session', '2026/2027')
            ->call('createExam');

        $response->assertHasNoErrors();

        $exam = \App\Models\CbtExam::first();
        $this->assertNotNull($exam);

        // Mount the editor component
        Livewire::actingAs($admin)
            ->test(\App\Livewire\Cbt\ExamEditor::class, ['exam' => $exam])
            ->assertSet('examId', $exam->id);
    }
}
