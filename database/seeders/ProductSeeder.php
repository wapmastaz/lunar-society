<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * The Affiliate Membership Package: affiliate membership plus the Space Pebble
     * Collectible, sold separately from Founder slots. Hidden while the price is 0.
     */
    public function run(): void
    {
        $slug = config('lunar.affiliate.slug');
        $price = (float) config('lunar.affiliate.price');

        // It replaces the earlier stand-alone Space Pebble product.
        Product::where('slug', 'space-pebble')->whereNot('slug', $slug)->update(['slug' => $slug]);

        Product::updateOrCreate(['slug' => $slug], [
            'name' => 'Affiliate Membership Package',
            'tagline' => 'Join the Society without a Founder slot',
            'description' => 'Become an affiliate member of the Lunar Society and take home the Space Pebble Collectible, a commemorative keepsake of our journey toward the Moon. Affiliate membership is for supporters who want to be part of the Society and its updates. It does not include a Founder slot or a name on the Founders Plaque.',
            'highlights' => [
                'Affiliate membership in the Lunar Society',
                'The Space Pebble Collectible, shipped to you or as a gift',
                'A welcome package by email with the Society\'s history and guiding principles',
                'The Lunar Society newsletter with milestone updates',
            ],
            'price_cents' => (int) round($price * 100),
            'image_path' => config('lunar.affiliate.image'),
            'active' => $price > 0,
        ]);
    }
}
