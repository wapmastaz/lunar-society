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

    'brand' => "Mr SuperTrump's Lunar Society",
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
    'target_date' => 'January 18, 2029', // MJ Schultz's stated goal, always shown as a target
    'target_date_short' => '2029',
    'queue_via_scheduler' => (bool) env('QUEUE_VIA_SCHEDULER', false),
    'hold_minutes' => 15,
    'name_lock_hours' => 48,
    'name_max_length' => 30,
    'couple_addon_cents' => 3000,

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

    // The Space Pebble Collectible sold on its own. Hidden until a price is set;
    // run `php artisan db:seed --class=ProductSeeder` after changing these.
    'pebble' => [
        'price' => env('LUNAR_PEBBLE_PRICE'), // dollars, e.g. 49
        'image' => env('LUNAR_PEBBLE_IMAGE'), // path under public/, e.g. images/space-pebble.jpg
    ],

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
        'preamble' => 'We, the founding members of The Lunar Society, unite in the spirit of free inquiry, mutual curiosity, and cosmic stewardship. Inspired by the fellowship of philosophers, makers, and thinkers who once gathered under the light of the full moon, we establish this Society to bridge the gap between theoretical knowledge and real-world application, extending our vigilance from the Earth beneath our feet to the stars above our heads.',
        'mission' => 'The purpose of the Society is to foster interdisciplinary dialogue, collaborative experimentation, and the advancement of science, technology, commerce, and the humanities for the betterment of humankind and the cosmos.',
        'peace' => 'The Society is dedicated to the active fostering of Intergalactic Peace in Space. We stand firmly for the prevention and avoidance of space wars.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Required legal text (verbatim from the client)
    |--------------------------------------------------------------------------
    */

    'disclaimer' => "Mr SuperTrump's Lunar Society is an independent listener engagement project produced by the Adventures With Mr SuperTrump podcast and is not affiliated with, sponsored by, or endorsed by Donald J. Trump, the Trump Organization, NASA, or any government agency.",

    'satire_notice' => "The Lunar Society is a creative extension of an independent comedy broadcast. Our goal is to send the Founders Plaque to the Moon on a commercial payload. If that can't happen, it will be permanently displayed in the podcast studio.",

    'consent_spelling' => 'I certify that the spelling of the name is correct. I understand the name locks 48 hours after my confirmation email and cannot be changed after that, because the plaque is laser engraved.',

    'consent_terms' => 'I have read and agree to the Terms of Service. I understand and agree that this is an independent project unaffiliated with Donald J. Trump or any government agency, as detailed in the Disclaimer.',

];
