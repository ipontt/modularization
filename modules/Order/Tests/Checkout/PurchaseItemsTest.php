<?php

namespace Modules\Order\Tests\Checkout;

use App\Models\User;
use Database\Factories\UserFactory;
use Event;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mail;
use Mockery\MockInterface;
use Modules\Order\Checkout\OrderFulfilled;
use Modules\Order\Checkout\PurchaseItems;
use Modules\Order\Contracts\PendingPayment;
use Modules\Order\Order;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Payment\Actions\CreatePaymentForOrderInMemory;
use Modules\Payment\Actions\CreatePaymentForOrderInterface;
use Modules\Payment\Exceptions\PaymentFailedException;
use Modules\Payment\InMemoryGateway;
use Modules\Payment\PayBuddySDK;
use Modules\Payment\Payment;
use Modules\Product\Collections\CartItemCollection;
use Modules\Product\Database\factories\ProductFactory;
use Modules\Product\DTOs\CartItem;
use Modules\Product\DTOs\ProductDTO;
use Modules\Product\Models\Product;
use Modules\User\UserDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

uses(RefreshDatabase::class);


it('creates_an_order', function () {
    Mail::fake();
    Event::fake();

    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 10,
        'price' => 100_00
    ]);

    $createPayment = new CreatePaymentForOrderInMemory;
    $this->app->instance(CreatePaymentForOrderInterface::class, $createPayment);

    $cartItemCollection = CartItemCollection::fromProduct(ProductDTO::fromEloquentModel($product), 2);
    $pendingPayment = new PendingPayment(new InMemoryGateway(), PayBuddySDK::validToken());
    $userDto = UserDTO::fromEloquentModel($user);

    $purchaseItems = app(PurchaseItems::class);
    $order = $purchaseItems->handle($cartItemCollection, $pendingPayment, $userDto);

    $orderLine = $order->lines[0];

    $this->assertEquals($product->price * 2, $order->total);
    $this->assertCount(1, $order->lines);
    $this->assertEquals($product->id, $orderLine->productId);
    $this->assertEquals($product->price, $orderLine->productPrice);
    $this->assertEquals(2, $orderLine->quantity);

    $payment = $createPayment->payments[0];
    $this->assertCount(1, $createPayment->payments);
    $this->assertEquals($userDto->id, $payment->user_id);

    Event::assertDispatched(OrderFulfilled::class, function (OrderFulfilled $event) use ($userDto, $order) {
        return $event->order === $order && $event->user === $userDto;
    });
});

it('does not create an order if something fails', function () {
    Mail::fake();
    Event::fake();

    $this->expectException(PaymentFailedException::class);

    $createPayment = new CreatePaymentForOrderInMemory();
    $createPayment->shouldFail();
    $this->app->instance(CreatePaymentForOrderInterface::class, $createPayment);

    $user = UserFactory::new()->create();
    $product = ProductFactory::new()->create();

    $cartItemCollection = CartItemCollection::fromProduct(ProductDTO::fromEloquentModel($product), 2);
    $pendingPayment = new PendingPayment(new InMemoryGateway(), PayBuddySDK::validToken());
    $userDto = UserDTO::fromEloquentModel($user);

    $purchaseItems = app(PurchaseItems::class);

    try {
        $purchaseItems->handle($cartItemCollection, $pendingPayment, $userDto);
    } finally {
        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, Payment::count());
        $this->assertCount(0, $createPayment->payments);
        Event::assertNotDispatched(OrderFulfilled::class);
    }
});
