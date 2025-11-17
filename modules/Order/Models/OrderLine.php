<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $order_id
 * @property int $product_id
 * @property int $product_price
 * @property int $quantity
 */
class OrderLine extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_price',
        'quantity',
    ];
}
