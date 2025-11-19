<?php

namespace Modules\Payment;

use Modules\Payment\Exceptions\PaymentFailedException;
use RuntimeException;

class PayBuddyGateway implements PaymentGateway
{
    public function __construct(
        protected PayBuddySDK $payBuddySDK,
    ) {}

    /**
     * @param PaymentDetails $details
     * @return SuccessfulPayment
     * @throws PaymentFailedException
     */
    public function charge(PaymentDetails $details): SuccessfulPayment
    {
        try {
            $charge = $this->payBuddySDK->charge(
                $details->token,
                $details->total,
                $details->description,
            );
        } catch (RuntimeException $exception) {
            throw new PaymentFailedException($exception->getMessage());
        }

        return new SuccessfulPayment(
            id: $charge['id'],
            total: $charge['amount'],
            provider: $this->id(),
        );
    }

    public function id(): PaymentProvider
    {
        return PaymentProvider::PayBuddy;
    }
}
