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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_id')->unique();
            $table->foreignId('manufacturer_id')->constrained()->onDelete('cascade');
            $table->date('purchase_date');
            $table->integer('purchase_no')->nullable();
            $table->string('chalan_no');
            $table->text('details')->nullable();
            $table->enum('payment_type', ['cash', 'bank', 'credit']);
            $table->decimal('grand_total', 10, 2);
            $table->decimal('total_tax', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->decimal('total_discount', 10, 2)->default(0);
            $table->decimal('total_vat', 10, 2)->default(0);
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
