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
        Product::create(['slug' => 'follower', 'name' => 'Follower', 'price_cents' => 4900, 'active' => true]);
        config(['lunar.offerings.moon-dust-sample' => 0]);
        $this->seed(ProductSeeder::class);

        $this->assertFalse(Product::firstWhere('slug', 'follower')->active);
        $this->assertFalse(Product::firstWhere('slug', 'moon-dust-sample')->active);
        $this->get('/souvenirs/moon-dust-sample')->assertNotFound();

        $this->get('/')
            ->assertSee('We Need a Little Help from Our Friends')
            ->assertSee('separate from Registered Founder memberships')
            ->assertSee('Booster')
            ->assertSee('$25')
            ->assertSee('Supporter')
            ->assertSee('$49')
            ->assertSee('New Lunar Society Member')
            ->assertSee('$79')
            ->assertDontSee('Follower')
            ->assertSee('Alpha Founder')
            ->assertSee('MJ Schultz, aka Mr SuperTrump, occupies positions #001 to #005')
            ->assertSee('Holiday Gift Delivery Notice');
    }

    public function test_only_the_99_specimen_and_up_carry_the_fee(): void
    {
        $this->seed(ProductSeeder::class);

        // [PayPal amount, fee]
        foreach (['booster' => [2500, 0], 'supporter' => [4900, 0], 'member' => [7900, 0], 'moon-dust-sample' => [11400, 1500]] as $slug => [$amount, $fee]) {
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
        $this->get('/souvenirs/supporter')->assertSee('Supporters list')->assertDontSee('+ $15');
        $this->get('/souvenirs/member')->assertSee('welcome e-card')->assertSee('New Members list')->assertDontSee('+ $15');
        $this->get('/souvenirs/moon-dust-sample')
            ->assertSee('lunar meteorite dust')
            ->assertSee('certificate of authenticity')
            ->assertSee('not on the Founders Plaque')
            ->assertSee('+ $15')
            ->assertDontSee('Name for our');
    }

    public function test_listed_offerings_need_a_name_for_the_list(): void
    {
        $this->seed(ProductSeeder::class);

        Livewire::test('pages::souvenir-checkout', ['product' => Product::firstWhere('slug', 'member')])
            ->set('email', 'fan@example.test')
            ->set('consent_terms', true)
            ->call('start')
            ->assertHasErrors(['list_name' => 'required']);
    }

    public function test_lists_show_paid_names_and_boosters_only_from_december_20(): void
    {
        $this->seed(ProductSeeder::class);

        foreach (['booster' => 'Bob Booster', 'supporter' => 'Sam Supporter', 'member' => 'Mia Member'] as $slug => $name) {
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
            ->assertSee('New Members')
            ->assertSee('Mia M.')
            ->assertSee('Sam S.')
            ->assertDontSee('Bob B.')
            ->assertSee('The Booster list will be published here on December 20, 2026.'));

        $this->travelTo('2026-12-20 12:00', fn () => $this->get('/supporters')->assertSee('Bob B.'));
    }

    public function test_specimen_is_paid_in_full_by_paypal_and_ships(): void
    {
        Mail::fake();
        Queue::fake();
        $this->seed(ProductSeeder::class);
        $this->fakePayPal('114.00');

        $order = app(Registry::class)->reserveSouvenir(Product::firstWhere('slug', 'moon-dust-sample'), ['email' => 'dust@example.test']);

        $this->postJson(route('checkout.create', $order))->assertOk();
        $this->postJson(route('checkout.capture', $order))->assertOk();

        $this->assertTrue($order->refresh()->isPaid());

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request['purchase_units'][0]['amount']['value'] === '114.00'
            && $request['purchase_units'][0]['amount']['breakdown']['handling']['value'] === '15.00'
            && $request['payment_source']['paypal']['experience_context']['shipping_preference'] === 'GET_FROM_FILE');

        Mail::assertQueued(OrderConfirmation::class, function ($mail) {
            $mail->assertSeeInHtml('Holiday Gift Delivery Notice');
            $mail->assertDontSeeInHtml('Barter');

            return $mail->hasTo('dust@example.test');
        });
        Mail::assertQueued(SaleNotification::class, fn ($mail) => str_starts_with($mail->envelope()->subject, 'New payment: Moon Dust Specimen, $114.00'));
    }

    public function test_new_member_gets_the_welcome_e_card(): void
    {
        $this->seed(ProductSeeder::class);

        $order = new Order(['amount_cents' => 7900, 'payer_first_name' => 'Mia', 'list_name' => 'Mia Member']);
        $order->setRelation('product', Product::firstWhere('slug', 'member'));

        $mail = new OrderConfirmation($order);
        $this->assertSame('Your welcome e-card: you are a New Lunar Society Member!', $mail->envelope()->subject);
        $mail->assertSeeInHtml('You are now a New Lunar Society Member.');
        $mail->assertSeeInHtml('your special member offers will follow by email');
        $mail->assertSeeInHtml('nothing is shipped');

        config(['lunar.member_offers' => ['10% off the Moon Dust Specimen']]);
        (new OrderConfirmation($order))->assertSeeInHtml('10% off the Moon Dust Specimen');
    }

    public function test_admin_counts_and_filters_orders_by_class(): void
    {
        $this->seed(ProductSeeder::class);

        foreach (['booster' => 'Bob Booster', 'booster ' => 'Bea Booster', 'member' => 'Mia Member'] as $slug => $name) {
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
            ->assertSeeInOrder(['Booster', '2', 'New Lunar Society Member', '1'])
            ->set('tier', 'product:booster')
            ->assertSee('Bob Booster')
            ->assertSee('Bea Booster')
            ->assertDontSee('Mia Member');
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
