<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Tenant isolation: user must belong to the current tenant context.
        $tenantId = \App\Support\TenantSettings::tenantId();
        if ($tenantId) {
            if ($user->is_super_admin || (int) $user->tenant_id !== (int) $tenantId) {
                throw ValidationException::withMessages([
                    'email' => ['This account does not belong to this school instance.'],
                ]);
            }
        } else {
            // Non-tenant context (main domain) — only super admins allowed.
            if (! $user->is_super_admin) {
                throw ValidationException::withMessages([
                    'email' => ['Please login from your school domain.'],
                ]);
            }
        }

        // Active check
        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Contact the administrator.'],
            ]);
        }

        $deviceName = $request->device_name ?? 'mobile_app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_super_admin' => $user->is_super_admin,
                'tenant_id' => $user->tenant_id,
                'profile_photo_url' => $user->profile_photo_url,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_super_admin' => $user->is_super_admin,
                'profile_photo_url' => $user->profile_photo_url,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
