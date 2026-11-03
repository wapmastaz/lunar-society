<?php

namespace Tests\Feature\Lunar;

use App\Enums\OrderStatus;
use App\Exceptions\SoldOutException;
use App\Models\Order;
use App\Services\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRegistry;
use Tests\TestCase;

class RegistryTest extends TestCase
{
    use InteractsWithRegistry, RefreshDatabase;

    protected Registry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRegistry();
        $this->registry = app(Registry::class);
    }

    public function test_holds_count_against_capacity_until_they_expire(): void
    {
        $alpha = $this->tier('alpha');

        foreach (range(1, 49) as $n) {
            $this->paidOrder('alpha');
        }

        $this->registry->reserve($alpha, ['email' => 'a@example.test', 'plaque_name' => 'Hold One']);
        $this->assertTrue($alpha->isSoldOut());

        $this->expectException(SoldOutException::class);
        $this->registry->reserve($alpha, ['email' => 'b@example.test', 'plaque_name' => 'Hold Two']);
    }

    public function test_expired_holds_free_the_slot(): void
    {
        $alpha = $this->tier('alpha');

        foreach (range(1, 49) as $n) {
            $this->paidOrder('alpha');
        }

        $hold = $this->registry->reserve($alpha, ['email' => 'a@example.test', 'plaque_name' => 'Hold One']);
        $this->travel(16)->minutes();

        $this->assertSame(1, $this->registry->expireHolds());
        $this->assertSame(OrderStatus::Expired, $hold->fresh()->status);
        $this->assertFalse($alpha->isSoldOut());
    }

    public function test_slot_numbers_fill_the_lowest_gap_in_the_tier(): void
    {
        $this->paidOrder('alpha', ['slot_number' => 1]);
        $this->paidOrder('alpha', ['slot_number' => 3]);

        $order = $this->registry->reserve($this->tier('alpha'), ['email' => 'a@example.test', 'plaque_name' => 'Gap Filler']);

        $this->assertSame(2, $this->registry->assignSlot($order));
    }

    public function test_standard_slots_start_at_51(): void
    {
        $order = $this->registry->reserve($this->tier('standard'), ['email' => 'a@example.test', 'plaque_name' => 'First Standard']);

        $this->assertSame(51, $this->registry->assignSlot($order));
    }

    public function test_couple_and_family_names_add_the_fee(): void
    {
        foreach (['Mr & Mrs John Doe', 'John and Jane Doe', 'Doe Family', 'John Doe Family'] as $name) {
            $this->assertTrue(Registry::requiresCoupleAddon($name), $name);
        }

        foreach (['John Doe', 'Alexander Sanders', 'Andy Familia'] as $name) {
            $this->assertFalse(Registry::requiresCoupleAddon($name), $name);
        }

        $order = $this->registry->reserve($this->tier('standard'), ['email' => 'a@example.test', 'plaque_name' => 'Doe Family']);
        $this->assertSame(17900, $order->amount_cents);
        $this->assertTrue($order->couple_addon);
    }

    public function test_an_expired_hold_is_revived_only_if_capacity_allows(): void
    {
        $alpha = $this->tier('alpha');
        $hold = $this->registry->reserve($alpha, ['email' => 'a@example.test', 'plaque_name' => 'Late Payer']);
        $this->travel(20)->minutes();

        $this->assertTrue($this->registry->confirmHold($hold));
        $this->assertTrue($hold->fresh()->holdIsActive());

        $this->travel(20)->minutes();
        foreach (range(1, 50) as $n) {
            $this->paidOrder('alpha');
        }

        $this->assertFalse($this->registry->confirmHold($hold));
        $this->assertSame(OrderStatus::Expired, $hold->fresh()->status);
    }

    public function test_public_names_are_masked_unless_opted_in(): void
    {
        $this->assertSame('John D.', Order::maskName('John Doe'));
        $this->assertSame('Mr & Mrs John D.', Order::maskName('Mr & Mrs John Doe'));
        $this->assertSame('Cher', Order::maskName('Cher'));

        $order = $this->paidOrder('standard', ['plaque_name' => 'John Doe', 'show_full_name' => true]);
        $this->assertSame('John Doe', $order->publicName());
    }
}
