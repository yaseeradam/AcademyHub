<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed();

        $this->get('/')->assertRedirect('/login');

        $admin = User::query()->where('email', 'admin@academyhub.local')->firstOrFail();

        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
    }
}
