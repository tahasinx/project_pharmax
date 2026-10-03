<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            if (! Schema::hasColumn('medicines', 'qr_code_size')) {
                $table->unsignedSmallInteger('qr_code_size')->default(180)->after('qr_code_image_path');
            }
            if (! Schema::hasColumn('medicines', 'barcode_size')) {
                $table->unsignedSmallInteger('barcode_size')->default(88)->after('barcode_image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            if (Schema::hasColumn('medicines', 'qr_code_size')) {
                $table->dropColumn('qr_code_size');
            }
            if (Schema::hasColumn('medicines', 'barcode_size')) {
                $table->dropColumn('barcode_size');
            }
        });
    }
};
