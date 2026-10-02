<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('price');
            $table->unsignedInteger('alert_qty')->default(0)->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'alert_qty']);
        });
    }
};
