<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
