<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

// Handle missing .env file during installation
// This must happen BEFORE Laravel boots, as Laravel requires APP_KEY to bootstrap
$envFile     = __DIR__ . '/../.env';
$envExample  = __DIR__ . '/../.env.example';
$lockFile    = __DIR__ . '/../storage/install.lock';
$isInstalled = file_exists($lockFile);

// If no lock file exists and no .env, create minimal .env for installation
if (!$isInstalled && !file_exists($envFile)) {
    if (file_exists($envExample)) {
        copy($envExample, $envFile);
    } else {
        // Create minimal .env with required structure
        $minimalEnv = "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n\n";
        file_put_contents($envFile, $minimalEnv);
    }
}

// Ensure APP_KEY exists and SESSION_DRIVER=file during installation
if (file_exists($envFile) && !$isInstalled) {
    $envContent = file_get_contents($envFile);
    $needsUpdate = false;
    
    // Parse .env manually to check APP_KEY (env() doesn't work before Laravel boots)
    $hasAppKey = preg_match('/^APP_KEY=(.+)$/m', $envContent, $matches);
    
    if (!$hasAppKey || empty(trim($matches[1] ?? ''))) {
        // Generate temporary key for installation
        $key = 'base64:' . base64_encode(random_bytes(32));
        if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
            $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
        } else {
            // Add APP_KEY at the beginning
            $envContent = "APP_KEY={$key}\n" . $envContent;
        }
        $needsUpdate = true;
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
}

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
