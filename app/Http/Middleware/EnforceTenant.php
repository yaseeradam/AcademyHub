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
        $host = $request->getHost();
        $tenantId = TenantSettings::tenantId();

        // Super admin routes operate outside any tenant context — skip enforcement.
        if ($request->is('superadmin', 'superadmin/*')) {
            \Illuminate\Support\Facades\Log::debug('EnforceTenant: SuperAdmin route skip', ['host' => $host, 'url' => $request->fullUrl()]);
            return $next($request);
        }

        $check = Auth::check();
        \Illuminate\Support\Facades\Log::debug('EnforceTenant: Checking authentication', [
            'host' => $host,
            'auth_check' => $check,
            'tenantId' => $tenantId,
            'session_id' => $request->hasSession() ? $request->session()->getId() : 'NO_SESSION',
        ]);

        if (! $check) {
            return $next($request);
        }

        $user = $request->user();
        \Illuminate\Support\Facades\Log::debug('EnforceTenant: Authenticated user details', [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_tenant_id' => $user->tenant_id,
            'is_super_admin' => $user->is_super_admin,
        ]);

        // Superadmins should operate from the main domain (no tenant context).
        if ($user && $user->is_super_admin) {
            if ($tenantId && ! app()->environment('testing')) {
                \Illuminate\Support\Facades\Log::warning('EnforceTenant: SuperAdmin on tenant domain — logging out', [
                    'user_id' => $user->id,
                    'tenantId' => $tenantId,
                ]);
                if (method_exists(Auth::guard(), 'logout')) {
                    Auth::logout();
                }
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                abort(403, 'Super Admin accounts must use the main domain.');
            }

            return $next($request);
        }

        // Non-superadmin accounts must be in a tenant context and match it.
        $bypassForLegacyTests = app()->environment('testing') && is_null($tenantId);
        if (! $bypassForLegacyTests && (! $tenantId || ! $user || (int) $user->tenant_id !== (int) $tenantId)) {
            \Illuminate\Support\Facades\Log::warning('EnforceTenant: Non-superadmin tenant mismatch — logging out', [
                'user_id' => $user ? $user->id : null,
                'user_tenant_id' => $user ? $user->tenant_id : null,
                'resolved_tenant_id' => $tenantId,
            ]);
            if (method_exists(Auth::guard(), 'logout')) {
                Auth::logout();
            }
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

        \Illuminate\Support\Facades\Log::debug('EnforceTenant: Tenant matched successfully', [
            'user_id' => $user->id,
            'tenant_id' => $tenantId,
        ]);

        return $next($request);
    }
}

