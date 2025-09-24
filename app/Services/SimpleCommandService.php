<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SimpleCommandService
{
    /**
     * Get full path for command using Laravel-native methods
     */
    private function getCommandPath(string $command): string
    {
        // Use Laravel's Process to find command paths dynamically
        $commandPaths = $this->getDynamicCommandPaths();

        return $commandPaths[$command] ?? $command;
    }

    /**
     * Dynamically find command paths using system commands
     */
    private function getDynamicCommandPaths(): array
    {
        // Cache command paths to avoid repeated lookups
        $cacheKey = 'terminal_command_paths_' . PHP_OS;

        return cache()->remember($cacheKey, 3600, function () {
            $paths     = [];
            $commands  = ['composer', 'php', 'node', 'npm', 'git'];

            foreach ($commands as $cmd) {
                $path = $this->findCommandPath($cmd);
                if ($path) {
                    $paths[$cmd] = $path;
                }
            }

            // Add OS-specific commands
            $isWindows     = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $paths['ls']   = $isWindows ? 'dir' : 'ls';
            $paths['pwd']  = $isWindows ? 'cd' : 'pwd';
            $paths['whoami'] = 'whoami';
            $paths['date'] = 'date';

            return $paths;
        });
    }

    /**
     * Find command path using system which/where commands
     */
    private function findCommandPath(string $command): ?string
    {
        try {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

            if ($isWindows) {
                // Windows: use 'where' command
                $process = new Process(['where', $command]);
                $process->run();

                if ($process->isSuccessful()) {
                    $output = trim($process->getOutput());
                    $lines  = explode("\n", $output);
                    return trim($lines[0]); // Return first match
                }
            } else {
                // Unix/Linux: use 'which' command
                $process = new Process(['which', $command]);
                $process->run();

                if ($process->isSuccessful()) {
                    return trim($process->getOutput());
                }
            }
        } catch (\Exception $e) {
            // Fallback to common paths if which/where fails
            return $this->getFallbackPath($command);
        }

        return null;
    }

    /**
     * Fallback paths if dynamic detection fails
     */
    private function getFallbackPath(string $command): ?string
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        $fallbackPaths = [
            'windows' => [
                'composer' => 'C:\\xampp\\php\\composer.phar',
                'php'      => 'C:\\xampp\\php\\php.exe',
                'node'     => 'C:\\Program Files\\nodejs\\node.exe',
                'npm'      => 'C:\\Program Files\\nodejs\\npm.cmd',
                'git'      => 'C:\\Program Files\\Git\\bin\\git.exe',
            ],
            'unix' => [
                'composer' => '/usr/local/bin/composer',
                'php'      => '/usr/bin/php',
                'node'     => '/usr/local/bin/node',
                'npm'      => '/usr/local/bin/npm',
                'git'      => '/usr/bin/git',
            ]
        ];

        $osKey = $isWindows ? 'windows' : 'unix';
        return $fallbackPaths[$osKey][$command] ?? null;
    }

    /**
     * Check if running in cloud environment
     */
    private function isCloudEnvironment(): bool
    {
        // Check for common cloud hosting indicators
        $indicators = [
            'cpanel'         => function_exists('cpanel') || isset($_SERVER['CPANEL']),
            'shared_hosting' => isset($_SERVER['SHARED_HOSTING']),
            'cloudflare'     => isset($_SERVER['HTTP_CF_CONNECTING_IP']),
            'aws'            => isset($_SERVER['AWS_EXECUTION_ENV']),
            'heroku'         => isset($_SERVER['DYNO']),
            'digital_ocean'  => isset($_SERVER['DIGITALOCEAN']),
            'path_check'     => strpos(__DIR__, '/home/') === 0 || strpos(__DIR__, '/var/www/') === 0
        ];

        return in_array(true, $indicators);
    }

    /**
     * Execute a command
     */
    public function execute(string $command, array $arguments = [], ?string $workingDirectory = null): array
    {
        // Prepare working directory
        $workingDir = $workingDirectory ?? base_path();

        // Get full command path
        $commandPath = $this->getCommandPath($command);

        // Build full command
        $fullCommand = array_merge([$commandPath], $arguments);

        // Create process
        $process = new Process($fullCommand, $workingDir);
        $process->setTimeout(300); // 5 minutes timeout

        try {
            // Run the process
            $process->run();

            return [
                'success'          => $process->isSuccessful(),
                'output'           => $process->getOutput(),
                'error'            => $process->getErrorOutput(),
                'exit_code'        => $process->getExitCode(),
                'command'          => implode(' ', $fullCommand),
                'working_directory' => $workingDir
            ];
        } catch (ProcessFailedException $e) {
            return [
                'success'          => false,
                'error'            => $e->getMessage(),
                'output'           => $process->getOutput(),
                'exit_code'        => $process->getExitCode(),
                'command'          => implode(' ', $fullCommand),
                'working_directory' => $workingDir
            ];
        }
    }

    /**
     * Get available commands (for help display)
     */
    public function getAvailableCommands(): array
    {
        return [
            'composer' => ['install', 'update', 'dump-autoload', 'require', 'remove', 'show', 'outdated'],
            'npm'      => ['install', 'run', 'build', 'dev', 'prod', 'test', 'audit', 'update'],
            'php'      => ['artisan', '--version', '-v', '-m'],
            'artisan'  => ['migrate', 'migrate:fresh', 'migrate:rollback', 'db:seed', 'make:controller', 'make:model', 'make:migration', 'make:seeder', 'make:request', 'make:middleware', 'route:list', 'config:cache', 'config:clear', 'cache:clear', 'view:clear', 'optimize', 'optimize:clear', 'key:generate', 'storage:link', 'queue:work', 'queue:restart', 'tinker'],
            'git'      => ['status', 'pull', 'push', 'add', 'commit', 'log', 'branch'],
            'node'     => ['--version', '-v'],
            'ls'       => ['-la', '-l', '-a'],
            'dir'      => ['/w', '/p', '/a'],
            'pwd'      => [],
            'cd'       => [],
            'whoami'   => [],
            'date'     => []
        ];
    }

    /**
     * Get environment information
     */
    public function getEnvironmentInfo(): array
    {
        $isWindows     = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $isCloud       = $this->isCloudEnvironment();
        $commandPaths  = $this->getDynamicCommandPaths();

        return [
            'os'                     => PHP_OS,
            'is_windows'             => $isWindows,
            'is_cloud'               => $isCloud,
            'environment_type'       => $isCloud ? 'Cloud/cPanel' : ($isWindows ? 'Windows/XAMPP' : 'Linux/Mac'),
            'php_version'            => PHP_VERSION,
            'laravel_version'        => app()->version(),
            'working_directory'      => base_path(),
            'server_software'        => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root'          => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'detected_command_paths' => $commandPaths,
            'php_binary'             => PHP_BINARY,
            'path_separator'         => PATH_SEPARATOR
        ];
    }

    /**
     * Clear command path cache
     */
    public function clearCommandPathCache(): void
    {
        $cacheKey = 'terminal_command_paths_' . PHP_OS;
        cache()->forget($cacheKey);
    }

    /**
     * Refresh command paths (clear cache and re-detect)
     */
    public function refreshCommandPaths(): array
    {
        $this->clearCommandPathCache();
        return $this->getDynamicCommandPaths();
    }
}
