<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Order\Events\OrderFulfilled;
use Modules\Product\CartItem;
use Modules\Product\CartItemCollection;
use Modules\Product\Listeners\DecreaseProductStock;
use Modules\Product\Models\Product;
use Modules\Product\ProductDTO;

uses(RefreshDatabase::class);

it('decreases the product stock for all the product lines', function () {
    $product_one = Product::factory()->create(['stock' => 5]);
    $product_two = Product::factory()->create(['stock' => 5]);

    $cart_items = new CartItemCollection(collect([
        new CartItem(ProductDTO::fromEloquentModel($product_one), 2),
        new CartItem(ProductDTO::fromEloquentModel($product_two), 1),
    ]));

    $event = new OrderFulfilled(
        orderId: -1,
        total: 100_00_00,
        localizedTotal: '$100.00',
        cartItems: $cart_items,
        userId: -1,
        userEmail: 'test@email'
    );

    $this->app->make(DecreaseProductStock::class)->handle($event);

    $this->assertTrue($product_one->fresh()->stock === 3);
    $this->assertTrue($product_two->fresh()->stock === 4);
});
