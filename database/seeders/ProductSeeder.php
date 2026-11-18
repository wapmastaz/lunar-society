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

        // The November line-up, retired for the Booster, Follower, Supporter and Trade Associate
        // offerings. Kept (inactive) so past orders still show what was bought.
        Product::whereIn('slug', ['space-pebble', 'supporter', 'generous-supporter', 'affiliate-membership', 'moon-dust-sample'])->update(['active' => false]);

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
            'follower' => [
                'name' => 'Follower',
                'tagline' => 'A souvenir and a place on our Followers list',
                'description' => 'Receive a souvenir, the Space Pebble Collectible, and get listed on our Followers list. The pebble is a commemorative keepsake, not lunar material. It does not include a Founder slot.',
                'highlights' => ['The Space Pebble Collectible souvenir, shipped to you or as a gift', 'Your name on our Followers list'],
                'price' => $prices['follower'],
                'image_path' => config('lunar.souvenir_image'),
                'ships' => true,
                'listing' => 'followers',
                'sort' => 2,
            ],
            'moon-dust-supporter' => [
                'name' => 'Supporter',
                'tagline' => 'Lunar Moon Dust for your space collection',
                'description' => 'Receive a specimen of Lunar Moon Dust (lunar meteorite dust) for your space collection, and be listed as a Supporter on our Supporters list. It does not include a Founder slot.',
                'highlights' => ['A specimen of Lunar Moon Dust for your space collection', 'Your name on our Supporters list'],
                'price' => $prices['moon-dust-supporter'],
                'ships' => true,
                'listing' => 'supporters',
                'sort' => 3,
            ],
            'trade-associate' => [
                'name' => 'Trade Associate',
                'tagline' => 'For our Trade associates, through Barter Sales',
                'description' => 'For our Trade associates. The package price is paid as a Barter payment through Barter Sales, outside PayPal, so only the fee is paid here. You receive the same vial gift that comes in the Founder package. It does not include a Founder slot.',
                'highlights' => ['A vial of lunar meteorite dust', 'Its certificate of authenticity', 'The same vial gift as the Founder package'],
                'price' => $prices['trade-associate'],
                'ships' => true,
                'barter' => true,
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
