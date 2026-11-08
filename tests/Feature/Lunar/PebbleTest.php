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

    public function test_pebble_shows_as_coming_soon_until_it_has_a_price(): void
    {
        config(['lunar.pebble.price' => null]);
        $this->seed(ProductSeeder::class);

        $this->assertFalse(Product::firstWhere('slug', 'space-pebble')->active);

        $this->get('/')
            ->assertSee('Introducing the exclusive Space Pebble Collectible')
            ->assertSee('Coming soon')
            ->assertDontSee('View collectible &amp; order now', false);

        $this->get('/souvenirs/space-pebble')->assertNotFound();
        $this->get('/claim')->assertDontSee('Also available: the Space Pebble Collectible');
    }

    public function test_pebble_with_a_price_can_be_ordered_separately(): void
    {
        config(['lunar.pebble.price' => 49]);
        $this->seed(ProductSeeder::class);

        $pebble = Product::firstWhere('slug', 'space-pebble');
        $this->assertTrue($pebble->active);
        $this->assertSame(4900, $pebble->price_cents);

        $this->get('/')->assertSee('View collectible &amp; order now', false)->assertSee('$49');
        $this->get('/claim')->assertSee('Also available: the Space Pebble Collectible');
        $this->get('/faq')->assertSee('What is the Space Pebble Collectible?')->assertSee('It is not lunar material');

        $this->get('/souvenirs/space-pebble')
            ->assertOk()
            ->assertSee('One Space Pebble Collectible')
            ->assertSee('not lunar material');

        Livewire::test('pages::souvenir-checkout', ['product' => $pebble])
            ->set('email', 'gift@example.test')
            ->set('consent_terms', true)
            ->call('start')
            ->assertHasNoErrors();

        $order = Order::sole();
        $this->assertSame($pebble->id, $order->product_id);
        $this->assertNull($order->tier_id);
        $this->assertSame(4900, $order->amount_cents);
    }

    public function test_hero_matches_the_mock_and_labels_the_artwork(): void
    {
        $this->get('/')
            ->assertSee('Be Part of History')
            ->assertSee('Join the Luna Society')
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
