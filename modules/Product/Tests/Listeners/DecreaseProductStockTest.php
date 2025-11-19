<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Order\Checkout\OrderFulfilled;
use Modules\Order\Contracts\OrderDTO;
use Modules\Order\Contracts\OrderLineDTO;
use Modules\Product\CartItem;
use Modules\Product\CartItemCollection;
use Modules\Product\Listeners\DecreaseProductStock;
use Modules\Product\Models\Product;
use Modules\Product\ProductDTO;
use Modules\User\UserDTO;

uses(RefreshDatabase::class);

it('decreases the product stock for all the product lines', function () {
    $product_one = Product::factory()->create(['stock' => 5]);
    $product_two = Product::factory()->create(['stock' => 5]);

    $cart_items = new CartItemCollection(collect([
        new CartItem(ProductDTO::fromEloquentModel($product_one), 2),
        new CartItem(ProductDTO::fromEloquentModel($product_two), 1),
    ]));

    $event = new OrderFulfilled(
        order: new OrderDTO(
            id: -1,
            total: 100_00_00,
            localizedTotal: '$100.00',
            url: '/orders/-1',
            lines: [
                new OrderLineDTO(
                    productId: $product_one->id,
                    productPrice: $product_one->price,
                    quantity: 2,
                ),
                new OrderLineDTO(
                    productId: $product_two->id,
                    productPrice: $product_two->price,
                    quantity: 1,
                ),
            ],
        ),
        user: new UserDTO(
            id: -2,
            email: 'test@email',
            name: 'Test User',
        ),
    );

    $this->app->make(DecreaseProductStock::class)->handle($event);

    $this->assertTrue($product_one->fresh()->stock === 3);
    $this->assertTrue($product_two->fresh()->stock === 4);
});
