<?php

namespace Tests\Feature\Lunar;

use App\Enums\OrderStatus;
use App\Mail\OrderConfirmation;
use App\Mail\SaleNotification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Registry;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRegistry;
use Tests\TestCase;

class PebbleTest extends TestCase
{
    use InteractsWithRegistry, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRegistry();
    }

    public function test_offerings_are_listed_apart_from_founders_and_hide_at_zero(): void
    {
        Product::create(['slug' => 'generous-supporter', 'name' => 'Generous Supporter', 'price_cents' => 4900, 'active' => true]);
        config(['lunar.offerings.trade-associate' => 0]);
        $this->seed(ProductSeeder::class);

        $this->assertFalse(Product::firstWhere('slug', 'generous-supporter')->active);
        $this->assertFalse(Product::firstWhere('slug', 'trade-associate')->active);
        $this->get('/souvenirs/trade-associate')->assertNotFound();

        $this->get('/')
            ->assertSee('We Need a Little Help from Our Friends')
            ->assertSee('separate from Registered Founder memberships')
            ->assertSee('Booster')
            ->assertSee('$25')
            ->assertSee('Follower')
            ->assertSee('$49')
            ->assertSee('Supporter')
            ->assertSee('$79')
            ->assertDontSee('Generous Supporter')
            ->assertSee('$5 for the Fallen Astronauts Education Fund and $10 for shipping and handling')
            ->assertSee('Alpha Founder')
            ->assertSee('MJ Schultz, aka Mr SuperTrump, occupies positions #001 to #005')
            ->assertSee('Holiday Gift Delivery Notice');
    }

    public function test_each_offering_charges_the_right_amount_through_paypal(): void
    {
        $this->seed(ProductSeeder::class);

        // [PayPal amount, fee]: the Booster has no fee, the Trade Associate pays only the fee.
        foreach (['booster' => [2500, 0], 'follower' => [6400, 1500], 'moon-dust-supporter' => [9400, 1500], 'trade-associate' => [1500, 1500]] as $slug => [$amount, $fee]) {
            $product = Product::firstWhere('slug', $slug);

            Livewire::test('pages::souvenir-checkout', ['product' => $product])
                ->set('email', "{$slug}@example.test")
                ->set('list_name', 'Jane Patriot')
                ->set('consent_terms', true)
                ->call('start')
                ->assertHasNoErrors();

            $order = Order::where('email', "{$slug}@example.test")->sole();
            $this->assertSame([$amount, $fee], [$order->amount_cents, $order->processing_fee_cents], $slug);
            $this->assertSame($product->listing ? 'Jane Patriot' : null, $order->list_name, $slug);
        }

        $this->get('/souvenirs/booster')->assertSee('Booster list')->assertSee('December 20')->assertDontSee('+ $15');
        $this->get('/souvenirs/follower')->assertSee('Space Pebble Collectible')->assertSee('not lunar material')->assertSee('Name for our Followers list');
        $this->get('/souvenirs/moon-dust-supporter')->assertSee('Lunar Moon Dust')->assertSee('Supporters list');
        $this->get('/souvenirs/trade-associate')
            ->assertSee('paid through Barter Sales, outside PayPal')
            ->assertSee('certificate of authenticity')
            ->assertDontSee('Name for our');
    }

    public function test_listed_offerings_need_a_name_for_the_list(): void
    {
        $this->seed(ProductSeeder::class);

        Livewire::test('pages::souvenir-checkout', ['product' => Product::firstWhere('slug', 'follower')])
            ->set('email', 'fan@example.test')
            ->set('consent_terms', true)
            ->call('start')
            ->assertHasErrors(['list_name' => 'required']);
    }

    public function test_lists_show_paid_names_and_boosters_only_from_december_20(): void
    {
        $this->seed(ProductSeeder::class);

        foreach (['booster' => 'Bob Booster', 'follower' => 'Fay Follower', 'moon-dust-supporter' => 'Sam Supporter'] as $slug => $name) {
            Order::create([
                'product_id' => Product::firstWhere('slug', $slug)->id,
                'status' => OrderStatus::Paid,
                'amount_cents' => 100,
                'email' => "{$slug}@example.test",
                'list_name' => $name,
                'paid_at' => now(),
            ]);
        }

        $this->travelTo('2026-12-19 12:00', fn () => $this->get('/supporters')
            ->assertOk()
            ->assertSee('Fay F.')
            ->assertSee('Sam S.')
            ->assertDontSee('Bob B.')
            ->assertSee('The Booster list will be published here on December 20, 2026.'));

        $this->travelTo('2026-12-20 12:00', fn () => $this->get('/supporters')->assertSee('Bob B.'));
    }

    public function test_trade_associate_pays_only_the_fee_by_paypal_and_gets_the_barter_receipt(): void
    {
        Mail::fake();
        Queue::fake();
        $this->seed(ProductSeeder::class);
        $this->fakePayPal('15.00');

        $order = app(Registry::class)->reserveSouvenir(Product::firstWhere('slug', 'trade-associate'), ['email' => 'trade@example.test']);

        $this->postJson(route('checkout.create', $order))->assertOk();
        $this->postJson(route('checkout.capture', $order))->assertOk();

        $this->assertTrue($order->refresh()->isPaid());

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request['purchase_units'][0]['amount']['value'] === '15.00'
            && ! isset($request['purchase_units'][0]['amount']['breakdown'])
            && $request['payment_source']['paypal']['experience_context']['shipping_preference'] === 'GET_FROM_FILE');

        Mail::assertQueued(OrderConfirmation::class, function ($mail) {
            $mail->assertSeeInHtml('paid as a Barter payment through Barter Sales');
            $mail->assertSeeInHtml('Thank you for your support of The Lunar Society Network.');

            return $mail->hasTo('trade@example.test');
        });
        Mail::assertQueued(SaleNotification::class, function ($mail) {
            $mail->assertSeeInHtml('$99 still to be collected through Barter Sales');

            return str_starts_with($mail->envelope()->subject, 'New payment: Trade Associate, $15.00');
        });
    }

    public function test_admin_counts_and_filters_orders_by_class(): void
    {
        $this->seed(ProductSeeder::class);

        foreach (['booster' => 'Bob Booster', 'booster ' => 'Bea Booster', 'follower' => 'Fay Follower'] as $slug => $name) {
            Order::create([
                'product_id' => Product::firstWhere('slug', trim($slug))->id,
                'status' => OrderStatus::Paid,
                'amount_cents' => 2500,
                'email' => str_replace(' ', '.', strtolower($name)).'@example.test',
                'list_name' => $name,
                'paid_at' => now(),
            ]);
        }

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::admin.orders')
            ->assertSeeInOrder(['Booster', '2', 'Follower', '1'])
            ->set('tier', 'product:booster')
            ->assertSee('Bob Booster')
            ->assertSee('Bea Booster')
            ->assertDontSee('Fay Follower');
    }

    public function test_hero_matches_the_mock_and_labels_the_artwork(): void
    {
        $this->get('/')
            ->assertSee('Be Part of History')
            ->assertSee('Join the Lunar Society Network')
            ->assertSee('Register Today')
            ->assertSee('Prestigious Membership')
            ->assertSee('images/hero.webp', false)
            ->assertDontSee('images/hero-mobile.webp', false)
            ->assertSee('object-fill', false)
            ->assertSee('aspect-ratio: 1672 / 941', false)
            ->assertSee('id="hero-copy"', false)
            ->assertSee("Artist's concept", false);
    }
}
