<?php

use App\Models\Tier;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::site')] #[Title('Make Space Great Again')] class extends Component {
    #[Computed]
    public function tiers()
    {
        return Tier::orderBy('slot_start')->get();
    }
}; ?>

<div>
    {{-- Hero --}}
    <section class="mx-auto max-w-6xl px-4 pb-16 pt-16 sm:px-6 sm:pt-20">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <p class="eyebrow">From the {{ config('lunar.podcast') }} podcast</p>
                <h1 class="mt-5 font-display text-6xl leading-[0.95] text-white sm:text-7xl">
                    Make Space<br><em class="text-gold">Great</em> Again.
                </h1>
                <p class="mt-6 text-xl text-zinc-100">Put your name on the Founders Plaque of Mr SuperTrump's Lunar Society.</p>
                <p class="mt-4 text-zinc-300">
                    Only 1,000 names will be laser engraved onto our aerospace-grade aluminum plaque. Our mission: get it to the Moon.
                    Join the founders and follow the whole journey on the show.
                </p>
                <div class="mt-8">
                    <flux:button href="{{ route('claim') }}" variant="primary" icon:trailing="arrow-right" class="h-12 px-6 text-base shadow-[0_8px_30px_-8px] shadow-gold/60">
                        Claim your Founder slot
                    </flux:button>
                    <p class="mt-4 max-w-48 text-sm text-zinc-400">Takes about a minute. Secure checkout with PayPal.</p>
                </div>
            </div>

            <x-plaque-preview />
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 sm:px-6">
        <livewire:slot-counter variant="stat" />
    </section>

    {{-- Tiers --}}
    <section aria-labelledby="tiers-heading" class="mx-auto mt-24 max-w-6xl px-4 sm:px-6">
        <div class="text-center">
            <p class="eyebrow">Two tiers · One plaque</p>
            <h2 id="tiers-heading" class="mt-4 font-display text-5xl text-white">Choose your place on the plaque</h2>
            <p class="mx-auto mt-4 max-w-md text-zinc-300">Founder numbers are assigned in order. Every name is laser engraved, one person per slot.</p>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-2">
            @foreach ($this->tiers as $tier)
                <x-tier-card :tier="$tier" />
            @endforeach
        </div>
    </section>

    {{-- How it works --}}
    <section aria-label="How it works" class="mx-auto mt-24 max-w-6xl px-4 sm:px-6">
        <ol class="panel grid divide-y divide-white/10 md:grid-cols-3 md:divide-x md:divide-y-0">
            @foreach ([
                ['Claim', 'Pick a tier and enter your name', 'Exactly as it should be engraved, max '.config('lunar.name_max_length').' characters.'],
                ['Engrave', '1,000 names, one plaque', 'Laser engraved in Founder number order on aerospace-grade aluminum.'],
                ['Follow', 'Follow the journey on the show', 'Every step toward the Moon is covered on '.config('lunar.podcast').'.'],
            ] as $i => [$label, $heading, $body])
                <li class="p-6">
                    <p class="eyebrow">{{ sprintf('%02d', $i + 1) }} · {{ $label }}</p>
                    <h3 class="mt-3 text-lg font-semibold text-white">{{ $heading }}</h3>
                    <p class="mt-2 text-sm text-zinc-400">{{ $body }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    @if (config('lunar.pebble_included'))
        <section class="mx-auto mt-24 max-w-3xl px-4 text-center sm:px-6">
            <h2 class="font-display text-4xl text-white">The ultimate out-of-this-world Christmas gift</h2>
            <p class="mt-4 text-lg text-zinc-300">
                Every Founder slot ships with a commemorative space pebble in a gift box, plus a printed certificate.
                Order by {{ config('lunar.shipping_cutoffs.standard') }} for standard shipping or {{ config('lunar.shipping_cutoffs.expedited') }} for expedited delivery before Christmas.
            </p>
        </section>
    @endif
</div>
