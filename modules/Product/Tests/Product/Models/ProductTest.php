<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Product;

uses(RefreshDatabase::class);

it('creates a product', function () {
    $product = Product::factory()->create();

    $this->assertTrue(true);
});
