<?php

namespace App\Domain\Access;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionCatalog
{
    public static function names(): array
    {
        return [
            'view-dashboard',
            'manage-medicines',
            'manage-customers',
            'manage-invoices',
            'manage-purchases',
            'manage-accounts',
            'manage-categories',
            'manage-manufacturers',
            'manage-settings',
            'view-reports',
            'manage-users',
            'pos-access',
            'print-invoices',
            'import-data',
            'export-data',
            'manage-system',
            'manage-data',
            'manage-branches',
            'manage-suppliers',
            'manage-inventory',
            'dispense',
            'manage-finance',
            'view-audit',
            'view-all-branches',
            'manage-controlled',
        ];
    }

    public static function sync(): void
    {
        foreach (self::names() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $roles = [
            'admin' => self::names(),
            'manager' => [
                'view-dashboard', 'manage-medicines', 'manage-customers', 'manage-invoices',
                'manage-purchases', 'manage-accounts', 'manage-categories', 'manage-manufacturers',
                'view-reports', 'pos-access', 'print-invoices', 'import-data', 'export-data',
                'manage-suppliers', 'manage-inventory', 'view-all-branches',
            ],
            'cashier' => ['view-dashboard', 'manage-customers', 'manage-invoices', 'pos-access', 'print-invoices'],
            'pharmacist' => [
                'view-dashboard', 'manage-medicines', 'manage-customers', 'manage-invoices',
                'pos-access', 'print-invoices', 'view-reports', 'dispense', 'manage-controlled',
            ],
            'storekeeper' => ['view-dashboard', 'manage-inventory', 'manage-medicines'],
            'purchase-officer' => ['view-dashboard', 'manage-purchases', 'manage-suppliers', 'manage-medicines'],
            'accountant' => ['view-dashboard', 'manage-finance', 'manage-accounts', 'view-reports'],
            'branch-manager' => [
                'view-dashboard', 'manage-medicines', 'manage-customers', 'manage-invoices',
                'manage-purchases', 'manage-inventory', 'manage-suppliers', 'view-reports',
                'pos-access', 'dispense', 'manage-branches',
            ],
            'auditor' => ['view-dashboard', 'view-reports', 'view-audit', 'view-all-branches'],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }
    }
}
