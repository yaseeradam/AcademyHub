<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventProprietorMutations
{
    /**
     * Handle an incoming request.
     * Enforce 100% read-only access for the proprietor role across all endpoints.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'proprietor') {
            return $next($request);
        }

        // Always allow safe HTTP methods
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        // Allow authentication and personal profile updates
        if ($request->routeIs('logout') ||
            $request->is('logout') ||
            $request->is('api/logout') ||
            $request->routeIs('profile.*') ||
            $request->routeIs('csrf-token') ||
            $request->routeIs('attendance.latest-scan')) {
            return $next($request);
        }

        // Handle Livewire component updates (allow read-only filter/pagination updates, block data mutation methods)
        if ($request->hasHeader('X-Livewire') || $request->is('livewire/*')) {
            $payload = $request->json('components', []);
            if (is_array($payload)) {
                foreach ($payload as $component) {
                    $calls = $component['calls'] ?? [];
                    foreach ($calls as $call) {
                        $method = strtolower((string) ($call['method'] ?? ''));
                        // Check for mutation keywords across all CRUD and state-altering workflows
                        if (preg_match('/^(save|delete|create|store|destroy|update|mark|void|apply|randomize|remove|bulk|issue|upload|start|import|add|record|reset|submit|grade|attach|detach|sync|publish|unpublish|disburse|repay|assign|allocate|process|toggle(?!show))/i', $method)) {
                            abort(403, 'Proprietor account has read-only executive access. Data modifications are disabled.');
                        }
                    }
                }
            }
            return $next($request);
        }

        // Block any other state-mutating HTTP requests
        abort(403, 'Proprietor account has read-only executive access. Data modifications are disabled.');
    }
}
