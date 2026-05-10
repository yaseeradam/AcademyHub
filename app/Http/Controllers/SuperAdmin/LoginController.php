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

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        if (! Auth::user()->is_super_admin) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Access denied.',
            ]);
        }

        if (! is_null(Auth::user()->tenant_id)) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Access denied.',
            ]);
        }

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
