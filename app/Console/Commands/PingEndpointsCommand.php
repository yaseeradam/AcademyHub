<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Models\Tenant;
use App\Models\Student;
use App\Models\User;
use App\Models\MarketplaceComponent;
use App\Models\TenantPluginBill;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicSession;

class PingEndpointsCommand extends Command
{
    protected $signature = 'platform:ping-endpoints';
    protected $description = 'Runs sequential diagnostics on all platform GET endpoints in the background';

    public function handle()
    {
        // 1. Gather all GET routes
        $routeCollection = Route::getRoutes();
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
        $totalCount = count($routes);

        if ($totalCount === 0) {
            Cache::put('health_ping_state', [
                'pinging' => false,
                'current_index' => -1,
                'total_count' => 0,
                'results' => [],
                'completed_count' => 0,
                'updated_at' => now()->toIso8601String()
            ], 86400);
            return 0;
        }

        // 2. Initialize Cache state
        Cache::put('health_ping_state', [
            'pinging' => true,
            'current_index' => 0,
            'total_count' => $totalCount,
            'results' => [],
            'completed_count' => 0,
            'updated_at' => now()->toIso8601String()
        ], 3600);

        // 3. Sequential ping loop
        foreach ($routes as $index => $route) {
            // Update current index and status
            $state = Cache::get('health_ping_state', []);
            $state['pinging'] = true;
            $state['current_index'] = $index;
            $state['completed_count'] = $index;
            $state['updated_at'] = now()->toIso8601String();
            Cache::put('health_ping_state', $state, 3600);

            // Execute ping
            $result = $this->pingSingle($route['uri']);

            // Save results back to Cache
            $state = Cache::get('health_ping_state', []);
            $state['results'][$route['uri']] = $result;
            $state['completed_count'] = $index + 1;
            
            if ($index + 1 >= $totalCount) {
                $state['pinging'] = false;
                $state['current_index'] = -1;
            }
            
            $state['updated_at'] = now()->toIso8601String();
            Cache::put('health_ping_state', $state, 3600);

            // Short delay to avoid local rate-limiting and make UI smooth
            usleep(100000); // 100ms
        }

        return 0;
    }

    private function pingSingle(string $uri): array
    {
        // Resolve wildcards in the URI using DB scopes bypassed queries
        $resolvedUri = preg_replace_callback('/\{([^}]+)\}/', function ($matches) {
            $param = $matches[1];
            
            try {
                switch ($param) {
                    case 'student':
                        return Student::withoutGlobalScopes()->where('status', 'active')->first()?->id ?? 1;
                    case 'teacher':
                        return User::withoutGlobalScopes()->where('role', 'teacher')->first()?->id ?? 1;
                    case 'parent':
                        return User::withoutGlobalScopes()->where('role', 'parent')->first()?->id ?? 1;
                    case 'bursar':
                        return User::withoutGlobalScopes()->where('role', 'bursar')->first()?->id ?? 1;
                    case 'user':
                        return User::withoutGlobalScopes()->first()?->id ?? 1;
                    case 'tenant':
                        return Tenant::first()?->id ?? 1;
                    case 'component':
                        return MarketplaceComponent::first()?->id ?? 1;
                    case 'bill':
                        return TenantPluginBill::first()?->id ?? 1;
                    case 'admin':
                        return User::withoutGlobalScopes()->where('role', 'admin')->first()?->id ?? 1;
                    case 'class':
                    case 'school_class':
                        return SchoolClass::withoutGlobalScopes()->first()?->id ?? 1;
                    case 'subject':
                        return Subject::withoutGlobalScopes()->first()?->id ?? 1;
                    case 'session':
                    case 'academic_session':
                        return AcademicSession::withoutGlobalScopes()->first()?->id ?? 1;
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
        curl_setopt($ch, CURLOPT_USERAGENT, 'AcademyHub Diagnostic Ping/1.0');
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Host: {$targetHost}"
        ]);

        curl_exec($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $totalTime = round((microtime(true) - $startTime) * 1000, 2);
        
        if (curl_errno($ch)) {
            $errorMsg = curl_error($ch);
            curl_close($ch);
            return [
                'resolved_url' => $pingUrl,
                'status_code' => 0,
                'response_time_ms' => $totalTime,
                'status_message' => 'Connection Refused: ' . $errorMsg,
            ];
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

        return [
            'resolved_url' => $pingUrl,
            'status_code' => $httpCode,
            'response_time_ms' => $totalTime,
            'status_message' => $statusMsg,
        ];
    }
}
