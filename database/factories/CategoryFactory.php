<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Antibiotics',
                'Pain Relief',
                'Cardiovascular',
                'Diabetes',
                'Respiratory',
                'Gastrointestinal',
                'Neurological',
                'Dermatology',
                'Vitamins',
                'Supplements',
                'First Aid',
                'Pediatric'
            ]),
            'description' => $this->faker->optional()->sentence(),
            'status' => true,
        ];
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => false,
        ]);
    }
}
