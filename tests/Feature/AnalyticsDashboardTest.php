<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Score;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Analytics\Dashboard;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_analytics_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertStatus(200);
    }
}
