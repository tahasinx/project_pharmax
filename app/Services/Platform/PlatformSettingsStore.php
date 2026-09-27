<?php

namespace App\Services\Platform;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class PlatformSettingsStore
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $stored = PlatformSetting::query()->pluck('value', 'key')->all();

        return [
            'name' => $stored['name'] ?? 'Epharma',
            'tagline' => $stored['tagline'] ?? 'Pharmacy platform',
            'support_email' => $stored['support_email'] ?? '',
            'support_phone' => $stored['support_phone'] ?? '',
            'address' => $stored['address'] ?? '',
            'default_currency' => $stored['default_currency'] ?? 'BDT',
            'invoice_footer' => $stored['invoice_footer'] ?? '',
            'theme_primary' => $this->theme($stored)['primary'],
            'theme_shape' => $this->theme($stored)['shape'],
            'theme_font_family' => $this->theme($stored)['font_family'],
            'theme_font_href' => $this->theme($stored)['font_href'],
            'theme_font_size' => $this->theme($stored)['font_size'],
            'theme_font_weight' => $this->theme($stored)['font_weight'],
            'email_enabled' => ($stored['email_enabled'] ?? '0') === '1',
            'email_host' => $stored['email_host'] ?? '',
            'email_port' => (int) ($stored['email_port'] ?? 587),
            'email_encryption' => $stored['email_encryption'] ?? 'tls',
            'email_username' => $stored['email_username'] ?? '',
            'email_password_set' => ($stored['email_password'] ?? '') !== '',
            'email_from_address' => $stored['email_from_address'] ?? '',
            'email_from_name' => $stored['email_from_name'] ?? '',
        ];
    }

    /**
     * @return array{primary: string, shape: string, font_family: string, font_href: string, font_size: int, font_weight: int}
     */
    public function theme(?array $stored = null): array
    {
        $stored ??= PlatformSetting::query()->pluck('value', 'key')->all();
        $primary = strtolower((string) ($stored['theme_primary'] ?? '#17342b'));
        if (! preg_match('/^#[0-9a-f]{6}$/', $primary)) {
            $primary = '#17342b';
        }
        $shape = (string) ($stored['theme_shape'] ?? 'rounded');
        if (! in_array($shape, ['default', 'rounded', 'flat'], true)) {
            $shape = 'rounded';
        }
        $family = trim((string) ($stored['theme_font_family'] ?? 'Inter'));
        if ($family === '' || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9 \-]{0,60}$/', $family)) {
            $family = 'Inter';
        }
        $size = (int) ($stored['theme_font_size'] ?? 16);
        if ($size < 12 || $size > 22) {
            $size = 16;
        }
        $weight = (int) ($stored['theme_font_weight'] ?? 400);
        if (! in_array($weight, [300, 400, 500, 600, 700, 800, 900], true)) {
            $weight = 400;
        }

        return [
            'primary' => $primary,
            'shape' => $shape,
            'font_family' => $family,
            'font_href' => $this->stylesheet((string) ($stored['theme_font_href'] ?? '')),
            'font_size' => $size,
            'font_weight' => $weight,
            'radius' => match ($shape) {
                'flat' => '0px',
                'default' => '4px',
                default => '10px',
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mailer(): array
    {
        $stored = PlatformSetting::query()->pluck('value', 'key')->all();

        return [
            'enabled' => ($stored['email_enabled'] ?? '0') === '1',
            'host' => (string) ($stored['email_host'] ?? ''),
            'port' => (int) ($stored['email_port'] ?? 587),
            'encryption' => (string) ($stored['email_encryption'] ?? 'tls'),
            'username' => (string) ($stored['email_username'] ?? ''),
            'password' => $this->decrypt((string) ($stored['email_password'] ?? '')),
            'from_address' => (string) ($stored['email_from_address'] ?? ''),
            'from_name' => (string) ($stored['email_from_name'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function save(array $values): void
    {
        if (array_key_exists('email_password', $values)) {
            $secret = (string) $values['email_password'];
            unset($values['email_password']);
            if ($secret !== '') {
                $values['email_password'] = Crypt::encryptString($secret);
            }
        }
        if (array_key_exists('email_enabled', $values)) {
            $values['email_enabled'] = $values['email_enabled'] ? '1' : '0';
        }
        foreach ($values as $key => $value) {
            PlatformSetting::query()->updateOrCreate(['key' => $key], ['value' => $value === null ? '' : (string) $value]);
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

    public function stylesheet(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/href\s*=\s*["\']([^"\']+)["\']/i', $raw, $match)) {
            $raw = $match[1];
        }
        $raw = trim(html_entity_decode($raw, ENT_QUOTES));
        if (! str_starts_with($raw, 'https://') || filter_var($raw, FILTER_VALIDATE_URL) === false) {
            return '';
        }
        $host = parse_url($raw, PHP_URL_HOST);
        $allowed = ['fonts.googleapis.com', 'fonts.gstatic.com', 'cdn.jsdelivr.net', 'cdnjs.cloudflare.com'];
        if (! is_string($host) || ! in_array(strtolower($host), $allowed, true)) {
            return '';
        }

        return $raw;
    }

    private function decrypt(string $value): string
    {
        if ($value === '') {
            return '';
        }
        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return '';
        }
    }
}
