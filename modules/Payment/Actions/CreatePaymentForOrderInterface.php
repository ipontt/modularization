<?php

namespace Modules\Payment\Actions;

use Modules\Payment\Exceptions\PaymentFailedException;
use Modules\Payment\Payment;
use Modules\Payment\PaymentGateway;

interface CreatePaymentForOrderInterface
{
    /** @throws PaymentFailedException */
    public function handle(
        int $orderId,
        int $userId,
        int $total,
        PaymentGateway $paymentGateway,
        string $paymentToken,
    ): Payment;
}
