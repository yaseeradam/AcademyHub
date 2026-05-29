<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies that WhatsApp Bot API requests carry a valid shared secret.
 *
 * The bot must send the key in the `X-WhatsApp-Api-Key` header.
 * The expected key is set via the WHATSAPP_API_KEY env variable.
 */
class VerifyWhatsAppApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.whatsapp.api_key');

        if (empty($expected)) {
            abort(503, 'WhatsApp API key is not configured on this server.');
        }

        $provided = $request->header('X-WhatsApp-Api-Key') ?: $request->query('key');

        if (! $provided || ! hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API key.',
            ], 401);
        }

        return $next($request);
    }
}
