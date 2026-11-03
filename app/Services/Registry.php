<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\SoldOutException;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tier;
use Illuminate\Support\Facades\DB;

/**
 * Founder slot accounting. A tier's capacity is shared between paid orders
 * and pending orders holding an unexpired reservation, so a slot can never
 * be sold twice and every captured payment is guaranteed a number.
 */
class Registry
{
    /**
     * Names that need the couple/family add-on.
     */
    public static function requiresCoupleAddon(string $name): bool
    {
        return (bool) preg_match('/(&|\band\b|\bfamily\b)/i', $name);
    }

    public function priceFor(Tier $tier, bool $coupleAddon): int
    {
        return $tier->price_cents + ($coupleAddon ? (int) config('lunar.couple_addon_cents') : 0);
    }

    /**
     * Create a pending order that holds one slot in the tier.
     *
     * @param  array{email: string, plaque_name: string, listener_handle?: string|null, show_full_name?: bool, ip_address?: string|null}  $data
     *
     * @throws SoldOutException
     */
    public function reserve(Tier $tier, array $data): Order
    {
        return DB::transaction(function () use ($tier, $data) {
            $tier = Tier::query()->lockForUpdate()->findOrFail($tier->id);

            if ($tier->occupyingOrders()->count() >= $tier->capacity()) {
                throw new SoldOutException("{$tier->name} slots are sold out.");
            }

            $couple = self::requiresCoupleAddon($data['plaque_name']);

            return Order::create([
                'tier_id' => $tier->id,
                'status' => OrderStatus::Pending,
                'amount_cents' => $this->priceFor($tier, $couple),
                'email' => $data['email'],
                'plaque_name' => $data['plaque_name'],
                'couple_addon' => $couple,
                'listener_handle' => $data['listener_handle'] ?? null,
                'show_full_name' => $data['show_full_name'] ?? false,
                'consent_text' => config('lunar.consent_spelling')."\n".config('lunar.consent_terms'),
                'consented_at' => now(),
                'hold_expires_at' => now()->addMinutes((int) config('lunar.hold_minutes')),
                'ip_address' => $data['ip_address'] ?? null,
            ]);
        });
    }

    /**
     * Create a pending souvenir order (no slot, no capacity limit).
     *
     * @param  array{email: string, ip_address?: string|null}  $data
     */
    public function reserveSouvenir(Product $product, array $data): Order
    {
        return Order::create([
            'product_id' => $product->id,
            'status' => OrderStatus::Pending,
            'amount_cents' => $product->price_cents,
            'email' => $data['email'],
            'consent_text' => config('lunar.consent_terms'),
            'consented_at' => now(),
            'hold_expires_at' => now()->addMinutes((int) config('lunar.hold_minutes')),
            'ip_address' => $data['ip_address'] ?? null,
        ]);
    }

    /**
     * Make sure a pending order still owns a slot right before capturing
     * payment. Extends an expired hold if capacity allows.
     */
    public function confirmHold(Order $order): bool
    {
        if (! $order->isFounder()) {
            return $order->status === OrderStatus::Pending;
        }

        return DB::transaction(function () use ($order) {
            $tier = Tier::query()->lockForUpdate()->findOrFail($order->tier_id);
            $order->refresh();

            if ($order->status !== OrderStatus::Pending && $order->status !== OrderStatus::Expired) {
                return false;
            }

            if (! $order->holdIsActive() && $tier->occupyingOrders($order->id)->count() >= $tier->capacity()) {
                $order->update(['status' => OrderStatus::Expired]);

                return false;
            }

            $order->update([
                'status' => OrderStatus::Pending,
                'hold_expires_at' => now()->addMinutes((int) config('lunar.hold_minutes')),
            ]);

            return true;
        });
    }

    /**
     * Give a paid founder order the lowest free number in its tier.
     */
    public function assignSlot(Order $order): int
    {
        return DB::transaction(function () use ($order) {
            $tier = Tier::query()->lockForUpdate()->findOrFail($order->tier_id);

            $taken = Order::query()
                ->where('tier_id', $tier->id)
                ->whereNotNull('slot_number')
                ->pluck('slot_number')
                ->flip();

            for ($n = $tier->slot_start; $n <= $tier->slot_end; $n++) {
                if (! isset($taken[$n])) {
                    $order->update(['slot_number' => $n]);

                    return $n;
                }
            }

            throw new SoldOutException("No free number left in {$tier->name}.");
        });
    }

    /**
     * Mark expired holds so their slots return to the pool.
     */
    public function expireHolds(): int
    {
        return Order::query()
            ->where('status', OrderStatus::Pending)
            ->where('hold_expires_at', '<=', now())
            ->update(['status' => OrderStatus::Expired]);
    }

    /**
     * Total paid founder slots across all tiers.
     */
    public function claimedTotal(): int
    {
        return Order::query()->paid()->whereNotNull('tier_id')->count();
    }
}
