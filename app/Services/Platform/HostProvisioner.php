<?php

namespace App\Services\Platform;

use RuntimeException;
use Symfony\Component\Process\Process;

class HostProvisioner
{
    public function enabled(): bool
    {
        return is_executable('/usr/local/sbin/epharma-host-provision');
    }

    public function environmentName(): string
    {
        return app()->environment('staging') ? 'staging' : 'production';
    }

    /**
     * @return array{ok: bool, output: string, vhost: string, ssl: string}
     */
    public function add(string $slug): array
    {
        return $this->run('add', $slug);
    }

    /**
     * @return array{ok: bool, output: string, vhost: string, ssl: string}
     */
    public function remove(string $slug): array
    {
        return $this->run('remove', $slug);
    }

    public function dropDatabase(string $slug): void
    {
        $result = $this->run('drop-db', $slug);
        if (! $result['ok']) {
            throw new RuntimeException(trim($result['output']) ?: 'Database drop failed.');
        }
    }

    /**
     * @return array{ok: bool, output: string, vhost: string, ssl: string}
     */
    private function run(string $action, string $slug): array
    {
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new RuntimeException('Slug is not valid.');
        }
        if (! $this->enabled()) {
            return ['ok' => false, 'output' => 'Host provisioner is not installed.', 'vhost' => 'missing', 'ssl' => 'missing'];
        }

        $process = new Process([
            'sudo', '-n', '/usr/local/sbin/epharma-host-provision',
            $action, '--env='.$this->environmentName(), $slug,
        ]);
        $process->setTimeout(180);
        $process->run();
        $output = trim($process->getOutput()."\n".$process->getErrorOutput());
        $ssl = str_contains($output, 'SSL_OK') ? 'ok' : (str_contains($output, 'SSL_FAIL') ? 'failed' : 'missing');
        $vhost = str_contains($output, 'VHOST_OK') || str_contains($output, 'HOST_ALREADY_PRESENT') || $action === 'remove' ? 'ok' : 'missing';

        return [
            'ok' => $process->isSuccessful(),
            'output' => $output,
            'vhost' => $vhost,
            'ssl' => $process->isSuccessful() ? ($ssl === 'missing' ? 'ok' : $ssl) : $ssl,
        ];
    }
}
