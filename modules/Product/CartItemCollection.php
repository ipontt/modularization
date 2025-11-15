<?php

namespace Modules\Product;

use Illuminate\Support\Collection;
use Modules\Product\Models\Product;

class CartItemCollection
{
    /** @param Collection<int, CartItem> $items */
    public function __construct(
        public protected(set) Collection $items,
    ) {}

    /** @param Collection<int, array{id: int, quantity: int}> $data */
    public static function fromCheckoutData(Collection $data): CartItemCollection
    {
        $cartItems = $data->map(fn ($productDetail) => new CartItem(
            product: ProductDTO::fromEloquentModel(Product::query()->findOrFail($productDetail['id'])),
            quantity: $productDetail['quantity'],
        ));

        return new self($cartItems);
    }

    public function total(): int
    {
        return $this->items->sum(fn (CartItem $item) => $item->quantity * $item->product->price);
    }
}
