<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medicine_id'     => Medicine::factory(),
            'batch_number'    => $this->faker->regexify('BATCH[0-9]{3}'),
            'expiry_date'     => $this->faker->dateTimeBetween('+6 months', '+2 years'),
            'quantity'        => $this->faker->numberBetween(10, 500),
            'min_stock_level' => $this->faker->numberBetween(5, 20),
            'max_stock_level' => $this->faker->numberBetween(100, 1000),
            'purchase_price'  => $this->faker->randomFloat(2, 5, 50),
            'selling_price'   => $this->faker->randomFloat(2, 10, 80),
            'supplier'        => $this->faker->company(),
            'notes'           => $this->faker->optional()->sentence(),
            'is_active'       => true,
        ];
    }

    /**
     * Indicate that the stock is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the stock is low.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity'        => $this->faker->numberBetween(1, 5),
            'min_stock_level' => 10,
        ]);
    }

    /**
     * Indicate that the stock is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Indicate that the stock is expiring soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
        ]);
    }
}
