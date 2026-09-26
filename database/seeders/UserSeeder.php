<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Domain\Access\PermissionCatalog::sync();

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

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                collect($userData)->except('role')->toArray()
            );
            $user->syncRoles($role);
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
