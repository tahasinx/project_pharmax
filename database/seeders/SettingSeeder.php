<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'title'         => 'PharmaCare Modern',
            'menu_title'    => 'PharmaCare',
            'address'       => '123 Pharmacy Street, Medical City, MC 12345',
            'email'         => 'info@pharmacare.com',
            'phone'         => '+1 (555) 123-4567',
            'language'      => 'en',
            'currency'      => 'USD',
            'discount_type' => 'percentage',
            'timezone'      => 'UTC',
            'rtl'           => false,
            'footer_text'   => '© 2024 PharmaCare Modern. All rights reserved.',
        ]);
    }
}
