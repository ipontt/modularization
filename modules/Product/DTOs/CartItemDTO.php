<?php

namespace Modules\Product\DTOs;

use Modules\Product\Models\Product;

readonly class CartItemDTO
{
    public function __construct(
        public Product $product,
        public int $quantity,
    ) {}
}
