<?php

namespace Modules\Product\DTOs;

readonly class CartItem
{
    public function __construct(
        public ProductDTO $product,
        public int $quantity,
    ) {}
}
