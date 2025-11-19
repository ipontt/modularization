<?php

namespace Modules\Order\Infrastructure\Providers;

use Modules\Order\Checkout\OrderFulfilled;
use Modules\Order\Checkout\SendOrderConfirmationEmail;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        OrderFulfilled::class => [
            SendOrderConfirmationEmail::class,
        ]
    ];
}
