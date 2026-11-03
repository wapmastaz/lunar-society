<?php

namespace Database\Seeders;

use App\Models\Tier;
use Illuminate\Database\Seeder;

class TierSeeder extends Seeder
{
    public function run(): void
    {
        Tier::updateOrCreate(['code' => 'alpha'], [
            'name' => 'Alpha Founder',
            'price_cents' => 19900,
            'slot_start' => 1,
            'slot_end' => 50,
        ]);

        Tier::updateOrCreate(['code' => 'standard'], [
            'name' => 'Standard Founder',
            'price_cents' => 14900,
            'slot_start' => 51,
            'slot_end' => 1000,
        ]);
    }
}
