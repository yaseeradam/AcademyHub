<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantDiscovery
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $mainDomain = config('app.url'); // e.g., http://frontalminds.com.ng
        $mainDomainHost = parse_url($mainDomain, PHP_URL_HOST);

        // Check if we are on a subdomain
        if ($host !== $mainDomainHost && str_ends_with($host, $mainDomainHost)) {
            $subdomain = str_replace('.' . $mainDomainHost, '', $host);

            // Try to find the school (tenant) by slug
            $tenant = Tenant::where('slug', $subdomain)->first();

            if (!$tenant) {
                // If school not found, redirect to main site or show 404
                abort(404, "School instance '$subdomain' not found.");
            }

            // Important: Bind the tenant to the application container
            // This allows you to access it anywhere using app('currentTenant')
            app()->instance('currentTenant', $tenant);

            // You can also add it to the request for easy access
            $request->attributes->add(['tenant' => $tenant]);
        }

        return $next($request);
    }
}
