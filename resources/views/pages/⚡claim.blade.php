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
use Livewire\Attributes\Session;
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

    // Answers are kept in the session so a page reload doesn't wipe the form.
    #[Session(key: 'claim.tier')]
    public string $tierCode = 'standard';
    #[Session(key: 'claim.plaque_name')]
    public string $plaque_name = '';
    #[Session(key: 'claim.couple_addon')]
    public bool $couple_addon = false;
    #[Session(key: 'claim.buyer_name')]
    public string $buyer_name = '';
    #[Session(key: 'claim.email')]
    public string $email = '';
    #[Session(key: 'claim.phone')]
    public string $phone = '';
    #[Session(key: 'claim.address_line1')]
    public string $address_line1 = '';
    #[Session(key: 'claim.address_line2')]
    public string $address_line2 = '';
    #[Session(key: 'claim.city')]
    public string $city = '';
    #[Session(key: 'claim.state')]
    public string $state = '';
    #[Session(key: 'claim.postal_code')]
    public string $postal_code = '';
    #[Session(key: 'claim.country')]
    public string $country = 'US';
    #[Session(key: 'claim.us_citizen')]
    public bool $us_citizen = false;
    #[Session(key: 'claim.consent_terms')]
    public bool $consent_terms = false;
    #[Session(key: 'claim.consent_spelling')]
    public bool $consent_spelling = false;
    #[Session(key: 'claim.marketing_opt_in')]
    public bool $marketing_opt_in = false;
    #[Session(key: 'claim.signature')]
    public string $signature = '';

    // The held order, so a reload during payment goes straight back to PayPal.
    #[Locked]
    #[Session(key: 'claim.order')]
    public ?string $orderUuid = null;

    public function mount(?string $tier = null): void
    {
        $tiers = $this->tiers;
        $requested = $tiers->firstWhere('code', $tier);
        $current = $tiers->firstWhere('code', $this->tierCode);

        if ($requested && ! $requested->isSoldOut()) {
            $this->tierCode = $requested->code;
        } elseif (! $current || $current->isSoldOut()) {
            $this->tierCode = $tiers->first(fn (Tier $t) => ! $t->isSoldOut())?->code ?? 'standard';
        }

        $this->resumeOrder();
    }

    /**
     * Pick up where the buyer left off: back on the payment step while the hold
     * is alive, or a fresh form once the order is paid.
     */
    protected function resumeOrder(): void
    {
        $order = $this->order;

        if (! $order) {
            $this->orderUuid = null;

            return;
        }

        if ($order->isPaid()) {
            $this->reset();
            unset($this->order);
            $this->mount();

            return;
        }

        if ($order->status === OrderStatus::Pending && $order->hold_expires_at?->isFuture()) {
            $this->step = 2;

            return;
        }

        // The hold lapsed: keep the answers, start the payment over.
        $this->orderUuid = null;
        unset($this->order);
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
            'buyer_name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9 ().\-]{7,25}$/'],
            'address_line1' => ['required', 'string', 'max:150'],
            'address_line2' => ['nullable', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'in:'.implode(',', array_keys(config('countries')))],
            'state' => $this->country === 'US'
                ? ['required', 'in:'.implode(',', array_keys(self::STATES))]
                : ['required', 'string', 'max:100'],
            'postal_code' => $this->country === 'US'
                ? ['required', 'regex:/^\d{5}(-\d{4})?$/']
                : ['required', 'string', 'max:20'],
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
            'phone.regex' => 'Enter a phone number using digits, spaces, +, ( ) or -.',
            'state.in' => 'Choose a state.',
            'us_citizen.accepted' => 'Registration is open to US citizens only.',
            'consent_terms.accepted' => 'Please confirm you have read the Charter and agree to the terms.',
            'consent_spelling.accepted' => 'Please confirm the spelling of the name.',
            'signature.required' => 'Type your full name to sign the application.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'plaque_name' => 'name on the certificate',
            'tierCode' => 'membership',
            'buyer_name' => 'full name',
            'address_line1' => 'street address',
            'address_line2' => 'apartment or suite',
            'postal_code' => $this->country === 'US' ? 'ZIP code' : 'postal code',
        ];
    }

    public function updatedPlaqueName(): void
    {
        $this->plaque_name = preg_replace('/\s+/', ' ', ltrim($this->plaque_name));

        // A couple or family name always needs the add-on.
        if ($this->nameNeedsAddon) {
            $this->couple_addon = true;
        }
    }

    public function updatedCountry(): void
    {
        // US uses state codes from the list; other countries type a region.
        $this->state = '';
        $this->resetValidation(['state', 'postal_code']);
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
                'buyer_name' => trim($this->buyer_name),
                'phone' => trim($this->phone),
                'address_line1' => trim($this->address_line1),
                'address_line2' => trim($this->address_line2) ?: null,
                'city' => trim($this->city),
                'country' => $this->country,
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

            {{-- II. Contact --}}
            <fieldset class="mt-8 border-t border-white/10 pt-7">
                <legend class="sr-only">II. Contact</legend>
                <p class="font-charter-title text-sm font-bold uppercase tracking-[0.25em] text-gold">II. Contact</p>
                <div class="mt-5 grid gap-x-4 gap-y-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="buyer_name" label="Full name" description="Your own full legal name." autocomplete="name" required />
                    </div>
                    <div>
                        <flux:input wire:model="email" type="email" label="Email address" autocomplete="email" required />
                    </div>
                    <div>
                        <flux:input wire:model="phone" type="tel" label="Phone number" autocomplete="tel" required />
                    </div>
                </div>
                <p class="mt-2 text-sm text-zinc-400">Your receipt and certificate are sent to this email.</p>
            </fieldset>

            {{-- III. Shipping address --}}
            <fieldset class="mt-8 border-t border-white/10 pt-7">
                <legend class="sr-only">III. Shipping address</legend>
                <p class="font-charter-title text-sm font-bold uppercase tracking-[0.25em] text-gold">III. Shipping address</p>
                <div class="mt-5 grid gap-x-4 gap-y-5 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <flux:input wire:model="address_line1" label="Street address" autocomplete="address-line1" required />
                    </div>
                    <div class="sm:col-span-2">
                        <flux:input wire:model="address_line2" label="Apt / Suite" placeholder="Optional" autocomplete="address-line2" />
                    </div>
                    <div class="sm:col-span-6">
                        <flux:input wire:model="city" label="City" autocomplete="address-level2" required />
                    </div>
                    @if ($country === 'US')
                        <div class="sm:col-span-4">
                            <flux:select wire:model="state" label="State" autocomplete="address-level1" required>
                                <flux:select.option value="">Choose a state</flux:select.option>
                                @foreach ($this::STATES as $code => $name)
                                    <flux:select.option value="{{ $code }}">{{ $name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div class="sm:col-span-2">
                            <flux:input wire:model="postal_code" label="ZIP code" inputmode="numeric" autocomplete="postal-code" maxlength="10" required />
                        </div>
                    @else
                        <div class="sm:col-span-4">
                            <flux:input wire:model="state" label="State / Province / Region" autocomplete="address-level1" required />
                        </div>
                        <div class="sm:col-span-2">
                            <flux:input wire:model="postal_code" label="Postal code" autocomplete="postal-code" maxlength="20" required />
                        </div>
                    @endif
                    <div class="sm:col-span-6">
                        <flux:select wire:model.live="country" label="Country" autocomplete="country" required>
                            @foreach (config('countries') as $code => $name)
                                <flux:select.option value="{{ $code }}">{{ $name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </fieldset>

            {{-- Choose membership --}}
            <fieldset class="mt-8 border-t border-white/10 pt-7">
                <legend class="font-charter-title text-sm font-bold uppercase tracking-[0.25em] text-gold">Choose membership</legend>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($this->tiers as $tier)
                        @php
                            $active = $tierCode === $tier->code;
                            $label = $tier->code === 'alpha' ? 'A: Alpha' : 'B: Beta';
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
                <p class="mt-3 text-sm text-zinc-400">Plus {{ \App\Services\Registry::feeExplained() }} per order.</p>
                <flux:error name="tierCode" class="mt-2" />
            </fieldset>

            {{-- Acknowledgement and consents --}}
            <div class="mt-8 space-y-4 border-t border-white/10 pt-7">
                <p class="font-charter text-xl leading-relaxed text-zinc-200">{{ config('lunar.acknowledgement') }}</p>

                <flux:field variant="inline">
                    <flux:checkbox wire:model="us_citizen" />
                    <flux:label class="!leading-snug"><span class="block">{{ config('lunar.citizenship') }}</span></flux:label>
                    <flux:error name="us_citizen" />
                </flux:field>

                <flux:field variant="inline">
                    <flux:checkbox wire:model="consent_terms" />
                    <flux:label class="!leading-snug"><span class="block">
                        I have read the <a href="{{ route('charter') }}" target="_blank" class="text-gold underline underline-offset-2">Charter</a> and agree to the
                        <a href="{{ route('terms') }}" target="_blank" class="text-gold underline underline-offset-2">Terms of Service</a>.
                        I understand and agree that this is an independent project unaffiliated with Donald J. Trump or any government agency, as detailed in the
                        <a href="{{ route('disclaimer') }}" target="_blank" class="text-gold underline underline-offset-2">Disclaimer</a>.</span>
                    </flux:label>
                    <flux:error name="consent_terms" />
                </flux:field>

                <flux:field variant="inline">
                    <flux:checkbox wire:model="consent_spelling" />
                    <flux:label class="!leading-snug"><span class="block">{{ config('lunar.consent_spelling') }}</span></flux:label>
                    <flux:error name="consent_spelling" />
                </flux:field>

                <flux:field variant="inline">
                    <flux:checkbox wire:model="marketing_opt_in" />
                    <flux:label class="!leading-snug"><span class="block">{{ config('lunar.marketing_consent') }} <span class="text-zinc-400">(Optional)</span></span></flux:label>
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
                    ...config('lunar.founder_package'),
                    'Your name laser engraved on the Founders Plaque and on the public Founders list',
                    'The Lunar Society Network newsletter, if you opt in',
                    $tierCode === 'alpha' ? 'Alpha: a top-row position, #001 to #050, and eligibility for board membership' : null,
                ]) as $perk)
                    <li class="flex gap-3">
                        <span aria-hidden="true" class="mt-1.5 size-1.5 shrink-0 rotate-45 bg-gold"></span>
                        <span>{{ $perk }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-5 rounded-lg border border-gold/30 bg-gold/5 p-3 text-sm leading-relaxed text-zinc-200">
                <span class="font-semibold text-gold">Holiday Gift Delivery Notice:</span> {{ config('lunar.delivery_notice') }}
            </p>
        </div>

        @if (\App\Models\Product::where('active', true)->exists())
            <a href="{{ route('souvenirs') }}" class="group mt-4 flex items-center gap-4 rounded-xl border border-gold/30 bg-gold/5 p-4 text-sm transition hover:border-gold/60">
                <x-pebble-art class="size-14 shrink-0" />
                <span class="min-w-0 flex-1 text-zinc-200">
                    <span class="block font-medium text-white">Also available: Booster, Follower, Supporter and Trade Associate</span>
                    Get on our lists, with a souvenir or Lunar Moon Dust for your collection. No Founder slot.
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
