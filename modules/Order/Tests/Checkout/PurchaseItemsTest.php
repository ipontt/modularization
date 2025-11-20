<?php

namespace Modules\Order\Tests\Checkout;

use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mail;
use Modules\Order\Checkout\OrderStarted;
use Modules\Order\Checkout\PurchaseItems;
use Modules\Order\Contracts\PendingPayment;
use Modules\Payment\Actions\CreatePaymentForOrderInMemory;
use Modules\Payment\Actions\CreatePaymentForOrderInterface;
use Modules\Payment\InMemoryGateway;
use Modules\Payment\PayBuddySDK;
use Modules\Product\Collections\CartItemCollection;
use Modules\Product\DTOs\ProductDTO;
use Modules\Product\Models\Product;
use Modules\User\UserDTO;

uses(RefreshDatabase::class);

it('creates_an_order', function () {
    Mail::fake();
    Event::fake();

    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 10,
        'price' => 100_00,
    ]);

    $createPayment = new CreatePaymentForOrderInMemory;
    $this->app->instance(CreatePaymentForOrderInterface::class, $createPayment);

    $cartItemCollection = CartItemCollection::fromProduct(ProductDTO::fromEloquentModel($product), 2);
    $pendingPayment = new PendingPayment(new InMemoryGateway, PayBuddySDK::validToken());
    $userDto = UserDTO::fromEloquentModel($user);

    $purchaseItems = app(PurchaseItems::class);
    $order = $purchaseItems->handle($cartItemCollection, $pendingPayment, $userDto);

    $orderLine = $order->lines[0];

    $this->assertEquals($product->price * 2, $order->total);
    $this->assertCount(1, $order->lines);
    $this->assertEquals($product->id, $orderLine->productId);
    $this->assertEquals($product->price, $orderLine->productPrice);
    $this->assertEquals(2, $orderLine->quantity);

    Event::assertDispatched(OrderStarted::class, function (OrderStarted $event) use ($userDto, $order) {
        return $event->order === $order && $event->user === $userDto;
    });
});
