<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Encryption\EncryptionServiceProvider;

/**
 * InstallationBootstrapServiceProvider
 * 
 * Handles bootstrap requirements during installation when .env doesn't exist.
 * Provides a temporary APP_KEY so Laravel can bootstrap for installation wizard.
 * 
 * @package App\Providers
 */
class InstallationBootstrapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Check if we're in installation mode (no .env or no APP_KEY)
        $envFile  = base_path('.env');
        $lockFile = storage_path('install.lock');
        $appKey   = env('APP_KEY');

        // If no lock file exists and no .env or no APP_KEY, we're installing
        $isInstalling = !file_exists($lockFile) && (!file_exists($envFile) || empty($appKey));

        if ($isInstalling) {
            // Create minimal .env if it doesn't exist
            if (!file_exists($envFile)) {
                $envExample = base_path('.env.example');
                if (file_exists($envExample)) {
                    copy($envExample, $envFile);
                } else {
                    // Create minimal .env with required keys
                    $minimalEnv = "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n\n";
                    file_put_contents($envFile, $minimalEnv);
                }
            }

            // Generate APP_KEY if missing and ensure SESSION_DRIVER=file
            if (file_exists($envFile)) {
                try {
                    // Read .env content
                    $envContent = file_get_contents($envFile);
                    $needsUpdate = false;
                    
                    // Generate APP_KEY if missing
                    if (empty($appKey)) {
                        $key = 'base64:' . base64_encode(random_bytes(32));
                        
                        // Update APP_KEY in .env
                        if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                            $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
                        } else {
                            $envContent .= "\nAPP_KEY=" . $key . "\n";
                        }
                        
                        $needsUpdate = true;
                        
                        // Clear config cache to reload .env
                        if (function_exists('config')) {
                            config(['app.key' => $key]);
                        }
                    }
                    
                    // Force SESSION_DRIVER=file during installation (before database exists)
                    if (preg_match('/^SESSION_DRIVER=.*$/m', $envContent)) {
                        $envContent = preg_replace('/^SESSION_DRIVER=.*$/m', 'SESSION_DRIVER=file', $envContent);
                        $needsUpdate = true;
                    } else {
                        // Add SESSION_DRIVER if missing
                        $envContent .= "\nSESSION_DRIVER=file\n";
                        $needsUpdate = true;
                    }
                    
                    if ($needsUpdate) {
                        file_put_contents($envFile, $envContent);
                    }
                } catch (\Exception $e) {
                    // Silently fail - installation wizard will handle it
                }
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

