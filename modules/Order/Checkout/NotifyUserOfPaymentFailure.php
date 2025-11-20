<?php

namespace Modules\Order\Checkout;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Payment\PaymentFailed;

class NotifyUserOfPaymentFailure implements ShouldQueue
{
    public function handle(PaymentFailed $event): void
    {
        Mail::to($event->user->email)->queue(new PaymentForOrderFailed($event->order));
    }
}
