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
    <section class="mx-auto max-w-6xl px-4 pb-16 pt-14 sm:px-6 sm:pt-20">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-gold">From the {{ config('lunar.podcast') }} podcast</p>
                <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-white sm:text-6xl">Make Space Great Again.</h1>
                <p class="mt-4 text-xl text-zinc-200">Put your name on the Founders Plaque of Mr SuperTrump's Lunar Society.</p>
                <p class="mt-4 text-zinc-300">
                    Only 1,000 names will be laser engraved onto our aerospace-grade aluminum plaque. Our mission: get it to the Moon.
                    Join the founders and follow the whole journey on the show.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <flux:button href="{{ route('claim') }}" variant="primary" class="text-base">CLAIM YOUR FOUNDER SLOT</flux:button>
                    <p class="text-sm text-zinc-400">Takes about a minute. Secure checkout with PayPal.</p>
                </div>
            </div>

            <div class="space-y-4">
                <div aria-hidden="true" class="relative mx-auto aspect-[23/17] w-full max-w-md rounded-xl border border-zinc-400/40 bg-gradient-to-br from-zinc-300 via-zinc-100 to-zinc-400 p-5 shadow-2xl shadow-gold/10">
                    <p class="text-center text-[10px] font-bold uppercase tracking-[0.3em] text-zinc-700">Mr SuperTrump's Lunar Society</p>
                    <p class="text-center text-xs font-semibold text-zinc-600">Founders Plaque</p>
                    <div class="mt-3 grid grid-cols-4 gap-x-3 gap-y-1">
                        @foreach (range(1, 32) as $i)
                            <span class="h-1 rounded bg-zinc-500/50"></span>
                        @endforeach
                    </div>
                    <p class="absolute inset-x-0 bottom-3 text-center text-[9px] tracking-widest text-zinc-600">1,000 NAMES · 11.5" × 8.5" · 6061-T6 ALUMINUM</p>
                </div>
                <livewire:slot-counter variant="stat" />
            </div>
        </div>
    </section>

    {{-- Tiers --}}
    <section aria-labelledby="tiers-heading" class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 id="tiers-heading" class="text-center text-3xl font-bold text-white">Choose your place on the plaque</h2>
        <div class="mt-10 grid gap-6 md:grid-cols-2">
            @foreach ($this->tiers as $tier)
                <x-tier-card :tier="$tier" :featured="$tier->code === 'alpha'" />
            @endforeach
        </div>
        <p class="mt-4 text-center text-sm text-zinc-400">Couple or family name in one slot? Add it for +${{ number_format(config('lunar.couple_addon_cents') / 100) }}.</p>
    </section>

    {{-- Why 1,000 --}}
    <section class="mx-auto mt-24 max-w-3xl px-4 text-center sm:px-6">
        <h2 class="text-3xl font-bold text-white">Why only 1,000?</h2>
        <p class="mt-4 text-lg text-zinc-300">
            The plaque is 11.5" x 8.5". At a readable engraving size, that fits exactly 1,000 names in a four-column grid.
            Once slot #1000 is claimed, the plaque is closed for good.
        </p>
    </section>

    {{-- Mission roadmap --}}
    <section aria-labelledby="mission-heading" class="mx-auto mt-24 max-w-6xl px-4 sm:px-6">
        <h2 id="mission-heading" class="text-center text-3xl font-bold text-white">The mission</h2>
        <ol class="mt-10 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['Fill all 1,000 slots', 'Founders claim their place. Every name is listed on our public Founders page.'],
                ['Engrave and unveil', 'Once the plaque is full we engrave it and unveil it live on the podcast. Target: 2027 to 2028.'],
                ['Aim for the Moon', 'We pursue a commercial lunar payload slot as commercial Moon missions mature, likely in the mid-2030s. Until then, and if it never flies, the plaque is displayed in the studio.'],
            ] as $i => [$heading, $body])
                <li class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <span class="grid size-9 place-items-center rounded-full bg-gold font-bold text-[#070b1a]">{{ $i + 1 }}</span>
                    <h3 class="mt-4 text-lg font-semibold text-white">{{ $heading }}</h3>
                    <p class="mt-2 text-sm text-zinc-300">{{ $body }}</p>
                </li>
            @endforeach
        </ol>
        <p class="mt-6 text-center text-sm text-zinc-400">
            The Moon is our goal, not a guarantee. <a href="{{ route('faq') }}" class="underline hover:text-white">Read the FAQ</a> for the details.
        </p>
    </section>

    @if (config('lunar.pebble_included'))
        <section class="mx-auto mt-24 max-w-3xl px-4 text-center sm:px-6">
            <h2 class="text-3xl font-bold text-white">The ultimate out-of-this-world Christmas gift</h2>
            <p class="mt-4 text-lg text-zinc-300">
                Every Founder slot ships with a commemorative space pebble in a gift box, plus a printed certificate.
                Order by {{ config('lunar.shipping_cutoffs.standard') }} for standard shipping or {{ config('lunar.shipping_cutoffs.expedited') }} for expedited delivery before Christmas.
            </p>
        </section>
    @endif

    {{-- Closer --}}
    <section class="mx-auto mt-24 max-w-3xl px-4 text-center sm:px-6">
        <h2 class="text-3xl font-bold text-white">Don't get left on the launchpad.</h2>
        <div class="mt-6">
            <flux:button href="{{ route('claim') }}" variant="primary" class="text-base">CLAIM YOUR FOUNDER SLOT</flux:button>
        </div>
    </section>
</div>
