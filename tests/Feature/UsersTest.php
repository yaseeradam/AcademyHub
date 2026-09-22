<?php

namespace Tests\Feature;

use App\Livewire\Users\Index as UsersIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_from_users_module(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(UsersIndex::class)
            ->set('name', 'Test Teacher')
            ->set('email', 'teacher2@academyhub.local')
            ->set('role', 'teacher')
            ->set('isActive', true)
            ->set('password', 'password123')
            ->call('createUser');

        $this->assertDatabaseHas('users', [
            'email' => 'teacher2@academyhub.local',
            'role' => 'teacher',
            'is_active' => 1,
        ]);
    }
}

