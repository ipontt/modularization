<?php

namespace Modules\Order\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\Order\Models\OrderLine;

readonly class OrderLineDTO
{
    public function __construct(
        public int $productId,
        public int $productPrice,
        public int $quantity,
    ) {}

    public static function fromEloquentModel(OrderLine $orderLine): self
    {
        return new self(
            $orderLine->product_id,
            $orderLine->product_price,
            $orderLine->quantity,
        );
    }

    /**
     * @param Collection<int, OrderLine> $orderLines
     * @return OrderLineDTO[]
     */
    public static function fromEloquentCollection(Collection $orderLines): array
    {
        return $orderLines->map(fn (OrderLine $orderLine) => self::fromEloquentModel($orderLine))->all();
    }
}
