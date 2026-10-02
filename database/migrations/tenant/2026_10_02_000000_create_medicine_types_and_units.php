<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->foreignId('medicine_type_id')->nullable()->after('generic_id')->constrained('medicine_types')->nullOnDelete();
        });

        $now = now();
        foreach (['Allopathic', 'Ayurvedic', 'Unani', 'Herbal', 'Homeopathic'] as $name) {
            DB::table('medicine_types')->insert(['name' => $name, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach (['Piece', 'Strip', 'Box', 'Bottle', 'Vial', 'Tube', 'ml'] as $name) {
            DB::table('units')->insert(['name' => $name, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('medicine_type_id');
        });
        Schema::dropIfExists('units');
        Schema::dropIfExists('medicine_types');
    }
};
