<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('database.tenant.enabled', false)) {
            abort(404);
        }

        if ($request->attributes->get('tenant.mode') !== 'central') {
            abort(404);
        }

        return $next($request);
    }
}
