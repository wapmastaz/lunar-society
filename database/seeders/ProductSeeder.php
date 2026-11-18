<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Appreciative Contributions, sold separately from Founder memberships.
     * A product with a price of 0 stays hidden.
     */
    public function run(): void
    {
        $affiliate = config('lunar.affiliate.slug');
        $prices = config('lunar.contributions');

        // The affiliate package replaced the earlier stand-alone Space Pebble product.
        Product::where('slug', 'space-pebble')->whereNot('slug', $affiliate)->update(['slug' => $affiliate]);

        $products = [
            'supporter' => [
                'name' => 'Supporter',
                'tagline' => 'Appreciative Contribution',
                'description' => 'A thank-you contribution that helps the Lunar Society Network grow toward the Moon. Nothing is shipped, and it does not include a Founder slot.',
                'highlights' => ['Our heartfelt thanks', 'Your support of the Lunar Society Network mission'],
                'price' => $prices['supporter'],
                'ships' => false,
                'sort' => 1,
            ],
            'generous-supporter' => [
                'name' => 'Generous Supporter',
                'tagline' => 'Appreciative Contribution',
                'description' => 'A generous thank-you contribution that helps the Lunar Society Network grow toward the Moon. Nothing is shipped, and it does not include a Founder slot.',
                'highlights' => ['Our heartfelt thanks', 'Your generous support of the Lunar Society Network mission'],
                'price' => $prices['generous-supporter'],
                'ships' => false,
                'sort' => 2,
            ],
            $affiliate => [
                'name' => 'Affiliate Membership Package',
                'tagline' => 'Join the Society without a Founder slot',
                'description' => 'Become an affiliate member of the Lunar Society Network and take home the Space Pebble Collectible, a commemorative keepsake of our journey toward the Moon. Affiliate membership is for supporters who want to be part of the Society and its updates. It does not include a Founder slot or a name on the Founders Plaque.',
                'highlights' => [
                    'Affiliate membership in the Lunar Society Network',
                    'The Space Pebble Collectible, shipped to you or as a gift',
                    "A welcome package by email with the Society's history and guiding principles",
                    'The Lunar Society Network newsletter with milestone updates',
                ],
                'price' => config('lunar.affiliate.price'),
                'image_path' => config('lunar.affiliate.image'),
                'ships' => true,
                'sort' => 3,
            ],
            'moon-dust-sample' => [
                'name' => 'Moon Dust Sample',
                'tagline' => 'The Founder vial gift, on its own',
                'description' => 'The same vial of lunar meteorite dust and certificate of authenticity that comes in the Founder gift package, sent on its own. It does not include a Founder slot or a name on the Founders Plaque.',
                'highlights' => [
                    'A vial of lunar meteorite dust',
                    'Its certificate of authenticity',
                    'Shipped to you or as a gift',
                ],
                'price' => $prices['moon-dust-sample'],
                'ships' => true,
                'sort' => 4,
            ],
        ];

        foreach ($products as $slug => $p) {
            $price = (float) $p['price'];

            Product::updateOrCreate(['slug' => $slug], [
                'name' => $p['name'],
                'tagline' => $p['tagline'],
                'description' => $p['description'],
                'highlights' => $p['highlights'],
                'price_cents' => (int) round($price * 100),
                'image_path' => $p['image_path'] ?? null,
                'ships' => $p['ships'],
                'sort' => $p['sort'],
                'active' => $price > 0,
            ]);
        }
    }
}
