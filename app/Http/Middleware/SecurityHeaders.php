<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content Security Policy (Vite HMR allowed only in local/debug)
        $allowVite = app()->environment('local') || (bool) config('app.debug');
        $viteHttp  = $allowVite ? ' http://127.0.0.1:5173 http://localhost:5173' : '';
        $viteWs    = $allowVite ? ' ws://127.0.0.1:5173 ws://localhost:5173' : '';

        $csp = "default-src 'self'; ".
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.skypack.dev{$viteHttp}; ".
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net{$viteHttp}; ".
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net{$viteHttp}; ".
            "img-src 'self' data: blob: https:{$viteHttp}; ".
            "connect-src 'self' https://medex.com.bd{$viteHttp}{$viteWs}; ".
            "frame-ancestors 'none';";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
