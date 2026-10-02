<?php

namespace App\Http;

use App\Http\Middleware\AuditLogging;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckInstallation;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnsureCentralHost;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\ForceFileSessions;
use App\Http\Middleware\HandleApiErrors;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\InputSanitization;
use App\Http\Middleware\KeepCentralOnPlatform;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RateLimiting;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SwitchTenantDatabase;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\ValidateSignature;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        ForceFileSessions::class,
        SecurityHeaders::class,
        InputSanitization::class,
        AuditLogging::class,
        CheckInstallation::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            SwitchTenantDatabase::class,
            KeepCentralOnPlatform::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            HandleInertiaRequests::class,
            HandleApiErrors::class,
            AddLinkHeadersForPreloadedAssets::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            ThrottleRequests::class.':api',
            SubstituteBindings::class,
            RateLimiting::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth'               => Authenticate::class,
        'auth.basic'         => AuthenticateWithBasicAuth::class,
        'auth.session'       => AuthenticateSession::class,
        'cache.headers'      => SetCacheHeaders::class,
        'can'                => Authorize::class,
        'guest'              => RedirectIfAuthenticated::class,
        'password.confirm'   => RequirePassword::class,
        'permission'         => PermissionMiddleware::class,
        'role'               => RoleMiddleware::class,
        'role_or_permission' => RoleOrPermissionMiddleware::class,
        'precognitive'       => HandlePrecognitiveRequests::class,
        'signed'             => ValidateSignature::class,
        'throttle'           => ThrottleRequests::class,
        'verified'           => EnsureEmailIsVerified::class,
        'rate.limit'         => RateLimiting::class,
        'security.headers'   => SecurityHeaders::class,
        'input.sanitize'     => InputSanitization::class,
        'audit.log'          => AuditLogging::class,
        'central.host'       => EnsureCentralHost::class,
        'platform.admin'     => EnsurePlatformAdmin::class,
    ];
}
