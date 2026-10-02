<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['manufacturer_id']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->unsignedBigInteger('manufacturer_id')->nullable()->change();
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['manufacturer_id']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->unsignedBigInteger('manufacturer_id')->nullable(false)->change();
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->cascadeOnDelete();
        });
    }
};
