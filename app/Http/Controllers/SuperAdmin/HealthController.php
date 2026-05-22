<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HealthController extends Controller
{
    public function index(Request $request)
    {
        // 1. Gather System Load & Environment Information
        $systemLoad = function_exists('sys_getloadavg') ? sys_getloadavg() : [0.0, 0.0, 0.0];
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();

        // 2. Memory limits & Peak usage
        $memLimit = ini_get('memory_limit');
        $memPeak = memory_get_peak_usage(true);
        $memPeakMB = round($memPeak / 1024 / 1024, 2);

        // 3. Disk space checks
        $diskTotal = @disk_total_space('/') ?: 0;
        $diskFree = @disk_free_space('/') ?: 0;
        $diskUsed = $diskTotal - $diskFree;
        $diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;
        
        $diskTotalGB = round($diskTotal / 1024 / 1024 / 1024, 2);
        $diskUsedGB = round($diskUsed / 1024 / 1024 / 1024, 2);

        // 4. Database diagnostic test
        $dbStart = microtime(true);
        try {
            DB::connection()->getPdo();
            $dbStatus = 'Healthy';
            $dbLatency = round((microtime(true) - $dbStart) * 1000, 2) . ' ms';
        } catch (\Exception $e) {
            $dbStatus = 'Unhealthy (Error)';
            $dbLatency = 'N/A';
        }

        // 5. Cache diagnostic test
        $cacheStart = microtime(true);
        try {
            Cache::put('health_check_ping', 'pong', 5);
            $ping = Cache::get('health_check_ping');
            $cacheStatus = $ping === 'pong' ? 'Healthy' : 'Unhealthy';
            $cacheLatency = round((microtime(true) - $cacheStart) * 1000, 2) . ' ms';
        } catch (\Exception $e) {
            $cacheStatus = 'Unhealthy (Error)';
            $cacheLatency = 'N/A';
        }

        // 6. SMTP Email socket ping
        $mailHost = config('mail.mailers.smtp.host');
        $mailPort = config('mail.mailers.smtp.port', 587);
        $mailStatus = 'Unconfigured';
        if ($mailHost) {
            $fp = @fsockopen($mailHost, $mailPort, $errno, $errstr, 1.5);
            if ($fp) {
                $mailStatus = 'Healthy';
                fclose($fp);
            } else {
                $mailStatus = 'SMTP Connection Refused';
            }
        }

        // 7. Paystack External API latency ping
        $paystackStart = microtime(true);
        $paystackStatus = 'Offline';
        $paystackLatency = 'N/A';
        try {
            $ch = curl_init('https://api.paystack.co');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode > 0) {
                $paystackStatus = 'Online';
                $paystackLatency = round((microtime(true) - $paystackStart) * 1000, 2) . ' ms';
            }
            curl_close($ch);
        } catch (\Exception $e) {
            $paystackStatus = 'Offline';
        }

        // 8. Queue diagnostics
        $failedJobsCount = 0;
        try {
            if (Schema::hasTable('failed_jobs')) {
                $failedJobsCount = DB::table('failed_jobs')->count();
            }
        } catch (\Exception $e) {
            // failed jobs table might not exist
        }

        // 9. Read GET routes for diagnostic ping suite instead of laravel logs
        $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();
        $routes = [];

        foreach ($routeCollection as $value) {
            $methods = $value->methods();
            if (in_array('GET', $methods)) {
                $uri = $value->uri();
                
                // Exclude system paths
                if (
                    str_starts_with($uri, '_') ||
                    str_starts_with($uri, 'sanctum') ||
                    str_starts_with($uri, 'telescope') ||
                    str_starts_with($uri, 'horizon') ||
                    str_starts_with($uri, 'superadmin') ||
                    str_contains($uri, 'api/user')
                ) {
                    continue;
                }

                $routes[] = [
                    'uri' => '/' . ltrim($uri, '/'),
                    'name' => $value->getName() ?? 'unnamed',
                    'action' => str_replace('App\\Http\\Controllers\\', '', $value->getActionName()),
                ];
            }
        }
        usort($routes, fn($a, $b) => strcmp($a['uri'], $b['uri']));

        // 10. Fetch all tenants for Masquerading Console
        $search = $request->input('search');
        $tenantsQuery = Tenant::query();
        if ($search) {
            $tenantsQuery->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('domain', 'like', "%{$search}%");
        }
        $tenants = $tenantsQuery->latest()->get()->map(function ($tenant) {
            // Find school admin user
            $admin = User::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('role', 'admin')
                ->first();
            $tenant->admin_user = $admin;
            return $tenant;
        });

        $systemStats = [
            'load'             => $systemLoad,
            'php'              => $phpVersion,
            'laravel'          => $laravelVersion,
            'mem_limit'        => $memLimit,
            'mem_peak'         => $memPeakMB,
            'disk_total'       => $diskTotalGB,
            'disk_used'        => $diskUsedGB,
            'disk_percent'     => $diskPercent,
            'db_status'        => $dbStatus,
            'db_latency'       => $dbLatency,
            'cache_status'     => $cacheStatus,
            'cache_latency'    => $cacheLatency,
            'mail_status'      => $mailStatus,
            'paystack_status'  => $paystackStatus,
            'paystack_latency' => $paystackLatency,
            'failed_jobs'      => $failedJobsCount,
        ];

        $initialPingState = Cache::get('health_ping_state') ?: [
            'pinging' => false,
            'current_index' => -1,
            'total_count' => count($routes),
            'results' => (object)[],
            'completed_count' => 0,
            'updated_at' => now()->toIso8601String()
        ];

        return view('superadmin.health', compact('systemStats', 'routes', 'tenants', 'search', 'initialPingState'));
    }

    public function clearCache()
    {
        abort_unless(auth()->user()?->is_super_admin, 403);

        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            return back()->with('success', 'Application cache, configurations, and compiled views cleared successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear system cache: ' . $e->getMessage());
        }
    }

    public function clearLogs()
    {
        abort_unless(auth()->user()?->is_super_admin, 403);

        $logPath = storage_path('logs/laravel.log');
        try {
            if (file_exists($logPath)) {
                file_put_contents($logPath, '');
            }
            return back()->with('success', 'System log file truncated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear log file: ' . $e->getMessage());
        }
    }

    public function endpoints(Request $request)
    {
        $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();
        $routes = [];

        foreach ($routeCollection as $value) {
            $methods = $value->methods();
            // Filter to GET routes
            if (in_array('GET', $methods)) {
                $uri = $value->uri();
                
                // Exclude system paths
                if (
                    str_starts_with($uri, '_') ||
                    str_starts_with($uri, 'sanctum') ||
                    str_starts_with($uri, 'telescope') ||
                    str_starts_with($uri, 'horizon') ||
                    str_starts_with($uri, 'superadmin') ||
                    str_contains($uri, 'api/user')
                ) {
                    continue;
                }

                $routes[] = [
                    'uri' => '/' . ltrim($uri, '/'),
                    'name' => $value->getName() ?? 'unnamed',
                    'action' => str_replace('App\\Http\\Controllers\\', '', $value->getActionName()),
                ];
            }
        }

        // Sort routes by URI for structured layout
        usort($routes, fn($a, $b) => strcmp($a['uri'], $b['uri']));

        return view('superadmin.health.endpoints', compact('routes'));
    }

    public function pingEndpoint(Request $request)
    {
        $uri = $request->input('uri');
        if (!$uri) {
            return response()->json(['error' => 'URI is required.'], 400);
        }

        // Resolve wildcards in the URI using DB scopes bypassed queries
        $resolvedUri = preg_replace_callback('/\{([^}]+)\}/', function ($matches) {
            $param = $matches[1];
            
            try {
                switch ($param) {
                    case 'student':
                        return \App\Models\Student::withoutGlobalScopes()->where('status', 'active')->first()?->id ?? 1;
                    case 'teacher':
                        return \App\Models\User::withoutGlobalScopes()->where('role', 'teacher')->first()?->id ?? 1;
                    case 'parent':
                        return \App\Models\User::withoutGlobalScopes()->where('role', 'parent')->first()?->id ?? 1;
                    case 'bursar':
                        return \App\Models\User::withoutGlobalScopes()->where('role', 'bursar')->first()?->id ?? 1;
                    case 'user':
                        return \App\Models\User::withoutGlobalScopes()->first()?->id ?? 1;
                    case 'tenant':
                        return \App\Models\Tenant::first()?->id ?? 1;
                    case 'component':
                        return \App\Models\MarketplaceComponent::first()?->id ?? 1;
                    case 'bill':
                        return \App\Models\TenantPluginBill::first()?->id ?? 1;
                    case 'admin':
                        return \App\Models\User::withoutGlobalScopes()->where('role', 'admin')->first()?->id ?? 1;
                    case 'class':
                    case 'school_class':
                        return \App\Models\SchoolClass::withoutGlobalScopes()->first()?->id ?? 1;
                    case 'subject':
                        return \App\Models\Subject::withoutGlobalScopes()->first()?->id ?? 1;
                    case 'session':
                    case 'academic_session':
                        return \App\Models\AcademicSession::withoutGlobalScopes()->first()?->id ?? 1;
                    default:
                        return 1;
                }
            } catch (\Throwable $e) {
                return 1;
            }
        }, $uri);

        // Build target host (first active tenant, or default config URL)
        $appUrl = config('app.url');
        $parsed = parse_url($appUrl);
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? 'localhost';

        $tenant = Tenant::where('status', 'active')->first();
        if ($tenant) {
            $targetHost = $tenant->domain ?: $tenant->slug . '.' . $host;
        } else {
            $targetHost = $host;
        }

        $pingUrl = $scheme . '://' . $targetHost . $port . '/' . ltrim($resolvedUri, '/');

        // Execute Curl ping
        $startTime = microtime(true);
        
        $ch = curl_init($pingUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MyAcademy Diagnostic Ping/1.0');
        
        // Also set Host header just in case local wildcard hostname resolution is tricky!
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Host: {$targetHost}"
        ]);

        curl_exec($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $totalTime = round((microtime(true) - $startTime) * 1000, 2);
        
        if (curl_errno($ch)) {
            $errorMsg = curl_error($ch);
            curl_close($ch);
            return response()->json([
                'resolved_url' => $pingUrl,
                'status_code' => 0,
                'response_time_ms' => $totalTime,
                'status_message' => 'Connection Refused: ' . $errorMsg,
            ]);
        }
        
        curl_close($ch);

        // Translate HTTP code to status messages
        $statusMsg = match ($httpCode) {
            200 => '200 OK (Reachable)',
            301, 302 => '302 Found (Auth Protected Redirect)',
            400 => '400 Bad Request',
            401 => '401 Unauthorized (Auth Required)',
            403 => '403 Forbidden (Blocked)',
            404 => '404 Not Found (Missing)',
            500 => '500 Server Error (Fatal)',
            default => $httpCode . ' Response Code',
        };

        return response()->json([
            'resolved_url' => $pingUrl,
            'status_code' => $httpCode,
            'response_time_ms' => $totalTime,
            'status_message' => $statusMsg,
        ]);
    }

    public function startBackgroundPing(Request $request)
    {
        abort_unless(auth()->user()?->is_super_admin, 403);

        $state = Cache::get('health_ping_state');
        if ($state && ($state['pinging'] ?? false)) {
            return response()->json(['success' => true, 'already_running' => true]);
        }

        Cache::put('health_ping_state', [
            'pinging' => true,
            'current_index' => 0,
            'total_count' => 0,
            'results' => [],
            'completed_count' => 0,
            'updated_at' => now()->toIso8601String()
        ], 3600);

        // Run Artisan command in the background asynchronously
        $command = 'php ' . base_path('artisan') . ' platform:ping-endpoints > /dev/null 2>&1 &';
        exec($command);

        return response()->json(['success' => true, 'already_running' => false]);
    }

    public function pingStatus(Request $request)
    {
        abort_unless(auth()->user()?->is_super_admin, 403);

        $state = Cache::get('health_ping_state') ?: [
            'pinging' => false,
            'current_index' => -1,
            'total_count' => 0,
            'results' => (object)[],
            'completed_count' => 0,
            'updated_at' => now()->toIso8601String()
        ];

        return response()->json($state);
    }
}
