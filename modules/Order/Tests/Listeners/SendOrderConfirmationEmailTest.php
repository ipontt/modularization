<?php

use Modules\Order\Events\OrderFulfilled;
use Modules\Order\Listeners\SendOrderConfirmationEmail;
use Modules\Order\Mail\OrderReceived;
use Modules\Product\CartItemCollection;

it('can send order confirmation email', function () {
    Mail::fake();

    $event = new OrderFulfilled(
        orderId: -1,
        total: 100_00_00,
        localizedTotal: '$100.00',
        cartItems: new CartItemCollection(collect()),
        userId: -1,
        userEmail: 'test@email'
    );

    $this->app->make(SendOrderConfirmationEmail::class)->handle($event);

    Mail::assertQueued(OrderReceived::class, 'test@email');
});
