<?php

namespace Modules\Order\Providers;

use Modules\Order\Events\OrderFulfilled;
use Modules\Order\Listeners\SendOrderConfirmationEmail;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        OrderFulfilled::class => [
            SendOrderConfirmationEmail::class,
        ]
    ];
}
