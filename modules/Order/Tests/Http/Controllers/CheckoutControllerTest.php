<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Events\OrderFulfilled;
use Modules\Order\Listeners\SendOrderConfirmationEmail;
use Modules\Order\Mail\OrderReceived;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\Listeners\DecreaseProductStock;
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

    $paymentToken = PayBuddy::validToken();

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

    Event::assertDispatchedTimes(OrderFulfilled::class, 1);
    Event::assertDispatched(OrderFulfilled::class, function (OrderFulfilled $event) use ($order, $user) {
        $this->assertTrue($event->orderId === $order->id);
        $this->assertTrue($event->total === $order->total);
        $this->assertTrue($event->localizedTotal === $order->localizedTotal());
        $this->assertTrue($event->userId === $user->id);
        $this->assertTrue($event->userEmail === $user->email);

        return true;
    });
    Event::assertListening(OrderFulfilled::class, SendOrderConfirmationEmail::class);
    Event::assertListening(OrderFulfilled::class, DecreaseProductStock::class);

    // Order
    $this->assertNotNull($order);
    $this->assertTrue($order->user->is($user));
    $this->assertEquals(600_00, $order->total);
    $this->assertEquals('completed', $order->status);

    // Payment
    $payment = $order->lastPayment;
    $this->assertEquals('paid', $payment->status);
    $this->assertEquals('PayBuddy', $payment->payment_gateway);
    $this->assertEquals(36, strlen($payment->payment_id));
    $this->assertEquals(600_00, $payment->total);
    $this->assertTrue($payment->user->is($user));

    // Order Lines
    $this->assertCount(2, $order->lines);
    foreach ($products as $product) {
        $order_line = $order->lines->firstWhere('product_id', $product->id);
        $this->assertEquals($product->price, $order_line->product_price);
        $this->assertEquals(1, $order_line->quantity);
    }
});

it('fails with an invalid token', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $paymentToken = PayBuddy::invalidToken();

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
});
