<?php

namespace App\Http\Middleware;

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
    public function version(Request $request): string|null
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
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'app' => [
                'name' => config('app.name'),
            ],
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'menus' => fn() => $request->user() ? $this->getUserMenus($request->user()) : [],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'warning' => fn() => $request->session()->get('warning'),
                'info' => fn() => $request->session()->get('info'),
            ],
        ];
    }

    private function getUserMenus($user)
    {
        // Admin users see all menus
        if ($user->hasRole('admin')) {
            return \App\Models\Menu::active()
                ->ordered()
                ->get()
                ->filter(function ($menu) use ($user) {
                    // Check if user has the required permission
                    if ($menu->permission) {
                        return $user->can($menu->permission);
                    }
                    return true;
                });
        }

        // Other users see only menus assigned to their roles
        $userRoles = $user->roles->pluck('id');

        return \App\Models\Menu::active()
            ->ordered()
            ->whereHas('roles', function ($query) use ($userRoles) {
                $query->whereIn('roles.id', $userRoles);
            })
            ->get()
            ->filter(function ($menu) use ($user) {
                // Check if user has the required permission
                if ($menu->permission) {
                    return $user->can($menu->permission);
                }
                return true;
            });
    }
}
