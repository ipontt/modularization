<?php

namespace Modules\Order\DTOs;

use Modules\Order\Models\Order;

readonly class OrderDTO
{
    /** @param OrderLineDTO[] $lines */
    public function __construct(
        public int $id,
        public int $total,
        public string $localizedTotal,
        public string $url,
        public array $lines,
    ) {}

    public static function fromEloquentModel(Order $order): self
    {
        return new self(
            $order->id,
            $order->total,
            $order->localizedTotal(),
            $order->url(),
            OrderLineDTO::fromEloquentCollection($order->lines),
        );
    }
}
