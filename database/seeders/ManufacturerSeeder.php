<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use Illuminate\Database\Seeder;

class ManufacturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manufacturers = [
            [
                'name'    => 'Pfizer Inc.',
                'address' => '235 East 42nd Street, New York, NY 10017',
                'mobile'  => '+1 (212) 733-2323',
                'email'   => 'info@pfizer.com',
                'details' => 'Leading pharmaceutical company',
            ],
            [
                'name'    => 'Johnson & Johnson',
                'address' => '1 Johnson & Johnson Plaza, New Brunswick, NJ 08933',
                'mobile'  => '+1 (732) 524-0400',
                'email'   => 'info@jnj.com',
                'details' => 'Healthcare and pharmaceutical company',
            ],
            [
                'name'    => 'Novartis AG',
                'address' => 'Lichtstrasse 35, 4056 Basel, Switzerland',
                'mobile'  => '+41 61 324 1111',
                'email'   => 'info@novartis.com',
                'details' => 'Swiss multinational pharmaceutical corporation',
            ],
            [
                'name'    => 'Roche Holding AG',
                'address' => 'Grenzacherstrasse 124, 4070 Basel, Switzerland',
                'mobile'  => '+41 61 688 1111',
                'email'   => 'info@roche.com',
                'details' => 'Swiss multinational healthcare company',
            ],
            [
                'name'    => 'Merck & Co.',
                'address' => '126 E Lincoln Ave, Rahway, NJ 07065',
                'mobile'  => '+1 (908) 740-4000',
                'email'   => 'info@merck.com',
                'details' => 'American multinational pharmaceutical company',
            ],
        ];

        foreach ($manufacturers as $manufacturer) {
            Manufacturer::create($manufacturer);
        }
    }
}
