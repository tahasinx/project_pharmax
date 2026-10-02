<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SwitchTenantDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('database.tenant.enabled')) {
            return $next($request);
        }

        $subdomain = $this->subdomain($request->getHost());
        if ($subdomain === null) {
            $this->useDatabase((string) config('database.connections.mysql.database'));

            return $next($request);
        }

        $central   = (array) config('database.tenant.central_subdomains', ['admin']);
        $centralDb = (string) config('database.central_database');

        if (in_array($subdomain, $central, true)) {
            $this->useDatabase($centralDb);
            $this->guardSession($request, $centralDb);
            $request->attributes->set('tenant.mode', 'central');

            return $next($request);
        }

        $company = Company::query()->where('slug', $subdomain)->first();
        if (! $company) {
            abort(404, 'Unknown pharmacy.');
        }

        $provision = (string) ($company->provision_status ?: 'active');
        if (in_array($provision, ['pending', 'running'], true)) {
            abort(503, 'This pharmacy is still being provisioned.');
        }
        if ($provision === 'failed') {
            abort(404, 'Pharmacy provisioning failed.');
        }
        if ($company->status !== 'active') {
            abort(403, 'This pharmacy is locked.');
        }
        if (! $company->isActive() || $company->database_name === '') {
            abort(404, 'This pharmacy is not ready.');
        }

        $this->useDatabase($company->database_name);
        $this->guardSession($request, $company->database_name);
        $request->attributes->set('tenant.mode', 'tenant');
        $request->attributes->set('tenant.company', $company);
        app()->instance('tenant.company', $company);

        return $next($request);
    }

    private function subdomain(string $host): ?string
    {
        $host = strtolower(trim($host));
        $base = strtolower((string) config('database.tenant.base_domain', 'epharma.test'));
        if ($base === '' || $host === $base || ! str_ends_with($host, '.'.$base)) {
            return null;
        }

        $label = explode('.', substr($host, 0, -strlen('.'.$base)))[0] ?? '';

        return $label !== '' ? $label : null;
    }

    private function useDatabase(string $database): void
    {
        if ($database !== '' && config('database.connections.mysql.database') !== $database) {
            Config::set('database.connections.mysql.database', $database);
            DB::purge('mysql');
            DB::reconnect('mysql');
        }

        $this->scopeCache($database !== '' ? $database : (string) config('database.connections.mysql.database'));
    }

    private function scopeCache(string $database): void
    {
        static $base = null;
        $base ??= (string) config('cache.prefix');
        $safe = preg_replace('/[^A-Za-z0-9_]/', '_', $database) ?: 'app';
        Config::set('cache.prefix', $base.'_'.$safe);
    }

    private function guardSession(Request $request, string $database): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $authDb = $request->session()->get('auth_database');
        if (! is_string($authDb) || $authDb === '' || $authDb === $database) {
            return;
        }

        Auth::logout();
        $request->session()->forget('auth_database');
        $request->session()->regenerateToken();
    }
}
