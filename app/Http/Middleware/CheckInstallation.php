<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckInstallation Middleware
 *
 * Manages installation state:
 * - If not installed: Redirects to /install, allows access to install routes
 * - If installed: Prevents access to /install routes, allows access to app
 */
class CheckInstallation
{
    /**
     * Check if application is installed.
     */
    private function isInstalled(): bool
    {
        $lockFile = storage_path('install.lock');

        // Primary check: lock file must exist
        // If no lock file, application is definitely not installed
        if (! file_exists($lockFile)) {
            return false;
        }

        // If lock file exists, consider installed
        // Don't check database connection here to avoid redirect loops
        return true;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Wrap in try-catch to handle cases where .env doesn't exist
        try {
            $isInstalled = $this->isInstalled();
        } catch (\Exception $e) {
            // If any error occurs (config not loaded, etc.), consider not installed
            $isInstalled = false;
        }

        $isInstallRoute = $request->is('install*');
        $isApiRoute     = $request->is('api/*');
        $isCsrfRoute    = $request->is('get/new/csrf-token');

        // If application is installed
        if ($isInstalled) {
            // Prevent access to installation routes (except complete page for display)
            if ($isInstallRoute && ! $request->is('install/complete')) {
                // Redirect to login page using absolute path to avoid URL duplication
                $loginPath = '/login';

                return redirect($loginPath)->with('info', 'Application is already installed.');
            }

            // Allow access to everything else
            return $next($request);
        }

        // If application is NOT installed
        // Allow access to installation routes (these work without .env)
        if ($isInstallRoute) {
            return $next($request);
        }

        // Allow access to API routes (for webhooks during installation)
        if ($isApiRoute) {
            return $next($request);
        }

        // Allow CSRF token route (needed for installation forms)
        if ($isCsrfRoute) {
            return $next($request);
        }

        // Redirect ALL other routes to installation (including welcome page)
        return redirect()->route('install.index');
    }
}
