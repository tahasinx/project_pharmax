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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no');
            $table->string('voucher_type'); // DV=Debit Voucher, CV=Credit Voucher, JV=Journal Voucher, Contra=Contra Voucher
            $table->date('voucher_date');
            $table->string('account_head_code');
            $table->text('narration')->nullable();
            $table->decimal('debit', 10, 2)->default(0);
            $table->decimal('credit', 10, 2)->default(0);
            $table->boolean('is_posted')->default(false);
            $table->boolean('is_opening')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};