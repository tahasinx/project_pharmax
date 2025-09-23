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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('manufacturer_id')->constrained()->onDelete('cascade');
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable();
            $table->integer('box_size')->default(1);
            $table->string('product_location')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('manufacturer_price', 10, 2);
            $table->string('unit')->nullable();
            $table->text('details')->nullable();
            $table->string('image')->nullable();
            $table->text('qr_code_data')->nullable();
            $table->string('qr_code_type')->default('product_id');
            $table->string('qr_code_image_path')->nullable();
            $table->text('barcode_data')->nullable();
            $table->string('barcode_type')->default('code128');
            $table->string('barcode_image_path')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
