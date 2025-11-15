<?php

use Illuminate\Support\Facades\Route;

Route::name('product::')->group(function () {
    Route::get('product-test', fn() => 'Hello World')->name('test');
});
