<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
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
            'name'    => $this->faker->name(),
            'mobile'  => $this->faker->unique()->numerify('01#########'),
            'email'   => $this->faker->optional()->safeEmail(),
            'phone'   => $this->faker->optional()->phoneNumber(),
            'address' => $this->faker->optional()->address(),
            'city'    => $this->faker->optional()->city(),
            'state'   => $this->faker->optional()->state(),
            'zip'     => $this->faker->optional()->postcode(),
            'country' => $this->faker->optional()->country(),
            'status'  => true,
        ];
    }

    /**
     * Indicate that the customer is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    /**
     * Indicate that the customer has no contact information.
     */
    public function noContact(): static
    {
        return $this->state(fn (array $attributes) => [
            'mobile' => null,
            'email'  => null,
            'phone'  => null,
        ]);
    }
}
