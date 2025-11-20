<?php

namespace Modules\Order\Checkout;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Modules\Order\Contracts\OrderDTO;
use Modules\Order\Contracts\PendingPayment;
use Modules\Order\Order;
use Modules\Payment\Actions\CreatePaymentForOrderInterface;
use Modules\Product\Collections\CartItemCollection;
use Modules\User\UserDTO;

class PurchaseItems
{
    public function __construct(
        protected CreatePaymentForOrderInterface $createPaymentForOrder,
        protected DatabaseManager $databaseManager,
        protected Dispatcher $events,
    ) {}

    public function handle(CartItemCollection $items, PendingPayment $pendingPayment, UserDTO $user): OrderDTO
    {
        $order = $this->databaseManager->transaction(function () use ($items, $user): OrderDTO {
            $order = Order::startForUser($user->id);
            $order->addLinesFromCartItems($items);
            //            $order->fulfill();
            $order->start();

            //            $this->createPaymentForOrder->handle(
            //                orderId: $order->id,
            //                userId: $user->id,
            //                total: $items->total(),
            //                paymentGateway: $pendingPayment->provider,
            //                paymentToken: $pendingPayment->paymentToken,
            //            );

            return OrderDTO::fromEloquentModel($order);
        });

        $this->events->dispatch(
            new OrderStarted(
                order: $order,
                user: $user,
                pendingPayment: $pendingPayment,
            ),
            //            new OrderFulfilled(
            //                order: $order,
            //                user: $user,
            //            ),
        );

        return $order;
    }
}
