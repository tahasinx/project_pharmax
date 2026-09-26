<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
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
            return $next($request);
        }

        $central = (array) config('database.tenant.central_subdomains', ['admin']);
        $centralDb = (string) config('database.central_database');

        if (in_array($subdomain, $central, true)) {
            $this->useDatabase($centralDb);
            $request->attributes->set('tenant.mode', 'central');

            return $next($request);
        }

        $company = Company::query()->where('slug', $subdomain)->first();
        if (! $company || ! $company->isActive()) {
            abort(404, 'Unknown pharmacy.');
        }

        $this->useDatabase($company->database_name);
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
        if ($database === '' || config('database.connections.mysql.database') === $database) {
            return;
        }

        Config::set('database.connections.mysql.database', $database);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }
}
