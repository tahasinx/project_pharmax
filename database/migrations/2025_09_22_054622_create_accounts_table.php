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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            // Modern columns for the new interface
            $table->string('name')->nullable();
            $table->string('code', 50)->nullable();
            $table->string('type', 20)->nullable(); // asset, liability, equity, revenue, expense
            $table->decimal('balance', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);

            // Legacy CodeIgniter-style columns
            $table->string('head_code')->unique();
            $table->string('head_name');
            $table->string('parent_head_name')->nullable();
            $table->integer('head_level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_transaction')->default(false);
            $table->boolean('is_gl')->default(false);
            $table->string('head_type'); // A=Asset, L=Liability, E=Expense, I=Income
            $table->boolean('is_budget')->default(false);
            $table->boolean('is_depreciation')->default(false);
            $table->decimal('depreciation_rate', 5, 2)->default(0);
            $table->foreignId('manufacturer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
