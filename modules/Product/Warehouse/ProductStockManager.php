<?php

namespace Modules\Product\Warehouse;

use Modules\Product\Models\Product;

class ProductStockManager
{
    public function decrement(int $product_id, int $amount): void
    {
        Product::query()->findOrFail($product_id)->decrement('stock', $amount);
    }
}
