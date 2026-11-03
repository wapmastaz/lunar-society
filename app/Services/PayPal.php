<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin client for PayPal Orders v2 and webhook verification.
 */
class PayPal
{
    public function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function accessToken(): string
    {
        return Cache::remember('paypal.token.'.config('services.paypal.mode'), now()->addMinutes(30), function () {
            $response = Http::asForm()
                ->withBasicAuth((string) config('services.paypal.client_id'), (string) config('services.paypal.secret'))
                ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials'])
                ->throw();

            return (string) $response->json('access_token');
        });
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withToken($this->accessToken())
            ->acceptJson()
            ->timeout(20);
    }

    /**
     * Create a PayPal order for our pending order and return its id.
     */
    public function createOrder(Order $order): string
    {
        $value = number_format($order->amount_cents / 100, 2, '.', '');
        $needsShipping = ! $order->isFounder() || config('lunar.pebble_included');

        $description = $order->isFounder()
            ? "{$order->tier->name} slot".($order->couple_addon ? ' + couple/family name' : '')
            : $order->product->name;

        $response = $this->client()
            ->withHeaders(['PayPal-Request-Id' => 'create-'.$order->uuid])
            ->post('/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $order->uuid,
                    'custom_id' => $order->uuid,
                    'description' => mb_substr(config('lunar.brand').': '.$description, 0, 127),
                    'amount' => [
                        'currency_code' => config('services.paypal.currency'),
                        'value' => $value,
                    ],
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'brand_name' => mb_substr(config('lunar.brand'), 0, 127),
                            'shipping_preference' => $needsShipping ? 'GET_FROM_FILE' : 'NO_SHIPPING',
                            'user_action' => 'PAY_NOW',
                        ],
                    ],
                ],
            ])
            ->throw();

        $id = (string) $response->json('id');

        if ($id === '') {
            throw new RuntimeException('PayPal did not return an order id.');
        }

        return $id;
    }

    /**
     * Capture an approved PayPal order. Returns the decoded response.
     *
     * @return array<string, mixed>
     */
    public function captureOrder(string $paypalOrderId): array
    {
        return $this->client()
            ->withHeaders(['PayPal-Request-Id' => 'capture-'.$paypalOrderId])
            ->withBody('{}', 'application/json')
            ->post("/v2/checkout/orders/{$paypalOrderId}/capture")
            ->throw()
            ->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function showOrder(string $paypalOrderId): array
    {
        return $this->client()->get("/v2/checkout/orders/{$paypalOrderId}")->throw()->json();
    }

    /**
     * Ask PayPal to verify a webhook delivery's signature.
     *
     * @param  array<string, mixed>  $event
     */
    public function verifyWebhook(array $headers, array $event): bool
    {
        $webhookId = config('services.paypal.webhook_id');

        if (! $webhookId) {
            return false;
        }

        $header = fn (string $name) => $headers[strtolower($name)][0] ?? null;

        $response = $this->client()->post('/v1/notifications/verify-webhook-signature', [
            'auth_algo' => $header('paypal-auth-algo'),
            'cert_url' => $header('paypal-cert-url'),
            'transmission_id' => $header('paypal-transmission-id'),
            'transmission_sig' => $header('paypal-transmission-sig'),
            'transmission_time' => $header('paypal-transmission-time'),
            'webhook_id' => $webhookId,
            'webhook_event' => $event,
        ]);

        return $response->successful() && $response->json('verification_status') === 'SUCCESS';
    }
}
