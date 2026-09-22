<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'log-error',
        'iclock/*',         // ZKTeco K40 ADMS push (root-level)
        'api/iclock/*',     // ZKTeco K40 ADMS push (api-prefixed)
        'api/zkteco/*',     // ZKTeco alternative endpoints
    ];
}
