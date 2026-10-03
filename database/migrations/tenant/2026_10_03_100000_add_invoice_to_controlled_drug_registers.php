<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('controlled_drug_registers')) {
            return;
        }

        Schema::table('controlled_drug_registers', function (Blueprint $table) {
            if (! Schema::hasColumn('controlled_drug_registers', 'invoice_id')) {
                $table->foreignId('invoice_id')->nullable()->after('prescription_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('controlled_drug_registers', 'source')) {
                $table->string('source', 24)->default('prescription')->after('invoice_id');
            }
            if (! Schema::hasColumn('controlled_drug_registers', 'notes')) {
                $table->string('notes')->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('controlled_drug_registers')) {
            return;
        }

        Schema::table('controlled_drug_registers', function (Blueprint $table) {
            if (Schema::hasColumn('controlled_drug_registers', 'invoice_id')) {
                $table->dropConstrainedForeignId('invoice_id');
            }
            if (Schema::hasColumn('controlled_drug_registers', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('controlled_drug_registers', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
