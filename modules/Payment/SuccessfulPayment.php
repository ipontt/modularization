<?php

namespace Modules\Payment;

readonly class SuccessfulPayment
{
    public function __construct(
        public string $id,
        public int $total,
        public PaymentProvider $provider,
    ) {}
}
