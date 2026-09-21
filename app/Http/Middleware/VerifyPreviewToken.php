<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Melindungi route preview template dengan token rahasia.
 * Token dibaca dari query string ?token=... dan dibandingkan dengan
 * nilai config('app.preview_token').
 */
class VerifyPreviewToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('app.preview_token');

        if (empty($token) || !hash_equals($token, (string) $request->query('token', ''))) {
            abort(403, 'Token preview tidak valid.');
        }

        return $next($request);
    }
}
