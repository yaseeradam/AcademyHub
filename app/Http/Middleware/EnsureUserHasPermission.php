<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $permissions = array_values(array_filter(array_map('trim', $permissions)));

        if ($permissions !== []) {
            $ok = false;
            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission)) {
                    $ok = true;
                    break;
                }
            }

            abort_unless($ok, 403);
        }

        return $next($request);
    }
}

