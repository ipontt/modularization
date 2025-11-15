<?php

namespace Modules\Product;

use Illuminate\Support\Collection;
use Modules\Product\Models\Product;

class CartItemCollection
{
    public function __construct(
        /** @param Collection<int, CartItem> $items */
        public protected(set) Collection $items,
    ) {}

    public static function fromCheckoutData(Collection $data): CartItemCollection
    {
        $cartItems = $data->map(fn ($productDetail) => new CartItem(Product::query()->findOrFail($productDetail['id']), $productDetail['quantity']));

        return new static($cartItems);
    }

    public function total(): int
    {
        return $this->items->sum(fn (CartItem $item) => $item->quantity * $item->product->price);
    }
}
