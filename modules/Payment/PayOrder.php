<?php

namespace Modules\Payment;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Order\Checkout\OrderStarted;
use Modules\Payment\Actions\CreatePaymentForOrderInterface;
use Modules\Payment\Exceptions\PaymentFailedException;

class PayOrder implements ShouldQueue
{
    public function __construct(
        public CreatePaymentForOrderInterface $createPaymentForOrder,
        public Dispatcher $events,
    ) {}

    public function handle(OrderStarted $event): void
    {
        try {
            $this->createPaymentForOrder->handle(
                orderId: $event->order->id,
                userId: $event->user->id,
                total: $event->order->total,
                paymentGateway: $event->pendingPayment->provider,
                paymentToken: $event->pendingPayment->paymentToken,
            );
        } catch (PaymentFailedException $exception) {
            $this->events->dispatch(
                new PaymentFailed($event->order, $event->user, $exception->getMessage()),
            );

            throw $exception;
        }

        $this->events->dispatch(
            new PaymentSuceeded($event->order, $event->user),
        );
    }
}
