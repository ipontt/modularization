<?php

namespace Modules\Order\Actions;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Modules\Order\DTOs\OrderDTO;
use Modules\Order\DTOs\PendingPayment;
use Modules\Order\Events\OrderFulfilled;
use Modules\Order\Models\Order;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Product\CartItemCollection;
use Modules\User\UserDTO;

class PurchaseItems
{
    public function __construct(
        protected CreatePaymentForOrder $createPaymentForOrder,
        protected DatabaseManager $databaseManager,
        protected Dispatcher $events,
    ) {}

    public function handle(CartItemCollection $items, PendingPayment $pendingPayment, UserDTO $user): OrderDTO
    {
        $order = $this->databaseManager->transaction(function () use ($items, $pendingPayment, $user): OrderDTO {
            $order = Order::startForUser($user->id);
            $order->addLinesFromCartItems($items);
            $order->fulfill();

            $this->createPaymentForOrder->handle(
                orderId: $order->id,
                userId: $user->id,
                total: $items->total(),
                paymentGateway: $pendingPayment->provider,
                paymentToken: $pendingPayment->paymentToken,
            );

            return OrderDTO::fromEloquentModel($order);
        });

        $this->events->dispatch(
            new OrderFulfilled(
                order: $order,
                user: $user,
            ),
        );

        return $order;
    }
}
