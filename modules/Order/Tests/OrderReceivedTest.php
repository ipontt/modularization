<?php

use Modules\Order\Checkout\OrderReceived;

it('renders the mailable', function () {
    $mailable = new OrderReceived('$1.95');

    $this->assertIsString($mailable->render());
});
