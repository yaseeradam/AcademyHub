<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_details(): void
    {
        $user = User::factory()->create([
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('profile.details'), [
                'name' => 'New Name',
                'email' => 'new-email@school.local',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new-email@school.local', $user->email);
    }

    public function test_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'role' => 'teacher',
            'is_active' => true,
            'password' => 'old-password-123',
        ]);

        $this->actingAs($user)
            ->post(route('profile.password'), [
                'current_password' => 'old-password-123',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
    }
}

