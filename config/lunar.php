<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business details
    |--------------------------------------------------------------------------
    |
    | Values marked CONFIRM are still waiting on the client.
    |
    */

    'brand' => 'The Lunar Society Network',
    'podcast' => 'Adventures With Mr SuperTrump',
    'entity' => env('LUNAR_ENTITY', 'Mr SuperTrump'), // CONFIRM exact legal name on PayPal
    'domain' => env('LUNAR_DOMAIN', 'MrSuperTrump.com'), // CONFIRM
    'city' => env('LUNAR_CITY', 'Boca Raton, FL 33445'), // CONFIRM (welcome email said Delray Beach)
    'email' => env('LUNAR_EMAIL', 'MrSuperTrump77@gmail.com'),
    'phone' => env('LUNAR_PHONE'),
    'host_portrait' => env('LUNAR_HOST_PORTRAIT') ?: 'images/mj-schultz.webp', // path under public/
    'set_aside_recipient' => env('LUNAR_SET_ASIDE_RECIPIENT'), // CONFIRM; FAQ entry hidden until set

    /*
    |--------------------------------------------------------------------------
    | Registry rules
    |--------------------------------------------------------------------------
    */

    'capacity' => 1000,
    'plaque_size' => '12" x 18"',
    'plaque_material' => 'aerospace-grade 6061-T6 aluminum',
    'target_date' => 'January 18, 2029', // MJ Schultz's stated goal, always shown as a target
    'target_date_short' => '2029',
    'queue_via_scheduler' => (bool) env('QUEUE_VIA_SCHEDULER', false),
    'hold_minutes' => 15,
    'name_lock_hours' => 48,
    'name_max_length' => 30,
    'couple_addon_cents' => 3000,
    'processing_fee_cents' => (int) env('LUNAR_PROCESSING_FEE_CENTS', 1500), // flat: $5 Fallen Astronauts Education Fund + $10 S&H

    /*
    |--------------------------------------------------------------------------
    | Physical gift
    |--------------------------------------------------------------------------
    |
    | When the pebble gift box is included, PayPal collects a shipping address
    | and the shipping cutoffs are shown. CONFIRM with the client.
    |
    */

    'pebble_included' => (bool) env('LUNAR_PEBBLE_INCLUDED', false),

    // The physical Founder gift package, mailed to the address on the registration form.
    'founder_package' => [
        'A personalized Lunar Society Network certificate',
        'A Lunar Society Network presentation folder',
        'A vial of lunar meteorite dust with its certificate of authenticity',
        'Confirmation of your name\'s inclusion on the Founders Plaque',
        'Other special Lunar Society Network collectibles',
    ],

    // The gift package ships in a 12" x 14" mailing box.
    'package_box_size' => '12" x 14"',

    'delivery_by' => 'January 31, 2027',

    'delivery_notice' => 'Physical gift packages are expected to be delivered by January 31, 2027. Please consider this delivery timeframe when purchasing a membership as a Christmas gift.',

    // Ways to support besides a Founder slot (dollars; 0 hides one). Run
    // `php artisan db:seed --class=ProductSeeder --force` after changing these.
    'offerings' => [
        'booster' => env('LUNAR_BOOSTER_PRICE', 25),
        'follower' => env('LUNAR_FOLLOWER_PRICE', 49),
        'moon-dust-supporter' => env('LUNAR_MOON_DUST_SUPPORTER_PRICE', 79),
        'trade-associate' => env('LUNAR_TRADE_ASSOCIATE_PRICE', 99), // paid through Barter Sales
    ],

    // The Follower souvenir (the Space Pebble Collectible). Path under public/.
    'souvenir_image' => env('LUNAR_PEBBLE_IMAGE'),

    // The Booster list goes up on the site from this date.
    'booster_list_from' => '2026-12-20',

    // What the flat $15 fee pays for.
    'fee_note' => '$5 for the Fallen Astronauts Education Fund and $10 for shipping and handling',

    'shipping_cutoffs' => [
        'standard' => 'December 12',
        'expedited' => 'December 18',
    ],

    /*
    |--------------------------------------------------------------------------
    | Charter (shared by the homepage opening and the /charter page)
    |--------------------------------------------------------------------------
    */

    'charter' => [
        'adopted' => 'Adopted on this 13th day of November 2026, under the light of the full moon.',
        'place' => 'Delray Beach, Florida',
        'signatory' => 'Mitchell J. Schultz',
        'preamble' => 'We, the founding members of The Lunar Society Network, unite in the spirit of free inquiry, mutual curiosity, and cosmic stewardship. Inspired by the fellowship of philosophers, makers, and thinkers who once gathered under the light of the full moon, we establish this Society to bridge the gap between theoretical knowledge and real-world application, extending our vigilance from the Earth beneath our feet to the stars above our heads.',
        'mission' => 'The purpose of the Society is to foster interdisciplinary dialogue, collaborative experimentation, and the advancement of science, technology, commerce, and the humanities for the betterment of humankind and the cosmos.',
        'peace' => 'The Society is dedicated to the active fostering of Intergalactic Peace in Space. We stand firmly for the prevention and avoidance of space wars.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Required legal text (verbatim from the client)
    |--------------------------------------------------------------------------
    */

    'disclaimer' => 'The Lunar Society Network is an independent listener engagement project produced by the Adventures With Mr SuperTrump podcast and is not affiliated with, sponsored by, or endorsed by Donald J. Trump, the Trump Organization, NASA, or any government agency.',

    'satire_notice' => "The Lunar Society Network is a creative extension of an independent comedy broadcast. Our goal is to send the Founders Plaque to the Moon on a commercial payload. If that can't happen, it will be permanently displayed in the podcast studio.",

    'citizenship' => 'I certify that I am a United States citizen.',

    'acknowledgement' => 'The above applicant hereby applies to be a founding member of The Lunar Society Network and, by doing so, acknowledges having read the Society\'s Charter. Further, the applicant agrees to abide by these terms and conditions and now wishes to become a founding member of The Lunar Society Network.',

    'marketing_consent' => 'I give permission to receive additional emails and communications from The Lunar Society Network.',

    'consent_spelling' => 'I certify that the spelling of the name is correct. I understand the name locks 48 hours after my confirmation email and cannot be changed after that, because the plaque is laser engraved.',

    'consent_terms' => 'I have read and agree to the Terms of Service. I understand and agree that this is an independent project unaffiliated with Donald J. Trump or any government agency, as detailed in the Disclaimer.',

];
