<?php

namespace App\Domain\Medex;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MedexClient
{
    public const BASE = 'https://medex.com.bd';

    private readonly int $cacheTtlSeconds;

    private readonly string $userAgent;

    private readonly int $timeout;

    public function __construct(
        ?int $cacheTtlSeconds = null,
        ?string $userAgent = null,
        ?int $timeout = null,
    ) {
        $this->cacheTtlSeconds = $cacheTtlSeconds ?? $this->configInt('medex.cache_ttl', 43200);
        $this->userAgent = $userAgent ?? $this->configString(
            'medex.user_agent',
            'EpharmaCatalogBot/1.0 (+local pharmacy reference sync)'
        );
        $this->timeout = $timeout ?? $this->configInt('medex.timeout', 25);
    }

    public function enabled(): bool
    {
        return (bool) $this->configValue('medex.enabled', true);
    }

    public function cacheTtl(): int
    {
        return $this->cacheTtlSeconds;
    }

    public function isAllowedUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $port = $parts['port'] ?? null;

        if ($scheme !== 'https' || $host !== 'medex.com.bd') {
            return false;
        }

        if ($port !== null && (int) $port !== 443) {
            return false;
        }

        // Block credentials / userinfo in URL.
        if (isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        return true;
    }

    public function get(string $pathOrUrl, array $query = [], bool $useCache = true): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        $url = str_starts_with($pathOrUrl, 'http')
            ? $pathOrUrl
            : self::BASE.'/'.ltrim($pathOrUrl, '/');

        if (! $this->isAllowedUrl($url)) {
            $this->warn('MedEx blocked disallowed URL', ['url' => $url]);

            return null;
        }

        if ($query !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?').http_build_query($query);
        }

        $fetch = function () use ($url) {
            usleep(150_000);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => $this->timeout,
                CURLOPT_USERAGENT      => $this->userAgent,
                CURLOPT_HTTPHEADER     => ['Accept: text/html,application/xhtml+xml'],
            ]);
            $html     = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200 || ! is_string($html) || $html === '') {
                $this->warn('MedEx fetch failed', [
                    'url'   => $url,
                    'code'  => $httpCode,
                    'error' => $error,
                ]);

                return null;
            }

            return $html;
        };

        if (! $useCache) {
            return $fetch();
        }

        $key = 'medex:html:'.sha1($url);

        return Cache::remember($key, $this->cacheTtlSeconds, $fetch);
    }

    public function absolute(?string $maybe): ?string
    {
        if (! $maybe) {
            return null;
        }
        if (str_starts_with($maybe, 'http')) {
            return $maybe;
        }

        return self::BASE.$maybe;
    }

    public function segmentFromRequest(bool|int|string|null $herbal): string
    {
        return filter_var($herbal, FILTER_VALIDATE_BOOLEAN) ? 'herbal' : 'allopathic';
    }

    private function configInt(string $key, int $default): int
    {
        return (int) $this->configValue($key, $default);
    }

    private function configString(string $key, string $default): string
    {
        return (string) $this->configValue($key, $default);
    }

    private function configValue(string $key, mixed $default): mixed
    {
        if (! function_exists('config')) {
            return $default;
        }

        try {
            return config($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function warn(string $message, array $context = []): void
    {
        try {
            Log::warning($message, $context);
        } catch (\Throwable) {
            // Unit tests may run without a facade root.
        }
    }
}
