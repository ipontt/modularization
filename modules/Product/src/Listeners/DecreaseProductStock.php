<?php

namespace Modules\Product\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Payment\PaymentSuceeded;
use Modules\Product\Warehouse\ProductStockManager;

class DecreaseProductStock implements ShouldQueue
{
    public function __construct(
        protected ProductStockManager $productStockManager,
    ) {}

    public function handle(PaymentSuceeded $event): void
    {
        foreach ($event->order->lines as $orderLine) {
            $this->productStockManager->decrement($orderLine->productId, $orderLine->quantity);
        }
    }
}
