<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('platform_plans') && ! Schema::hasColumn('platform_plans', 'features')) {
            Schema::table('platform_plans', function (Blueprint $table) {
                $table->json('features')->nullable()->after('currency');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('platform_plans', 'features')) {
            Schema::table('platform_plans', function (Blueprint $table) {
                $table->dropColumn('features');
            });
        }
    }
};
