<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add baseline security headers to every response.
 *
 * Headers applied:
 *   X-Content-Type-Options: nosniff
 *   X-Frame-Options: SAMEORIGIN
 *   Strict-Transport-Security: max-age=31536000; includeSubDomains
 *   Referrer-Policy: strict-origin-when-cross-origin
 *   Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()
 *
 * ponytail: no CSP yet — enabled via config/permissions.php when ready.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()');

        // Baseline CSP — restrict sources to what the app actually uses
        // ponytail: tighten 'script-src' with nonces once all inline scripts are extracted.
        $response->headers->set('Content-Security-Policy',
            "default-src 'none'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://static.cloudflareinsights.com https://cdn.jsdelivr.net; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com https://cdn.jsdelivr.net; "
            . "img-src 'self' data: https://*.tile.openstreetmap.org https://*.basemaps.cartocdn.com https://upload.wikimedia.org https://raw.githubusercontent.com https://cdnjs.cloudflare.com https://ui-avatars.com https://*.gravatar.com; "
            . "font-src 'self' https://fonts.gstatic.com; "
            . "connect-src 'self' https://*.tile.openstreetmap.org https://*.basemaps.cartocdn.com https://*.wikipedia.org https://nominatim.openstreetmap.org https://unpkg.com; "
            . "frame-src 'self'; "
            . "form-action 'self'; "
            . "base-uri 'self'; "
            . "manifest-src 'self'"
        );

        return $response;
    }
}