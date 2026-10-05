<?php

namespace App\Http\Middleware;

use App\Models\AcademicTerm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAcademicCalendarConfigured
{
    /**
     * Routes and patterns that are exempt from the academic calendar requirement.
     */
    protected array $exemptRoutes = [
        'academic-sessions',
        'login',
        'logout',
        'impersonate.*',
        'superadmin.*',
        'pwa.*',
        'offline',
        'manifest.json',
        'settings.subscription',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip for unauthenticated users, console requests, or superadmin operations
        if (!auth()->check() || $request->is('superadmin', 'superadmin/*')) {
            return $next($request);
        }

        $user = auth()->user();

        // Check if an active academic term exists for the current school/tenant
        $hasActiveTerm = AcademicTerm::active() !== null;

        // Share variable with all Blade views so navigation and banners can reflect status
        \Illuminate\Support\Facades\View::share('hasActiveAcademicTerm', $hasActiveTerm);

        if (!$hasActiveTerm) {
            // Allow Livewire component updates for the academic-sessions component
            if ($request->is('livewire/update')) {
                $payload = $request->json()->all();
                if (isset($payload['components']) && is_array($payload['components'])) {
                    foreach ($payload['components'] as $comp) {
                        $snapshot = isset($comp['snapshot']) ? json_decode($comp['snapshot'], true) : [];
                        $memo = $snapshot['memo'] ?? [];
                        $compName = $memo['name'] ?? '';
                        if ($compName === 'academics.sessions') {
                            return $next($request);
                        }
                    }
                }
            }

            // Allow exempt routes
            foreach ($this->exemptRoutes as $exempt) {
                if ($request->routeIs($exempt) || $request->is($exempt)) {
                    return $next($request);
                }
            }

            // If user has administrative authority (admin or proprietor), redirect to the setup page
            if (in_array($user->role, ['admin', 'proprietor'], true)) {
                return redirect()->route('academic-sessions')
                    ->with('warning', 'Academic Calendar Setup Required: A school always needs to set its current Academic Session and Active Term first. Please configure and activate your session and term to get started.');
            }
        }

        return $next($request);
    }
}
