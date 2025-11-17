<?php

namespace Modules\Product\Providers;

use Modules\Order\Events\OrderFulfilled;
use Modules\Product\Listeners\DecreaseProductStock;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        OrderFulfilled::class => [
            DecreaseProductStock::class,
        ],
    ];
}
