<?php

namespace Tests\Concerns;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Tier;
use Database\Seeders\TierSeeder;
use Illuminate\Support\Facades\Http;

trait InteractsWithRegistry
{
    protected function setUpRegistry(): void
    {
        $this->seed(TierSeeder::class);
    }

    protected function tier(string $code): Tier
    {
        return Tier::where('code', $code)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function paidOrder(string $tierCode = 'standard', array $attributes = []): Order
    {
        $tier = $this->tier($tierCode);
        $slot = $attributes['slot_number'] ?? (Order::where('tier_id', $tier->id)->max('slot_number') ?? $tier->slot_start - 1) + 1;

        return Order::create(array_merge([
            'tier_id' => $tier->id,
            'status' => OrderStatus::Paid,
            'amount_cents' => $tier->price_cents,
            'email' => "founder{$slot}@example.test",
            'plaque_name' => "Founder Number{$slot}",
            'slot_number' => $slot,
            'paid_at' => now(),
            'locks_at' => now()->addHours(48),
        ], $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    protected function captureResponse(string $value, string $status = 'COMPLETED', string $orderId = 'PP-ORDER-1'): array
    {
        return [
            'id' => $orderId,
            'status' => $status,
            'payer' => ['name' => ['given_name' => 'Jane', 'surname' => 'Patriot']],
            'purchase_units' => [[
                'shipping' => [
                    'name' => ['full_name' => 'Jane Patriot'],
                    'address' => ['address_line_1' => '1 Main St', 'admin_area_2' => 'Boca Raton', 'admin_area_1' => 'FL', 'postal_code' => '33445', 'country_code' => 'US'],
                ],
                'payments' => ['captures' => [[
                    'id' => 'CAP-1',
                    'status' => $status,
                    'amount' => ['currency_code' => 'USD', 'value' => $value],
                ]]],
            ]],
        ];
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function fakePayPal(string $captureValue = '164.00', array $extra = []): void
    {
        Http::fake(array_merge([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'test-token']),
            '*/v2/checkout/orders' => Http::response(['id' => 'PP-ORDER-1', 'status' => 'CREATED']),
            '*/v2/checkout/orders/PP-ORDER-1/capture' => Http::response($this->captureResponse($captureValue)),
            '*/v2/checkout/orders/PP-ORDER-1' => Http::response($this->captureResponse($captureValue)),
        ], $extra));
    }
}
