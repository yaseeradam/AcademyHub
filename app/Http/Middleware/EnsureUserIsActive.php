<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $isActive = $user instanceof \App\Models\Student
                ? $user->status === 'Active'
                : (bool) $user->is_active;

            if (!$isActive) {
                // Revoke current Sanctum token if present to prevent reuse
                if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'Your account is inactive. Contact the administrator.',
                    ], 403);
                }

                Auth::logout();

                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'Your account is inactive. Contact the administrator.']);
            }
        }

        return $next($request);
    }
}
