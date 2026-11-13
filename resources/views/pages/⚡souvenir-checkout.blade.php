<?php

use App\Models\Order;
use App\Models\Product;
use App\Services\Registry;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::site')] #[Title('Souvenir checkout')] class extends Component {
    #[Locked]
    public Product $product;

    public string $email = '';
    public bool $consent_terms = false;

    #[Locked]
    public ?string $orderUuid = null;

    public function mount(Product $product): void
    {
        abort_unless($product->active, 404);
        $this->product = $product;
    }

    #[Computed]
    public function order(): ?Order
    {
        return $this->orderUuid ? Order::firstWhere('uuid', $this->orderUuid) : null;
    }

    public function start(Registry $registry): void
    {
        $this->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'consent_terms' => ['accepted'],
        ], ['consent_terms.accepted' => 'Please agree to the Terms and the independence disclaimer.']);

        $key = 'reserve:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many attempts from this connection. Please wait a few minutes.');

            return;
        }

        RateLimiter::hit($key, 600);

        $this->orderUuid = $registry->reserveSouvenir($this->product, [
            'email' => $this->email,
            'ip_address' => request()->ip(),
        ])->uuid;
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16">
    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-zinc-400 hover:text-white">
        <flux:icon.arrow-left variant="micro" class="size-4" /> Back to the Lunar Society
    </a>

    <div class="mt-8 grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
        <div class="animate-rise">
            @if ($product->image_path)
                <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="aspect-square w-full rounded-3xl object-cover shadow-2xl shadow-black/60 ring-1 ring-white/15">
            @else
                <div class="rounded-3xl border border-white/10 bg-gradient-to-br from-panel via-[#121729] to-[#1a1710] p-8">
                    <x-pebble-art class="mx-auto w-full max-w-md" />
                </div>
            @endif
        </div>

        <div class="animate-rise" style="--reveal-delay: 120ms">
            <p class="eyebrow">{{ $product->tagline ?? 'Lunar Society collectible' }}</p>
            <h1 class="mt-4 font-display text-5xl leading-[1.02] text-white sm:text-6xl">{{ $product->name }}</h1>
            <p class="mt-4 font-display text-4xl text-white">{{ $product->priceLabel() }} <span class="font-sans text-base text-zinc-400">+ {{ \App\Services\Registry::processingFeeLabel() }} processing</span></p>

            @if ($product->description)
                <p class="mt-5 leading-relaxed text-zinc-300">{{ $product->description }}</p>
            @endif

            @if ($product->highlights)
                <p class="mt-7 font-mono text-xs uppercase tracking-[0.2em] text-zinc-400">What you get</p>
                <ul class="mt-3 space-y-3 text-zinc-100">
                    @foreach ($product->highlights as $item)
                        <li class="flex gap-3">
                            <span aria-hidden="true" class="mt-2 size-1.5 shrink-0 rotate-45 bg-gold"></span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <p class="mt-6 text-sm text-zinc-400">
                The Space Pebble is a commemorative keepsake, not lunar material. This package does not include a Founder slot or a name on the Founders Plaque.
                <a href="{{ route('claim') }}" class="text-gold underline underline-offset-2">Claim a founder slot</a>.
            </p>
            <p class="mt-2 text-sm text-zinc-400">
                Ships to the address you give PayPal. For Christmas delivery, order by {{ config('lunar.shipping_cutoffs.standard') }} (standard)
                or {{ config('lunar.shipping_cutoffs.expedited') }} (expedited).
            </p>

            <div class="panel mt-8 p-6">
                @if (! $this->order)
                    <form wire:submit="start" class="space-y-6" novalidate>
                        <flux:input wire:model="email" type="email" label="Email address" description="Your receipt and shipping updates are sent here." autocomplete="email" required />

                        <flux:field variant="inline">
                            <flux:checkbox wire:model="consent_terms" />
                            <flux:label class="!leading-snug">
                                I have read and agree to the <a href="{{ route('terms') }}" target="_blank" class="text-gold underline underline-offset-2">Terms of Service</a>.
                                I understand and agree that this is an independent project unaffiliated with Donald J. Trump or any government agency, as detailed in the <a href="{{ route('disclaimer') }}" target="_blank" class="text-gold underline underline-offset-2">Disclaimer</a>.
                            </flux:label>
                            <flux:error name="consent_terms" />
                        </flux:field>

                        <flux:button type="submit" variant="primary" icon:trailing="arrow-right" class="h-12 w-full text-base">Continue to PayPal</flux:button>
                        <p class="flex items-center justify-center gap-2 text-xs text-zinc-400">
                            <flux:icon.lock-closed variant="micro" class="size-4" /> Secure checkout with PayPal. You'll enter the shipping address there.
                        </p>
                    </form>
                @else
                    <div
                        wire:ignore
                        x-data="paypalButtons(@js(route('checkout.create', $this->order)), @js(route('checkout.capture', $this->order)))"
                        class="animate-rise"
                    >
                        <div x-ref="buttons" x-show="!processing" class="min-h-24"></div>
                        <p x-show="processing" class="py-6 text-center text-zinc-200">Confirming your payment…</p>
                        <p x-show="error" x-text="error" role="alert" class="mt-3 rounded-lg bg-red-500/15 p-3 text-sm text-red-200"></p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
