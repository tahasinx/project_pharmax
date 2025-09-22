<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;
use Spatie\Permission\Models\Role;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $cashierRole = Role::where('name', 'cashier')->first();
        $pharmacistRole = Role::where('name', 'pharmacist')->first();

        // Create default menu items
        $menus = [
            [
                'name' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => '🏠',
                'order' => 1,
                'is_active' => true,
                'permission' => 'view-dashboard',
                'roles' => ['admin', 'manager', 'pharmacist']
            ],
            [
                'name' => 'POS',
                'route' => 'pos',
                'icon' => '🛒',
                'order' => 2,
                'is_active' => true,
                'permission' => 'pos-access',
                'roles' => ['admin', 'manager', 'cashier', 'pharmacist']
            ],
            [
                'name' => 'Medicines',
                'route' => 'medicines.index',
                'icon' => '💊',
                'order' => 3,
                'is_active' => true,
                'permission' => 'manage-medicines',
                'roles' => ['admin', 'manager', 'pharmacist']
            ],
            [
                'name' => 'Manufacturers',
                'route' => 'manufacturers.index',
                'icon' => '🏭',
                'order' => 4,
                'is_active' => true,
                'permission' => 'manage-manufacturers',
                'roles' => ['admin', 'manager']
            ],
            [
                'name' => 'Customers',
                'route' => 'customers.index',
                'icon' => '👥',
                'order' => 5,
                'is_active' => true,
                'permission' => 'manage-customers',
                'roles' => ['admin', 'manager', 'cashier', 'pharmacist']
            ],
            [
                'name' => 'Invoices',
                'route' => 'invoices.index',
                'icon' => '📄',
                'order' => 6,
                'is_active' => true,
                'permission' => 'manage-invoices',
                'roles' => ['admin', 'manager', 'cashier', 'pharmacist']
            ],
            [
                'name' => 'Purchases',
                'route' => 'purchases.index',
                'icon' => '📦',
                'order' => 7,
                'is_active' => true,
                'permission' => 'manage-purchases',
                'roles' => ['admin', 'manager']
            ],
            [
                'name' => 'Accounts',
                'route' => 'accounts.index',
                'icon' => '💰',
                'order' => 9,
                'is_active' => true,
                'permission' => 'manage-accounts',
                'roles' => ['admin', 'manager']
            ],
            [
                'name' => 'Users',
                'route' => 'users.index',
                'icon' => '👤',
                'order' => 10,
                'is_active' => true,
                'permission' => 'manage-users',
                'roles' => ['admin']
            ],
            [
                'name' => 'Menus',
                'route' => 'menus.index',
                'icon' => '📋',
                'order' => 11,
                'is_active' => true,
                'permission' => 'manage-users',
                'roles' => ['admin']
            ],
            [
                'name' => 'Stock',
                'route' => 'stocks.index',
                'icon' => '📦',
                'order' => 8,
                'is_active' => true,
                'permission' => 'manage-medicines',
                'roles' => ['admin', 'manager', 'pharmacist']
            ],
            [
                'name' => 'Settings',
                'route' => 'settings.index',
                'icon' => '⚙️',
                'order' => 12,
                'is_active' => true,
                'permission' => 'manage-settings',
                'roles' => ['admin']
            ]
        ];

        foreach ($menus as $menuData) {
            $roles = $menuData['roles'];
            unset($menuData['roles']);

            $menu = Menu::create($menuData);

            // Assign roles
            $roleIds = [];
            foreach ($roles as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $roleIds[] = $role->id;
                }
            }
            $menu->roles()->sync($roleIds);
        }

        $this->command->info('Default menu items created successfully!');
    }
}
