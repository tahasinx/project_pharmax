<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share UI settings (including currency) with Inertia
        $uiSettings = [
            'currency_symbol'   => '$',
            'currency_position' => 'before',
        ];

        try {
            if (Storage::exists('settings.json')) {
                $json = json_decode(Storage::get('settings.json'), true);
                if (is_array($json)) {
                    $uiSettings = array_replace($uiSettings, [
                        'currency_symbol'   => $json['currency_symbol']   ?? $uiSettings['currency_symbol'],
                        'currency_position' => $json['currency_position'] ?? $uiSettings['currency_position'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // noop: fallback defaults
        }

        Inertia::share('ui', [
            'currency_symbol'   => $uiSettings['currency_symbol'],
            'currency_position' => $uiSettings['currency_position'],
        ]);
    }
}
