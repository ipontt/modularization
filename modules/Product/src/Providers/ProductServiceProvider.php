<?php

namespace Modules\Product\Providers;

use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'product');
        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');

        $this->app->register(EventServiceProvider::class);
    }
}
