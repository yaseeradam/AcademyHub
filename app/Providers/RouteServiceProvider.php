<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // WhatsApp bot key bypass check
            $isWhatsAppBot = false;
            $expectedKey = config('services.whatsapp.api_key');
            
            if ($expectedKey && $request->is('api/whatsapp/*')) {
                $providedKey = $request->header('X-WhatsApp-Api-Key') ?: $request->query('key');
                if ($providedKey && hash_equals($expectedKey, $providedKey)) {
                    $isWhatsAppBot = true;
                }
            }

            if ($isWhatsAppBot) {
                // Allow a much higher rate limit for our verified background WhatsApp bot
                return Limit::perMinute(600)->by($request->ip());
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('web_global', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('payment_callback', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('cbt_portal', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('cbt_attempt', function (Request $request) {
            $studentId = session('student_id') ?: $request->ip();
            return Limit::perMinute(60)->by($studentId);
        });

        RateLimiter::for('login_attempts', function (Request $request) {
            $key = $request->input('email') ?: $request->input('admission_no') ?: $request->ip();
            return Limit::perMinute(5)->by($key . '_' . $request->ip());
        });

        RateLimiter::for('auth_views', function (Request $request) {
            return Limit::perMinute(15)->by($request->ip());
        });

        RateLimiter::for('media_uploads', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
