<?php

namespace Modules\Payment\Infrastructure\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Payment\Payment;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'total' => fake()->numberBetween(100, 10000),
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => (string) Str::uuid7(),
            'user_id' => User::factory(),
        ];
    }
}
