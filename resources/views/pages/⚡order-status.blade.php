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

<div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6" @if ($order->status === OrderStatus::Pending) wire:poll.5s="refreshStatus" @endif>
    @if ($order->isPaid() && $order->isFounder())
        <span aria-hidden="true" class="mx-auto grid size-16 place-items-center rounded-full bg-gold text-[#070b1a]">
            <flux:icon.rocket-launch variant="solid" class="size-8" />
        </span>
        <h1 class="mt-6 text-3xl font-extrabold text-white sm:text-4xl">Welcome to the Society, Founder!</h1>
        <p class="mt-3 text-5xl font-black tabular-nums text-gold">{{ $order->slotLabel() }}</p>
        <p class="mt-1 text-sm text-zinc-400">{{ $order->tier->name }} · {{ $order->plaque_name }}</p>
        <p class="mt-6 text-zinc-200">
            Your payment is confirmed. A confirmation email with your digital certificate is on its way to <strong>{{ $order->email }}</strong>.
            Tune in to {{ config('lunar.podcast') }} for plaque production updates and our progress toward a lunar payload.
        </p>
        <p class="mt-4 text-sm text-zinc-400">
            Spotted a typo? Reply to the confirmation email before {{ $order->locks_at?->timezone('America/New_York')->format('F j \a\t g:i A T') }}.
        </p>
        <div class="mt-8 flex justify-center gap-3">
            <flux:button href="{{ route('founders') }}" variant="primary">See the Founders list</flux:button>
            <flux:button href="{{ route('home') }}" variant="ghost">Back home</flux:button>
        </div>
    @elseif ($order->isPaid())
        <h1 class="text-3xl font-extrabold text-white">Thank you for your order!</h1>
        <p class="mt-4 text-zinc-200">Your {{ $order->itemLabel() }} order is confirmed. A receipt is on its way to <strong>{{ $order->email }}</strong>.</p>
    @elseif ($order->status === OrderStatus::Pending)
        <h1 class="text-2xl font-bold text-white">Waiting for payment confirmation…</h1>
        <p class="mt-4 text-zinc-300">This page updates automatically. If you closed PayPal before paying, you can <a href="{{ route('claim') }}" class="underline">start again</a>.</p>
    @else
        <h1 class="text-2xl font-bold text-white">This order is not active</h1>
        <p class="mt-4 text-zinc-300">
            The reservation {{ $order->status === OrderStatus::Refunded ? 'was refunded' : 'expired before payment' }}.
            <a href="{{ route('claim') }}" class="underline">Claim a new slot</a>.
        </p>
    @endif
</div>
