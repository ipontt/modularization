<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'price',
        'stock',
    ];
}
