<?php

namespace Modules\Product\Collections;

use Illuminate\Support\Collection;
use Modules\Product\DTOs\CartItem;
use Modules\Product\DTOs\ProductDTO;
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

    public static function fromProduct(ProductDTO $product, int $quantity): CartItemCollection
    {
        $items = collect([
            new CartItem(
                product: $product,
                quantity: $quantity,
            ),
        ]);

        return new self($items);
    }

    public function total(): int
    {
        return $this->items->sum(fn (CartItem $item) => $item->quantity * $item->product->price);
    }
}
