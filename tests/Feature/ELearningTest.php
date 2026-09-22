<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ELearningTest extends TestCase
{
    use RefreshDatabase;

    public function test_elearning_notes_can_be_uploaded_successfully(): void
    {
        $this->seed();
        Storage::fake('public');

        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $tenant = Tenant::first();
        app()->instance('currentTenant', $tenant);

        $class = SchoolClass::first();
        $subject = Subject::first();

        $file = UploadedFile::fake()->create('lecture_notes.pdf', 500, 'application/pdf');

        Livewire::actingAs($admin)
            ->test(\App\Livewire\ELearning\Index::class)
            ->set('title', 'Introduction to Algebra')
            ->set('class_id', $class->id)
            ->set('subject_id', $subject->id)
            ->set('term_name', 'First Term')
            ->set('description', 'This is a description of the note.')
            ->set('file', $file)
            ->call('saveNote')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('class_notes', [
            'title' => 'Introduction to Algebra',
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);
    }
}
