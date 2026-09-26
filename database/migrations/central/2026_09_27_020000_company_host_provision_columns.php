<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'provision_step')) {
                $table->string('provision_step', 40)->nullable();
            }
            if (! Schema::hasColumn('companies', 'vhost_status')) {
                $table->string('vhost_status', 20)->nullable();
            }
            if (! Schema::hasColumn('companies', 'ssl_status')) {
                $table->string('ssl_status', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            foreach (['provision_step', 'vhost_status', 'ssl_status'] as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
