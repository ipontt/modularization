<?php

namespace Modules\Order\Actions;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Modules\Order\Events\OrderFulfilled;
use Modules\Order\Models\Order;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;

class PurchaseItems
{
    public function __construct(
        protected CreatePaymentForOrder $createPaymentForOrder,
        protected DatabaseManager $databaseManager,
        protected Dispatcher $events,
    ) {}

    public function handle(CartItemCollection $items, PayBuddy $paymentGateway, string $paymentToken, int $userId, string $userEmail): Order
    {
        $order = $this->databaseManager->transaction(function () use ($items, $paymentGateway, $paymentToken, $userId): Order {
            $order = Order::startForUser($userId);
            $order->addLinesFromCartItems($items);
            $order->fulfill();

            $this->createPaymentForOrder->handle(
                $order->id,
                $userId,
                $items->total(),
                $paymentGateway,
                $paymentToken,
            );

            return $order;
        });

        $this->events->dispatch(
            new OrderFulfilled(
                orderId: $order->id,
                total: $order->total,
                localizedTotal: $order->localizedTotal(),
                cartItems: $items,
                userId: $userId,
                userEmail: $userEmail,
            )
        );

        return $order;
    }
}
