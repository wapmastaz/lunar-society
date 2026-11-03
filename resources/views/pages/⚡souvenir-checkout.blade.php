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

<div class="mx-auto max-w-xl px-4 py-12 sm:px-6">
    <h1 class="text-3xl font-extrabold text-white">{{ $product->name }}</h1>
    <p class="mt-1 text-2xl font-bold text-white">{{ $product->priceLabel() }}</p>
    @if ($product->description)
        <p class="mt-3 text-zinc-300">{{ $product->description }}</p>
    @endif

    <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-6">
        @if (! $this->order)
            <form wire:submit="start" class="space-y-6" novalidate>
                <flux:input wire:model="email" type="email" label="Email address" description="Your receipt and shipping updates are sent here." autocomplete="email" required />

                <flux:field variant="inline">
                    <flux:checkbox wire:model="consent_terms" />
                    <flux:label class="!leading-snug">
                        I have read and agree to the <a href="{{ route('terms') }}" target="_blank" class="underline">Terms of Service</a>.
                        I understand and agree that this is an independent project unaffiliated with Donald J. Trump or any government agency, as detailed in the <a href="{{ route('disclaimer') }}" target="_blank" class="underline">Disclaimer</a>.
                    </flux:label>
                    <flux:error name="consent_terms" />
                </flux:field>

                <flux:button type="submit" variant="primary" class="w-full">Continue to PayPal</flux:button>
                <p class="text-center text-xs text-zinc-400">You'll enter the shipping address in PayPal.</p>
            </form>
        @else
            <div
                wire:ignore
                x-data="paypalButtons(@js(route('checkout.create', $this->order)), @js(route('checkout.capture', $this->order)))"
            >
                <div x-ref="buttons" x-show="!processing"></div>
                <p x-show="processing" class="py-6 text-center text-zinc-200">Confirming your payment…</p>
                <p x-show="error" x-text="error" role="alert" class="mt-3 rounded-lg bg-red-500/15 p-3 text-sm text-red-200"></p>
            </div>
        @endif
    </div>
</div>
