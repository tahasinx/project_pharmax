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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->string('batch_number')->nullable();           // Batch/Lot number
            $table->date('expiry_date')->nullable();              // Expiry date
            $table->integer('quantity')->default(0);              // Current stock quantity
            $table->integer('min_stock_level')->default(10);      // Minimum stock alert level
            $table->integer('max_stock_level')->nullable();       // Maximum stock level
            $table->decimal('purchase_price', 10, 2)->nullable(); // Purchase price per unit
            $table->decimal('selling_price', 10, 2)->nullable();  // Selling price per unit
            $table->string('supplier')->nullable();               // Supplier information
            $table->text('notes')->nullable();                    // Additional notes
            $table->boolean('is_active')->default(true);          // Stock status
            $table->timestamps();

            $table->index(['medicine_id', 'batch_number']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
