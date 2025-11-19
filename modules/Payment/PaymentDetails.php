<?php

namespace Modules\Payment;

readonly class PaymentDetails
{
    public function __construct(
        public string $token,
        public int $total,
        public string $description,
    ) {}
}
