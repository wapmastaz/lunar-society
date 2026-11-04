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
        foreach (['/', '/claim', '/founders', '/souvenirs', '/faq', '/about', '/terms', '/privacy', '/disclaimer'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee(config('lunar.disclaimer'));
        }
    }

    public function test_no_page_promises_a_lunar_base_or_uses_em_dashes(): void
    {
        foreach (['/', '/claim', '/faq', '/about', '/terms', '/disclaimer'] as $uri) {
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
            ->assertSee('no flight can be guaranteed')
            ->assertSee('$149')
            ->assertDontSee('definitive timeline');

        $this->get('/faq')
            ->assertSee('Who leads the society?')
            ->assertSee('Why January 18, 2029?')
            ->assertSee('That is a goal, not a guarantee');
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
