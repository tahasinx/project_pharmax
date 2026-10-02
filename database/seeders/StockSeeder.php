<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        // Get some medicines to create stock for
        $medicines = Medicine::take(10)->get();

        if ($medicines->isEmpty()) {
            $this->command->warn('No medicines found. Please run MedicineSeeder first.');

            return;
        }

        foreach ($medicines as $medicine) {
            // Create multiple stock entries for each medicine
            $stockCount = rand(1, 3);

            for ($i = 0; $i < $stockCount; $i++) {
                Stock::create([
                    'medicine_id'     => $medicine->id,
                    'batch_number'    => 'BATCH'.rand(100, 999),
                    'expiry_date'     => now()->addDays(rand(30, 365)),
                    'quantity'        => rand(5, 100),
                    'min_stock_level' => rand(10, 20),
                    'max_stock_level' => rand(100, 200),
                    'purchase_price'  => rand(10, 50),
                    'selling_price'   => rand(15, 60),
                    'supplier'        => 'Supplier '.rand(1, 5),
                    'notes'           => 'Sample stock entry',
                    'is_active'       => true,
                ]);
            }
        }

        $this->command->info('Sample stock entries created successfully!');
    }
}
