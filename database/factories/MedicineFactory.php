<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicine>
 */
class MedicineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id'         => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'name'               => $this->faker->words(2, true),
            'category_id'        => Category::factory(),
            'manufacturer_id'    => Manufacturer::factory(),
            'generic_name'       => $this->faker->word(),
            'strength'           => $this->faker->randomElement(['500mg', '250mg', '100mg', '50mg']),
            'box_size'           => $this->faker->numberBetween(1, 100),
            'product_location'   => $this->faker->randomElement(['Shelf A', 'Shelf B', 'Shelf C', 'Refrigerator']),
            'price'              => $this->faker->randomFloat(2, 5, 100),
            'manufacturer_price' => $this->faker->randomFloat(2, 3, 80),
            'unit'               => $this->faker->randomElement(['tablet', 'capsule', 'ml', 'mg', 'g']),
            'details'            => $this->faker->sentence(),
            'image'              => null,
            'medex_id'           => $this->faker->optional()->numerify('####'),
            'medex_name'         => $this->faker->optional()->slug(),
            'qr_code_data'       => $this->faker->optional()->regexify('[A-Z0-9]{8}'),
            'qr_code_type'       => 'product_id',
            'qr_code_image_path' => null,
            'barcode_data'       => $this->faker->optional()->regexify('[0-9]{12}'),
            'barcode_type'       => 'code128',
            'barcode_image_path' => null,
            'status'             => true,
        ];
    }

    /**
     * Indicate that the medicine is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
