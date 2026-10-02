<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('PharmaCare');
            $table->string('menu_title')->default('PharmaCare');
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->string('login_background')->nullable();
            $table->string('favicon')->nullable();
            $table->string('language')->default('en');
            $table->string('currency')->default('USD');
            $table->string('discount_type')->default('percentage');
            $table->string('timezone')->default('UTC');
            $table->boolean('rtl')->default(false);
            $table->text('footer_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
