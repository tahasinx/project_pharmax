<?php

namespace App\Services\Platform;

use App\Models\Company;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class CompanyProvisioner
{
    public function databaseName(string $slug): string
    {
        $name = (string) config('database.tenant.db_prefix', 'epharma_').str_replace('-', '_', $slug);
        if (! preg_match('/^[A-Za-z0-9_]+$/', $name) || str_ends_with($name, 'central')) {
            throw new RuntimeException('Database name is not valid.');
        }

        return $name;
    }

    public function provision(Company $company, bool $hostOnly = false): Company
    {
        $this->step($company, 'running', 'Provision started.', [
            'provision_status' => 'running',
            'provision_error' => null,
        ]);

        try {
            if (! $hostOnly) {
                $database = $company->database_name ?: $this->databaseName($company->slug);
                $this->step($company, 'database', 'Creating '.$database.'.');
                $this->createDatabase($database);
                $company->database_name = $database;
                $company->save();

                TenantRuntime::runOn($database, function () use ($company) {
                    $this->step($company, 'migrate', 'Running pharmacy migrations.');
                    Artisan::call('migrate', ['--force' => true, '--path' => 'database/migrations']);
                    $this->step($company, 'migrate', trim(Artisan::output()) ?: 'Migrations finished.');
                    $this->step($company, 'seed', 'Seeding the pharmacy.');
                    Artisan::call('db:seed', ['--force' => true, '--class' => 'Database\\Seeders\\DatabaseSeeder']);
                    $this->step($company, 'seed', 'Pharmacy seed finished.');
                });
            }

            $this->step($company, 'vhost', 'Requesting hostname and certificate.');
            $host = app(HostProvisioner::class)->add($company->slug);
            $this->step($company, 'ssl', $host['output'] ?: 'Host step finished.', [
                'vhost_status' => $host['vhost'],
                'ssl_status' => $host['ssl'],
            ]);

            $degraded = ! $host['ok'] || $host['ssl'] === 'failed';
            $company->update([
                'status' => 'active',
                'provision_status' => $degraded ? 'degraded' : 'active',
                'provision_error' => $degraded ? 'Certificate was not issued. The hostname may still be on HTTP.' : null,
                'provisioned_at' => now(),
                'admin_email' => $company->admin_email ?: 'admin@pharma.com',
            ]);
        } catch (Throwable $e) {
            $this->step($company, 'failed', $e->getMessage(), [
                'provision_status' => 'failed',
                'provision_error' => $e->getMessage(),
            ]);
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return $company->fresh();
    }

    public function startInBackground(Company $company, bool $hostOnly = false): void
    {
        $company->update([
            'provision_status' => 'pending',
            'provision_step' => 'queued',
            'provision_error' => null,
        ]);
        $binary = PHP_BINARY;
        $artisan = base_path('artisan');
        $args = [(string) $company->id];
        if ($hostOnly) {
            $args[] = '--host-only';
        }
        $command = 'cd '.escapeshellarg(base_path()).' && nohup '.escapeshellarg($binary).' '.escapeshellarg($artisan).' platform:provision '.implode(' ', array_map('escapeshellarg', $args)).' >/dev/null 2>&1 &';
        exec($command);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function step(Company $company, string $step, string $message, array $extra = []): void
    {
        $log = $company->provision_log ?? [];
        $log[] = ['at' => now()->toIso8601String(), 'step' => $step, 'message' => $message];
        $company->fill(array_merge([
            'provision_step' => $step,
            'provision_log' => $log,
        ], $extra));
        $company->save();
    }

    private function createDatabase(string $database): void
    {
        DB::connection('mysql_central')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }
}
