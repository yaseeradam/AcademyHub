<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        if (Auth::check() && Auth::user()->is_super_admin && is_null(Auth::user()->tenant_id)) {
            return redirect()->route('superadmin.dashboard');
        }

        return view('superadmin.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Look up user and validate credentials WITHOUT creating a session first.
        $user = \App\Models\User::withoutGlobalScope('tenant')->where('email', $credentials['email'])->first();

        if (! $user || ! \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        // Verify super admin status BEFORE creating a session.
        if (! $user->is_super_admin || ! is_null($user->tenant_id)) {
            throw ValidationException::withMessages([
                'email' => 'Access denied.',
            ]);
        }

        // All checks passed — now create the session.
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('superadmin.dashboard');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }
}
