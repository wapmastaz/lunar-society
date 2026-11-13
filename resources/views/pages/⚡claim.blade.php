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

/**
 * Registration and enrollment, on one page in the order of the client's form,
 * then PayPal (step 2).
 */
new #[Layout('layouts::site')] #[Title('Registration and Enrollment')] class extends Component {
    public const STATES = [
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado',
        'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'District of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia',
        'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
        'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts',
        'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana',
        'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico',
        'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
        'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota',
        'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington',
        'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
        'PR' => 'Puerto Rico', 'GU' => 'Guam', 'VI' => 'U.S. Virgin Islands', 'AS' => 'American Samoa', 'MP' => 'Northern Mariana Islands',
    ];

    public int $step = 1;

    public string $tierCode = 'standard';
    public string $plaque_name = '';
    public bool $couple_addon = false;
    public string $email = '';
    public string $city = '';
    public string $state = '';
    public string $postal_code = '';
    public bool $us_citizen = false;
    public bool $consent_terms = false;
    public bool $consent_spelling = false;
    public bool $marketing_opt_in = false;
    public string $signature = '';

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
    public function nameNeedsAddon(): bool
    {
        return Registry::requiresCoupleAddon($this->plaque_name);
    }

    #[Computed]
    public function total(): string
    {
        $cents = $this->selectedTier
            ? app(Registry::class)->totalFor($this->selectedTier, $this->couple_addon || $this->nameNeedsAddon)
            : 0;

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
            'plaque_name' => ['required', 'string', 'min:2', 'max:'.config('lunar.name_max_length'), "regex:/^[A-Za-z][A-Za-z .'&\-]*$/"],
            'couple_addon' => ['boolean'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'in:'.implode(',', array_keys(self::STATES))],
            'postal_code' => ['required', 'regex:/^\d{5}(-\d{4})?$/'],
            'tierCode' => ['required', 'exists:tiers,code'],
            'us_citizen' => ['accepted'],
            'consent_terms' => ['accepted'],
            'consent_spelling' => ['accepted'],
            'marketing_opt_in' => ['boolean'],
            'signature' => ['required', 'string', 'min:3', 'max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'plaque_name.regex' => 'Use letters, spaces and . \' & - only. No numbers, emojis or other symbols.',
            'postal_code.regex' => 'Enter a 5-digit ZIP code.',
            'state.in' => 'Choose a state.',
            'us_citizen.accepted' => 'Registration is open to US citizens only.',
            'consent_terms.accepted' => 'Please confirm you have read the Charter and agree to the terms.',
            'consent_spelling.accepted' => 'Please confirm the spelling of the name.',
            'signature.required' => 'Type your full name to sign the application.',
        ];
    }

    protected function validationAttributes(): array
    {
        return ['plaque_name' => 'name on the certificate', 'tierCode' => 'membership', 'postal_code' => 'ZIP code'];
    }

    public function updatedPlaqueName(): void
    {
        $this->plaque_name = preg_replace('/\s+/', ' ', ltrim($this->plaque_name));

        // A couple or family name always needs the add-on.
        if ($this->nameNeedsAddon) {
            $this->couple_addon = true;
        }
    }

    public function updatedCoupleAddon(bool $value): void
    {
        if (! $value && $this->nameNeedsAddon) {
            $this->couple_addon = true;
            $this->addError('couple_addon', 'This looks like a couple or family name, so the add-on is required.');
        }
    }

    public function reserve(Registry $registry): void
    {
        $this->plaque_name = trim($this->plaque_name);
        $this->postal_code = trim($this->postal_code);

        if ($this->selectedTier?->isSoldOut()) {
            $this->addError('tierCode', "{$this->selectedTier->name} slots are sold out. Please choose another membership.");

            return;
        }

        $this->validate();

        $key = 'reserve:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('signature', 'Too many reservations from this connection. Please wait a few minutes.');

            return;
        }

        RateLimiter::hit($key, 600);

        try {
            $order = $registry->reserve($this->selectedTier, [
                'email' => $this->email,
                'buyer_name' => trim($this->signature),
                'city' => trim($this->city),
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'marketing_opt_in' => $this->marketing_opt_in,
                'plaque_name' => $this->plaque_name,
                'couple_addon' => $this->couple_addon,
                'consent_text' => implode("\n", array_filter([
                    config('lunar.citizenship'),
                    config('lunar.acknowledgement'),
                    config('lunar.consent_terms'),
                    config('lunar.consent_spelling'),
                    $this->marketing_opt_in ? config('lunar.marketing_consent') : null,
                    'Signed: '.trim($this->signature),
                ])),
                'ip_address' => request()->ip(),
            ]);
        } catch (SoldOutException) {
            unset($this->tiers, $this->selectedTier);
            $this->addError('tierCode', "{$this->selectedTier->name} slots just sold out. Please choose another membership.");

            return;
        }

        $this->orderUuid = $order->uuid;
        $this->step = 2;
    }

    public function back(): void
    {
        // Give the slot back to the pool right away.
        if ($this->order?->status === OrderStatus::Pending) {
            $this->order->update(['status' => OrderStatus::Expired]);
        }

        $this->orderUuid = null;
        unset($this->order);
        $this->step = 1;
    }
}; ?>

<x-claim-shell :step="$step">
    @if ($step === 1)
        <form wire:submit="reserve" novalidate>
            <div class="text-center">
                <h2 class="font-charter-title text-2xl font-bold uppercase tracking-[0.06em] text-gold sm:text-3xl">Registration and Enrollment <span aria-hidden="true">*</span></h2>
                <p class="mt-2 font-charter text-lg italic text-zinc-300">* Only US Citizens</p>
            </div>

            {{-- I. Applicant --}}
            <fieldset class="mt-9 border-t border-white/10 pt-7">
                <legend class="sr-only">I. Applicant</legend>
                <p class="font-charter-title text-sm font-bold uppercase tracking-[0.25em] text-gold">I. Applicant: applies as</p>

                <flux:field class="mt-4">
                    <div class="flex items-center justify-between">
                        <flux:label badge="Required">Full name desired on certificate</flux:label>
                        <span class="font-mono text-xs tabular-nums text-zinc-400">{{ mb_strlen($plaque_name) }}/{{ config('lunar.name_max_length') }}</span>
                    </div>
                    <flux:input wire:model.live.debounce.250ms="plaque_name" maxlength="{{ config('lunar.name_max_length') }}" autocomplete="off" required />
                    <flux:description>This name is also engraved on the Founders Plaque. Max {{ config('lunar.name_max_length') }} characters.</flux:description>
                    <flux:error name="plaque_name" />
                </flux:field>

                <div class="mt-4 rounded-xl border border-white/10 bg-white/[0.03] p-4 text-sm text-zinc-300">
                    <p>Couple or family names, for example:</p>
                    <ul class="mt-2 grid gap-1 font-charter text-lg text-white sm:grid-cols-3">
                        <li>Jon &amp; Jane Doe</li>
                        <li>The Jon Doe Family</li>
                        <li>Mr &amp; Ms Jon Doe</li>
                    </ul>
                    <div class="mt-4 border-t border-white/10 pt-4">
                        <flux:switch wire:model.live="couple_addon" align="left" label="Couple or family name · +${{ number_format(config('lunar.couple_addon_cents') / 100) }} add-on" />
                        <flux:error name="couple_addon" />
                    </div>
                </div>

                <div class="mt-5">
                    <p class="font-mono text-xs uppercase tracking-[0.25em] text-zinc-400">Engraving preview</p>
                    <x-engraving-preview class="mt-3" :name="$plaque_name" />
                </div>
            </fieldset>

            {{-- II. Email --}}
            <fieldset class="mt-8 border-t border-white/10 pt-7">
                <legend class="sr-only">II. Email address</legend>
                <p class="font-charter-title text-sm font-bold uppercase tracking-[0.25em] text-gold">II. Email address</p>
                <flux:input wire:model="email" type="email" class="mt-4" autocomplete="email" required aria-label="Email address" description="Your receipt and certificate are sent here." />
            </fieldset>

            {{-- III. Area --}}
            <fieldset class="mt-8 border-t border-white/10 pt-7">
                <legend class="sr-only">III. Area</legend>
                <p class="font-charter-title text-sm font-bold uppercase tracking-[0.25em] text-gold">III. Area</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-[minmax(0,1fr)_12rem_8rem]">
                    <flux:input wire:model="city" label="City" autocomplete="address-level2" required />
                    <flux:select wire:model="state" label="State" autocomplete="address-level1" required>
                        <flux:select.option value="">Choose…</flux:select.option>
                        @foreach ($this::STATES as $code => $name)
                            <flux:select.option value="{{ $code }}">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="postal_code" label="ZIP" inputmode="numeric" autocomplete="postal-code" maxlength="10" required />
                </div>
            </fieldset>

            {{-- Choose membership --}}
            <fieldset class="mt-8 border-t border-white/10 pt-7">
                <legend class="font-charter-title text-sm font-bold uppercase tracking-[0.25em] text-gold">Choose membership</legend>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($this->tiers as $tier)
                        @php
                            $active = $tierCode === $tier->code;
                            $label = $tier->code === 'alpha' ? 'A: Alpha' : 'B: General';
                        @endphp
                        <label @class([
                            'relative flex cursor-pointer items-center justify-between gap-4 rounded-xl border p-5 transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-gold',
                            'border-gold bg-white/[0.06]' => $active,
                            'border-white/10 hover:border-white/25' => ! $active,
                            'pointer-events-none opacity-50' => $tier->isSoldOut(),
                        ])>
                            <input type="radio" wire:model.live="tierCode" value="{{ $tier->code }}" class="sr-only" @disabled($tier->isSoldOut())>
                            <span>
                                <span class="block text-lg font-semibold text-white">{{ $label }} ({{ $tier->priceLabel() }})</span>
                                <span class="mt-1 block font-mono text-xs text-zinc-400">
                                    {{ sprintf('#%03d–#%03d', $tier->slot_start, $tier->slot_end) }} · {{ $tier->isSoldOut() ? 'Sold out' : number_format($tier->remaining()).' left' }}
                                </span>
                            </span>
                            <span aria-hidden="true" @class(['grid size-6 shrink-0 place-items-center rounded-full border', 'border-gold' => $active, 'border-zinc-600' => ! $active])>
                                @if ($active)<span class="size-3 rounded-full bg-gold"></span>@endif
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-3 text-sm text-zinc-400">Plus a {{ \App\Services\Registry::processingFeeLabel() }} processing fee per order.</p>
                <flux:error name="tierCode" class="mt-2" />
            </fieldset>

            {{-- Acknowledgement and consents --}}
            <div class="mt-8 space-y-4 border-t border-white/10 pt-7">
                <p class="font-charter text-xl leading-relaxed text-zinc-200">{{ config('lunar.acknowledgement') }}</p>

                <flux:field variant="inline">
                    <flux:checkbox wire:model="us_citizen" />
                    <flux:label class="!leading-snug">{{ config('lunar.citizenship') }}</flux:label>
                    <flux:error name="us_citizen" />
                </flux:field>

                <flux:field variant="inline">
                    <flux:checkbox wire:model="consent_terms" />
                    <flux:label class="!leading-snug">
                        I have read the <a href="{{ route('charter') }}" target="_blank" class="text-gold underline underline-offset-2">Charter</a> and agree to the
                        <a href="{{ route('terms') }}" target="_blank" class="text-gold underline underline-offset-2">Terms of Service</a>.
                        I understand and agree that this is an independent project unaffiliated with Donald J. Trump or any government agency, as detailed in the
                        <a href="{{ route('disclaimer') }}" target="_blank" class="text-gold underline underline-offset-2">Disclaimer</a>.
                    </flux:label>
                    <flux:error name="consent_terms" />
                </flux:field>

                <flux:field variant="inline">
                    <flux:checkbox wire:model="consent_spelling" />
                    <flux:label class="!leading-snug">{{ config('lunar.consent_spelling') }}</flux:label>
                    <flux:error name="consent_spelling" />
                </flux:field>

                <flux:field variant="inline">
                    <flux:checkbox wire:model="marketing_opt_in" />
                    <flux:label class="!leading-snug">{{ config('lunar.marketing_consent') }} <span class="text-zinc-400">(Optional)</span></flux:label>
                </flux:field>
            </div>

            {{-- Signature --}}
            <div class="mt-8 border-t border-white/10 pt-7">
                <label for="signature" class="block font-charter-title text-sm font-bold uppercase tracking-[0.25em] text-gold">Signature of applicant</label>
                <div class="mt-4 flex items-end gap-3 border-b border-gold/60 pb-1">
                    <span aria-hidden="true" class="font-charter text-4xl text-gold">X</span>
                    <input id="signature" wire:model="signature" type="text" autocomplete="name" placeholder="Type your full legal name"
                        class="w-full bg-transparent pb-1 font-charter text-3xl italic text-white placeholder:text-zinc-600 placeholder:not-italic placeholder:text-lg focus:outline-none" required>
                </div>
                <p class="mt-2 text-sm text-zinc-400">Applicant. Typing your name here is your electronic signature.</p>
                <flux:error name="signature" />
            </div>

            <flux:button type="submit" variant="primary" icon:trailing="arrow-right" class="mt-9 h-12 w-full text-base">Submit application &amp; pay {{ $this->total }}</flux:button>
        </form>

        <div class="mt-8 rounded-xl border border-white/10 bg-white/[0.03] p-5">
            <p class="font-mono text-xs uppercase tracking-[0.2em] text-zinc-400">Every founder receives</p>
            <ul class="mt-4 grid gap-3 text-sm text-zinc-200 sm:grid-cols-2">
                @foreach (array_filter([
                    'Your name laser engraved on the Founders Plaque',
                    'A numbered digital Founder certificate',
                    'Your name on the public Founders list',
                    'The Lunar Society newsletter, if you opt in',
                    config('lunar.pebble_included') ? 'A commemorative space pebble in a gift box' : null,
                    $tierCode === 'alpha' ? 'Alpha: a top-row position, #001 to #050, and a gold-foil certificate' : null,
                ]) as $perk)
                    <li class="flex gap-3">
                        <span aria-hidden="true" class="mt-1.5 size-1.5 shrink-0 rotate-45 bg-gold"></span>
                        <span>{{ $perk }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        @php $pebble = \App\Models\Product::where('slug', config('lunar.affiliate.slug'))->where('active', true)->first(); @endphp
        @if ($pebble)
            <a href="{{ route('souvenirs.show', $pebble) }}" class="group mt-4 flex items-center gap-4 rounded-xl border border-gold/30 bg-gold/5 p-4 text-sm transition hover:border-gold/60">
                <x-pebble-art class="size-14 shrink-0" />
                <span class="min-w-0 flex-1 text-zinc-200">
                    <span class="block font-medium text-white">Also available: the {{ $pebble->name }}</span>
                    Affiliate membership and the Space Pebble Collectible, {{ $pebble->priceLabel() }} + {{ \App\Services\Registry::processingFeeLabel() }} processing. No Founder slot.
                </span>
                <flux:icon.arrow-right variant="micro" class="size-5 shrink-0 text-gold transition group-hover:translate-x-1" />
            </a>
        @endif
    @elseif ($step === 2 && $this->order)
        <h2 class="text-2xl font-semibold text-white">Secure payment</h2>
        <p class="mt-1 text-zinc-400">Your slot is held for {{ config('lunar.hold_minutes') }} minutes while you pay with PayPal.</p>

        <div
            class="mt-7 flex items-center gap-3 rounded-xl border border-white/15 bg-white/[0.04] px-4 py-3"
            x-data="{ ends: {{ $this->order->hold_expires_at?->getTimestampMs() ?? 0 }}, left: '' }"
            x-init="const tick = () => { const s = Math.max(0, Math.floor((ends - Date.now()) / 1000)); left = Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0'); }; tick(); setInterval(tick, 1000)"
        >
            <span class="font-mono text-lg tabular-nums text-gold" x-text="left"></span>
            <span class="text-sm text-zinc-200">remaining on your hold</span>
        </div>

        <div
            wire:ignore
            x-data="paypalButtons(@js(route('checkout.create', $this->order)), @js(route('checkout.capture', $this->order)))"
            class="mt-6"
        >
            <div x-ref="buttons" x-show="!processing" class="min-h-24"></div>
            <p x-show="processing" class="py-8 text-center text-zinc-200">Confirming your payment…</p>
            <p x-show="error" x-text="error" role="alert" class="mt-3 rounded-lg bg-red-500/15 p-3 text-sm text-red-200"></p>
        </div>

        <p class="mt-5 text-xs text-zinc-400">Your Founder number is assigned only after our server confirms the PayPal payment.</p>

        <div class="mt-8 flex items-center border-t border-white/10 pt-6">
            <flux:button wire:click="back" variant="ghost" icon="arrow-left">Back to the form</flux:button>
        </div>
    @endif

    <x-slot:summary>
        <x-order-summary :tier="$this->selectedTier" :couple="$couple_addon || $this->nameNeedsAddon" :total="$this->total" :fee="\App\Services\Registry::processingFeeCents()" />
    </x-slot:summary>
</x-claim-shell>
