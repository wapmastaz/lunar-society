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
    public int $step = 1;

    public string $tierCode = 'standard';
    public string $buyer_name = '';
    public string $email = '';
    public string $listener_handle = '';
    public string $plaque_name = '';
    public bool $couple_addon = false;
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
    public function nameNeedsAddon(): bool
    {
        return Registry::requiresCoupleAddon($this->plaque_name);
    }

    #[Computed]
    public function total(): string
    {
        $cents = $this->selectedTier
            ? app(Registry::class)->priceFor($this->selectedTier, $this->couple_addon || $this->nameNeedsAddon)
            : 0;

        return '$'.number_format($cents / 100);
    }

    #[Computed]
    public function previewSlot(): ?string
    {
        $n = $this->selectedTier ? app(Registry::class)->nextFreeSlot($this->selectedTier) : null;

        return $n ? sprintf('#%03d', $n) : null;
    }

    #[Computed]
    public function order(): ?Order
    {
        return $this->orderUuid ? Order::with('tier')->firstWhere('uuid', $this->orderUuid) : null;
    }

    /**
     * Validation rules for each step; the review step re-checks everything.
     */
    protected function stepRules(int $step): array
    {
        $rules = [
            1 => [
                'tierCode' => ['required', 'exists:tiers,code'],
            ],
            2 => [
                'buyer_name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email:rfc', 'max:255'],
                'listener_handle' => ['nullable', 'string', 'max:100'],
            ],
            3 => [
                'plaque_name' => ['required', 'string', 'min:2', 'max:'.config('lunar.name_max_length'), "regex:/^[A-Za-z][A-Za-z .'&\-]*$/"],
                'couple_addon' => ['boolean'],
                'show_full_name' => ['boolean'],
            ],
            4 => [
                'consent_spelling' => ['accepted'],
                'consent_terms' => ['accepted'],
            ],
        ];

        return $step === 4 ? array_merge(...array_values($rules)) : $rules[$step];
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
        return ['plaque_name' => 'name for the plaque', 'tierCode' => 'tier', 'buyer_name' => 'full name'];
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

    public function next(): void
    {
        if ($this->step === 1 && $this->selectedTier?->isSoldOut()) {
            $this->addError('tierCode', "{$this->selectedTier->name} slots are sold out. Please choose another tier.");

            return;
        }

        if ($this->step === 3) {
            $this->plaque_name = trim($this->plaque_name);
        }

        $this->validate($this->stepRules($this->step));
        $this->step++;
    }

    public function back(): void
    {
        if ($this->step === 5) {
            $this->releaseHold();
        }

        $this->step = max(1, $this->step - 1);
    }

    public function goTo(int $step): void
    {
        if ($step >= 1 && $step < $this->step && $this->step <= 4) {
            $this->step = $step;
        }
    }

    public function reserve(Registry $registry): void
    {
        $this->validate($this->stepRules(4));

        $key = 'reserve:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('consent_terms', 'Too many reservations from this connection. Please wait a few minutes.');

            return;
        }

        RateLimiter::hit($key, 600);

        try {
            $order = $registry->reserve($this->selectedTier, [
                'email' => $this->email,
                'buyer_name' => trim($this->buyer_name),
                'plaque_name' => trim($this->plaque_name),
                'couple_addon' => $this->couple_addon,
                'listener_handle' => $this->listener_handle ?: null,
                'show_full_name' => $this->show_full_name,
                'ip_address' => request()->ip(),
            ]);
        } catch (SoldOutException) {
            unset($this->tiers, $this->selectedTier);
            $this->step = 1;
            $this->addError('tierCode', "{$this->selectedTier->name} slots just sold out. Please choose another tier.");

            return;
        }

        $this->orderUuid = $order->uuid;
        $this->step = 5;
    }

    protected function releaseHold(): void
    {
        // Give the slot back to the pool right away.
        if ($this->order?->status === OrderStatus::Pending) {
            $this->order->update(['status' => OrderStatus::Expired]);
        }

        $this->orderUuid = null;
        unset($this->order);
    }
}; ?>

<x-claim-shell :step="$step" jump>
    @if ($step === 1)
        <h2 class="text-2xl font-semibold text-white">Choose your tier</h2>
        <p class="mt-1 text-zinc-400">Founder numbers are assigned in order within each tier.</p>

        <fieldset class="mt-7">
            <legend class="sr-only">Tier</legend>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($this->tiers as $tier)
                    @php($active = $tierCode === $tier->code)
                    <label @class([
                        'relative flex cursor-pointer flex-col rounded-xl border p-6 transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-gold',
                        'border-gold bg-white/[0.06]' => $active,
                        'border-white/10 hover:border-white/25' => ! $active,
                        'pointer-events-none opacity-50' => $tier->isSoldOut(),
                    ])>
                        <input type="radio" wire:model.live="tierCode" value="{{ $tier->code }}" class="sr-only" @disabled($tier->isSoldOut())>
                        <span class="flex items-center justify-between">
                            <span @class(['font-mono text-xs uppercase tracking-[0.2em]', 'text-gold' => $tier->code === 'alpha', 'text-zinc-300' => $tier->code !== 'alpha'])>
                                Slots {{ sprintf('#%03d–#%03d', $tier->slot_start, $tier->slot_end) }}
                            </span>
                            <span aria-hidden="true" @class(['grid size-5 place-items-center rounded-full border', 'border-gold' => $active, 'border-zinc-600' => ! $active])>
                                @if ($active)<span class="size-2.5 rounded-full bg-gold"></span>@endif
                            </span>
                        </span>
                        <span class="mt-5 text-xl font-semibold text-white">{{ $tier->name }}</span>
                        <span class="mt-4 flex items-baseline justify-between">
                            <span class="text-3xl font-bold text-white">{{ $tier->priceLabel() }}</span>
                            <span class="text-sm text-zinc-400">{{ $tier->isSoldOut() ? 'Sold out' : number_format($tier->remaining()).' left' }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <flux:error name="tierCode" class="mt-3" />
        </fieldset>

        <div class="mt-7 rounded-xl border border-white/10 bg-white/[0.03] p-5">
            <p class="font-mono text-xs uppercase tracking-[0.2em] text-zinc-400">Every founder receives</p>
            <ul class="mt-4 grid gap-3 text-sm text-zinc-200 sm:grid-cols-2">
                @foreach (array_filter([
                    'Your name laser engraved on the Founders Plaque',
                    'A numbered digital Founder certificate',
                    'Your name on the public Founders list',
                    'The Lunar Society newsletter on Beehiiv',
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
    @elseif ($step === 2)
        <h2 class="text-2xl font-semibold text-white">Your details</h2>
        <p class="mt-1 text-zinc-400">Your digital certificate and project updates are sent here.</p>

        <div class="mt-7 space-y-6">
            <flux:input wire:model="buyer_name" label="Full name" autocomplete="name" required badge="Required" />
            <flux:input wire:model="email" type="email" label="Email address" autocomplete="email" required badge="Required" />
            <flux:input wire:model="listener_handle" label="Podcast listener handle or comment" badge="Optional" maxlength="100" />
        </div>
    @elseif ($step === 3)
        <h2 class="text-2xl font-semibold text-white">Name for the plaque</h2>
        <p class="mt-1 text-zinc-400">Enter the name exactly as it should be engraved, max {{ config('lunar.name_max_length') }} characters. One person per slot.</p>

        <div class="mt-7 space-y-6">
            <flux:field>
                <div class="flex items-center justify-between">
                    <flux:label badge="Required">Engraving</flux:label>
                    <span class="font-mono text-xs tabular-nums text-zinc-400">{{ mb_strlen($plaque_name) }}/{{ config('lunar.name_max_length') }}</span>
                </div>
                <flux:input wire:model.live.debounce.250ms="plaque_name" maxlength="{{ config('lunar.name_max_length') }}" autocomplete="off" required />
                <flux:error name="plaque_name" />
            </flux:field>

            <div class="rounded-xl border border-white/10 p-4">
                <flux:switch
                    wire:model.live="couple_addon"
                    align="left"
                    label="Couple or family name · +${{ number_format(config('lunar.couple_addon_cents') / 100) }}"
                    description='For example "Mr & Mrs John Doe" or "Doe Family".'
                />
                <flux:error name="couple_addon" />
            </div>

            <div class="rounded-xl border border-white/10 p-4">
                <flux:switch
                    wire:model="show_full_name"
                    align="left"
                    label="Show my full name on the public Founders list"
                    description="If this is off, the list shows first name and last initial."
                />
            </div>

            <div>
                <p class="font-mono text-xs uppercase tracking-[0.25em] text-zinc-400">Engraving preview</p>
                <x-engraving-preview class="mt-3" :name="$plaque_name" :number="$this->previewSlot" />
                <p class="mt-2 text-xs text-zinc-500">Preview number is the next free slot. Your Founder number is confirmed after payment.</p>
            </div>
        </div>
    @elseif ($step === 4)
        <h2 class="text-2xl font-semibold text-white">Review your order</h2>
        <p class="mt-1 text-zinc-400">Check the spelling carefully. This is what gets engraved.</p>

        <dl class="mt-7 grid grid-cols-[7rem_1fr] gap-x-4 gap-y-3 text-sm sm:text-base">
            <dt class="text-zinc-400">Tier</dt>
            <dd class="font-medium text-white">{{ $this->selectedTier->name }} · {{ sprintf('#%03d–#%03d', $this->selectedTier->slot_start, $this->selectedTier->slot_end) }}</dd>
            <dt class="text-zinc-400">Full name</dt>
            <dd class="font-medium text-white">{{ $buyer_name }}</dd>
            <dt class="text-zinc-400">Email</dt>
            <dd class="break-all font-medium text-white">{{ $email }}</dd>
            <dt class="text-zinc-400">Engraving</dt>
            <dd class="font-mono font-bold uppercase tracking-wider text-white">{{ $plaque_name }}</dd>
            <dt class="text-zinc-400">Founders list</dt>
            <dd class="text-white">{{ $show_full_name ? $plaque_name : \App\Models\Order::maskName($plaque_name) }}</dd>
        </dl>

        <div class="mt-7 space-y-4">
            <flux:field variant="inline">
                <flux:checkbox wire:model="consent_spelling" />
                <flux:label class="!leading-snug">{{ config('lunar.consent_spelling') }}</flux:label>
                <flux:error name="consent_spelling" />
            </flux:field>

            <flux:field variant="inline">
                <flux:checkbox wire:model="consent_terms" />
                <flux:label class="!leading-snug">
                    I have read and agree to the <a href="{{ route('terms') }}" target="_blank" class="text-gold underline underline-offset-2">Terms of Service</a>.
                    I understand and agree that this is an independent project unaffiliated with Donald J. Trump or any government agency, as detailed in the <a href="{{ route('disclaimer') }}" target="_blank" class="text-gold underline underline-offset-2">Disclaimer</a>.
                </flux:label>
                <flux:error name="consent_terms" />
            </flux:field>
        </div>
    @elseif ($step === 5 && $this->order)
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
    @endif

    @if ($step <= 5)
        <div class="mt-8 flex items-center justify-between border-t border-white/10 pt-6">
            <flux:button wire:click="back" variant="ghost" icon="arrow-left" :disabled="$step === 1">Back</flux:button>

            @if ($step < 4)
                <flux:button wire:click="next" variant="primary" icon:trailing="arrow-right" class="h-11 px-6">Continue</flux:button>
            @elseif ($step === 4)
                <flux:button wire:click="reserve" variant="primary" class="h-11 px-6">Reserve &amp; pay {{ $this->total }}</flux:button>
            @endif
        </div>
    @endif

    <x-slot:summary>
        <x-order-summary :tier="$this->selectedTier" :couple="$couple_addon || $this->nameNeedsAddon" :total="$this->total" />
    </x-slot:summary>
</x-claim-shell>
