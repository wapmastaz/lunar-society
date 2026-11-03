<?php

namespace Tests\Feature\Lunar;

use App\Enums\OrderStatus;
use App\Mail\OrderConfirmation;
use App\Services\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InteractsWithRegistry;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use InteractsWithRegistry, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRegistry();
        Mail::fake();
        config(['services.paypal.webhook_id' => 'WH-TEST']);
    }

    protected function verified(string $status = 'SUCCESS'): array
    {
        return ['*/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => $status])];
    }

    public function test_unverified_webhooks_are_rejected(): void
    {
        $this->fakePayPal(extra: $this->verified('FAILURE'));

        $this->postJson('/paypal/webhook', ['event_type' => 'PAYMENT.CAPTURE.COMPLETED'])->assertStatus(400);
    }

    public function test_capture_completed_webhook_pays_an_abandoned_checkout(): void
    {
        $this->fakePayPal('149.00', $this->verified());
        $order = app(Registry::class)->reserve($this->tier('standard'), ['email' => 'jane@example.test', 'plaque_name' => 'Jane Patriot']);
        $order->update(['paypal_order_id' => 'PP-ORDER-1']);

        $event = [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['id' => 'CAP-1', 'supplementary_data' => ['related_ids' => ['order_id' => 'PP-ORDER-1']]],
        ];

        $this->postJson('/paypal/webhook', $event)->assertOk();
        $this->postJson('/paypal/webhook', $event)->assertOk();

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(51, $order->fresh()->slot_number);
        Mail::assertQueued(OrderConfirmation::class, 1);
    }

    public function test_full_refund_releases_the_slot_but_partial_does_not(): void
    {
        $this->fakePayPal(extra: $this->verified());
        $order = $this->paidOrder('standard', ['paypal_capture_id' => 'CAP-9']);

        $refund = fn (string $value) => [
            'event_type' => 'PAYMENT.CAPTURE.REFUNDED',
            'resource' => [
                'id' => 'REF-1',
                'status' => 'COMPLETED',
                'amount' => ['value' => $value, 'currency_code' => 'USD'],
                'links' => [['rel' => 'up', 'href' => 'https://api-m.sandbox.paypal.com/v2/payments/captures/CAP-9']],
            ],
        ];

        $this->postJson('/paypal/webhook', $refund('10.00'))->assertOk();
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);

        $this->postJson('/paypal/webhook', $refund('149.00'))->assertOk();
        $this->assertSame(OrderStatus::Refunded, $order->fresh()->status);
        $this->assertNull($order->fresh()->slot_number);
    }
}
