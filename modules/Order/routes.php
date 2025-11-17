<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\CheckoutController;
use Modules\Order\Models\Order;

Route::name('order::')->group(function () {

    Route::middleware('auth')->group(function () {
        Route::post('checkout', CheckoutController::class)->name('checkout');

        Route::get('order/{order}', fn (Order $order) => $order)->name('orders.show');
    });
});
