<?php

namespace Modules\Payment\Infrastructure\Providers;

use Modules\Order\Checkout\OrderStarted;
use Modules\Payment\PayOrder;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        OrderStarted::class => [
            PayOrder::class,
        ],
    ];
}
