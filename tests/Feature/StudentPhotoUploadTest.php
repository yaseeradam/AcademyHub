<?php

namespace Tests\Feature;

use App\Livewire\Students\Form as StudentsForm;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class StudentPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_student_passport_photo(): void
    {
        Storage::fake('uploads');
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();
        $student = Student::query()->firstOrFail();

        // 1x1 transparent PNG (no GD dependency)
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO8bK0cAAAAASUVORK5CYII='
        );

        Livewire::actingAs($admin)
            ->test(StudentsForm::class, ['student' => $student])
            ->set('passport', UploadedFile::fake()->createWithContent('passport.png', $png))
            ->call('save')
            ->assertDispatched('student-saved');

        $student->refresh();
        $this->assertNotEmpty($student->passport_photo);
        $this->assertStringNotContainsString('\\', (string) $student->passport_photo);

        Storage::disk('uploads')->assertExists($student->passport_photo);
    }
}
