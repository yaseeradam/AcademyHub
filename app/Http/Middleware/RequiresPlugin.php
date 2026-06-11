<?php

namespace App\Http\Middleware;

use App\Models\MarketplaceComponent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresPlugin
{
    /**
     * Usage in routes:
     *   Route::middleware('plugin:cbt')->group(...)
     *
     * The $slug parameter must match the `slug` column in marketplace_components.
     * Access is denied with 403 if the tenant has not installed the plugin.
     */
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if (! $tenant && ($user = $request->user())) {
            $tenant = $user->tenant;
        }

        if (! $tenant) {
            abort(403, 'No tenant context.');
        }

        $installed = $tenant
            ->activeMarketplaceComponents()
            ->where('slug', $slug)
            ->exists();

        if (! $installed) {
            // If it's an AJAX / Livewire request, return JSON
            if ($request->expectsJson() || $request->header('X-Livewire')) {
                return response()->json([
                    'message' => 'This plugin is not installed for your school.',
                ], 403);
            }

            // Student session redirection fallback
            if (session()->has('student_id')) {
                // Prevent redirect loop if the missing plugin is student-dashboard itself
                if ($slug === 'student-dashboard') {
                    $request->session()->forget(['tenant_id', 'student_id', 'student_name', 'student_admission', 'student_class', 'login_type', 'student_must_reset_password']);
                    $request->session()->regenerateToken();
                    return redirect()
                        ->route('login')
                        ->with('warning', 'Student Dashboard is not active for this school.');
                }

                return redirect()
                    ->route('student.dashboard')
                    ->with('warning', 'This feature is not active for your school.');
            }

            // Staff/Parent fallback
            $user = $request->user();
            if ($user && ($user->role === 'admin' || $user->is_super_admin)) {
                return redirect()
                    ->route('marketplace')
                    ->with('warning', 'You need to install the plugin to access this feature.');
            }

            return redirect()
                ->route('dashboard')
                ->with('warning', 'This feature is not active for your school.');
        }

        return $next($request);
    }
}
