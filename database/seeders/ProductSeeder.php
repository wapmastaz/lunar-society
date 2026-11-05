<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * The Space Pebble Collectible, sold separately from founder slots.
     * It stays hidden until a price is set (LUNAR_PEBBLE_PRICE in .env).
     */
    public function run(): void
    {
        $price = config('lunar.pebble.price');

        Product::updateOrCreate(['slug' => 'space-pebble'], [
            'name' => 'Space Pebble Collectible',
            'tagline' => 'Own a piece of space history',
            'description' => 'Celebrate the excitement of lunar exploration with our special Space Pebble Collectible. Created as a memorable keepsake for supporters of the Lunar Society, it offers another way to be part of our journey toward the future of lunar exploration. Whether you are joining as a founding member or looking for a unique space-inspired gift, it is a special addition to the Lunar Society experience.',
            'highlights' => [
                'One Space Pebble Collectible',
                'A special keepsake from the Lunar Society project',
                'An opportunity to celebrate and support our lunar exploration vision',
            ],
            'price_cents' => $price ? (int) round($price * 100) : 0,
            'image_path' => config('lunar.pebble.image'),
            'active' => (bool) $price,
        ]);
    }
}
