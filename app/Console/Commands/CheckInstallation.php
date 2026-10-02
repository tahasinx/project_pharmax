<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CheckInstallation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if PharmaCare Modern is properly installed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking PharmaCare Modern Installation...');
        $this->newLine();

        $checks = [
            'Installation Flag'   => $this->checkInstallationFlag(),
            'Database Connection' => $this->checkDatabaseConnection(),
            'Environment File'    => $this->checkEnvironmentFile(),
            'Storage Permissions' => $this->checkStoragePermissions(),
            'Cache Permissions'   => $this->checkCachePermissions(),
            'Dependencies'        => $this->checkDependencies(),
            'Database Tables'     => $this->checkDatabaseTables(),
        ];

        $allPassed = true;

        foreach ($checks as $check => $result) {
            if ($result['status']) {
                $this->line("✅ {$check}: {$result['message']}");
            } else {
                $this->line("❌ {$check}: {$result['message']}");
                $allPassed = false;
            }
        }

        $this->newLine();

        if ($allPassed) {
            $this->info('🎉 Installation check passed! PharmaCare Modern is properly installed.');
        } else {
            $this->error('⚠️  Installation check failed! Please fix the issues above.');
        }

        return $allPassed ? 0 : 1;
    }

    protected function checkInstallationFlag()
    {
        $installed = File::exists(storage_path('app/installed'));

        return [
            'status'  => $installed,
            'message' => $installed ? 'Found' : 'Missing - Run installation first',
        ];
    }

    protected function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();

            return [
                'status'  => true,
                'message' => 'Connected successfully',
            ];
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ];
        }
    }

    protected function checkEnvironmentFile()
    {
        $envExists  = File::exists(base_path('.env'));
        $envContent = $envExists ? File::get(base_path('.env')) : '';
        $hasAppKey  = str_contains($envContent, 'APP_KEY=') && ! str_contains($envContent, 'APP_KEY=');

        return [
            'status'  => $envExists && $hasAppKey,
            'message' => $envExists ? ($hasAppKey ? 'Configured' : 'Missing APP_KEY') : 'File not found',
        ];
    }

    protected function checkStoragePermissions()
    {
        $storageWritable = is_writable(storage_path());

        return [
            'status'  => $storageWritable,
            'message' => $storageWritable ? 'Writable' : 'Not writable',
        ];
    }

    protected function checkCachePermissions()
    {
        $cacheWritable = is_writable(base_path('bootstrap/cache'));

        return [
            'status'  => $cacheWritable,
            'message' => $cacheWritable ? 'Writable' : 'Not writable',
        ];
    }

    protected function checkDependencies()
    {
        $composerLockExists = File::exists(base_path('composer.lock'));
        $vendorExists       = File::exists(base_path('vendor'));

        return [
            'status'  => $composerLockExists && $vendorExists,
            'message' => ($composerLockExists && $vendorExists) ? 'Installed' : 'Missing dependencies',
        ];
    }

    protected function checkDatabaseTables()
    {
        try {
            $tables     = DB::select('SHOW TABLES');
            $tableCount = count($tables);

            return [
                'status'  => $tableCount > 0,
                'message' => "Found {$tableCount} tables",
            ];
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => 'Error checking tables: '.$e->getMessage(),
            ];
        }
    }
}
