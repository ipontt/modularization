<?php

use Illuminate\Support\Str;
use Modules\Order\Checkout\OrderReceived;
use Modules\Order\Checkout\OrderStarted;
use Modules\Order\Checkout\SendOrderConfirmationEmail;
use Modules\Order\Contracts\OrderDTO;
use Modules\Order\Contracts\PendingPayment;
use Modules\Payment\InMemoryGateway;
use Modules\User\UserDTO;

it('can send order confirmation email', function () {
    Mail::fake();

    $event = new OrderStarted(
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
        pendingPayment: new PendingPayment(
            provider: new InMemoryGateway,
            paymentToken: (string) Str::uuid7(),
        )
    );

    $this->app->make(SendOrderConfirmationEmail::class)->handle($event);

    Mail::assertQueued(OrderReceived::class, 'test@email');
});
