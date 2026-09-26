<?php

namespace App\Services\Platform;

use App\Models\PlatformSetting;

class PlatformSettingsStore
{
    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        $stored = PlatformSetting::query()->pluck('value', 'key')->all();

        return array_merge([
            'name' => 'Epharma',
            'tagline' => 'Pharmacy platform',
            'support_email' => '',
            'support_phone' => '',
            'address' => '',
            'default_currency' => 'BDT',
            'invoice_footer' => '',
        ], $stored);
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public function save(array $values): void
    {
        foreach ($values as $key => $value) {
            PlatformSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function tenancySnapshot(): array
    {
        return [
            'enabled' => (bool) config('database.tenant.enabled'),
            'base_domain' => (string) config('database.tenant.base_domain'),
            'prefix' => (string) config('database.tenant.db_prefix'),
            'central' => (string) config('database.central_database'),
            'central_hosts' => config('database.tenant.central_subdomains', []),
        ];
    }
}
