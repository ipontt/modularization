<?php

namespace Modules\Payment;

use Illuminate\Support\Str;

class InMemoryGateway implements PaymentGateway
{
    public function charge(PaymentDetails $details): SuccessfulPayment
    {
        return new SuccessfulPayment(
            id: (string) Str::uuid7(),
            total: $details->total,
            provider: $this->id(),
        );
    }

    public function id(): PaymentProvider
    {
        return PaymentProvider::InMemory;
    }
}
