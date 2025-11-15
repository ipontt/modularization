<?php

namespace Modules\Product;

use Modules\Product\Models\Product;

readonly class CartItem
{
    public function __construct(
        public ProductDTO $product,
        public int $quantity,
    ) {}
}
