<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_head_office')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $orgId = DB::table('organizations')->insertGetId([
            'name' => 'Pharmax',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $branchId = DB::table('branches')->insertGetId([
            'organization_id' => $orgId,
            'name' => 'Head Office',
            'code' => 'HO',
            'is_head_office' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $warehouseId = DB::table('warehouses')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Main Warehouse',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $counterId = DB::table('counters')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Main Counter',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module');
            $table->string('record_type');
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['record_type', 'record_id']);
        });

        Schema::create('generics', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('counter_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
        });
        DB::table('users')->update(['branch_id' => $branchId, 'counter_id' => $counterId]);

        Schema::table('medicines', function (Blueprint $table) {
            $table->foreignId('generic_id')->nullable()->after('generic_name')->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('generic_id')->constrained()->nullOnDelete();
            $table->string('dosage_form')->nullable();
            $table->string('atc_code')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('is_controlled')->default(false);
            $table->boolean('is_antibiotic')->default(false);
            $table->boolean('is_high_risk')->default(false);
            $table->boolean('is_refrigerated')->default(false);
            $table->boolean('is_narcotic')->default(false);
        });

        Schema::create('medicine_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('factor_to_base')->default(1);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->unsignedInteger('credit_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('medicine_id')->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('warehouse_id')->constrained()->nullOnDelete();
            $table->date('manufacturing_date')->nullable();
            $table->decimal('mrp', 10, 2)->nullable();
            $table->integer('free_quantity')->default(0);
            $table->string('status', 32)->default('available');
            $table->boolean('recalled')->default(false);
        });
        DB::table('stocks')->update([
            'branch_id' => $branchId,
            'warehouse_id' => $warehouseId,
            'status' => 'available',
        ]);

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('counter_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
        });
        DB::statement("ALTER TABLE invoices MODIFY payment_type VARCHAR(32) NOT NULL");
        DB::table('invoices')->update(['branch_id' => $branchId, 'counter_id' => $counterId]);

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('stock_id')->nullable()->after('medicine_id')->constrained()->nullOnDelete();
            $table->decimal('cost_amount', 12, 2)->default(0);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('manufacturer_id')->constrained()->nullOnDelete();
        });
        DB::statement("ALTER TABLE purchases MODIFY payment_type VARCHAR(32) NOT NULL");
        DB::table('purchases')->update(['branch_id' => $branchId]);

        DB::statement("ALTER TABLE stock_transactions MODIFY type VARCHAR(32) NOT NULL");

        Schema::table('customers', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_medicines')->nullable();
        });

        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type', 20);
            $table->timestamps();
        });
        $now = now();
        DB::table('ledger_accounts')->insert([
            ['code' => '1000', 'name' => 'Cash', 'type' => 'asset', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1010', 'name' => 'Bank', 'type' => 'asset', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '4000', 'name' => 'Sales', 'type' => 'income', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '4100', 'name' => 'Sales Returns', 'type' => 'income', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '5100', 'name' => 'Expiry Loss', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
            ['code' => '5200', 'name' => 'Purchase Returns', 'type' => 'expense', 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->date('entry_date');
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('memo')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('order_date');
            $table->string('status', 32)->default('ordered');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('received_quantity')->default(0);
            $table->decimal('rate', 12, 2);
            $table->timestamps();
        });

        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->date('received_date');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_number');
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('free_quantity')->default(0);
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('mrp', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->date('return_date');
            $table->decimal('total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('condition', 32);
            $table->decimal('credit_amount', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->date('return_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stock_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('condition', 32);
            $table->boolean('restock')->default(false);
            $table->decimal('amount', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('tender_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');
            $table->string('method', 32);
            $table->decimal('amount', 14, 2);
            $table->timestamps();
            $table->index(['payable_type', 'payable_id']);
        });

        Schema::create('held_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('counter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label')->nullable();
            $table->json('payload');
            $table->string('status', 32)->default('held');
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('doctor_name');
            $table->string('diagnosis')->nullable();
            $table->string('status', 32)->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('strength')->nullable();
            $table->string('dose')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->string('route')->nullable();
            $table->string('instructions')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('dispensed_quantity')->default(0);
            $table->boolean('substitution_allowed')->default(false);
            $table->timestamps();
        });

        Schema::create('controlled_drug_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('dispensed_at');
            $table->timestamps();
        });

        Schema::create('clinical_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('other_medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
            $table->string('rule_type', 32);
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('status', 32)->default('requested');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_delta');
            $table->string('type', 32);
            $table->string('reason');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('clinical_rules');
        Schema::dropIfExists('controlled_drug_registers');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('held_bills');
        Schema::dropIfExists('tender_payments');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('ledger_accounts');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'gender', 'allergies', 'chronic_medicines']);
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_id');
            $table->dropColumn('cost_amount');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('counter_id');
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropConstrainedForeignId('warehouse_id');
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['manufacturing_date', 'mrp', 'free_quantity', 'status', 'recalled']);
        });
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('medicine_units');
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('generic_id');
            $table->dropColumn([
                'dosage_form', 'atc_code', 'sku', 'requires_prescription', 'is_controlled',
                'is_antibiotic', 'is_high_risk', 'is_refrigerated', 'is_narcotic',
            ]);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('counter_id');
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::dropIfExists('brands');
        Schema::dropIfExists('generics');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('counters');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('organizations');
    }
};
