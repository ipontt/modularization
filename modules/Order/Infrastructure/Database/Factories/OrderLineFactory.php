<?php

namespace Modules\Order\Infrastructure\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Order\Order;
use Modules\Order\OrderLine;
use Modules\Product\Models\Product;

/** @extends Factory<OrderLine> */
class OrderLineFactory extends Factory
{
    protected $model = OrderLine::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_price_in_cents' => $this->faker->randomNumber(),
            'quantity' => 1,
        ];
    }
}
