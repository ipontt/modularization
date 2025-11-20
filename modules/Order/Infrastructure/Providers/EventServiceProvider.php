<?php

namespace Modules\Order\Infrastructure\Providers;

use Modules\Order\Checkout\NotifyUserOfPaymentFailure;
use Modules\Order\Checkout\OrderStarted;
use Modules\Order\Checkout\SendOrderConfirmationEmail;
use Modules\Order\CompleteOrder;
use Modules\Order\MarkOrderAsFailed;
use Modules\Payment\PaymentFailed;
use Modules\Payment\PaymentSuceeded;
use Modules\Product\Listeners\DecreaseProductStock;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        OrderStarted::class => [
            SendOrderConfirmationEmail::class,
        ],

        PaymentSuceeded::class => [
            CompleteOrder::class,
            DecreaseProductStock::class,
        ],

        PaymentFailed::class => [
            NotifyUserOfPaymentFailure::class,
            MarkOrderAsFailed::class,
        ],
    ];
}
