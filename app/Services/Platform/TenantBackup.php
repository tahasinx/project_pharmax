<?php

namespace App\Services\Platform;

use App\Models\Company;
use RuntimeException;
use Symfony\Component\Process\Process;

class TenantBackup
{
    public function directory(Company $company): string
    {
        $dir = storage_path('app/platform-backups/'.$company->slug);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException('Could not create the backup folder.');
        }

        return $dir;
    }

    /**
     * @return list<array{name: string, bytes: int, created_at: string}>
     */
    public function listFor(Company $company): array
    {
        $dir = $this->directory($company);
        $files = glob($dir.'/*.sql.gz') ?: [];
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map(fn (string $path) => [
            'name' => basename($path),
            'bytes' => filesize($path) ?: 0,
            'created_at' => date('c', filemtime($path) ?: time()),
        ], $files);
    }

    /**
     * @return array{name: string, bytes: int}
     */
    public function create(Company $company): array
    {
        if (! TenantRuntime::databaseExists($company->database_name)) {
            throw new RuntimeException('The pharmacy database does not exist.');
        }

        $name = $company->slug.'-'.now()->format('YmdHis').'.sql.gz';
        $path = $this->directory($company).DIRECTORY_SEPARATOR.$name;
        $command = sprintf(
            'mysqldump --single-transaction --quick -h%s -P%s -u%s %s | gzip > %s',
            escapeshellarg((string) config('database.connections.mysql.host')),
            escapeshellarg((string) config('database.connections.mysql.port')),
            escapeshellarg((string) config('database.connections.mysql.username')),
            escapeshellarg($company->database_name),
            escapeshellarg($path)
        );
        $env = [];
        foreach ($_SERVER as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $env[$key] = $value;
            }
        }
        $env['MYSQL_PWD'] = (string) config('database.connections.mysql.password');
        $process = Process::fromShellCommandline($command, null, $env);
        $process->setTimeout(180);
        $process->run();
        if (! $process->isSuccessful() || ! is_file($path)) {
            @unlink($path);
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'mysqldump failed.');
        }

        return ['name' => $name, 'bytes' => filesize($path) ?: 0];
    }

    public function path(Company $company, string $filename): string
    {
        if (! preg_match('/^[A-Za-z0-9_-]+\.sql\.gz$/', $filename)) {
            throw new RuntimeException('Backup name is not valid.');
        }
        $path = $this->directory($company).DIRECTORY_SEPARATOR.$filename;
        if (! is_file($path)) {
            throw new RuntimeException('Backup was not found.');
        }

        return $path;
    }

    public function delete(Company $company, string $filename): void
    {
        $path = $this->path($company, $filename);
        if (! unlink($path)) {
            throw new RuntimeException('Could not delete the backup.');
        }
    }
}
