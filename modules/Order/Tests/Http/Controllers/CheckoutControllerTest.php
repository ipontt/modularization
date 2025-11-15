<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\Models\Product;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('successfuly creates an order', function () {
    $user = User::factory()->create();
    $products = Product::factory()
        ->count(2)
        ->sequence(
            ['name' => 'Very expensive air fryer', 'price' => 100_00, 'stock' => 10],
            ['name' => 'Macbook Pro M3', 'price' => 500_00, 'stock' => 10],
        )
        ->create();

    $paymentToken = PayBuddy::validToken();

    $response = $this->actingAs($user)
        ->post(route('order::checkout', [
            'payment_token' => $paymentToken,
            'products' => [
                ['id' => $products->first()->id, 'quantity' => 1],
                ['id' => $products->last()->id, 'quantity' => 1]
            ]
        ]));

    $response->assertStatus(Response::HTTP_CREATED);

    $order = Order::query()->latest('id')->first();

    // Order
    $this->assertNotNull($order);
    $this->assertTrue($order->user->is($user));
    $this->assertEquals(600_00, $order->total);
    $this->assertEquals('paid', $order->status);
    $this->assertEquals('PayBuddy', $order->payment_gateway);
    $this->assertEquals(36, strlen($order->payment_id));

    // Order Lines
    $this->assertCount(2, $order->lines);
    foreach ($products as $product) {
        $order_line = $order->lines->firstWhere('product_id', $product->id);
        $this->assertEquals($product->price, $order_line->product_price);
        $this->assertEquals(1, $order_line->quantity);
    }
});
