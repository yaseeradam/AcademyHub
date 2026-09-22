<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfilePhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_and_remove_profile_photo(): void
    {
        Storage::fake('uploads');

        $user = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

        // 1x1 transparent PNG (no GD dependency)
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO8bK0cAAAAASUVORK5CYII='
        );

        $this->actingAs($user)
            ->post(route('profile.photo'), [
                'photo' => UploadedFile::fake()->createWithContent('photo.png', $png),
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertNotEmpty($user->profile_photo);
        $this->assertStringNotContainsString('\\', (string) $user->profile_photo);

        $path = (string) $user->profile_photo;
        Storage::disk('uploads')->assertExists($path);

        $this->actingAs($user)
            ->delete(route('profile.photo.destroy'))
            ->assertRedirect();

        $user->refresh();
        $this->assertNull($user->profile_photo);
        Storage::disk('uploads')->assertMissing($path);
    }
}

