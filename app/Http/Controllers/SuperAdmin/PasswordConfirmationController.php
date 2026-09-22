<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordConfirmationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = auth()->user();

        if (Hash::check($request->password, $user->password)) {
            return response()->json(['verified' => true]);
        }

        return response()->json([
            'verified' => false,
            'message' => 'The provided password does not match our records.'
        ], 422);
    }
}
