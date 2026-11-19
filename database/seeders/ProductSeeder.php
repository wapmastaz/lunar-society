<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Ways to support besides a Founder slot. A product with a price of 0 stays hidden.
     */
    public function run(): void
    {
        $prices = config('lunar.offerings');
        $listedFrom = Carbon::parse(config('lunar.booster_list_from'))->format('F j');

        // Earlier line-ups, retired. Kept (inactive) so past orders still show what was bought.
        Product::whereIn('slug', ['space-pebble', 'generous-supporter', 'affiliate-membership', 'follower', 'moon-dust-supporter', 'trade-associate'])->update(['active' => false]);

        $products = [
            'booster' => [
                'name' => 'Booster',
                'tagline' => 'Your name on our Booster list',
                'description' => "Get your name on our Booster list here on the site, published after {$listedFrom}. Boosters receive a listing only: nothing is shipped, and it does not include a Founder slot.",
                'highlights' => ["Your name on the Booster list on our site, published after {$listedFrom}", 'Our heartfelt thanks'],
                'price' => $prices['booster'],
                'ships' => false,
                'listing' => 'boosters',
                'sort' => 1,
            ],
            'supporter' => [
                'name' => 'Supporter',
                'tagline' => 'Your name on our Supporters list',
                'description' => 'Support the Lunar Society Network and get your name on our Supporters list. Nothing is shipped, and it does not include a Founder slot.',
                'highlights' => ['Your name on our Supporters list', 'Our heartfelt thanks'],
                'price' => $prices['supporter'],
                'ships' => false,
                'listing' => 'supporters',
                'sort' => 2,
            ],
            'member' => [
                'name' => 'New Lunar Society Member',
                'tagline' => 'Welcome to the Society',
                'description' => 'Become a New Lunar Society Member. You receive a special welcome e-card by email with some special offers, and your name on our New Members list. No specimen or package is shipped, and it does not include a Founder slot.',
                'highlights' => ['A special welcome e-card by email, with special offers', 'Your name on our New Members list'],
                'price' => $prices['member'],
                'ships' => false,
                'listing' => 'members',
                'sort' => 3,
            ],
            'moon-dust-sample' => [
                'name' => 'Moon Dust Specimen',
                'tagline' => 'The Founder specimen, without a name on the plaque',
                'description' => 'Receive the same vial of lunar meteorite dust and certificate of authenticity that comes in the Founder gift package, shipped to you. Your name is not on the Founders Plaque.',
                'highlights' => ['A vial of lunar meteorite dust', 'Its certificate of authenticity', 'Shipped to you or as a gift'],
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
                'listing' => $p['listing'] ?? null,
                'barter' => $p['barter'] ?? false,
                'sort' => $p['sort'],
                'active' => $price > 0,
            ]);
        }
    }
}
