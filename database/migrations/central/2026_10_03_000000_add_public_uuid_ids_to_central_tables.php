<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $tables = [
        'companies'              => 'company_id',
        'users'                  => 'user_id',
        'platform_plans'         => 'platform_plan_id',
        'platform_subscriptions' => 'platform_subscription_id',
        'platform_invoices'      => 'platform_invoice_id',
        'platform_settings'      => 'platform_setting_id',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $column) {
            if (! Schema::connection('mysql_central')->hasTable($table)
                || Schema::connection('mysql_central')->hasColumn($table, $column)) {
                continue;
            }

            Schema::connection('mysql_central')->table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->uuid($column)->nullable()->unique()->after('id');
            });

            DB::connection('mysql_central')->table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $column) {
                foreach ($rows as $row) {
                    DB::connection('mysql_central')->table($table)->where('id', $row->id)->update([
                        $column => (string) Str::uuid(),
                    ]);
                }
            });

            DB::connection('mysql_central')->statement("ALTER TABLE `{$table}` MODIFY `{$column}` CHAR(36) NOT NULL");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $column) {
            if (! Schema::connection('mysql_central')->hasTable($table)
                || ! Schema::connection('mysql_central')->hasColumn($table, $column)) {
                continue;
            }

            Schema::connection('mysql_central')->table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropColumn($column);
            });
        }
    }
};
