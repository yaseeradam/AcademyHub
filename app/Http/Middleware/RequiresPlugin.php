<?php

namespace App\Http\Middleware;

use App\Models\MarketplaceComponent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresPlugin
{
    /**
     * Usage in routes:
     *   Route::middleware('plugin:cbt')->group(...)
     *
     * The $slug parameter must match the `slug` column in marketplace_components.
     * Access is denied with 403 if the tenant has not installed the plugin.
     */
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $user = $request->user();

        if (! $user || ! $user->tenant) {
            abort(403, 'No tenant context.');
        }

        $installed = $user->tenant
            ->activeMarketplaceComponents()
            ->where('slug', $slug)
            ->exists();

        if (! $installed) {
            // If it's an AJAX / Livewire request, return JSON
            if ($request->expectsJson() || $request->header('X-Livewire')) {
                return response()->json([
                    'message' => 'This plugin is not installed for your school.',
                ], 403);
            }

            // Otherwise redirect to marketplace with a flash message
            return redirect()
                ->route('marketplace')
                ->with('warning', 'You need to install the plugin to access this feature.');
        }

        return $next($request);
    }
}
