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

        $tenant = null;

        // The main domain (no tenant context) must never resolve to a tenant.
        // Otherwise superadmin routes become inaccessible if a tenant is accidentally
        // configured with the same domain as APP_URL.
        if (! $mainDomainHost || $host !== $mainDomainHost) {
            // 1) Custom domain mapping (exact host match)
            $tenant = Tenant::query()->where('domain', $host)->first();
        }

        // 2) Subdomain mapping: {slug}.{mainDomainHost}
        if (! $tenant && $mainDomainHost && $host !== $mainDomainHost && str_ends_with($host, $mainDomainHost)) {
            $subdomain = str_replace('.' . $mainDomainHost, '', $host);
            $tenant = Tenant::query()->where('slug', $subdomain)->first();

            if (! $tenant && $subdomain !== '' && str_starts_with($subdomain, 'www.')) {
                $tenant = Tenant::query()->where('slug', substr($subdomain, 4))->first();
            }
        }

        if ($tenant) {
            \Illuminate\Support\Facades\Log::debug('TenantDiscovery: Resolved tenant', [
                'host' => $host,
                'tenant_id' => $tenant->id,
                'tenant_slug' => $tenant->slug,
            ]);
            app()->instance('currentTenant', $tenant);
            $request->attributes->add(['tenant' => $tenant]);
        } elseif ($mainDomainHost && $host !== $mainDomainHost) {
            \Illuminate\Support\Facades\Log::debug('TenantDiscovery: Mismatch subdomain', [
                'host' => $host,
                'mainDomainHost' => $mainDomainHost,
            ]);
            // Only skip 404 for bare localhost/127.0.0.1 — subdomain mismatches should still 404
            if (!in_array($host, ['localhost', '127.0.0.1'])) {
                abort(404, "School instance '{$host}' not found.");
            }
        } else {
            \Illuminate\Support\Facades\Log::debug('TenantDiscovery: Main domain or fallback', [
                'host' => $host,
                'mainDomainHost' => $mainDomainHost,
            ]);
        }

        return $next($request);
    }
}
