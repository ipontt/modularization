<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Checkout\OrderStarted;
use Modules\Order\Order;
use Modules\Payment\PayBuddySDK;
use Modules\Product\Models\Product;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('successfuly creates an order', function () {
    Mail::fake();
    Event::fake();

    $user = User::factory()->create();
    $products = Product::factory()
        ->count(2)
        ->sequence(
            ['name' => 'Very expensive air fryer', 'price' => 100_00, 'stock' => 10],
            ['name' => 'Macbook Pro M3', 'price' => 500_00, 'stock' => 10],
        )
        ->create();

    $paymentToken = PayBuddySDK::validToken();

    $response = $this->actingAs($user)
        ->post(route('order::checkout', [
            'payment_token' => $paymentToken,
            'products' => [
                ['id' => $products->first()->id, 'quantity' => 1],
                ['id' => $products->last()->id, 'quantity' => 1],
            ],
        ]));

    $order = Order::query()->latest('id')->first();

    $response
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonPath('data.order_url', $order->url());

    // Order
    $this->assertNotNull($order);
    $this->assertTrue($order->user->is($user));
    $this->assertEquals(600_00, $order->total);
    $this->assertEquals(Order::PENDING, $order->status);

    // Order Lines
    $this->assertCount(2, $order->lines);
    foreach ($products as $product) {
        $order_line = $order->lines->firstWhere('product_id', $product->id);
        $this->assertEquals($product->price, $order_line->product_price);
        $this->assertEquals(1, $order_line->quantity);
    }

    Event::assertDispatched(OrderStarted::class);
});

it('fails with an invalid token', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $paymentToken = PayBuddySDK::invalidToken();

    $response = $this->actingAs($user)
        ->postJson(route('order::checkout', [
            'payment_token' => $paymentToken,
            'products' => [
                ['id' => $product->id, 'quantity' => 1],
            ],
        ]));

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['payment_token']);

    $this->assertEquals(0, Order::query()->count());
})->skip();
