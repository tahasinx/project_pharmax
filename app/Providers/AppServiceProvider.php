<?php

namespace App\Providers;

use App\Models\Setting;
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
        // Pharmacy (tenant) migrations live under database/migrations/tenant
        // so RefreshDatabase / plain migrate never pick up central/.
        $this->loadMigrationsFrom(database_path('migrations/tenant'));

        // Share UI settings (including currency, tax, company info, and date format) with Inertia
        $uiSettings = [
            'currency_symbol'   => '$',
            'currency_position' => 'before',
            'default_tax_rate'  => 10,
            'date_format'       => 'Y-m-d',
            'company_name'      => config('app.name', 'PharmaCare'),
            'company_email'     => 'info@pharmacare.com',
            'company_phone'     => '+1 (555) 123-4567',
            'company_address'   => '123 Pharmacy Street, Medical City',
        ];

        // Load company info from database
        try {
            $setting = Setting::first();
            if ($setting) {
                $uiSettings['company_name']    = $setting->title ?? $uiSettings['company_name'];
                $uiSettings['company_email']   = $setting->email ?? $uiSettings['company_email'];
                $uiSettings['company_phone']   = $setting->phone ?? $uiSettings['company_phone'];
                $uiSettings['company_address'] = $setting->address ?? $uiSettings['company_address'];
            }
        } catch (\Throwable $e) {
            // noop: fallback defaults
        }

        try {
            if (Storage::exists('settings.json')) {
                $json = json_decode(Storage::get('settings.json'), true);
                if (is_array($json)) {
                    $uiSettings = array_replace($uiSettings, [
                        'currency_symbol'   => $json['currency_symbol'] ?? $uiSettings['currency_symbol'],
                        'currency_position' => $json['currency_position'] ?? $uiSettings['currency_position'],
                        'default_tax_rate'  => $json['default_tax_rate'] ?? $uiSettings['default_tax_rate'],
                        'date_format'       => $json['date_format'] ?? $uiSettings['date_format'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // noop: fallback defaults
        }

        Inertia::share('ui', [
            'currency_symbol'   => $uiSettings['currency_symbol'],
            'currency_position' => $uiSettings['currency_position'],
            'default_tax_rate'  => $uiSettings['default_tax_rate'],
            'date_format'       => $uiSettings['date_format'],
            'company_name'      => $uiSettings['company_name'],
            'company_email'     => $uiSettings['company_email'],
            'company_phone'     => $uiSettings['company_phone'],
            'company_address'   => $uiSettings['company_address'],
        ]);
    }
}
