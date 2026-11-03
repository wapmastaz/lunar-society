<?php

namespace Tests\Feature\Lunar;

use App\Enums\OrderStatus;
use App\Jobs\SubscribeToBeehiiv;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Services\Registry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRegistry;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use InteractsWithRegistry, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRegistry();
        Mail::fake();
        Queue::fake([SubscribeToBeehiiv::class]);
    }

    public function test_claim_form_requires_both_consents_and_a_valid_name(): void
    {
        Livewire::test('pages::claim')
            ->set('plaque_name', 'John 🚀 Doe')
            ->set('email', 'jane@example.test')
            ->call('reserve')
            ->assertHasErrors(['plaque_name' => 'regex', 'consent_spelling' => 'accepted', 'consent_terms' => 'accepted']);

        Livewire::test('pages::claim')
            ->set('plaque_name', str_repeat('a', 31))
            ->call('reserve')
            ->assertHasErrors(['plaque_name' => 'max']);

        $this->assertSame(0, Order::count());
    }

    public function test_claim_form_reserves_a_slot_with_server_side_price(): void
    {
        Livewire::test('pages::claim', ['tier' => 'alpha'])
            ->assertSet('tierCode', 'alpha')
            ->set('plaque_name', 'Mr & Mrs John Doe')
            ->set('email', 'jane@example.test')
            ->set('consent_spelling', true)
            ->set('consent_terms', true)
            ->call('reserve')
            ->assertHasNoErrors()
            ->assertSee('Your slot is reserved');

        $order = Order::sole();
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(22900, $order->amount_cents);
        $this->assertNotNull($order->consented_at);
        $this->assertStringContainsString('unaffiliated with Donald J. Trump', $order->consent_text);
    }

    public function test_sold_out_tier_cannot_be_preselected(): void
    {
        foreach (range(1, 50) as $n) {
            $this->paidOrder('alpha');
        }

        Livewire::test('pages::claim', ['tier' => 'alpha'])->assertSet('tierCode', 'standard');
        $this->get('/')->assertSee('ALPHA FOUNDER SLOTS SOLD OUT');
    }

    public function test_full_paypal_flow_marks_order_paid_and_sends_confirmation(): void
    {
        $this->fakePayPal('149.00');
        $order = app(Registry::class)->reserve($this->tier('standard'), ['email' => 'jane@example.test', 'plaque_name' => 'Jane Patriot']);

        $this->postJson(route('checkout.create', $order))->assertOk()->assertJson(['id' => 'PP-ORDER-1']);
        $this->postJson(route('checkout.capture', $order))->assertOk()->assertJson(['redirect' => route('orders.show', $order)]);

        $order->refresh();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame(51, $order->slot_number);
        $this->assertSame('CAP-1', $order->paypal_capture_id);
        $this->assertSame('Jane', $order->payer_first_name);
        $this->assertTrue($order->locks_at->between(now()->addHours(47), now()->addHours(49)));

        Mail::assertQueued(OrderConfirmation::class, fn ($mail) => $mail->hasTo('jane@example.test') && $mail->order->slot_number === 51);
        Queue::assertPushed(SubscribeToBeehiiv::class);

        $this->get(route('orders.show', $order))->assertSee('#051')->assertSee('Welcome to the Society, Founder!');

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request['purchase_units'][0]['amount']['value'] === '149.00'
            && $request['payment_source']['paypal']['experience_context']['shipping_preference'] === 'NO_SHIPPING');
    }

    public function test_capture_is_idempotent(): void
    {
        $this->fakePayPal('149.00');
        $order = app(Registry::class)->reserve($this->tier('standard'), ['email' => 'jane@example.test', 'plaque_name' => 'Jane Patriot']);

        $this->postJson(route('checkout.create', $order));
        $this->postJson(route('checkout.capture', $order))->assertOk();
        $this->postJson(route('checkout.capture', $order))->assertOk();

        Mail::assertQueuedCount(1);
    }

    public function test_amount_mismatch_does_not_mark_paid(): void
    {
        $this->fakePayPal('1.00');
        $order = app(Registry::class)->reserve($this->tier('standard'), ['email' => 'jane@example.test', 'plaque_name' => 'Jane Patriot']);

        $this->postJson(route('checkout.create', $order));
        $this->postJson(route('checkout.capture', $order))->assertStatus(202);

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
        Mail::assertNothingQueued();
    }

    public function test_capture_is_refused_when_the_hold_lapsed_and_tier_filled(): void
    {
        $this->fakePayPal('199.00');
        $order = app(Registry::class)->reserve($this->tier('alpha'), ['email' => 'jane@example.test', 'plaque_name' => 'Jane Patriot']);
        $this->postJson(route('checkout.create', $order));

        $this->travel(20)->minutes();
        foreach (range(1, 50) as $n) {
            $this->paidOrder('alpha');
        }

        $this->postJson(route('checkout.capture', $order))->assertStatus(409);
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/capture'));
    }

    public function test_pebble_mode_asks_paypal_for_a_shipping_address(): void
    {
        config(['lunar.pebble_included' => true]);
        $this->fakePayPal();
        $order = app(Registry::class)->reserve($this->tier('standard'), ['email' => 'jane@example.test', 'plaque_name' => 'Jane Patriot']);

        $this->postJson(route('checkout.create', $order))->assertOk();

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request['payment_source']['paypal']['experience_context']['shipping_preference'] === 'GET_FROM_FILE');
    }

    public function test_confirmation_email_renders_with_certificate(): void
    {
        $order = $this->paidOrder('alpha', ['plaque_name' => 'Jane Patriot', 'payer_first_name' => 'Jane']);

        $mail = new OrderConfirmation($order->load('tier'));
        $mail->assertSeeInHtml('Dear Jane');
        $mail->assertSeeInHtml('#001 of 1,000');
        $mail->assertSeeInHtml('is not affiliated with');
        $this->assertCount(1, $mail->attachments());

        $pdf = Pdf::loadView('pdf.certificate', ['order' => $order])->output();
        $this->assertStringStartsWith('%PDF', $pdf);
    }
}
