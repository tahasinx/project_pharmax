<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalAmount = $this->faker->randomFloat(2, 50, 500);
        $paidAmount  = $this->faker->randomFloat(2, 0, $totalAmount);
        $dueAmount   = $totalAmount - $paidAmount;

        return [
            'invoice_id'       => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'customer_id'      => Customer::factory(),
            'date'             => $this->faker->dateTimeBetween('-30 days', 'now'),
            'invoice_no'       => $this->faker->unique()->numberBetween(1000, 9999),
            'total_amount'     => $totalAmount,
            'total_tax'        => $this->faker->randomFloat(2, 0, $totalAmount * 0.1),
            'previous_due'     => $this->faker->randomFloat(2, 0, 100),
            'paid_amount'      => $paidAmount,
            'due_amount'       => $dueAmount,
            'total_discount'   => $this->faker->randomFloat(2, 0, $totalAmount * 0.05),
            'invoice_discount' => $this->faker->randomFloat(2, 0, $totalAmount * 0.02),
            'bank_id'          => null,
            'user_id'          => User::factory(),
            'details'          => $this->faker->optional()->sentence(),
            'payment_type'     => $this->faker->randomElement(['cash', 'bank', 'credit']),
            'status'           => true,
        ];
    }

    /**
     * Indicate that the invoice is paid in full.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['total_amount'],
            'due_amount'  => 0,
        ]);
    }

    /**
     * Indicate that the invoice is partially paid.
     */
    public function partiallyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['total_amount'] * 0.5,
            'due_amount'  => $attributes['total_amount'] * 0.5,
        ]);
    }

    /**
     * Indicate that the invoice is unpaid.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => 0,
            'due_amount'  => $attributes['total_amount'],
        ]);
    }
}
