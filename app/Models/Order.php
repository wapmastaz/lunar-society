<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $tier_id
 * @property int|null $product_id
 * @property OrderStatus $status
 * @property int $amount_cents
 * @property string $email
 * @property string|null $buyer_name
 * @property string|null $plaque_name
 * @property bool $couple_addon
 * @property string|null $listener_handle
 * @property bool $show_full_name
 * @property string|null $consent_text
 * @property Carbon|null $consented_at
 * @property string|null $payer_first_name
 * @property string|null $payer_last_name
 * @property array<string, mixed>|null $shipping
 * @property int|null $slot_number
 * @property Carbon|null $hold_expires_at
 * @property string|null $paypal_order_id
 * @property string|null $paypal_capture_id
 * @property Carbon|null $paid_at
 * @property Carbon|null $locks_at
 * @property Carbon|null $refunded_at
 * @property-read Tier|null $tier
 * @property-read Product|null $product
 */
class Order extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'couple_addon' => 'boolean',
            'marketing_opt_in' => 'boolean',
            'show_full_name' => 'boolean',
            'shipping' => 'array',
            'consented_at' => 'datetime',
            'hold_expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'locks_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return BelongsTo<Tier, $this>
     */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @param  Builder<Order>  $query
     */
    public function scopePaid(Builder $query): void
    {
        $query->where('status', OrderStatus::Paid);
    }

    public function isFounder(): bool
    {
        return $this->tier_id !== null;
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    public function isLocked(): bool
    {
        return $this->locks_at !== null && $this->locks_at->isPast();
    }

    public function holdIsActive(): bool
    {
        return $this->status === OrderStatus::Pending
            && $this->hold_expires_at !== null
            && $this->hold_expires_at->isFuture();
    }

    /**
     * First name for greetings: PayPal's payer name, else the form's full name.
     */
    public function greetingName(): ?string
    {
        return $this->payer_first_name ?: (Str::of((string) $this->buyer_name)->before(' ')->trim()->value() ?: null);
    }

    public function itemLabel(): string
    {
        return $this->tier?->name ?? $this->product?->name ?? 'Order';
    }

    public function slotLabel(): ?string
    {
        return $this->slot_number ? sprintf('#%03d', $this->slot_number) : null;
    }

    public function amountLabel(): string
    {
        return '$'.number_format($this->amount_cents / 100, 2);
    }

    /**
     * Name as shown on the public Founders list.
     */
    public function publicName(): string
    {
        if ($this->show_full_name) {
            return (string) $this->plaque_name;
        }

        return self::maskName((string) $this->plaque_name);
    }

    /**
     * "John Doe" -> "John D.", "Mr & Mrs John Doe" -> "Mr & Mrs John D.".
     */
    public static function maskName(string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];

        if (count($words) < 2) {
            return $name;
        }

        $last = array_pop($words);

        return implode(' ', $words).' '.Str::upper(Str::substr($last, 0, 1)).'.';
    }
}
