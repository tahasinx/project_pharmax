<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip installation check for installation routes
        if ($request->is('install*')) {
            return $next($request);
        }

        // Check if application is installed
        if (!$this->isInstalled()) {
            return redirect()->route('install');
        }

        return $next($request);
    }

    protected function isInstalled()
    {
        return file_exists(storage_path('app/installed'));
    }
}
