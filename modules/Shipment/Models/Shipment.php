<?php

namespace Modules\Shipment\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'provider',
        'provider_shipment_id',
    ];
}
