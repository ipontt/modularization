<?php

namespace Modules\Order;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Payment\PaymentFailed;

class MarkOrderAsFailed implements ShouldQueue
{
    public function handle(PaymentFailed $event): void
    {
        Order::query()->findOrFail($event->order->id)->markAsFailed();
    }
}
