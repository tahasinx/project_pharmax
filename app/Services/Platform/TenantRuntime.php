<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantRuntime
{
    public static function runOn(string $database, callable $callback): mixed
    {
        $original = (string) config('database.connections.mysql.database');
        Config::set('database.connections.mysql.database', $database);
        DB::purge('mysql');
        DB::reconnect('mysql');

        try {
            return $callback();
        } finally {
            Config::set('database.connections.mysql.database', $original);
            DB::purge('mysql');
            DB::reconnect('mysql');
        }
    }

    public static function databaseExists(string $database): bool
    {
        $row = DB::connection('mysql_central')->select('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$database]);

        return $row !== [];
    }
}
