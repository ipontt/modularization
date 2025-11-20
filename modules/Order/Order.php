<?php

namespace Modules\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Number;
use Modules\Order\Infrastructure\Database\Factories\OrderFactory;
use Modules\Payment\Payment;
use Modules\Product\Collections\CartItemCollection;

/**
 * @property int $user_id
 * @property string $status
 * @property int $total
 */
#[UseFactory(OrderFactory::class)]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    const string PENDING = 'pending';

    const string COMPLETED = 'completed';

    const string PAYMENT_FAILED = 'payment_failed';

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

    public function localizedTotal(): string
    {
        return Number::currency(number: $this->total / 100, in: 'USD', locale: 'en-US') ?: (string) $this->total;
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
    public function start(): void
    {
        if ($this->lines->isEmpty()) {
            throw new OrderMissingOrderLinesException;
        }

        $this->status = self::PENDING;

        $this->save();
        $this->lines()->saveMany($this->lines);
    }

    public function markAsFailed(): void
    {
        if ($this->isCompleted()) {
            throw new \RuntimeException('A completed order cannot be marked as failed.');
        }

        $this->status = self::PAYMENT_FAILED;

        $this->save();
    }

    public function isCompleted(): bool
    {
        return $this->status === self::COMPLETED;
    }

    public function complete(): void
    {
        $this->status = self::COMPLETED;

        $this->save();
    }
}
