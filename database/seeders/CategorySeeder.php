<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Antibiotics', 'description' => 'Medicines that fight bacterial infections'],
            ['name' => 'Pain Relief', 'description' => 'Medicines for pain management'],
            ['name' => 'Cardiovascular', 'description' => 'Heart and blood vessel medications'],
            ['name' => 'Diabetes', 'description' => 'Medicines for diabetes management'],
            ['name' => 'Respiratory', 'description' => 'Medicines for breathing problems'],
            ['name' => 'Digestive', 'description' => 'Medicines for digestive issues'],
            ['name' => 'Vitamins', 'description' => 'Vitamin and mineral supplements'],
            ['name' => 'Skin Care', 'description' => 'Medicines for skin conditions'],
            ['name' => 'Mental Health', 'description' => 'Medicines for mental health conditions'],
            ['name' => 'Pediatric', 'description' => 'Medicines for children'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
