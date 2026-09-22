<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ImpersonationController extends Controller
{
    public function start(Request $request, User $user)
    {
        // 1. Ensure the user is a superadmin
        abort_unless(auth()->user()?->is_super_admin, 403, 'Only superadmins can impersonate.');

        // 2. Generate a secure timing-safe single-use cache token
        $token = Str::random(40);
        Cache::put('impersonate_' . $token, [
            'superadmin_id' => auth()->id(),
            'user_id' => $user->id,
        ], now()->addMinutes(2));

        // 3. Construct school admin subdomain URL
        $tenant = $user->tenant;
        if (!$tenant) {
            return back()->with('error', 'The target user does not belong to a school tenant.');
        }

        $appUrl = config('app.url'); // e.g. http://localhost:8000
        $parsed = parse_url($appUrl);
        $domain = $parsed['host'] ?? 'localhost';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $scheme = $parsed['scheme'] ?? 'http';

        // Check if tenant has a custom domain mapped, otherwise use subdomain
        $host = $tenant->domain ? $tenant->domain : $tenant->slug . '.' . $domain;
        
        $redirectUrl = $scheme . '://' . $host . $port . '/impersonate/login?token=' . $token;

        return redirect($redirectUrl);
    }

    public function login(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            abort(403, 'Impersonation token is missing.');
        }

        $data = Cache::pull('impersonate_' . $token);
        if (!$data) {
            abort(403, 'Impersonation token is invalid or has expired.');
        }

        $targetUser = User::withoutGlobalScopes()->findOrFail($data['user_id']);

        // Ensure user belongs to the current tenant resolved on this domain
        $currentTenant = app('currentTenant');
        if (!$currentTenant || (int)$targetUser->tenant_id !== (int)$currentTenant->id) {
            abort(403, 'Unauthorized tenant access.');
        }

        // Save the original superadmin ID in the tenant session
        session(['original_superadmin_id' => $data['superadmin_id']]);

        // Log in as target user
        Auth::login($targetUser);

        return redirect()->route('dashboard')->with('success', 'You are now impersonating ' . $targetUser->name);
    }

    public function stop()
    {
        if (!session()->has('original_superadmin_id')) {
            return redirect('/');
        }

        session()->forget('original_superadmin_id');
        Auth::logout();

        $appUrl = config('app.url'); // e.g. http://localhost:8000
        return redirect($appUrl . '/superadmin/health')->with('success', 'Returned to Super Admin Console.');
    }
}
