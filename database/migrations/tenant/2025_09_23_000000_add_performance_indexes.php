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
        // Add indexes to medicines table
        if (Schema::hasTable('medicines')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->index(['name', 'status']);
                $table->index(['category_id', 'status']);
                $table->index(['manufacturer_id', 'status']);
                $table->index(['generic_name']);
                $table->index(['product_id']);
                $table->index(['medex_id']);
                $table->index(['created_at']);
            });
        }

        // Add indexes to invoices table
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index(['customer_id', 'date']);
                $table->index(['date']);
                $table->index(['invoice_no']);
                $table->index(['invoice_id']);
                $table->index(['payment_type']);
                $table->index(['status']);
                $table->index(['user_id']);
                $table->index(['created_at']);
            });
        }

        // Add indexes to invoice_items table
        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->index(['invoice_id']);
                $table->index(['medicine_id']);
                $table->index(['batch_id']);
            });
        }

        // Add indexes to stocks table
        // Note: expiry_date and medicine_id+batch_number indexes already exist in create_stocks_table migration
        if (Schema::hasTable('stocks')) {
            Schema::table('stocks', function (Blueprint $table) {
                // Only add indexes that don't already exist
                $table->index(['medicine_id', 'is_active']);
                $table->index(['quantity']);
                $table->index(['is_active']);
                $table->index(['created_at']);
            });
        }

        // Add indexes to stock_transactions table
        // Note: Some indexes already exist in create_stock_transactions_table migration
        if (Schema::hasTable('stock_transactions')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->index(['type']);
                $table->index(['invoice_id']);
                $table->index(['purchase_id']);
                $table->index(['user_id']);
            });
        }

        // Add indexes to customers table
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index(['name']);
                $table->index(['mobile']);
                $table->index(['email']);
                $table->index(['status']);
                $table->index(['created_at']);
            });
        }

        // Add indexes to purchases table
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->index(['manufacturer_id']);
                $table->index(['purchase_date']);
                $table->index(['purchase_no']);
                $table->index(['purchase_id']);
                $table->index(['status']);
                $table->index(['user_id']);
                $table->index(['created_at']);
            });
        }

        // Add indexes to purchase_items table
        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->index(['purchase_id']);
                $table->index(['medicine_id']);
                $table->index(['batch_id']);
            });
        }

        // Add indexes to categories table
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->index(['name']);
                $table->index(['status']);
            });
        }

        // Add indexes to manufacturers table
        if (Schema::hasTable('manufacturers')) {
            Schema::table('manufacturers', function (Blueprint $table) {
                $table->index(['name']);
                $table->index(['status']);
            });
        }

        // Add indexes to users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['email']);
                $table->index(['created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from medicines table
        if (Schema::hasTable('medicines')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropIndex(['name', 'status']);
                $table->dropIndex(['category_id', 'status']);
                $table->dropIndex(['manufacturer_id', 'status']);
                $table->dropIndex(['generic_name']);
                $table->dropIndex(['product_id']);
                $table->dropIndex(['medex_id']);
                $table->dropIndex(['created_at']);
            });
        }

        // Remove indexes from invoices table
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex(['customer_id', 'date']);
                $table->dropIndex(['date']);
                $table->dropIndex(['invoice_no']);
                $table->dropIndex(['invoice_id']);
                $table->dropIndex(['payment_type']);
                $table->dropIndex(['status']);
                $table->dropIndex(['user_id']);
                $table->dropIndex(['created_at']);
            });
        }

        // Remove indexes from invoice_items table
        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropIndex(['invoice_id']);
                $table->dropIndex(['medicine_id']);
                $table->dropIndex(['batch_id']);
            });
        }

        // Remove indexes from stocks table (only the ones we added)
        if (Schema::hasTable('stocks')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropIndex(['medicine_id', 'is_active']);
                $table->dropIndex(['quantity']);
                $table->dropIndex(['is_active']);
                $table->dropIndex(['created_at']);
            });
        }

        // Remove indexes from stock_transactions table (only the ones we added)
        if (Schema::hasTable('stock_transactions')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->dropIndex(['type']);
                $table->dropIndex(['invoice_id']);
                $table->dropIndex(['purchase_id']);
                $table->dropIndex(['user_id']);
            });
        }

        // Remove indexes from customers table
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex(['name']);
                $table->dropIndex(['mobile']);
                $table->dropIndex(['email']);
                $table->dropIndex(['status']);
                $table->dropIndex(['created_at']);
            });
        }

        // Remove indexes from purchases table
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropIndex(['manufacturer_id']);
                $table->dropIndex(['purchase_date']);
                $table->dropIndex(['purchase_no']);
                $table->dropIndex(['purchase_id']);
                $table->dropIndex(['status']);
                $table->dropIndex(['user_id']);
                $table->dropIndex(['created_at']);
            });
        }

        // Remove indexes from purchase_items table
        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->dropIndex(['purchase_id']);
                $table->dropIndex(['medicine_id']);
                $table->dropIndex(['batch_id']);
            });
        }

        // Remove indexes from categories table
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex(['name']);
                $table->dropIndex(['status']);
            });
        }

        // Remove indexes from manufacturers table
        if (Schema::hasTable('manufacturers')) {
            Schema::table('manufacturers', function (Blueprint $table) {
                $table->dropIndex(['name']);
                $table->dropIndex(['status']);
            });
        }

        // Remove indexes from users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['email']);
                $table->dropIndex(['created_at']);
            });
        }
    }
};
