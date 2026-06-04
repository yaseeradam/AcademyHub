<?php

namespace App\Http\Middleware;

use App\Support\TenantSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class LoadTenantSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        TenantSettings::loadToConfig();

        return $next($request);
    }
}

