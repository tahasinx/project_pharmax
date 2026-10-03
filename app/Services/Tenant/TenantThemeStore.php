<?php

namespace App\Services\Tenant;

use App\Services\Platform\PlatformSettingsStore;
use Illuminate\Support\Facades\Storage;

class TenantThemeStore
{
    /**
     * @return array{primary: string, shape: string, font_family: string, font_href: string, font_size: int, font_weight: int, radius: string, primary_rgb: string}
     */
    public function theme(): array
    {
        return $this->applyPlatformFont($this->sanitize($this->raw()));
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  bool  $allowTypography
     * @return array{primary: string, shape: string, font_family: string, font_href: string, font_size: int, font_weight: int, radius: string, primary_rgb: string}
     */
    public function save(array $input, bool $allowTypography = false): array
    {
        $current = $this->sanitize($this->raw());
        $incoming = $this->sanitize($input);

        $theme = [
            'primary'     => $incoming['primary'],
            'shape'       => $incoming['shape'],
            'font_family' => $allowTypography ? $incoming['font_family'] : $current['font_family'],
            'font_href'   => $allowTypography ? $incoming['font_href'] : $current['font_href'],
            'font_size'   => $allowTypography ? $incoming['font_size'] : $current['font_size'],
            'font_weight' => $allowTypography ? $incoming['font_weight'] : $current['font_weight'],
            'radius'      => $incoming['radius'],
            'primary_rgb' => $incoming['primary_rgb'],
        ];

        $json = [];

        try {
            if (Storage::exists('settings.json')) {
                $decoded = json_decode(Storage::get('settings.json'), true);
                if (is_array($decoded)) {
                    $json = $decoded;
                }
            }
        } catch (\Throwable) {
            $json = [];
        }

        $json['theme_primary'] = $theme['primary'];
        $json['theme_shape'] = $theme['shape'];

        if ($allowTypography) {
            $json['theme_font_family'] = $theme['font_family'];
            $json['theme_font_href'] = $theme['font_href'];
            $json['theme_font_size'] = $theme['font_size'];
            $json['theme_font_weight'] = $theme['font_weight'];
        }

        Storage::put('settings.json', json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $this->applyPlatformFont($theme);
    }

    /**
     * @return array{primary: string, shape: string, font_family: string, font_href: string, font_size: int, font_weight: int}
     */
    public function defaults(): array
    {
        return [
            'primary'     => '#5156be',
            'shape'       => 'rounded',
            'font_family' => 'IBM Plex Sans',
            'font_href'   => '',
            'font_size'   => 16,
            'font_weight' => 400,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{primary: string, shape: string, font_family: string, font_href: string, font_size: int, font_weight: int, radius: string, primary_rgb: string}
     */
    public function sanitize(array $input): array
    {
        $defaults = $this->defaults();
        $primary = strtolower(trim((string) ($input['primary'] ?? $input['theme_primary'] ?? $defaults['primary'])));
        if (! preg_match('/^#[0-9a-f]{6}$/', $primary)) {
            $primary = $defaults['primary'];
        }

        $shape = (string) ($input['shape'] ?? $input['theme_shape'] ?? $defaults['shape']);
        if (! in_array($shape, ['default', 'rounded', 'flat'], true)) {
            $shape = $defaults['shape'];
        }

        $family = trim((string) ($input['font_family'] ?? $input['theme_font_family'] ?? $defaults['font_family']));
        if ($family === '' || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9 \-]{0,60}$/', $family)) {
            $family = $defaults['font_family'];
        }

        $size = (int) ($input['font_size'] ?? $input['theme_font_size'] ?? $defaults['font_size']);
        if ($size < 12 || $size > 22) {
            $size = $defaults['font_size'];
        }

        $weight = (int) ($input['font_weight'] ?? $input['theme_font_weight'] ?? $defaults['font_weight']);
        if (! in_array($weight, [300, 400, 500, 600, 700, 800, 900], true)) {
            $weight = $defaults['font_weight'];
        }

        $hex = ltrim($primary, '#');
        $rgb = implode(', ', [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ]);

        return [
            'primary'     => $primary,
            'shape'       => $shape,
            'font_family' => $family,
            'font_href'   => $this->stylesheet((string) ($input['font_href'] ?? $input['theme_font_href'] ?? '')),
            'font_size'   => $size,
            'font_weight' => $weight,
            'radius'      => match ($shape) {
                'flat'    => '0px',
                'default' => '4px',
                default   => '10px',
            },
            'primary_rgb' => $rgb,
        ];
    }

    /**
     * Platform/app-admin typography overrides tenant-local font keys.
     *
     * @param  array{primary: string, shape: string, font_family: string, font_href: string, font_size: int, font_weight: int, radius: string, primary_rgb: string}  $theme
     * @return array{primary: string, shape: string, font_family: string, font_href: string, font_size: int, font_weight: int, radius: string, primary_rgb: string}
     */
    private function applyPlatformFont(array $theme): array
    {
        try {
            $platform = app(PlatformSettingsStore::class)->theme();
            $theme['font_family'] = $platform['font_family'];
            $theme['font_href'] = $platform['font_href'];
            $theme['font_size'] = $platform['font_size'];
            $theme['font_weight'] = $platform['font_weight'];
        } catch (\Throwable) {
            // Keep tenant/local defaults when platform DB is unavailable.
        }

        return $theme;
    }

    /**
     * @return array<string, mixed>
     */
    private function raw(): array
    {
        $defaults = $this->defaults();

        try {
            if (! Storage::exists('settings.json')) {
                return $defaults;
            }
            $json = json_decode(Storage::get('settings.json'), true);
            if (! is_array($json)) {
                return $defaults;
            }

            return [
                'theme_primary'     => $json['theme_primary'] ?? $defaults['primary'],
                'theme_shape'       => $json['theme_shape'] ?? $defaults['shape'],
                'theme_font_family' => $json['theme_font_family'] ?? $defaults['font_family'],
                'theme_font_href'   => $json['theme_font_href'] ?? $defaults['font_href'],
                'theme_font_size'   => $json['theme_font_size'] ?? $defaults['font_size'],
                'theme_font_weight' => $json['theme_font_weight'] ?? $defaults['font_weight'],
            ];
        } catch (\Throwable) {
            return $defaults;
        }
    }

    private function stylesheet(string $href): string
    {
        $href = trim($href);
        if ($href === '') {
            return '';
        }
        if (! preg_match('#^https://(fonts\.googleapis\.com|fonts\.bunny\.net|cdn\.jsdelivr\.net|cdnjs\.cloudflare\.com)/#i', $href)) {
            return '';
        }

        return $href;
    }
}
