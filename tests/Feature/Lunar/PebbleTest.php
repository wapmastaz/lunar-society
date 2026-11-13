<?php

namespace Tests\Feature\Lunar;

use App\Models\Order;
use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_affiliate_package_is_hidden_when_its_price_is_zero(): void
    {
        config(['lunar.affiliate.price' => 0]);
        $this->seed(ProductSeeder::class);

        $this->assertFalse(Product::firstWhere('slug', 'affiliate-membership')->active);

        $this->get('/')
            ->assertSee('Introducing the Affiliate Membership Package')
            ->assertSee('Coming soon')
            ->assertDontSee('View package &amp; join', false);

        $this->get('/souvenirs/affiliate-membership')->assertNotFound();
        $this->get('/claim')->assertDontSee('Also available: the Affiliate Membership Package');
    }

    public function test_affiliate_package_costs_79_plus_processing_and_ships(): void
    {
        Product::create(['slug' => 'space-pebble', 'name' => 'Old pebble', 'price_cents' => 0, 'active' => false]);
        $this->seed(ProductSeeder::class);

        $package = Product::sole();
        $this->assertSame('affiliate-membership', $package->slug);
        $this->assertTrue($package->active);
        $this->assertSame(7900, $package->price_cents);

        $this->get('/')->assertSee('View package &amp; join', false)->assertSee('$79')->assertSee('+ $15 processing');
        $this->get('/claim')->assertSee('Also available: the Affiliate Membership Package');
        $this->get('/faq')->assertSee('What is the Affiliate Membership Package?')->assertSee('not lunar material');

        $this->get('/souvenirs/affiliate-membership')
            ->assertOk()
            ->assertSee('Affiliate membership in the Lunar Society')
            ->assertSee('does not include a Founder slot')
            ->assertSee('not lunar material');

        Livewire::test('pages::souvenir-checkout', ['product' => $package])
            ->set('email', 'gift@example.test')
            ->set('consent_terms', true)
            ->call('start')
            ->assertHasNoErrors();

        // A reload goes straight back to the PayPal step.
        Livewire::test('pages::souvenir-checkout', ['product' => $package])
            ->assertSet('email', 'gift@example.test')
            ->assertSet('orderUuid', Order::sole()->uuid);

        $order = Order::sole();
        $this->assertSame($package->id, $order->product_id);
        $this->assertNull($order->tier_id);
        $this->assertSame(9400, $order->amount_cents);
        $this->assertSame(1500, $order->processing_fee_cents);
    }

    public function test_hero_matches_the_mock_and_labels_the_artwork(): void
    {
        $this->get('/')
            ->assertSee('Be Part of History')
            ->assertSee('Join the Lunar Society')
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
