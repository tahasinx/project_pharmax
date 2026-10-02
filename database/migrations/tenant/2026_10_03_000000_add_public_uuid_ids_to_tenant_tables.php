<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Public UUID columns for tenant domain tables (ispx pattern).
     * Internal bigint `id` stays the PK; FKs remain unsigned bigints.
     *
     * @var array<string, string>
     */
    private array $tables = [
        'accounts'                  => 'account_id',
        'audit_logs'                => 'audit_log_id',
        'branches'                  => 'branch_id',
        'brands'                    => 'brand_id',
        'categories'                => 'category_id',
        'clinical_rules'            => 'clinical_rule_id',
        'controlled_drug_registers' => 'controlled_drug_register_id',
        'counters'                  => 'counter_id',
        'customers'                 => 'customer_id',
        'generics'                  => 'generic_id',
        'goods_receipts'            => 'goods_receipt_id',
        'goods_receipt_items'       => 'goods_receipt_item_id',
        'held_bills'                => 'held_bill_id',
        'invoices'                  => 'invoice_id', // skipped if business invoice_id already exists
        'invoice_items'             => 'invoice_item_id',
        'journal_entries'           => 'journal_entry_id',
        'journal_lines'             => 'journal_line_id',
        'ledger_accounts'           => 'ledger_account_id',
        'manufacturers'             => 'manufacturer_id',
        'medicines'                 => 'medicine_id',
        'medicine_types'            => 'medicine_type_id',
        'medicine_units'            => 'medicine_unit_id',
        'menus'                     => 'menu_id',
        'organizations'             => 'organization_id',
        'prescriptions'             => 'prescription_id',
        'prescription_items'        => 'prescription_item_id',
        'purchases'                 => 'purchase_id', // skipped if business purchase_id already exists
        'purchase_items'            => 'purchase_item_id',
        'purchase_invoices'         => 'purchase_invoice_id',
        'purchase_orders'           => 'purchase_order_id',
        'purchase_order_items'      => 'purchase_order_item_id',
        'purchase_returns'          => 'purchase_return_id',
        'purchase_return_items'     => 'purchase_return_item_id',
        'sales_returns'             => 'sales_return_id',
        'sales_return_items'        => 'sales_return_item_id',
        'settings'                  => 'setting_id',
        'stocks'                    => 'stock_id',
        'stock_adjustments'         => 'stock_adjustment_id',
        'stock_transactions'        => 'stock_transaction_id',
        'stock_transfers'           => 'stock_transfer_id',
        'suppliers'                 => 'supplier_id',
        'tender_payments'           => 'tender_payment_id',
        'transactions'              => 'transaction_id',
        'units'                     => 'unit_id',
        'users'                     => 'user_id',
        'warehouses'                => 'warehouse_id',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $column) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, $column)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->uuid($column)->nullable()->unique()->after('id');
            });

            DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $column) {
                foreach ($rows as $row) {
                    DB::table($table)->where('id', $row->id)->update([
                        $column => (string) Str::uuid(),
                    ]);
                }
            });

            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` CHAR(36) NOT NULL");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropColumn($column);
            });
        }
    }
};
