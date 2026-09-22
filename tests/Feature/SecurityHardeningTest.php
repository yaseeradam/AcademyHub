<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_csp_header_is_present(): void
    {
        $hotPath = public_path('hot');
        $hasHot = file_exists($hotPath);
        if ($hasHot) {
            rename($hotPath, $hotPath . '.bak');
        }

        try {
            config(['app.env' => 'production']);
            $this->app['env'] = 'production';
            
            $response = $this->get('/');
            
            $response->assertHeader('Content-Security-Policy');
            $csp = $response->headers->get('Content-Security-Policy');
            
            $this->assertStringContainsString("default-src 'self'", $csp);
            $this->assertStringContainsString("js.paystack.co", $csp);
            $this->assertStringContainsString("api.paystack.co", $csp);
            $this->assertStringContainsString("cdn.jsdelivr.net", $csp);
        } finally {
            if ($hasHot && file_exists($hotPath . '.bak')) {
                rename($hotPath . '.bak', $hotPath);
            }
        }
    }

    public function test_login_endpoint_is_throttled_to_five_attempts(): void
    {
        // Issue 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'hacker@attacker.com',
                'password' => 'wrong-pass',
            ]);
            $response->assertStatus(302);
        }

        // 6th attempt should be blocked with 429
        $response = $this->post('/login', [
            'email' => 'hacker@attacker.com',
            'password' => 'wrong-pass',
        ]);
        $response->assertStatus(429);
    }

    public function test_api_login_endpoint_is_throttled(): void
    {
        // Issue 5 failed API logins
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'hacker@attacker.com',
                'password' => 'wrong-pass',
            ]);
            $response->assertStatus(422);
        }

        // 6th attempt should return 429
        $response = $this->postJson('/api/login', [
            'email' => 'hacker@attacker.com',
            'password' => 'wrong-pass',
        ]);
        $response->assertStatus(429);
    }
}
