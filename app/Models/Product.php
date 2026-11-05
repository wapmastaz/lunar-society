<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
 */
class Product extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'highlights' => 'array'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function priceLabel(): string
    {
        return '$'.number_format($this->price_cents / 100);
    }
}
