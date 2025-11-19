<?php

use Modules\Order\Checkout\OrderFulfilled;
use Modules\Order\Checkout\OrderReceived;
use Modules\Order\Checkout\SendOrderConfirmationEmail;
use Modules\Order\Contracts\OrderDTO;
use Modules\User\UserDTO;

it('can send order confirmation email', function () {
    Mail::fake();

    $event = new OrderFulfilled(
        order: new OrderDTO(
            id: -1,
            total: 100_00_00,
            localizedTotal: '$100.00',
            url: '/orders/-1',
            lines: [],
        ),
        user: new UserDTO(
            id: -2,
            email: 'test@email',
            name: 'Test User',
        ),
    );

    $this->app->make(SendOrderConfirmationEmail::class)->handle($event);

    Mail::assertQueued(OrderReceived::class, 'test@email');
});
