<?php

namespace Modules\Order\Checkout;

use Modules\Order\Contracts\OrderDTO;
use Modules\Order\Contracts\PendingPayment;
use Modules\User\UserDTO;

readonly class OrderStarted
{
    public function __construct(
        public OrderDTO $order,
        public UserDTO $user,
        public PendingPayment $pendingPayment,
    ) {}
}
