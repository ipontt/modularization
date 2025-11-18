<?php

namespace Modules\Order\Events;

use Modules\Order\DTOs\OrderDTO;
use Modules\User\UserDTO;

readonly class OrderFulfilled
{
    public function __construct(
        public OrderDTO $order,
        public UserDTO $user,
    ) {}
}
