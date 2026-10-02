<?php

namespace App\Services\Platform;

use App\Models\Company;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class SchemaCompare
{
    /**
     * @return list<string>
     */
    public function files(): array
    {
        return collect(glob(database_path('migrations/tenant/*.php')) ?: [])
            ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function companies(): array
    {
        $files = $this->files();

        return Company::query()->orderBy('name')->get()->map(function (Company $company) use ($files) {
            return $this->compareDatabase($company->database_name, $files) + [
                'company_id' => $company->id,
                'name'       => $company->name,
                'slug'       => $company->slug,
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function central(): array
    {
        $files = collect(glob(database_path('migrations/central/*.php')) ?: [])
            ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
            ->sort()
            ->values()
            ->all();

        return $this->compareDatabase((string) config('database.central_database'), $files, 'mysql_central') + [
            'name' => 'Central registry',
        ];
    }

    public const TENANT_PATH = 'database/migrations/tenant';

    public const CENTRAL_PATH = 'database/migrations/central';

    public function upgradeCompany(Company $company): string
    {
        return TenantRuntime::runOn($company->database_name, function () {
            Artisan::call('migrate', [
                '--force' => true,
                '--path'  => self::TENANT_PATH,
            ]);

            return trim(Artisan::output()) ?: 'Migrations finished.';
        });
    }

    public function upgradeCentral(): string
    {
        Artisan::call('migrate', [
            '--force'    => true,
            '--database' => 'mysql_central',
            '--path'     => self::CENTRAL_PATH,
        ]);

        return trim(Artisan::output()) ?: 'Central migrations finished.';
    }

    /**
     * @param  list<string>  $files
     * @return array<string, mixed>
     */
    private function compareDatabase(string $database, array $files, ?string $connection = null): array
    {
        if ($database === '' || ! TenantRuntime::databaseExists($database)) {
            return ['status' => 'db_missing', 'pending' => $files, 'ran' => 0];
        }

        try {
            $read = function () use ($files) {
                if (! DB::getSchemaBuilder()->hasTable('migrations')) {
                    return ['status' => 'needs_update', 'pending' => $files, 'ran' => 0];
                }
                $ran     = DB::table('migrations')->pluck('migration')->all();
                $pending = array_values(array_diff($files, $ran));

                return [
                    'status'  => $pending === [] ? 'in_sync' : 'needs_update',
                    'pending' => $pending,
                    'ran'     => count($ran),
                ];
            };

            if ($connection === 'mysql_central') {
                $builder = DB::connection('mysql_central');
                if (! $builder->getSchemaBuilder()->hasTable('migrations')) {
                    return ['status' => 'needs_update', 'pending' => $files, 'ran' => 0];
                }
                $ran     = $builder->table('migrations')->pluck('migration')->all();
                $pending = array_values(array_diff($files, $ran));

                return [
                    'status'  => $pending === [] ? 'in_sync' : 'needs_update',
                    'pending' => $pending,
                    'ran'     => count($ran),
                ];
            }

            return TenantRuntime::runOn($database, $read);
        } catch (Throwable $e) {
            return ['status' => 'error', 'pending' => [], 'ran' => 0, 'message' => $e->getMessage()];
        }
    }
}
