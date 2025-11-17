<?php

namespace Modules\Payment\Actions;

use Modules\Order\Exceptions\PaymentFailedException;
use Modules\Payment\PayBuddy;
use Modules\Payment\Payment;
use RuntimeException;

class CreatePaymentForOrder
{
    public function handle(
        int $orderId,
        int $userId,
        int $total,
        PayBuddy $paymentGateway,
        string $paymentToken,
    ): Payment {
        try {
            $charge = $paymentGateway->charge(
                token: $paymentToken,
                amount: $total,
                description: 'Test payment',
            );
        } catch (RuntimeException) {
            throw PaymentFailedException::dueToInvalidToken();
        }

        return Payment::query()->create([
            'total' => $total,
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => $charge['id'],
            'user_id' => $userId,
            'order_id' => $orderId,
        ]);
    }
}
