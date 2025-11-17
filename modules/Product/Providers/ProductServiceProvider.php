<?php

namespace Modules\Product\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Order\Events\OrderFulfilled;
use Modules\Product\Listeners\DecreaseProductStock;

class ProductServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'product');
        $this->loadRoutesFrom(__DIR__.'/../routes.php');

        $this->app->register(EventServiceProvider::class);
    }
}
