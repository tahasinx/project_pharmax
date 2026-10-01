<?php

namespace App\Services\Platform;

use App\Domain\Access\PermissionCatalog;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Database\Seeders\PharmacyFoundationSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    /**
     * @return array{ok: bool, database_name: string, message: string}
     */
    public function checkDatabase(string $database, string $slug): array
    {
        $name = trim($database) !== '' ? trim($database) : $this->databaseName($slug);
        if (! preg_match('/^[A-Za-z0-9_]+$/', $name) || str_ends_with($name, 'central')) {
            return ['ok' => false, 'database_name' => $name, 'message' => 'Database name is not valid.'];
        }
        if (Company::query()->where('database_name', $name)->exists()) {
            return ['ok' => false, 'database_name' => $name, 'message' => "Database {$name} is already registered."];
        }
        if (TenantRuntime::databaseExists($name)) {
            return ['ok' => false, 'database_name' => $name, 'message' => "Database {$name} already exists on this MySQL server."];
        }

        return ['ok' => true, 'database_name' => $name, 'message' => "Database {$name} is available."];
    }

    /**
     * @param  array{name: string, email: string, password: string}  $admin
     */
    public function rememberAdmin(Company $company, array $admin): void
    {
        Cache::put($this->adminKey($company), $admin, now()->addHours(6));
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

                $admin = Cache::get($this->adminKey($company));
                TenantRuntime::runOn($database, function () use ($company, $admin) {
                    $this->step($company, 'migrate', 'Running pharmacy migrations.');
                    Artisan::call('migrate', ['--force' => true, '--path' => 'database/migrations']);
                    $this->step($company, 'migrate', trim(Artisan::output()) ?: 'Migrations finished.');
                    $this->step($company, 'seed', 'Seeding roles, settings, and menus.');
                    PermissionCatalog::sync();
                    if (! Setting::query()->exists()) {
                        Artisan::call('db:seed', ['--force' => true, '--class' => SettingSeeder::class]);
                    }
                    Artisan::call('db:seed', ['--force' => true, '--class' => MenuSeeder::class]);
                    Artisan::call('db:seed', ['--force' => true, '--class' => PharmacyFoundationSeeder::class]);
                    $this->installAdmin($company, is_array($admin) ? $admin : null);
                });
                Cache::forget($this->adminKey($company));
            }

            $hosts = app(HostProvisioner::class);
            if (! $hosts->enabled()) {
                $mapped = app(LocalHostMapper::class)->add($company->slug);
                $this->step($company, 'vhost', $mapped, [
                    'vhost_status' => 'local',
                    'ssl_status' => 'skipped',
                ]);
                $company->update([
                    'status' => 'active',
                    'provision_status' => 'active',
                    'provision_error' => null,
                    'provisioned_at' => now(),
                ]);

                return $company->fresh();
            }

            $this->step($company, 'vhost', 'Requesting hostname and certificate.');
            $host = $hosts->add($company->slug);
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
        $args = [PHP_BINARY, base_path('artisan'), 'platform:provision', (string) $company->id];
        if ($hostOnly) {
            $args[] = '--host-only';
        }
        if (PHP_OS_FAMILY === 'Windows') {
            $command = 'start /B "" '.implode(' ', array_map('escapeshellarg', $args));
            pclose(popen($command, 'r'));

            return;
        }
        $command = 'cd '.escapeshellarg(base_path()).' && nohup '.implode(' ', array_map('escapeshellarg', $args)).' >/dev/null 2>&1 &';
        exec($command);
    }

    /**
     * @param  array{name?: string, email?: string, password?: string}|null  $admin
     */
    private function installAdmin(Company $company, ?array $admin): void
    {
        $email = (string) ($admin['email'] ?? $company->admin_email);
        $name = (string) ($admin['name'] ?? $company->name.' Admin');
        $password = (string) ($admin['password'] ?? '');
        if ($email === '' || $password === '') {
            throw new RuntimeException('Pharmacy admin login was not supplied. Create the pharmacy again.');
        }

        $attributes = [
            'name' => $name,
            'password' => $password,
            'email_verified_at' => now(),
        ];
        if (Schema::hasColumn('users', 'is_platform_admin')) {
            $attributes['is_platform_admin'] = false;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            $attributes
        );
        $user->syncRoles('admin');
        $this->step($company, 'admin', 'Pharmacy admin ready: '.$email);
    }

    private function adminKey(Company $company): string
    {
        return 'platform.company-admin.'.$company->id;
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
