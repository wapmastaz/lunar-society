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

    {{-- The society --}}
    <section aria-labelledby="society-heading" class="mx-auto mt-24 max-w-6xl px-4 sm:px-6">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-16">
            <div>
                <p class="eyebrow">The Lunar Society</p>
                <h2 id="society-heading" class="mt-4 font-display text-5xl leading-[1.05] text-white">A society for the Moon base of the future</h2>
                <p class="mt-5 text-zinc-400">
                    Named in the spirit of the Lunar Society of Birmingham, the celebrated 18th-century circle of British inventors and
                    thinkers who met by the light of the full moon.
                </p>
            </div>
            <div class="space-y-5 text-lg leading-relaxed text-zinc-200">
                <p>
                    The Lunar Society Registry is the home of the Society for the Future Establishment of the Trumpian Lunar International
                    Moon Base, a base we hope to see built in the years ahead. All visitors are welcome.
                </p>
                <p>
                    The society is led by Executive Director MJ Schultz, the creative voice behind the {{ config('lunar.podcast') }} podcast,
                    and an author, actor, writer and philanthropist. It pairs bold space advocacy with the momentum of today's return to the Moon.
                </p>
                <p>
                    We are officially seeking our 1,000 foundational MAGA community Trump supporters to back the Trumpian Moon Base agenda.
                    Secure your place in the registry today and you're not just watching history unfold, you're etching your name into it.
                    It also makes one of the most novel gifts of the holiday season.
                </p>
            </div>
        </div>
    </section>

    {{-- The plaque pledge --}}
    <section aria-labelledby="pledge-heading" class="mx-auto mt-24 max-w-6xl px-4 sm:px-6">
        <div class="text-center">
            <p class="eyebrow">The Founder's Legacy</p>
            <h2 id="pledge-heading" class="mt-4 font-display text-5xl text-white">The Astronaut Plaque Pledge</h2>
            <p class="mx-auto mt-4 max-w-xl text-zinc-300">
                We are micro-funding a physical society plaque that we hope an astronaut will one day carry to the lunar surface.
                It is strictly limited to our first 1,000 founding members.
            </p>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['Your name on the plaque', 'For '.$this->tiers->firstWhere('code', 'standard')?->priceLabel().', or '.$this->tiers->firstWhere('code', 'alpha')?->priceLabel().' for an Alpha slot, your name is permanently engraved on the official registry plaque, in Founder number order.'],
                ['The destination we aim for', "Our goal is the resource-rich lunar South Pole, within the boundaries of a future international Moon base. No such base exists yet, and no flight can be guaranteed."],
                ['The countdown to '.config('lunar.target_date_short'), 'MJ Schultz has set '.config('lunar.target_date').' as the society\'s target for installing the plaque. Permanent lunar bases are planned for the 2030s, so until one is ready, MJ Schultz keeps the plaque safe in the podcast studio.'],
            ] as [$heading, $body])
                <article class="panel p-6 sm:p-7">
                    <h3 class="font-display text-2xl text-white">{{ $heading }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-300">{{ $body }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Tiers --}}
    <section id="tiers" aria-labelledby="tiers-heading" class="mx-auto mt-24 max-w-6xl scroll-mt-6 px-4 sm:px-6">
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

    {{-- Newsletter --}}
    <section aria-labelledby="newsletter-heading" class="mx-auto mt-24 max-w-6xl px-4 sm:px-6">
        <div class="grid gap-8 rounded-2xl border border-white/10 bg-gradient-to-br from-[#1a1710] via-panel to-[#0f1428] p-7 sm:p-12 lg:grid-cols-2">
            <div>
                <p class="eyebrow">Stay connected</p>
                <h2 id="newsletter-heading" class="mt-4 font-display text-4xl text-white sm:text-5xl">The Lunar Society newsletter</h2>
                <p class="mt-4 text-zinc-300">
                    The road to {{ config('lunar.target_date_short') }} is a long-term mission. Every registered founder receives our newsletter
                    on Beehiiv to stay locked into the updates over the years. Unsubscribe anytime.
                </p>
            </div>
            <ul class="space-y-4 self-center text-zinc-200">
                @foreach ([
                    'Milestone updates on the Trumpian lunar roadmap',
                    'Behind-the-scenes insights directly from Mr SuperTrump',
                    'Analysis of global space agency developments and lunar surface surveys',
                ] as $item)
                    <li class="flex gap-3">
                        <span aria-hidden="true" class="mt-2 size-1.5 shrink-0 rotate-45 bg-gold"></span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Closer --}}
    <section class="mx-auto mt-24 max-w-3xl px-4 text-center sm:px-6">
        <p class="eyebrow">Secure your founding spot</p>
        <h2 class="mt-4 font-display text-5xl text-white">Claim 1 of the 1,000 founding spots before they're gone</h2>
        <p class="mx-auto mt-5 max-w-xl text-zinc-300">
            Join Executive Director MJ Schultz and space enthusiasts across the country. Put your name on the society plaque, get the
            newsletter, and tune in to the semi-monthly {{ config('lunar.podcast') }} podcast for roadmap updates.
        </p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <flux:button href="{{ route('claim') }}" variant="primary" icon:trailing="arrow-right" class="h-12 px-6 text-base">Join the registry</flux:button>
            <flux:button href="#tiers" variant="outline" class="h-12 px-6 text-base">See what founders receive</flux:button>
        </div>
    </section>
</div>
