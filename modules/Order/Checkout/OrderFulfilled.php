<?php

namespace Modules\Order\Checkout;

use Modules\Order\Contracts\OrderDTO;
use Modules\User\UserDTO;

readonly class OrderFulfilled
{
    public function __construct(
        public OrderDTO $order,
        public UserDTO $user,
    ) {}
}
