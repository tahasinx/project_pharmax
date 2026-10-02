<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Pfizer',
                'Johnson & Johnson',
                'Novartis',
                'Roche',
                'Merck & Co.',
                'GlaxoSmithKline',
                'Sanofi',
                'AbbVie',
                'Bristol-Myers Squibb',
                'Eli Lilly',
                'Amgen',
                'Gilead Sciences',
            ]),
            'email'   => $this->faker->optional()->companyEmail(),
            'mobile'  => $this->faker->optional()->numerify('01#########'),
            'address' => $this->faker->optional()->address(),
            'details' => $this->faker->optional()->sentence(),
            'status'  => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
