<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $student = $request->user();

        if ($student && $student instanceof \App\Models\Student && $student->status !== 'Active') {
            // Revoke current Sanctum token to force logout on mobile / front-end instantly
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();
            }

            return response()->json([
                'message' => 'Your student account is no longer active. Please contact the school administrator.'
            ], 403);
        }

        return $next($request);
    }
}
