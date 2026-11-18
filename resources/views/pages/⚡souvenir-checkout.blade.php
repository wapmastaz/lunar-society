<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Services\Registry;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Session;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::site')] #[Title('Souvenir checkout')] class extends Component {
    #[Locked]
    public Product $product;

    // Kept in the session so a page reload doesn't wipe the form or the PayPal step.
    #[Session(key: 'souvenir.email')]
    public string $email = '';
    #[Session(key: 'souvenir.list_name')]
    public string $list_name = '';
    #[Session(key: 'souvenir.show_full_name')]
    public bool $show_full_name = false;
    #[Session(key: 'souvenir.consent_terms')]
    public bool $consent_terms = false;
    #[Session(key: 'souvenir.marketing_opt_in')]
    public bool $marketing_opt_in = false;

    #[Locked]
    #[Session(key: 'souvenir.order')]
    public ?string $orderUuid = null;

    public function mount(Product $product): void
    {
        abort_unless($product->active, 404);
        $this->product = $product;

        $order = $this->order;

        // Resume only a live hold for this product; start over once it's paid or lapsed.
        if ($order && ($order->product_id !== $product->id || $order->status !== OrderStatus::Pending || ! $order->hold_expires_at?->isFuture())) {
            if ($order->isPaid()) {
                $this->reset('email', 'list_name', 'show_full_name', 'consent_terms', 'marketing_opt_in');
            }

            $this->orderUuid = null;
            unset($this->order);
        }
    }

    #[Computed]
    public function order(): ?Order
    {
        return $this->orderUuid ? Order::firstWhere('uuid', $this->orderUuid) : null;
    }

    public function start(Registry $registry): void
    {
        $this->list_name = trim(preg_replace('/\s+/', ' ', $this->list_name));

        $this->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'list_name' => $this->product->listing ? ['required', 'string', 'max:'.config('lunar.name_max_length')] : ['nullable'],
            'consent_terms' => ['accepted'],
        ], [
            'list_name.required' => 'Please enter the name to show on the list.',
            'consent_terms.accepted' => 'Please agree to the Terms and the independence disclaimer.',
        ]);

        $key = 'reserve:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many attempts from this connection. Please wait a few minutes.');

            return;
        }

        RateLimiter::hit($key, 600);

        $this->orderUuid = $registry->reserveSouvenir($this->product, [
            'email' => $this->email,
            'list_name' => $this->list_name,
            'show_full_name' => $this->show_full_name,
            'marketing_opt_in' => $this->marketing_opt_in,
            'ip_address' => request()->ip(),
        ])->uuid;
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16">
    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-zinc-400 hover:text-white">
        <flux:icon.arrow-left variant="micro" class="size-4" /> Back to the Lunar Society Network
    </a>

    <div class="mt-8 grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
        <div class="animate-rise">
            @if ($product->image_path)
                <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="aspect-square w-full rounded-3xl object-cover shadow-2xl shadow-black/60 ring-1 ring-white/15">
            @else
                <div class="rounded-3xl border border-white/10 bg-gradient-to-br from-panel via-[#121729] to-[#1a1710] p-8">
                    @if ($product->slug === 'follower')
                        <x-pebble-art class="mx-auto w-full max-w-md" />
                    @else
                        <x-logo-mark large class="mx-auto w-full max-w-sm" />
                    @endif
                </div>
            @endif
        </div>

        <div class="animate-rise" style="--reveal-delay: 120ms">
            <p class="eyebrow">{{ $product->tagline ?? 'Lunar Society Network collectible' }}</p>
            <h1 class="mt-4 font-display text-5xl leading-[1.02] text-white sm:text-6xl">{{ $product->name }}</h1>
            <p class="mt-4 font-display text-4xl text-white">{{ $product->priceLabel() }}@if ($product->ships) <span class="font-sans text-base text-zinc-400">+ {{ \App\Services\Registry::processingFeeLabel() }}</span>@endif</p>
            @if ($product->ships)
                <p class="mt-1 text-sm text-zinc-400">The {{ \App\Services\Registry::feeExplained() }}.</p>
            @endif
            @if ($product->barter)
                <p class="mt-3 rounded-lg border border-gold/30 bg-gold/5 p-3 text-sm text-zinc-200">
                    <span class="font-semibold text-gold">Two-step payment — total {{ '$'.number_format(($product->price_cents + \App\Services\Registry::processingFeeCents()) / 100) }}:</span>
                    the {{ $product->priceLabel() }} is paid through Barter Sales (outside PayPal), then the {{ \App\Services\Registry::processingFeeLabel() }} ({{ config('lunar.fee_note') }}) is collected separately here by PayPal.
                </p>
            @endif

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
                Separate from Registered Founder memberships: this does not include a Founder slot or a name on the Founders Plaque.
                <a href="{{ route('claim') }}" class="text-gold underline underline-offset-2">Become a Founder</a>.
            </p>
            @if ($product->ships)
                <p class="mt-3 rounded-lg border border-gold/30 bg-gold/5 p-3 text-sm text-zinc-200">
                    <span class="font-semibold text-gold">Holiday Gift Delivery Notice:</span> {{ config('lunar.delivery_notice') }}
                    It ships to the address you give PayPal.
                </p>
            @else
                <p class="mt-2 text-sm text-zinc-400">Nothing is shipped. The Lunar Society Network is a for-profit organization, so contributions are not tax-deductible.</p>
            @endif

            <div class="panel mt-8 p-6">
                @if (! $this->order)
                    <form wire:submit="start" class="space-y-6" novalidate>
                        <flux:input wire:model="email" type="email" label="Email address" description="{{ $product->ships ? 'Your receipt and shipping updates are sent here.' : 'Your receipt is sent here.' }}" autocomplete="email" required />

                        @if ($product->listTitle())
                            <div class="space-y-3">
                                <flux:input wire:model="list_name" label="Name for our {{ $product->listTitle() }}" description="Shown as first name and last initial unless you tick the box below." maxlength="{{ config('lunar.name_max_length') }}" autocomplete="name" required />
                                <flux:field variant="inline">
                                    <flux:checkbox wire:model="show_full_name" />
                                    <flux:label class="!leading-snug">Show my full name on the list</flux:label>
                                </flux:field>
                            </div>
                        @endif

                        <flux:field variant="inline">
                            <flux:checkbox wire:model="consent_terms" />
                            <flux:label class="!leading-snug"><span class="block">
                                I have read and agree to the <a href="{{ route('terms') }}" target="_blank" class="text-gold underline underline-offset-2">Terms of Service</a>.
                                I understand and agree that this is an independent project unaffiliated with Donald J. Trump or any government agency, as detailed in the <a href="{{ route('disclaimer') }}" target="_blank" class="text-gold underline underline-offset-2">Disclaimer</a>.</span>
                            </flux:label>
                            <flux:error name="consent_terms" />
                        </flux:field>

                        <flux:field variant="inline">
                            <flux:checkbox wire:model="marketing_opt_in" />
                            <flux:label class="!leading-snug"><span class="block">{{ config('lunar.marketing_consent') }} <span class="text-zinc-400">(Optional)</span></span></flux:label>
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
