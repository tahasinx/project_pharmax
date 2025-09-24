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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('invoice_no');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('total_tax', 10, 2)->default(0);
            $table->decimal('previous_due', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->decimal('total_discount', 10, 2)->default(0);
            $table->decimal('invoice_discount', 10, 2)->default(0);
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('details')->nullable();
            $table->enum('payment_type', ['cash', 'bank', 'credit']);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
