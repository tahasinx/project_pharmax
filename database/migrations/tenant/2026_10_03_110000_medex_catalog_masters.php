<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dosage_forms')) {
            Schema::create('dosage_forms', function (Blueprint $table) {
                $table->id();
                $table->uuid('dosage_form_id')->unique();
                $table->string('name')->unique();
                $table->string('medex_slug')->nullable()->index();
                $table->unsignedInteger('brand_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('medicines') && ! Schema::hasColumn('medicines', 'dosage_form_id')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->foreignId('dosage_form_id')->nullable()->after('dosage_form')->constrained('dosage_forms')->nullOnDelete();
            });
        }

        foreach (['generics', 'brands', 'manufacturers'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (! Schema::hasColumn($table, 'segment')) {
                    $blueprint->string('segment', 24)->nullable()->after('name');
                }
                if (! Schema::hasColumn($table, 'medex_path')) {
                    $blueprint->string('medex_path')->nullable();
                }
                if (! Schema::hasColumn($table, 'medex_id')) {
                    $blueprint->string('medex_id')->nullable()->index();
                }
                if (! Schema::hasColumn($table, 'meta')) {
                    $blueprint->json('meta')->nullable();
                }
            });
        }

        if (! Schema::hasTable('medex_brand_indexes')) {
            Schema::create('medex_brand_indexes', function (Blueprint $table) {
                $table->id();
                $table->uuid('medex_brand_index_id')->unique();
                $table->string('name');
                $table->string('strength')->nullable();
                $table->string('form')->nullable();
                $table->string('generic_name')->nullable();
                $table->string('manufacturer_name')->nullable();
                $table->string('segment', 24)->default('allopathic')->index();
                $table->string('medex_path')->nullable();
                $table->string('medex_id')->nullable()->index();
                $table->string('medex_slug')->nullable();
                $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
                $table->foreignId('generic_id')->nullable()->constrained('generics')->nullOnDelete();
                $table->foreignId('manufacturer_id')->nullable()->constrained('manufacturers')->nullOnDelete();
                $table->foreignId('dosage_form_id')->nullable()->constrained('dosage_forms')->nullOnDelete();
                $table->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->unique(['medex_id', 'segment']);
                $table->index(['name', 'segment']);
            });
        }

        if (Schema::hasTable('medicine_types')) {
            $now = now();
            foreach (['Allopathic', 'Herbal', 'Device'] as $name) {
                $exists = DB::table('medicine_types')->where('name', $name)->exists();
                if (! $exists) {
                    $row = ['name' => $name, 'created_at' => $now, 'updated_at' => $now];
                    if (Schema::hasColumn('medicine_types', 'medicine_type_id')) {
                        $row['medicine_type_id'] = (string) Str::uuid();
                    }
                    DB::table('medicine_types')->insert($row);
                }
            }
        }

        if (Schema::hasTable('settings')) {
            // no-op placeholder for future medex settings row
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('medicines') && Schema::hasColumn('medicines', 'dosage_form_id')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropConstrainedForeignId('dosage_form_id');
            });
        }

        Schema::dropIfExists('medex_brand_indexes');

        foreach (['generics', 'brands', 'manufacturers'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                foreach (['segment', 'medex_path', 'medex_id', 'meta'] as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $blueprint->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('dosage_forms');
    }
};
