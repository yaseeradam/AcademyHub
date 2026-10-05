<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PwaController extends Controller
{
    /**
     * Generate dynamic Web App Manifest tailored to the current school/tenant.
     */
    public function manifest(Request $request): JsonResponse
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        
        $schoolName = config('academyhub.school_name');
        if (!$schoolName && $tenant) {
            $schoolName = $tenant->name;
        }
        $appName = $schoolName ?: config('app.name', 'AcademyHub');
        $shortName = Str::limit($appName, 14, '');

        $themeColor = '#0f172a';
        $backgroundColor = '#0f172a'; // Native dark slate background eliminates blinding white splash flash

        $schoolLogo = config('academyhub.school_logo');
        $logoUrl = config('academyhub.logo_url');
        if (!$logoUrl && $schoolLogo) {
            $logoUrl = asset('uploads/' . str_replace('\\', '/', $schoolLogo));
        }
        if (!$logoUrl && $tenant && !empty($tenant->logo)) {
            $logoUrl = asset('uploads/' . str_replace('\\', '/', $tenant->logo));
        }

        $icons = [];

        // If custom school logo is uploaded, offer it for both any and maskable
        if ($logoUrl) {
            $icons[] = [
                'src' => $logoUrl,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any'
            ];
            $icons[] = [
                'src' => $logoUrl,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any'
            ];
            $icons[] = [
                'src' => $logoUrl,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable'
            ];
        }

        // Standard app icons with separated purposes for best Android/Desktop rendering
        $icons[] = [
            'src' => asset('icons/icon-192.png'),
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any'
        ];
        $icons[] = [
            'src' => asset('icons/icon-192.png'),
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'maskable'
        ];
        $icons[] = [
            'src' => asset('icons/icon-512.png'),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any'
        ];
        $icons[] = [
            'src' => asset('icons/icon-512.png'),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable'
        ];
        $icons[] = [
            'src' => asset('favicon.ico'),
            'sizes' => '64x64',
            'type' => 'image/x-icon'
        ];

        $manifest = [
            'name' => $appName,
            'short_name' => $shortName,
            'description' => $appName . ' — School Management Suite, Attendance, CBT, Results & Portal',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'display_override' => ['window-controls-overlay', 'standalone', 'minimal-ui'],
            'orientation' => 'any',
            'prefer_related_applications' => false,
            'launch_handler' => [
                'client_mode' => 'auto'
            ],
            'background_color' => $backgroundColor,
            'theme_color' => $themeColor,
            'icons' => $icons,
            'categories' => ['education', 'productivity'],
            'lang' => 'en',
            'dir' => 'ltr',
            'shortcuts' => [
                [
                    'name' => 'Dashboard',
                    'short_name' => 'Dashboard',
                    'description' => 'Go to Dashboard',
                    'url' => '/dashboard',
                    'icons' => [['src' => asset('icons/icon-192.png'), 'sizes' => '192x192', 'purpose' => 'any']]
                ],
                [
                    'name' => 'Attendance',
                    'short_name' => 'Attendance',
                    'description' => 'View Attendance',
                    'url' => '/attendance',
                    'icons' => [['src' => asset('icons/icon-192.png'), 'sizes' => '192x192', 'purpose' => 'any']]
                ]
            ]
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Render the native-feel offline fallback page.
     */
    public function offline(): View
    {
        return view('pages.offline');
    }
}
