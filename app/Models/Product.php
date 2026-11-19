<?php

namespace App\Models;

use App\Services\Registry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $tagline
 * @property array<int, string>|null $highlights
 * @property string|null $description
 * @property int $price_cents
 * @property string|null $image_path
 * @property bool $active
 * @property bool $ships
 * @property string|null $listing
 * @property bool $barter
 */
class Product extends Model
{
    /** Public lists a product can put its buyer on. */
    public const LISTS = [
        'boosters' => 'Booster list',
        'followers' => 'Followers list', // retired offering, kept for past orders
        'supporters' => 'Supporters list',
        'members' => 'New Members list',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'ships' => 'boolean', 'barter' => 'boolean', 'highlights' => 'array'];
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function priceLabel(): string
    {
        return '$'.number_format($this->price_cents / 100);
    }

    /**
     * Price line with the fee: "$49 + $15", or "$99 via Barter Sales + $15 by PayPal".
     */
    public function priceWithFeeLabel(): string
    {
        $fee = Registry::feeFor($this);
        $fee = $fee > 0 ? ' + '.Registry::processingFeeLabel() : '';

        return $this->barter
            ? $this->priceLabel().' via Barter Sales'.$fee.' by PayPal'
            : $this->priceLabel().$fee;
    }

    /**
     * Title of the public list this product puts the buyer on, if any.
     */
    public function listTitle(): ?string
    {
        return $this->listing ? self::LISTS[$this->listing] ?? null : null;
    }
}
