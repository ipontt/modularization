<?php

use Modules\Order\Checkout\OrderReceived;

it('renders the mailable', function () {
    $order = new \Modules\Order\Contracts\OrderDTO(
        id: 1,
        total: 195,
        localizedTotal: '$1.95',
        url: route('order::orders.show', ['order' => 1]),
        lines: [],
    );

    $mailable = new OrderReceived($order);

    // $mailable = new OrderReceived('$1.95');

    $this->assertIsString($mailable->render());
});
