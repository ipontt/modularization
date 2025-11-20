<?php

use Illuminate\Support\Facades\Event;
use Modules\Order\CompleteOrder;
use Modules\Payment\PaymentSuceeded;
use Modules\Product\Listeners\DecreaseProductStock;

it('has listeners', function () {
    Event::fake();

    Event::assertListening(PaymentSuceeded::class, DecreaseProductStock::class);
    Event::assertListening(PaymentSuceeded::class, CompleteOrder::class);
});
