<?php

use Illuminate\Support\Facades\Route;

Route::name('order::')->group(function () {
    Route::get('order-test', fn () => 'Hello World')->name('test');
});
