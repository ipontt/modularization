<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

pest()->extend(Modules\Order\Tests\OrderTestCase::class)
    ->in(__DIR__.'/../modules/Order/Tests');

pest()->extend(Modules\Product\Tests\ProductTestCase::class)
    ->in(__DIR__.'/../modules/Product/Tests');

pest()->extend(Modules\Shipment\Tests\ShipmentTestCase::class)
    ->in(__DIR__.'/../modules/Shipment/Tests');

pest()->extend(Modules\Payment\Tests\PaymentTestCase::class)
    ->in(__DIR__.'/../modules/Payment/Tests');
