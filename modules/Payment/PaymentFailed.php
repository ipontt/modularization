<?php

namespace Modules\Payment;

use Modules\Order\Contracts\OrderDTO;
use Modules\User\UserDTO;

readonly class PaymentFailed
{
    public function __construct(
        public OrderDTO $order,
        public UserDTO $user,
        public string $reason,
    ) {}
}
