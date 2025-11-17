<?php

use Illuminate\Support\Facades\Route;

Route::name('shipment::')->group(function () {
    Route::get('shipment-test', fn () => 'Hello World')->name('index');
});
