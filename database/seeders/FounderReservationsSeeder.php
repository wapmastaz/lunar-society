<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Tier;
use Illuminate\Database\Seeder;

/**
 * Positions #001 to #005 belong to MJ Schultz (aka Mr SuperTrump), at a 50%
 * discount, paid outside PayPal. Their names head the plaque's five columns in script.
 * Safe to run again: a slot that is already taken is left alone.
 */
class FounderReservationsSeeder extends Seeder
{
    public const NAMES = [
        1 => 'Mitchell J Schultz',
        2 => 'Carla M Mattia-Schultz',
        3 => 'Daniel L Carson',
        4 => 'Mr & Mrs Scott M Schultz',
        5 => 'Leda Moscarella',
    ];

    public function run(): void
    {
        $alpha = Tier::where('code', 'alpha')->firstOrFail();

        foreach (self::NAMES as $slot => $name) {
            if (Order::where('slot_number', $slot)->exists()) {
                continue;
            }

            $couple = str_contains($name, '&');
            $price = $alpha->price_cents + ($couple ? (int) config('lunar.couple_addon_cents') : 0);

            Order::create([
                'tier_id' => $alpha->id,
                'status' => OrderStatus::Paid,
                'slot_number' => $slot,
                'amount_cents' => intdiv($price, 2),
                'processing_fee_cents' => 0,
                'email' => config('lunar.email'),
                'buyer_name' => 'MJ Schultz',
                'plaque_name' => $name,
                'couple_addon' => $couple,
                'show_full_name' => true,
                'consent_text' => 'Reserved for MJ Schultz (positions #001 to #005) at a 50% discount, paid outside PayPal.',
                'consented_at' => now(),
                'paid_at' => now(),
                'locks_at' => now()->addHours((int) config('lunar.name_lock_hours')),
            ]);
        }
    }
}
