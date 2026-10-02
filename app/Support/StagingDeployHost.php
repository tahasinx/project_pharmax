<?php

namespace App\Support;

/**
 * Staging platform host may promote code to production via GitHub.
 * Exact hostname allowlist — not a wildcard.
 */
class StagingDeployHost
{
    public static function matches(?string $host = null): bool
    {
        if (! config('github_deploy.enabled')) {
            return false;
        }

        $host = self::normalizeHost($host ?? request()->getHost());
        if ($host === '' || ! self::isValidHostname($host)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        $allowed = self::allowedHosts();
        if ($allowed === [] || ! in_array($host, $allowed, true)) {
            return false;
        }

        $parts      = explode('.', $host);
        $allowedSub = strtolower((string) config('github_deploy.allowed_subdomain', 'adminx'));

        return $allowedSub !== '' && ($parts[0] ?? '') === $allowedSub;
    }

    /**
     * @return list<string>
     */
    public static function allowedHosts(): array
    {
        $hosts = config('github_deploy.allowed_hosts', []);
        if (! is_array($hosts)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static function ($host): string {
                $normalized = self::normalizeHost((string) $host);

                return self::isValidHostname($normalized) ? $normalized : '';
            },
            $hosts
        ))));
    }

    public static function normalizeHost(?string $host): string
    {
        $host = strtolower(trim((string) $host));
        $host = preg_replace('/:\d+$/', '', $host) ?: $host;

        return rtrim($host, '.');
    }

    public static function isValidHostname(string $host): bool
    {
        return (bool) preg_match(
            '/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/',
            $host
        );
    }
}
