<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles first
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $cashierRole = Role::create(['name' => 'cashier']);
        $pharmacistRole = Role::create(['name' => 'pharmacist']);

        // Create permissions
        $permissions = [
            'view-dashboard',
            'manage-medicines',
            'manage-customers',
            'manage-invoices',
            'manage-purchases',
            'manage-accounts',
            'manage-categories',
            'manage-manufacturers',
            'manage-banks',
            'manage-settings',
            'view-reports',
            'manage-users',
            'pos-access',
            'print-invoices',
            'import-data',
            'export-data'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());

        $managerRole->givePermissionTo([
            'view-dashboard',
            'manage-medicines',
            'manage-customers',
            'manage-invoices',
            'manage-purchases',
            'manage-accounts',
            'manage-categories',
            'manage-manufacturers',
            'manage-banks',
            'view-reports',
            'pos-access',
            'print-invoices',
            'import-data',
            'export-data'
        ]);

        $cashierRole->givePermissionTo([
            'view-dashboard',
            'manage-customers',
            'manage-invoices',
            'pos-access',
            'print-invoices'
        ]);

        $pharmacistRole->givePermissionTo([
            'view-dashboard',
            'manage-medicines',
            'manage-customers',
            'manage-invoices',
            'pos-access',
            'print-invoices',
            'view-reports'
        ]);

        // Create users
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@pharma.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin'
            ],
            [
                'name' => 'John Manager',
                'email' => 'manager@pharma.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'manager'
            ],
            [
                'name' => 'Sarah Cashier',
                'email' => 'cashier@pharma.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'cashier'
            ],
            [
                'name' => 'Dr. Michael Pharmacist',
                'email' => 'pharmacist@pharma.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'pharmacist'
            ],
            [
                'name' => 'Test User',
                'email' => 'test@pharma.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'cashier'
            ]
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::create($userData);
            $user->assignRole($role);
        }

        $this->command->info('Users created successfully!');
        $this->command->info('Default login credentials:');
        $this->command->info('Admin: admin@pharma.com / password');
        $this->command->info('Manager: manager@pharma.com / password');
        $this->command->info('Cashier: cashier@pharma.com / password');
        $this->command->info('Pharmacist: pharmacist@pharma.com / password');
        $this->command->info('Test: test@pharma.com / password');
    }
}
