<?php

namespace Modules\Payment\Actions;

use Modules\Payment\Payment;
use Modules\Payment\PaymentDetails;
use Modules\Payment\PaymentGateway;

class CreatePaymentForOrder implements CreatePaymentForOrderInterface
{
    /** {inheritDoc} */
    public function handle(
        int $orderId,
        int $userId,
        int $total,
        PaymentGateway $paymentGateway,
        string $paymentToken,
    ): Payment {
        $payment = $paymentGateway->charge(
            details: new PaymentDetails(
                token: $paymentToken,
                total: $total,
                description: 'Test payment',
            ),
        );

        return Payment::query()->create([
            'total' => $payment->total,
            'status' => 'paid',
            'payment_gateway' => $payment->provider,
            'payment_id' => $payment->id,
            'user_id' => $userId,
            'order_id' => $orderId,
        ]);
    }
}
