<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

/**
 * ForceFileSessions Middleware
 *
 * Forces file-based sessions during installation to avoid
 * database dependency before installation is complete.
 */
class ForceFileSessions
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if we're in installation mode
        // Installation is not complete if lock file doesn't exist
        $lockFile     = storage_path('install.lock');
        $isInstalling = ! file_exists($lockFile);

        // If installing (no lock file) or accessing install routes, force file-based sessions
        if ($isInstalling || $request->is('install*')) {
            Config::set('session.driver', 'file');

            // Ensure session directory exists
            $sessionPath = storage_path('framework/sessions');
            if (! is_dir($sessionPath)) {
                @mkdir($sessionPath, 0755, true);
            }
        }

        return $next($request);
    }
}
