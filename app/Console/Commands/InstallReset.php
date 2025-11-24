<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * InstallReset Command
 *
 * Resets the application to initial installation state.
 * Removes installation lock and optionally clears configuration.
 *
 * @package App\Console\Commands
 */
class InstallReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:reset 
                            {--backup-env : Backup and remove .env file}
                            {--db : Reset database (run migrate:fresh)}
                            {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset application to initial installation state';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will reset the installation state. Continue?', false)) {
                $this->info('Reset cancelled.');
                return Command::FAILURE;
            }
        }

        $this->info('Resetting installation state...');
        $this->newLine();

        // Remove installation lock file
        $lockFile = storage_path('install.lock');
        if (File::exists($lockFile)) {
            File::delete($lockFile);
            $this->info('✓ Removed installation lock file');
        } else {
            $this->comment('  Installation lock file not found (already reset)');
        }

        // Handle .env file
        if ($this->option('backup-env')) {
            $envFile = base_path('.env');
            if (File::exists($envFile)) {
                $backupFile = base_path('.env.backup');
                File::copy($envFile, $backupFile);
                File::delete($envFile);
                $this->info('✓ Backed up and removed .env file (.env.backup created)');
            } else {
                $this->comment('  .env file not found');
            }
        }

        // Clear caches
        $this->info('Clearing caches...');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('cache:clear');
        $this->info('✓ Caches cleared');

        // Clear session files
        $sessionPath = storage_path('framework/sessions');
        if (File::isDirectory($sessionPath)) {
            $files = File::files($sessionPath);
            foreach ($files as $file) {
                File::delete($file);
            }
            $this->info('✓ Session files cleared');
        }

        // Clear bootstrap cache
        $bootstrapCache = base_path('bootstrap/cache');
        if (File::isDirectory($bootstrapCache)) {
            $cacheFiles = File::glob($bootstrapCache . '/*.php');
            foreach ($cacheFiles as $file) {
                if (basename($file) !== '.gitignore') {
                    File::delete($file);
                }
            }
            $this->info('✓ Bootstrap cache cleared');
        }

        // Reset database if requested
        if ($this->option('db')) {
            if (!$this->option('force')) {
                if (!$this->confirm('This will drop all database tables. Continue?', false)) {
                    $this->warn('Database reset cancelled.');
                } else {
                    $this->call('migrate:fresh');
                    $this->info('✓ Database reset (migrate:fresh completed)');
                }
            } else {
                $this->call('migrate:fresh');
                $this->info('✓ Database reset (migrate:fresh completed)');
            }
        }

        $this->newLine();
        $this->info('✓ Installation reset complete!');
        $this->newLine();
        $this->comment('Next steps:');
        $this->comment('  1. Visit http://yourdomain.com/install');
        $this->comment('  2. Follow the installation wizard');
        $this->newLine();

        return Command::SUCCESS;
    }
}

