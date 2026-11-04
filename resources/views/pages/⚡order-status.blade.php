<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::site')] #[Title('Your order')] class extends Component {
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load('tier', 'product');
    }

    public function refreshStatus(): void
    {
        $this->order->refresh();
    }
}; ?>

<div @if ($order->status === OrderStatus::Pending) wire:poll.5s="refreshStatus" @endif>
    @if ($order->isFounder() && in_array($order->status, [OrderStatus::Paid, OrderStatus::Pending]))
        <x-claim-shell :step="$order->isPaid() ? 6 : 5">
            @if ($order->isPaid())
                <div class="py-4 text-center">
                    <span aria-hidden="true" class="relative mx-auto grid size-16 place-items-center">
                        <span class="animate-ring absolute inset-0 rounded-full bg-gold"></span>
                        <span class="animate-pop relative grid size-16 place-items-center rounded-full bg-gold text-ink">
                            <flux:icon.check variant="solid" class="size-8" />
                        </span>
                    </span>
                    <h2 class="mt-6 font-display text-4xl text-white">Welcome, Founder <span class="text-gold">{{ $order->slotLabel() }}</span></h2>
                    <p class="mx-auto mt-4 max-w-md text-zinc-300">
                        Payment confirmed. A receipt and your digital certificate are on their way to <span class="text-white">{{ $order->email }}</span>.
                    </p>

                    <x-engraving-preview class="mt-7 justify-center" :name="$order->plaque_name" :number="$order->slotLabel()" />

                    <p class="mt-4 text-sm text-zinc-400">
                        Spotted a typo? Reply to the confirmation email before {{ $order->locks_at?->timezone('America/New_York')->format('F j \a\t g:i A T') }}.
                    </p>

                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <flux:button href="{{ route('founders', ['search' => $order->slotLabel()]) }}" variant="primary" class="h-11 px-6">See the plaque</flux:button>
                        <flux:button href="{{ route('claim') }}" variant="outline" class="h-11 px-6">Claim another</flux:button>
                    </div>
                </div>
            @else
                <div class="py-10 text-center">
                    <flux:icon.loading class="mx-auto size-8 text-gold" />
                    <h2 class="mt-5 text-2xl font-semibold text-white">Waiting for payment confirmation…</h2>
                    <p class="mt-3 text-zinc-300">This page updates automatically. If you closed PayPal before paying, you can <a href="{{ route('claim') }}" class="text-gold underline">start again</a>.</p>
                </div>
            @endif

            <x-slot:summary>
                <x-order-summary :tier="$order->tier" :couple="$order->couple_addon" :total="$order->amountLabel()" :number="$order->slotLabel()" />
            </x-slot:summary>
        </x-claim-shell>
    @else
        <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6">
            @if ($order->isPaid())
                <h1 class="font-display text-5xl text-white">Thank you for your order!</h1>
                <p class="mt-4 text-zinc-200">Your {{ $order->itemLabel() }} order is confirmed. A receipt is on its way to <strong>{{ $order->email }}</strong>.</p>
            @elseif ($order->status === OrderStatus::Pending)
                <h1 class="text-2xl font-semibold text-white">Waiting for payment confirmation…</h1>
                <p class="mt-4 text-zinc-300">This page updates automatically.</p>
            @else
                <h1 class="font-display text-4xl text-white">This order is not active</h1>
                <p class="mt-4 text-zinc-300">
                    The reservation {{ $order->status === OrderStatus::Refunded ? 'was refunded' : 'expired before payment' }}.
                    <a href="{{ route('claim') }}" class="text-gold underline">Claim a new slot</a>.
                </p>
            @endif
        </div>
    @endif
</div>
