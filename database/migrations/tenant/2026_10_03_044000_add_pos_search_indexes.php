<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('medicines', 'medicines_status_name_index', ['status', 'name']);
        $this->addIndexIfMissing('medicines', 'medicines_generic_name_index', ['generic_name']);
        if (Schema::hasColumn('medicines', 'sku')) {
            $this->addIndexIfMissing('medicines', 'medicines_sku_index', ['sku']);
        }
        if (Schema::hasColumn('medicines', 'generic_id')) {
            $this->addIndexIfMissing('medicines', 'medicines_generic_id_index', ['generic_id']);
        }

        if (Schema::hasTable('generics')) {
            $this->addIndexIfMissing('generics', 'generics_name_index', ['name']);
        }
    }

    public function down(): void
    {
        $this->dropIndexIfExists('medicines', 'medicines_status_name_index');
        $this->dropIndexIfExists('medicines', 'medicines_generic_name_index');
        $this->dropIndexIfExists('medicines', 'medicines_sku_index');
        $this->dropIndexIfExists('medicines', 'medicines_generic_id_index');
        $this->dropIndexIfExists('generics', 'generics_name_index');
    }

    /**
     * @param  list<string>  $columns
     */
    private function addIndexIfMissing(string $table, string $name, array $columns): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
            $blueprint->index($columns, $name);
        });
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name) {
            $blueprint->dropIndex($name);
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        $database = Schema::getConnection()->getDatabaseName();
        $row = DB::selectOne(
            'SELECT 1 AS ok FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $name]
        );

        return (bool) $row;
    }
};
