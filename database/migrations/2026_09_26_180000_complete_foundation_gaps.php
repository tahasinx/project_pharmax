<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('dead_stock_days')->default(90);
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->timestamp('invoiced_at')->nullable();
        });

        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number')->unique();
            $table->date('invoice_date');
            $table->decimal('total', 14, 2);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        if (Schema::hasTable('goods_receipts')) {
            DB::table('goods_receipts')->whereNull('invoiced_at')->update(['invoiced_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropColumn('invoiced_at');
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('dead_stock_days');
        });
    }
};
