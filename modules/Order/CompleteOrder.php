<?php

namespace Modules\Order;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Payment\PaymentSuceeded;

class CompleteOrder implements ShouldQueue
{
    public function handle(PaymentSuceeded $event): void
    {
        Order::query()->findOrFail($event->order->id)->complete();
    }
}
