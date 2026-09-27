<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KeepCentralOnPlatform
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->attributes->get('tenant.mode') !== 'central') {
            return $next($request);
        }

        if ($request->is('platform*', 'profile*', 'login', 'logout', 'forgot-password', 'reset-password*', 'company-login/*', 'sanctum/*')) {
            return $next($request);
        }

        return redirect('/platform');
    }
}
