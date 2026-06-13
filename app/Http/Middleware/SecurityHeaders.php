<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Only enforce strict CSP & HSTS in a true production deployment where Vite hot reload is NOT active
        if (app()->environment('production') && ! file_exists(public_path('hot'))) {
            $cspDomains = $this->getCspDomains($request);
            $cspDomainsStr = implode(' ', $cspDomains);

            $csp = "default-src {$cspDomainsStr}; " .
                   "script-src {$cspDomainsStr} 'unsafe-inline' 'unsafe-eval' https://js.paystack.co https://cdn.jsdelivr.net; " .
                   "style-src {$cspDomainsStr} 'unsafe-inline' https://fonts.googleapis.com; " .
                   "img-src {$cspDomainsStr} data: https:; " .
                   "connect-src {$cspDomainsStr} https://api.paystack.co; " .
                   "font-src {$cspDomainsStr} https://fonts.gstatic.com; " .
                   "frame-src {$cspDomainsStr} https://js.paystack.co;";
            $response->headers->set('Content-Security-Policy', $csp);

            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }

    /**
     * Dynamically construct allowed CSP domains including app.url and current request host.
     */
    private function getCspDomains(Request $request): array
    {
        $domains = ["'self'"];

        // Get central host from config
        $appUrl = config('app.url');
        if ($appUrl) {
            $centralHost = parse_url($appUrl, PHP_URL_HOST);
            if ($centralHost) {
                $domains[] = $centralHost;
                $domains[] = "{$centralHost}:*";
                if (!filter_var($centralHost, FILTER_VALIDATE_IP)) {
                    $domains[] = "*.{$centralHost}";
                    $domains[] = "*.{$centralHost}:*";
                }
            }
        }

        // Get current request host
        $requestHost = $request->getHost();
        if ($requestHost) {
            $domains[] = $requestHost;
            $domains[] = "{$requestHost}:*";
            if (!filter_var($requestHost, FILTER_VALIDATE_IP)) {
                $domains[] = "*.{$requestHost}";
                $domains[] = "*.{$requestHost}:*";
            }
        }

        return array_unique($domains);
    }
}


