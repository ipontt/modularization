<?php

namespace Modules\Order\Checkout;

use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail
{
    public function handle(OrderFulfilled $event): void
    {
        Mail::to($event->user->email)->queue(new OrderReceived($event->order->localizedTotal));
    }
}
