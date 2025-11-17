<?php

namespace Modules\Order\Actions;

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
    ) {}

    public function handle(CartItemCollection $cartItems, PayBuddy $paymentGateway, string $paymentToken, int $userId): Order
    {
        $total = $cartItems->total();

        $order = Order::query()->create([
            'status' => 'completed',
            'total' => $total,
            'user_id' => $userId,
        ]);

        foreach ($cartItems->items as $cartItem) {
            $this->productStockManager->decrement($cartItem->product->id, $cartItem->quantity);
            $order->lines()->create([
                'product_id' => $cartItem->product->id,
                'product_price' => $cartItem->product->price,
                'quantity' => $cartItem->quantity,
            ]);
        }

        $this->createPaymentForOrder->handle(
            $order->id,
            $userId,
            $total,
            $paymentGateway,
            $paymentToken,
        );

        return $order;
    }
}
