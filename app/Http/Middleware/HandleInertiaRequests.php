<?php

namespace App\Http\Middleware;

use App\Domain\Organization\BranchContext;
use App\Models\Menu;
use App\Models\Setting;
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
                    'central'  => $isCentral,
                    'deploy'   => StagingDeployHost::matches(),
                    'theme'    => $isInstallRoute ? null : $this->appTheme(),
                    'identity' => $isInstallRoute ? null : $this->platformIdentity($isCentral),
                ],
                'branch' => fn () => $request->user() && ! $isCentral && ! $isInstallRoute
                    ? BranchContext::shared($request->user())
                    : null,
                'app' => [
                    'name'    => $isInstallRoute ? config('app.name', 'Epharma') : $this->appDisplayName(),
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

    /**
     * Identity fields from platform admin settings (central chrome + invoice defaults).
     *
     * @return array<string, mixed>|null
     */
    private function platformIdentity(bool $isCentral): ?array
    {
        try {
            $all = app(PlatformSettingsStore::class)->all();

            return [
                'name'             => (string) ($all['name'] ?? 'Epharma'),
                'tagline'          => (string) ($all['tagline'] ?? ''),
                'support_email'    => (string) ($all['support_email'] ?? ''),
                'support_phone'    => (string) ($all['support_phone'] ?? ''),
                'address'          => (string) ($all['address'] ?? ''),
                'default_currency' => (string) ($all['default_currency'] ?? 'BDT'),
                'invoice_footer'   => (string) ($all['invoice_footer'] ?? ''),
                // Tenants keep their own brand colors; typography always follows platform.
                'controls_theme'   => $isCentral,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function appDisplayName(): string
    {
        try {
            $isCentral = request()->attributes->get('tenant.mode') === 'central';
            if (! $isCentral) {
                $title = Setting::query()->value('title');
                if (is_string($title) && trim($title) !== '') {
                    return trim($title);
                }
            }

            return $this->platformName();
        } catch (\Throwable) {
            return config('app.name', 'Epharma');
        }
    }

    private function brandMedia(): array
    {
        try {
            $isCentral = request()->attributes->get('tenant.mode') === 'central';
            $platform = app(PlatformSettingsStore::class)->media();

            if ($isCentral) {
                return $platform;
            }

            $setting = Setting::query()->first();
            $logo = $this->publicMediaUrl($setting?->logo);
            $favicon = $this->publicMediaUrl($setting?->favicon);

            return [
                'logo'    => $logo !== '' ? $logo : ($platform['logo'] ?? ''),
                'favicon' => $favicon !== '' ? $favicon : ($platform['favicon'] ?? ''),
            ];
        } catch (\Throwable) {
            return ['logo' => '', 'favicon' => ''];
        }
    }

    private function publicMediaUrl(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '' || str_contains($path, '..')) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.$path);
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
