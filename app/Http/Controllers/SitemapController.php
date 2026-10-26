<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Generate dynamic, multi-tenant aware XML sitemap.
     */
    public function index(Request $request): Response
    {
        $host = $request->getHost();
        $cacheKey = 'seo_sitemap_' . md5($host);

        $xml = Cache::remember($cacheKey, 3600, function () use ($request, $host) {
            $urls = [];
            $now = Carbon::now()->toIso8601String();
            $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

            // 1. Core Public Routes for the current host/tenant
            $urls[] = [
                'loc' => $baseUrl . '/',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ];

            $urls[] = [
                'loc' => $baseUrl . '/login',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '0.9',
            ];

            $urls[] = [
                'loc' => $baseUrl . '/cbt/portal',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];

            $urls[] = [
                'loc' => $baseUrl . '/cbt/student',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];

            $urls[] = [
                'loc' => $baseUrl . '/offline',
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ];

            // 2. If this is the main domain (not a tenant instance), discover active tenant portals
            $isMainDomain = !app()->bound('currentTenant') || app('currentTenant') === null;
            if ($isMainDomain) {
                try {
                    $tenants = Tenant::query()
                        ->where('status', 'active')
                        ->get(['id', 'name', 'slug', 'domain', 'updated_at']);

                    $mainDomain = config('app.url');
                    $scheme = parse_url($mainDomain, PHP_URL_SCHEME) ?: 'https';
                    $mainDomainHost = parse_url($mainDomain, PHP_URL_HOST);

                    foreach ($tenants as $tenant) {
                        $tenantLastMod = $tenant->updated_at ? $tenant->updated_at->toIso8601String() : $now;
                        $tenantBase = null;

                        if (!empty($tenant->domain)) {
                            $tenantBase = $scheme . '://' . $tenant->domain;
                        } elseif (!empty($tenant->slug) && $mainDomainHost) {
                            $tenantBase = $scheme . '://' . $tenant->slug . '.' . $mainDomainHost;
                        }

                        if ($tenantBase && $tenantBase !== $baseUrl) {
                            $urls[] = [
                                'loc' => $tenantBase . '/login',
                                'lastmod' => $tenantLastMod,
                                'changefreq' => 'weekly',
                                'priority' => '0.8',
                            ];
                            $urls[] = [
                                'loc' => $tenantBase . '/cbt/portal',
                                'lastmod' => $tenantLastMod,
                                'changefreq' => 'weekly',
                                'priority' => '0.7',
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    // Fall back cleanly if tenants table cannot be queried
                }
            }

            // Build XML payload
            $xmlLines = [];
            $xmlLines[] = '<?xml version="1.0" encoding="UTF-8"?>';
            $xmlLines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

            foreach ($urls as $item) {
                $xmlLines[] = '  <url>';
                $xmlLines[] = '    <loc>' . htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8') . '</loc>';
                $xmlLines[] = '    <lastmod>' . $item['lastmod'] . '</lastmod>';
                $xmlLines[] = '    <changefreq>' . $item['changefreq'] . '</changefreq>';
                $xmlLines[] = '    <priority>' . $item['priority'] . '</priority>';
                $xmlLines[] = '  </url>';
            }

            $xmlLines[] = '</urlset>';

            return implode("\n", $xmlLines);
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600, must-revalidate');
    }
}
