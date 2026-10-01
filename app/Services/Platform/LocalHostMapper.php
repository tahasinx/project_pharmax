<?php

namespace App\Services\Platform;

class LocalHostMapper
{
    public function add(string $slug): string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return 'This machine uses the server host script for the pharmacy name.';
        }

        $base = strtolower((string) config('database.tenant.base_domain'));
        $name = strtolower($slug).'.'.$base;
        $root = getenv('SystemRoot') ?: 'C:\\Windows';
        $path = $root.DIRECTORY_SEPARATOR.'System32'.DIRECTORY_SEPARATOR.'drivers'.DIRECTORY_SEPARATOR.'etc'.DIRECTORY_SEPARATOR.'hosts';

        if (! is_file($path) || ! is_writable($path)) {
            return 'Add 127.0.0.1 '.$name.' to the hosts file so the pharmacy opens in the browser.';
        }

        $contents = (string) file_get_contents($path);
        if (preg_match('/^[ \t]*127\.0\.0\.1[ \t]+'.preg_quote($name, '/').'([ \t]|$)/m', $contents)) {
            return $name.' already points at this machine.';
        }

        $line = '127.0.0.1 '.$name;
        file_put_contents($path, rtrim($contents).PHP_EOL.$line.PHP_EOL);

        return 'Mapped '.$line.'.';
    }
}
