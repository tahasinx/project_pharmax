<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id'   => \App\Models\Invoice::factory(),
            'medicine_id'  => Medicine::factory(),
            'batch_id'     => $this->faker->regexify('BATCH[0-9]{3}'),
            'quantity'     => $this->faker->numberBetween(1, 10),
            'rate'         => $this->faker->randomFloat(2, 10, 100),
            'discount'     => $this->faker->randomFloat(2, 0, 10),
            'total_amount' => function (array $attributes) {
                return ($attributes['quantity'] * $attributes['rate']) - $attributes['discount'];
            },
        ];
    }
}
