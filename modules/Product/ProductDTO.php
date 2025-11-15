<?php

namespace Modules\Product;

use Modules\Product\Models\Product;

readonly class ProductDTO
{
    public function __construct(
        public int $id,
        public int $price,
        public int $stock,
    ) {}

    public static function fromEloquentModel(Product $product): self
    {
        return new self(
            id: $product->id,
            price: $product->price,
            stock: $product->stock,
        );
    }
}
