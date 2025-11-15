<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'quantity',
        'user_id',
        'product_id',
    ];
}
