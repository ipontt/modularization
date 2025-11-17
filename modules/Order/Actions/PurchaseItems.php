<?php

namespace Modules\Order\Actions;

use Modules\Order\Exceptions\PaymentFailedException;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Modules\Product\Warehouse\ProductStockManager;
use RuntimeException;

class PurchaseItems
{
    public function __construct(
        protected ProductStockManager $productStockManager,
    ) {}

    public function handle(CartItemCollection $cartItems, PayBuddy $paymentGateway, string $paymentToken, int $userId): Order
    {
        $total = $cartItems->total();

        try {
            $charge = $paymentGateway->charge(
                token: $paymentToken,
                amount: $total,
                description: 'Test payment',
            );
        } catch (RuntimeException $e) {
            throw PaymentFailedException::dueToInvalidToken();
        }

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

        $payment = $order->payments()->create([
            'total' => $total,
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => $charge['id'],
            'user_id' => $userId,
        ]);

        return $order;
    }
}
