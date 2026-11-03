<?php

use App\Enums\OrderStatus;
use App\Exceptions\SoldOutException;
use App\Models\Order;
use App\Models\Tier;
use App\Services\Registry;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::site')] #[Title('Claim your Founder slot')] class extends Component {
    public string $tierCode = 'standard';
    public string $plaque_name = '';
    public string $email = '';
    public string $listener_handle = '';
    public bool $show_full_name = false;
    public bool $consent_spelling = false;
    public bool $consent_terms = false;

    #[Locked]
    public ?string $orderUuid = null;

    public function mount(?string $tier = null): void
    {
        $tiers = $this->tiers;
        $requested = $tiers->firstWhere('code', $tier);

        $this->tierCode = ($requested && ! $requested->isSoldOut())
            ? $requested->code
            : ($tiers->first(fn (Tier $t) => ! $t->isSoldOut())?->code ?? 'standard');
    }

    #[Computed]
    public function tiers()
    {
        return Tier::orderBy('slot_start')->get();
    }

    #[Computed]
    public function selectedTier(): ?Tier
    {
        return $this->tiers->firstWhere('code', $this->tierCode);
    }

    #[Computed]
    public function coupleAddon(): bool
    {
        return Registry::requiresCoupleAddon($this->plaque_name);
    }

    #[Computed]
    public function total(): string
    {
        $cents = $this->selectedTier ? app(Registry::class)->priceFor($this->selectedTier, $this->coupleAddon) : 0;

        return '$'.number_format($cents / 100);
    }

    #[Computed]
    public function order(): ?Order
    {
        return $this->orderUuid ? Order::with('tier')->firstWhere('uuid', $this->orderUuid) : null;
    }

    protected function rules(): array
    {
        return [
            'tierCode' => ['required', 'exists:tiers,code'],
            'plaque_name' => ['required', 'string', 'min:2', 'max:'.config('lunar.name_max_length'), "regex:/^[A-Za-z][A-Za-z .'&\-]*$/"],
            'email' => ['required', 'email:rfc', 'max:255'],
            'listener_handle' => ['nullable', 'string', 'max:100'],
            'show_full_name' => ['boolean'],
            'consent_spelling' => ['accepted'],
            'consent_terms' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'plaque_name.regex' => 'Use letters, spaces and . \' & - only. No numbers, emojis or other symbols.',
            'consent_spelling.accepted' => 'Please confirm the spelling of the name.',
            'consent_terms.accepted' => 'Please agree to the Terms and the independence disclaimer.',
        ];
    }

    protected function validationAttributes(): array
    {
        return ['plaque_name' => 'name for the plaque', 'tierCode' => 'tier'];
    }

    public function updatedPlaqueName(): void
    {
        $this->plaque_name = preg_replace('/\s+/', ' ', ltrim($this->plaque_name));
    }

    public function reserve(Registry $registry): void
    {
        $this->plaque_name = trim($this->plaque_name);
        $this->validate();

        $key = 'reserve:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('plaque_name', 'Too many reservations from this connection. Please wait a few minutes.');

            return;
        }

        RateLimiter::hit($key, 600);

        try {
            $order = $registry->reserve($this->selectedTier, [
                'email' => $this->email,
                'plaque_name' => $this->plaque_name,
                'listener_handle' => $this->listener_handle ?: null,
                'show_full_name' => $this->show_full_name,
                'ip_address' => request()->ip(),
            ]);
        } catch (SoldOutException) {
            unset($this->tiers, $this->selectedTier);
            $this->addError('tierCode', "{$this->selectedTier->name} slots just sold out. Please choose another tier.");

            return;
        }

        $this->orderUuid = $order->uuid;
    }

    public function editDetails(): void
    {
        // Release the hold so the slot goes back to the pool right away.
        if ($this->order?->status === OrderStatus::Pending) {
            $this->order->update(['status' => OrderStatus::Expired]);
        }

        $this->orderUuid = null;
        unset($this->order);
    }
}; ?>

<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
    <header class="text-center">
        <h1 class="text-3xl font-extrabold text-white sm:text-4xl">Secure Your Place on the Founders Plaque</h1>
        <p class="mx-auto mt-3 max-w-2xl text-zinc-300">
            Become one of 1,000 Founders whose names are laser engraved onto our aerospace-grade commemorative plaque.
            Space is strictly limited to 1,000 names.
        </p>
    </header>

    <div class="mt-8">
        <livewire:slot-counter variant="stat" />
    </div>

    @if (! $this->order)
        {{-- Step 1: details --}}
        <form wire:submit="reserve" class="mt-8 space-y-8 rounded-2xl border border-white/10 bg-white/5 p-6 sm:p-8" novalidate>
            <flux:radio.group wire:model.live="tierCode" label="Choose your tier" variant="cards" class="grid gap-3 sm:grid-cols-2">
                @foreach ($this->tiers as $tier)
                    <flux:radio
                        :value="$tier->code"
                        :disabled="$tier->isSoldOut()"
                        :label="$tier->name.' · '.$tier->priceLabel()"
                        :description="$tier->isSoldOut() ? 'Sold out' : 'Slots '.$tier->rangeLabel().' · '.number_format($tier->remaining()).' left'"
                    />
                @endforeach
            </flux:radio.group>

            <flux:field>
                <flux:label>Name for the plaque</flux:label>
                <flux:description>
                    Enter the name exactly as it should be engraved, max {{ config('lunar.name_max_length') }} characters.
                    One person per slot. Couple or family names (for example "Mr &amp; Mrs John Doe" or "Doe Family") are a +${{ number_format(config('lunar.couple_addon_cents') / 100) }} add-on.
                </flux:description>
                <flux:input wire:model.live.debounce.300ms="plaque_name" maxlength="{{ config('lunar.name_max_length') }}" autocomplete="name" required />
                <div class="flex justify-between text-xs text-zinc-400">
                    <span>
                        @if ($this->coupleAddon)
                            <span class="font-medium text-gold">Couple/family name detected: +${{ number_format(config('lunar.couple_addon_cents') / 100) }} add-on applied.</span>
                        @endif
                    </span>
                    <span class="tabular-nums">{{ mb_strlen($plaque_name) }}/{{ config('lunar.name_max_length') }}</span>
                </div>
                <flux:error name="plaque_name" />
            </flux:field>

            <flux:input
                wire:model="email"
                type="email"
                label="Email address"
                description="Your digital certificate and project updates are sent here."
                autocomplete="email"
                required
            />

            <flux:input wire:model="listener_handle" label="Podcast listener handle or comment (optional)" maxlength="100" />

            <flux:switch wire:model="show_full_name" label="Show my full name on the public Founders list" description="If this is off, the list shows first name and last initial." align="left" />

            <div class="space-y-4 rounded-xl border border-white/10 bg-black/20 p-4">
                <flux:field variant="inline">
                    <flux:checkbox wire:model="consent_spelling" />
                    <flux:label class="!leading-snug">{{ config('lunar.consent_spelling') }}</flux:label>
                    <flux:error name="consent_spelling" />
                </flux:field>

                <flux:field variant="inline">
                    <flux:checkbox wire:model="consent_terms" />
                    <flux:label class="!leading-snug">
                        I have read and agree to the <a href="{{ route('terms') }}" target="_blank" class="underline">Terms of Service</a>.
                        I understand and agree that this is an independent project unaffiliated with Donald J. Trump or any government agency, as detailed in the <a href="{{ route('disclaimer') }}" target="_blank" class="underline">Disclaimer</a>.
                    </flux:label>
                    <flux:error name="consent_terms" />
                </flux:field>
            </div>

            <flux:error name="tierCode" />

            <div class="flex flex-col items-center gap-3 border-t border-white/10 pt-6 sm:flex-row sm:justify-between">
                <p class="text-lg text-white">Total: <span class="font-bold">{{ $this->total }}</span></p>
                <flux:button type="submit" variant="primary" class="w-full sm:w-auto">RESERVE MY SLOT</flux:button>
            </div>
            <p class="text-center text-xs text-zinc-400 sm:text-right">Your slot is held for {{ config('lunar.hold_minutes') }} minutes while you pay with PayPal.</p>
        </form>
    @else
        {{-- Step 2: payment --}}
        <section class="mt-8 space-y-6 rounded-2xl border border-gold/40 bg-white/5 p-6 sm:p-8" aria-labelledby="pay-heading">
            <div>
                <h2 id="pay-heading" class="text-xl font-bold text-white">Your slot is reserved</h2>
                <p
                    class="mt-1 text-sm text-zinc-300"
                    x-data="{ ends: {{ $this->order->hold_expires_at?->getTimestampMs() ?? 0 }}, left: '' }"
                    x-init="const tick = () => { const s = Math.max(0, Math.floor((ends - Date.now()) / 1000)); left = Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0'); }; tick(); setInterval(tick, 1000)"
                >
                    Complete payment within <span class="font-semibold tabular-nums text-gold" x-text="left"></span> to keep it.
                </p>
            </div>

            <dl class="grid gap-3 rounded-xl bg-black/20 p-4 text-sm sm:grid-cols-2">
                <div><dt class="text-zinc-400">Tier</dt><dd class="font-medium text-white">{{ $this->order->tier->name }}</dd></div>
                <div><dt class="text-zinc-400">Name for the plaque</dt><dd class="font-medium text-white">{{ $this->order->plaque_name }}</dd></div>
                <div><dt class="text-zinc-400">Email</dt><dd class="font-medium text-white">{{ $this->order->email }}</dd></div>
                <div>
                    <dt class="text-zinc-400">Total</dt>
                    <dd class="font-medium text-white">{{ $this->order->amountLabel() }} @if ($this->order->couple_addon)<span class="text-zinc-400">(includes couple/family add-on)</span>@endif</dd>
                </div>
            </dl>

            <div
                wire:ignore
                x-data="paypalButtons(@js(route('checkout.create', $this->order)), @js(route('checkout.capture', $this->order)))"
                class="mx-auto max-w-md"
            >
                <div x-ref="buttons" x-show="!processing"></div>
                <p x-show="processing" class="py-6 text-center text-zinc-200">Confirming your payment…</p>
                <p x-show="error" x-text="error" role="alert" class="mt-3 rounded-lg bg-red-500/15 p-3 text-sm text-red-200"></p>
            </div>

            <div class="text-center">
                <flux:button wire:click="editDetails" variant="ghost" size="sm">Edit my details</flux:button>
            </div>
        </section>
    @endif
</div>
