<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Checkout\CheckoutController;
use Modules\Order\Order;

Route::name('order::')->group(function () {

    Route::middleware('auth')->group(function () {
        Route::post('checkout', CheckoutController::class)->name('checkout');

        Route::get('order/{order}', fn (Order $order) => $order)->name('orders.show');
    });
});
