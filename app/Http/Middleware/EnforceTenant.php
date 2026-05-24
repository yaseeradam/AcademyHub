<?php

namespace App\Http\Middleware;

use App\Support\TenantSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceTenant
{
    /**
     * Enforce that authenticated users can only use the correct tenant host.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Super admin routes operate outside any tenant context — skip enforcement.
        if ($request->is('superadmin', 'superadmin/*')) {
            return $next($request);
        }

        if (! Auth::check()) {
            return $next($request);
        }

        $user = $request->user();
        $tenantId = TenantSettings::tenantId();

        // Superadmins should operate from the main domain (no tenant context).
        if ($user && $user->is_super_admin) {
            if ($tenantId) {
                Auth::logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                abort(403, 'Super Admin accounts must use the main domain.');
            }

            return $next($request);
        }

        // Non-superadmin accounts must be in a tenant context and match it.
        if (! $tenantId || ! $user || (int) $user->tenant_id !== (int) $tenantId) {
            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized tenant context.'], 403);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Please login from your school domain/subdomain.']);
        }

        return $next($request);
    }
}

