<?php

namespace Modules\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Order\Exceptions\OrderMissingOrderLinesException;
use Modules\Payment\Payment;
use Modules\Product\CartItemCollection;

/**
 * @property int $user_id
 * @property string $status
 * @property int $total
 */
class Order extends Model
{
    const string PENDING = 'pending';

    const string COMPLETED = 'completed';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'status',
        'total',
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

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasOne<Payment, $this> */
    public function lastPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function url(): string
    {
        return route('order::orders.show', ['order' => $this]);
    }

    public static function startForUser(int $userId): self
    {
        return new self([
            'user_id' => $userId,
            'status' => self::PENDING,
        ]);
    }

    public function addLinesFromCartItems(CartItemCollection $items): void
    {
        foreach ($items->items as $item) {
            $this->lines->push(new OrderLine([
                'product_id' => $item->product->id,
                'product_price' => $item->product->price,
                'quantity' => $item->quantity,
            ]));
        }

        $this->total = $this->lines->sum(fn (OrderLine $line) => $line->product_price * $line->quantity);
    }

    /** @throws OrderMissingOrderLinesException */
    public function fulfill(): void
    {
        if ($this->lines->isEmpty()) {
            throw new OrderMissingOrderLinesException;
        }

        $this->status = self::COMPLETED;

        $this->save();
        $this->lines()->saveMany($this->lines);
    }
}
