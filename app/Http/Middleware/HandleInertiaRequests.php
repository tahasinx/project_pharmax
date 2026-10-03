<?php

namespace App\Http\Middleware;

use App\Domain\Organization\BranchContext;
use App\Models\Menu;
use App\Services\Platform\PlatformSettingsStore;
use App\Services\Tenant\TenantThemeStore;
use App\Support\StagingDeployHost;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Skip database-dependent operations for install routes
        $isInstallRoute = $request->is('install*');
        $isCentral      = $request->attributes->get('tenant.mode') === 'central';

        try {
            return [
                ...parent::share($request),
                'auth' => [
                    'user' => $request->user(),
                ],
                'platform' => [
                    'central' => $isCentral,
                    'deploy'  => StagingDeployHost::matches(),
                    'theme'   => $isInstallRoute ? null : $this->appTheme(),
                ],
                'branch' => fn () => $request->user() && ! $isCentral && ! $isInstallRoute
                    ? BranchContext::shared($request->user())
                    : null,
                'app' => [
                    'name'    => $isInstallRoute ? config('app.name', 'Epharma') : $this->platformName(),
                    'logo'    => $isInstallRoute ? '' : $this->brandMedia()['logo'],
                    'favicon' => $isInstallRoute ? '' : $this->brandMedia()['favicon'],
                ],
                'ziggy' => $isInstallRoute ? [] : fn () => [
                    ...(new Ziggy)->toArray(),
                    'location' => $request->url(),
                ],
                'menus' => fn () => ($request->user() && ! $isCentral && ! $isInstallRoute) ? $this->getUserMenus($request->user()) : [],
                'flash' => [
                    'success' => fn () => $request->session()->get('success'),
                    'error'   => fn () => $request->session()->get('error'),
                    'warning' => fn () => $request->session()->get('warning'),
                    'info'    => fn () => $request->session()->get('info'),
                ],
            ];
        } catch (\Exception $e) {
            // If database is not available (during installation), return minimal data
            if ($isInstallRoute) {
                return [
                    ...parent::share($request),
                    'auth' => [
                        'user' => null,
                    ],
                    'app' => [
                        'name' => config('app.name', 'Laravel'),
                    ],
                    'ziggy' => [],
                    'menus' => [],
                    'flash' => [
                        'success' => fn () => $request->session()->get('success'),
                        'error'   => fn () => $request->session()->get('error'),
                        'warning' => fn () => $request->session()->get('warning'),
                        'info'    => fn () => $request->session()->get('info'),
                    ],
                ];
            }
            throw $e;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function appTheme(): ?array
    {
        try {
            $isCentral = request()->attributes->get('tenant.mode') === 'central';
            if ($isCentral) {
                return app(PlatformSettingsStore::class)->theme();
            }

            return app(TenantThemeStore::class)->theme();
        } catch (\Throwable) {
            return null;
        }
    }

    private function platformName(): string
    {
        try {
            return (string) app(PlatformSettingsStore::class)->all()['name'];
        } catch (\Throwable) {
            return config('app.name', 'Epharma');
        }
    }

    private function brandMedia(): array
    {
        try {
            return app(PlatformSettingsStore::class)->media();
        } catch (\Throwable) {
            return ['logo' => '', 'favicon' => ''];
        }
    }

    private function getUserMenus($user)
    {
        return Menu::active()->ordered()->get()->filter(function ($menu) use ($user) {
            if (! $menu->permission) {
                return true;
            }

            return $user->can($menu->permission);
        })->values();
    }
}
