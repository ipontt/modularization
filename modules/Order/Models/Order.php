<?php

namespace Modules\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Product\Models\Product;

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

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<OrderLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    /** @return BelongsToMany<Product, $this, OrderLine> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_lines')
            ->using(OrderLine::class)
            ->as('order_line');
    }
}
