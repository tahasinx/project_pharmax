<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'mobile' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'address' => $this->faker->optional()->address(),
            'city' => $this->faker->optional()->city(),
            'state' => $this->faker->optional()->state(),
            'zip' => $this->faker->optional()->postcode(),
            'country' => $this->faker->optional()->country(),
            'status' => true,
        ];
    }

    /**
     * Indicate that the customer is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => false,
        ]);
    }

    /**
     * Indicate that the customer has no contact information.
     */
    public function noContact(): static
    {
        return $this->state(fn(array $attributes) => [
            'mobile' => null,
            'email' => null,
            'phone' => null,
        ]);
    }
}
