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

        if ($tenant->isSubscriptionExpired()) {
            if ($request->expectsJson() || $request->header('X-Livewire')) {
                return response()->json([
                    'message' => 'Your school\'s subscription has expired. Access to plugins is disabled.',
                ], 403);
            }

            if (session()->has('student_id')) {
                return redirect()
                    ->route('student.dashboard')
                    ->with('warning', 'Your school\'s subscription has expired. Please contact school administration.');
            }

            $user = $request->user();
            if ($user && ($user->role === 'admin' || $user->is_super_admin)) {
                return redirect()
                    ->route('settings.subscription')
                    ->with('warning', 'Your school\'s subscription has expired. Please renew your subscription to access plugins.');
            }

            return redirect()
                ->route('dashboard')
                ->with('warning', 'Your school\'s subscription has expired. Please contact school administration.');
        }

        $plugin = $tenant
            ->activeMarketplaceComponents()
            ->where('slug', $slug)
            ->first();

        if (! $plugin) {
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

        // If a student is logged in, check class targeting restrictions for this plugin
        if (session()->has('student_id')) {
            $student = \App\Models\Student::find(session('student_id'));
            if ($student) {
                $allowedClassIds = $plugin->pivot->allowed_class_ids ?? [];
                if (is_string($allowedClassIds)) {
                    $allowedClassIds = json_decode($allowedClassIds, true) ?: [];
                }
                $allowedClassIds = is_array($allowedClassIds) ? $allowedClassIds : [];
                $studentClassId = (string) $student->class_id;
                $allowedClassIds = array_map('strval', $allowedClassIds);
                if (!in_array($studentClassId, $allowedClassIds, true)) {
                    if ($request->expectsJson() || $request->header('X-Livewire')) {
                        return response()->json([
                            'message' => 'This feature is not active for your class.',
                        ], 403);
                    }
                    return redirect()
                        ->route('student.dashboard')
                        ->with('warning', 'This feature is not active for your class.');
                }
            }
        }

        return $next($request);
    }
}
