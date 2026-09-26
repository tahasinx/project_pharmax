<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class PlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::on('mysql_central')->firstOrNew(['email' => 'platform@epharma.cloud']);
        $user->name = 'Platform Admin';
        $user->is_platform_admin = true;
        $user->email_verified_at = $user->email_verified_at ?: now();
        if (! $user->exists) {
            $user->password = 'password';
        }
        $user->save();
    }
}
