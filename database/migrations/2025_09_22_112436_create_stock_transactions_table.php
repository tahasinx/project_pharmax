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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['purchase', 'sale', 'adjustment', 'return']);
            $table->integer('quantity'); // Positive for purchase, negative for sale
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['stock_id', 'type']);
            $table->index(['medicine_id', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
