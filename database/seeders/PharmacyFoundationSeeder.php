<?php

namespace Database\Seeders;

use App\Domain\Access\PermissionCatalog;
use App\Models\Branch;
use App\Models\LedgerAccount;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PharmacyFoundationSeeder extends Seeder
{
    public function run(): void
    {
        PermissionCatalog::sync();

        $accounts = [
            ['1000', 'Cash', 'asset'],
            ['1010', 'Bank', 'asset'],
            ['1100', 'Accounts Receivable', 'asset'],
            ['1200', 'Inventory', 'asset'],
            ['2000', 'Accounts Payable', 'liability'],
            ['4000', 'Sales', 'income'],
            ['4100', 'Sales Returns', 'income'],
            ['5000', 'Cost of Goods Sold', 'expense'],
            ['5100', 'Expiry Loss', 'expense'],
            ['5200', 'Purchase Returns', 'expense'],
        ];
        foreach ($accounts as [$code, $name, $type]) {
            LedgerAccount::firstOrCreate(['code' => $code], ['name' => $name, 'type' => $type]);
        }

        $branchId = Branch::query()->value('id');
        $menus = [
            ['Branches', 'branches.index', '🏢', 15, 'manage-branches'],
            ['Generics', 'generics.index', '🧪', 16, 'manage-medicines'],
            ['Brands', 'brands.index', '🏷️', 17, 'manage-medicines'],
            ['Suppliers', 'suppliers.index', '🚚', 18, 'manage-suppliers'],
            ['Purchase Orders', 'purchase-orders.index', '📝', 19, 'manage-purchases'],
            ['Expiry', 'stocks.expiry', '⏳', 20, 'manage-inventory'],
            ['Transfers', 'stock-transfers.index', '🔁', 21, 'manage-inventory'],
            ['Prescriptions', 'prescriptions.index', '🩺', 22, 'dispense'],
            ['Controlled Register', 'controlled.index', '📕', 23, 'manage-controlled'],
            ['Finance', 'finance.index', '📒', 24, 'manage-finance'],
            ['Audit Log', 'audit.index', '🛡️', 25, 'view-audit'],
            ['Sales Returns', 'sales-returns.create', '↩️', 26, 'manage-invoices'],
            ['Clinical Rules', 'clinical-rules.index', '⚠️', 27, 'manage-medicines'],
        ];

        $admin = Role::where('name', 'admin')->first();
        foreach ($menus as [$name, $route, $icon, $order, $permission]) {
            $menu = Menu::updateOrCreate(['route' => $route], [
                'name' => $name,
                'icon' => $icon,
                'order' => $order,
                'is_active' => true,
                'permission' => $permission,
            ]);
            $roleIds = Role::all()->filter(function ($role) use ($permission) {
                return $role->hasPermissionTo($permission);
            })->pluck('id');
            $menu->roles()->sync($roleIds->all());
        }

        if ($branchId) {
            \App\Models\User::whereNull('branch_id')->update(['branch_id' => $branchId]);
        }
    }
}
