<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

/**
 * InstallationServiceProvider
 * 
 * Ensures file-based sessions are used during installation
 * to avoid database dependency before installation is complete.
 * 
 * @package App\Providers
 */
class InstallationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Check if we're in installation mode
        // Installation is not complete if lock file doesn't exist
        $lockFile     = storage_path('install.lock');
        $isInstalling = !file_exists($lockFile);

        // If installing (no lock file) or accessing install routes, force file-based sessions
        if ($isInstalling || (request()->is('install*'))) {
            Config::set('session.driver', 'file');

            // Ensure session directory exists
            $sessionPath = storage_path('framework/sessions');
            if (!is_dir($sessionPath)) {
                @mkdir($sessionPath, 0755, true);
            }
        }
    }
}

