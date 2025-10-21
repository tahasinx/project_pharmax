<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Manufacturer>
 */
class ManufacturerFactory extends Factory
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
                'Gilead Sciences'
            ]),
            'contact_person' => $this->faker->optional()->name(),
            'email' => $this->faker->optional()->companyEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'address' => $this->faker->optional()->address(),
            'status' => true,
        ];
    }

    /**
     * Indicate that the manufacturer is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => false,
        ]);
    }
}
