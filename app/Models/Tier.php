<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $price_cents
 * @property int $slot_start
 * @property int $slot_end
 */
class Tier extends Model
{
    protected $guarded = [];

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function capacity(): int
    {
        return $this->slot_end - $this->slot_start + 1;
    }

    /**
     * Orders that occupy a slot: paid, or pending with an unexpired hold.
     *
     * @return Builder<Order>
     */
    public function occupyingOrders(?int $exceptOrderId = null): Builder
    {
        return Order::query()
            ->where('tier_id', $this->id)
            ->when($exceptOrderId, fn (Builder $q) => $q->whereKeyNot($exceptOrderId))
            ->where(function (Builder $q) {
                $q->where('status', OrderStatus::Paid)
                    ->orWhere(fn (Builder $q) => $q
                        ->where('status', OrderStatus::Pending)
                        ->where('hold_expires_at', '>', now()));
            });
    }

    public function claimedCount(): int
    {
        return $this->orders()->where('status', OrderStatus::Paid)->count();
    }

    public function remaining(): int
    {
        return max(0, $this->capacity() - $this->occupyingOrders()->count());
    }

    public function isSoldOut(): bool
    {
        return $this->remaining() === 0;
    }

    public function priceLabel(): string
    {
        return '$'.number_format($this->price_cents / 100);
    }

    public function rangeLabel(): string
    {
        return sprintf('#%03d to #%03d', $this->slot_start, $this->slot_end);
    }
}
