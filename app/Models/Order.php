<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'status',
        'total',
        'payment_gateway',
        'payment_id',
    ];
}
