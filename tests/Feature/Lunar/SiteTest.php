<?php

namespace Tests\Feature\Lunar;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRegistry;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use InteractsWithRegistry, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRegistry();
    }

    public function test_every_public_page_shows_the_disclaimer_as_text(): void
    {
        foreach (['/', '/claim', '/founders', '/souvenirs', '/charter', '/faq', '/about', '/terms', '/privacy', '/disclaimer'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee(config('lunar.disclaimer'));
        }
    }

    public function test_no_page_promises_a_lunar_base_or_uses_em_dashes(): void
    {
        foreach (['/', '/claim', '/charter', '/faq', '/about', '/terms', '/disclaimer'] as $uri) {
            $html = $this->get($uri)->getContent();

            $this->assertStringNotContainsString("\u{2014}", $html, "Em dash on {$uri}");
            $this->assertStringNotContainsStringIgnoringCase('2028–2030 Lunar Base', $html, $uri);
            $this->assertStringNotContainsStringIgnoringCase('filling rapidly', $html, $uri);
        }
    }

    public function test_founders_list_masks_names_by_default(): void
    {
        $this->paidOrder('standard', ['plaque_name' => 'John Doe']);
        $this->paidOrder('standard', ['plaque_name' => 'Jane Roe', 'show_full_name' => true]);

        $this->get('/founders')
            ->assertSee('John D.')
            ->assertDontSee('John Doe')
            ->assertSee('Jane Roe')
            ->assertSee('#051');
    }

    public function test_founders_search_matches_names_and_numbers(): void
    {
        $this->paidOrder('standard', ['plaque_name' => 'John Doe']);
        $this->paidOrder('standard', ['plaque_name' => 'Jane Roe']);

        $this->get('/founders?search=%23051')->assertSee('1 engraved match');
        $this->get('/founders?search=jane')->assertSee('1 engraved match');
        $this->get('/founders?search=nobody')->assertSee('0 engraved matches');
        $this->get('/founders?list=1')->assertSee('John D.')->assertSee('#052');
    }

    public function test_exports_require_login_and_list_names_in_slot_order(): void
    {
        $this->get(route('admin.exports.engraving'))->assertRedirect(route('login'));

        $this->paidOrder('standard', ['slot_number' => 60, 'plaque_name' => 'Second Founder']);
        $this->paidOrder('alpha', ['slot_number' => 1, 'plaque_name' => '=HYPERLINK Evil']);

        $csv = $this->actingAs(User::factory()->create())
            ->get(route('admin.exports.engraving'))
            ->assertOk()
            ->streamedContent();

        $lines = array_values(array_filter(explode("\n", $csv)));
        $this->assertStringStartsWith('Slot,Tier,"Plaque name"', $lines[0]);
        $this->assertStringStartsWith("1,\"Alpha Founder\",\"'=HYPERLINK Evil\"", $lines[1]);
        $this->assertStringStartsWith('60,', $lines[2]);
    }

    public function test_public_site_stays_dark_whatever_the_system_setting(): void
    {
        $html = $this->get('/claim')->getContent();

        $this->assertStringContainsString('class="dark"', $html);
        $this->assertStringNotContainsString("getItem('flux.appearance')", $html);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.orders'))
            ->assertSee("getItem('flux.appearance')", false);
    }

    public function test_society_intro_frames_the_moon_as_a_goal(): void
    {
        $this->get('/')
            ->assertSee('1,000 foundational MAGA community Trump supporters')
            ->assertSee('The Astronaut Plaque Pledge')
            ->assertSee('January 18, 2029')
            ->assertSee('Named in the spirit of the Lunar Society.')
            ->assertDontSee('Luna Society')
            ->assertSee('no flight can be guaranteed')
            ->assertSee('$149')
            ->assertDontSee('definitive timeline');

        $this->get('/faq')
            ->assertSee('Who leads the society?')
            ->assertSee('Why January 18, 2029?')
            ->assertSee('That is a goal, not a guarantee');
    }

    public function test_checkout_shows_perks_and_a_disclaimer_beside_the_summary(): void
    {
        $this->get('/claim')
            ->assertSee('Every founder receives')
            ->assertSee('Please note')
            ->assertSee('Delivery of the plaque to the Moon is a goal, not a guarantee.')
            ->assertSee('Plus a $15 processing fee per order.');

        $this->get('/')->assertSee('plus a $15 processing fee');
        $this->get('/faq')->assertSee('Are there any extra fees?');

        $this->get('/')->assertSee('Executive Director')->assertSee('data-reveal', false);
    }

    public function test_slot_counts_go_down_as_founders_register(): void
    {
        $this->get('/')->assertSee('1,000 left')->assertSee('50 of 50 left');

        $this->paidOrder('alpha');
        $this->paidOrder('alpha');
        $this->paidOrder('standard');

        $this->get('/')
            ->assertSee('997 left')
            ->assertSee('48 of 50 left')
            ->assertSee('949 of 950 left');

        $this->get('/claim')->assertSee('48 left');
    }

    public function test_header_floats_over_the_hero_only_on_the_home_page(): void
    {
        $this->get('/')
            ->assertSee('lg:absolute lg:inset-x-0 lg:top-0', false)
            ->assertSee('images/logo-sm.webp', false)
            ->assertSee('images/logo.webp', false);
        $this->get('/faq')
            ->assertDontSee('lg:absolute lg:inset-x-0 lg:top-0', false)
            ->assertSee('border-b border-white/[0.06] bg-ink/80 backdrop-blur', false);
    }

    public function test_about_page_tells_mjs_story_with_photos(): void
    {
        $this->get('/about')
            ->assertSee('Why MJ Schultz created the Lunar Society')
            ->assertSee('Junior Rocket Ranger')
            ->assertSee('None of them is involved')
            ->assertSee('not because they are easy, but because they are hard')
            ->assertSee('images/mj-schultz.webp', false)
            ->assertSee('images/story/mj-lunar-module.webp', false)
            ->assertSee('videos/mission-control.mp4', false)
            ->assertSee('Galactic Peace for Mankind');

        $this->get('/')->assertSee('images/mj-schultz.webp', false)->assertSee('#story', false);
    }

    public function test_charter_opens_the_home_page_and_has_its_own_page(): void
    {
        $home = $this->get('/')->assertOk()->assertSee(route('charter'), false)->getContent();
        $logo = strpos($home, 'images/logo.webp');
        $title = strpos($home, 'Founders Charter</a>');
        $quick = strpos($home, 'Quick Registry');
        $this->assertNotFalse($logo);
        $this->assertNotFalse($title);
        $this->assertNotFalse($quick);
        $this->assertLessThan($title, $logo);
        $this->assertLessThan($quick, $title);
        $this->assertLessThan(strpos($home, 'Be Part of History</span>'), $quick);

        $this->get('/charter')
            ->assertOk()
            ->assertSee('Charter of the Lunar Society')
            ->assertSee('Article VI')
            ->assertSee('Intergalactic Peace &amp; Astronaut Children', false)
            ->assertSee('Mitchell J. Schultz')
            ->assertSee('contributions are not tax-deductible')
            ->assertSee('The Space Pebble Collectible is available separately with the Affiliate Membership Package')
            ->assertDontSee('authentic lunar specimen');
    }

    public function test_footer_carries_the_terms_and_legal_disclosure(): void
    {
        foreach (['/', '/claim', '/faq'] as $uri) {
            $this->get($uri)
                ->assertSee('Lunar Society Registry: Terms &amp; Legal Disclosure', false)
                ->assertSee('Outer Space Treaty of 1967')
                ->assertSee('two hundred and fifty (250)')
                ->assertSee('Space Pebble Collectible is sold separately')
                ->assertDontSee('certified space artifact specimen');
        }
    }

    public function test_mobile_menu_is_an_accessible_drawer(): void
    {
        $this->get('/faq')
            ->assertSee('aria-controls="site-menu"', false)
            ->assertSee('id="site-menu"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('x-trap.noscroll.inert="menu"', false)
            ->assertSee('Open menu')
            ->assertSee('Close menu')
            ->assertDontSee('aria-label="Main mobile"', false);
    }

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
    }
}
