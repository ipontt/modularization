<?php

namespace Modules\Order\Actions;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Mail\OrderReceived;
use Modules\Order\Models\Order;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Modules\Product\Warehouse\ProductStockManager;

class PurchaseItems
{
    public function __construct(
        protected ProductStockManager $productStockManager,
        protected CreatePaymentForOrder $createPaymentForOrder,
        protected DatabaseManager $databaseManager,
    ) {}

    public function handle(CartItemCollection $items, PayBuddy $paymentGateway, string $paymentToken, int $userId, string $userEmail): Order
    {
        $order = $this->databaseManager->transaction(function () use ($items, $paymentGateway, $paymentToken, $userId): Order {
            $order = Order::startForUser($userId);
            $order->addLinesFromCartItems($items);
            $order->fulfill();

            foreach ($items->items as $cartItem) {
                $this->productStockManager->decrement($cartItem->product->id, $cartItem->quantity);
            }

            $this->createPaymentForOrder->handle(
                $order->id,
                $userId,
                $items->total(),
                $paymentGateway,
                $paymentToken,
            );

            return $order;
        });

        Mail::to($userEmail)->queue(new OrderReceived($order->localizedTotal()));

        return $order;
    }
}
