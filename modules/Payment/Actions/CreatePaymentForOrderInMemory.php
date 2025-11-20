<?php

namespace Modules\Payment\Actions;

use Illuminate\Support\Str;
use Modules\Payment\Exceptions\PaymentFailedException;
use Modules\Payment\Payment;
use Modules\Payment\PaymentGateway;
use Modules\Payment\PaymentProvider;

class CreatePaymentForOrderInMemory implements CreatePaymentForOrderInterface
{
    /** @var Payment[] */
    public array $payments = [];

    protected bool $shouldFail = false;

    /** {inheritDoc} */
    public function handle(int $orderId, int $userId, int $total, PaymentGateway $paymentGateway, string $paymentToken): Payment
    {
        if ($this->shouldFail) {
            throw new PaymentFailedException;
        }

        $payment = new Payment([
            'order_id' => $orderId,
            'user_id' => $userId,
            'total' => $total,
            'payment_gateway' => PaymentProvider::InMemory,
            'payment_token' => (string) Str::uuid7(),
        ]);

        $this->payments[] = $payment;

        return $payment;
    }

    public function shouldFail(): void
    {
        $this->shouldFail = true;
    }
}
