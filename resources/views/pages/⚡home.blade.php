<?php

use App\Models\Product;
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

    #[Computed]
    public function pebble(): ?Product
    {
        return Product::firstWhere('slug', 'space-pebble');
    }
}; ?>

<div>
    {{-- Hero: centered copy over a lightly blurred copy of the artwork. The blur and the
         dark vignette behind the text keep the plaque's lettering from competing with the headline. --}}
    <section class="relative isolate overflow-hidden">
        <div aria-hidden="true" class="absolute inset-0 -z-10">
            <img src="{{ asset('images/hero-backdrop.webp') }}" alt="" fetchpriority="high" class="hero-drift size-full object-cover">
            <div class="absolute inset-0 bg-ink/55"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(7,10,18,0.75)_0%,rgba(7,10,18,0.35)_55%,transparent_80%)]"></div>
            <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent to-ink"></div>
            <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-ink/70 to-transparent"></div>
        </div>

        <div class="mx-auto flex min-h-[78svh] max-w-3xl flex-col items-center justify-center px-4 pb-24 pt-20 text-center sm:px-6 sm:pt-28">
            <p class="eyebrow animate-rise">From the {{ config('lunar.podcast') }} podcast</p>
            <h1 class="animate-rise mt-5 font-display text-6xl leading-[0.95] text-white drop-shadow-[0_2px_24px_rgba(0,0,0,0.7)] sm:text-8xl" style="--reveal-delay: 120ms">
                Make Space <em class="text-gold text-gold-sheen pe-1">Great</em> Again.
            </h1>
            <p class="animate-rise mt-6 text-xl text-zinc-100 drop-shadow-[0_1px_12px_rgba(0,0,0,0.8)] sm:text-2xl" style="--reveal-delay: 260ms">
                Put your name on the Founders Plaque of Mr SuperTrump's Lunar Society.
            </p>
            <p class="animate-rise mt-4 max-w-2xl text-zinc-200 drop-shadow-[0_1px_12px_rgba(0,0,0,0.8)]" style="--reveal-delay: 380ms">
                Only 1,000 names will be laser engraved onto our aerospace-grade aluminum plaque. Our mission: get it to the Moon.
                Join the founders and follow the whole journey on the show.
            </p>
            <div class="animate-rise mt-9 flex flex-col items-center" style="--reveal-delay: 500ms">
                <flux:button href="{{ route('claim') }}" variant="primary" icon:trailing="arrow-right" class="animate-glow h-12 px-7 text-base">
                    Claim your Founder slot
                </flux:button>
                <p class="mt-4 text-sm text-zinc-300">Takes about a minute. Secure checkout with PayPal.</p>
            </div>
        </div>

        <p class="absolute bottom-4 right-4 font-mono text-[10px] uppercase tracking-[0.2em] text-zinc-400/80 sm:right-6">Background: artist's concept</p>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6" data-reveal>
        <livewire:slot-counter variant="stat" />
    </section>

    {{-- The society --}}
    <section aria-labelledby="society-heading" class="mx-auto mt-24 max-w-7xl px-4 sm:px-6">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-16">
            <div data-reveal>
                <p class="eyebrow">The Lunar Society</p>
                <h2 id="society-heading" class="mt-4 font-display text-5xl leading-[1.05] text-white">A society for the Moon base of the future</h2>
                <p class="mt-5 text-zinc-400">
                    Named in the spirit of the Lunar Society of Birmingham, the celebrated 18th-century circle of British inventors and
                    thinkers who met by the light of the full moon.
                </p>
            </div>
            <div class="space-y-5 text-lg leading-relaxed text-zinc-200" data-reveal style="--reveal-delay: 150ms">
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

                <a href="{{ route('about') }}" class="hover-lift group !mt-8 flex items-center gap-4 rounded-2xl border border-white/10 bg-panel p-4 text-base">
                    @if (config('lunar.host_portrait'))
                        <img src="{{ asset(config('lunar.host_portrait')) }}" alt="" class="size-16 shrink-0 rounded-full object-cover">
                    @else
                        <span class="hatch grid size-16 shrink-0 place-items-center rounded-full border border-white/10"><x-moon-mark class="size-8" /></span>
                    @endif
                    <span class="min-w-0 flex-1">
                        <span class="block font-mono text-[11px] uppercase tracking-[0.2em] text-gold">Executive Director</span>
                        <span class="mt-1 block font-display text-2xl text-white">MJ Schultz</span>
                        <span class="block text-sm text-zinc-400">Host of {{ config('lunar.podcast') }}. Author, actor, writer and philanthropist.</span>
                    </span>
                    <flux:icon.arrow-right variant="micro" class="size-5 shrink-0 text-zinc-500 transition group-hover:translate-x-1 group-hover:text-gold" />
                    <span class="sr-only">Read his profile</span>
                </a>
            </div>
        </div>
    </section>

    {{-- The plaque pledge --}}
    <section aria-labelledby="pledge-heading" class="mx-auto mt-24 max-w-7xl px-4 sm:px-6">
        <div class="text-center" data-reveal>
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
            ] as $i => [$heading, $body])
                <div class="flex" data-reveal style="--reveal-delay: {{ $i * 140 }}ms">
                    <article class="panel hover-lift w-full p-6 sm:p-7">
                        <h3 class="font-display text-2xl text-white">{{ $heading }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-zinc-300">{{ $body }}</p>
                    </article>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Tiers --}}
    <section id="tiers" aria-labelledby="tiers-heading" class="mx-auto mt-24 max-w-7xl scroll-mt-6 px-4 sm:px-6">
        <div class="text-center" data-reveal>
            <p class="eyebrow">Two tiers · One plaque</p>
            <h2 id="tiers-heading" class="mt-4 font-display text-5xl text-white">Choose your place on the plaque</h2>
            <p class="mx-auto mt-4 max-w-md text-zinc-300">Founder numbers are assigned in order. Every name is laser engraved, one person per slot.</p>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-2">
            @foreach ($this->tiers as $tier)
                {{-- Reveal on the wrapper, lift on the card, so the two transforms don't fight. --}}
                <div class="flex" data-reveal style="--reveal-delay: {{ $loop->index * 160 }}ms">
                    <x-tier-card :tier="$tier" class="hover-lift w-full" />
                </div>
            @endforeach
        </div>
    </section>

    {{-- Space Pebble Collectible (sold separately) --}}
    @if ($this->pebble)
        <x-pebble-feature :product="$this->pebble" class="mt-24" />
    @endif

    {{-- How it works --}}
    <section aria-label="How it works" class="mx-auto mt-24 max-w-7xl px-4 sm:px-6">
        <ol class="panel grid divide-y divide-white/10 md:grid-cols-3 md:divide-x md:divide-y-0" data-reveal>
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
    <section aria-labelledby="newsletter-heading" class="mx-auto mt-24 max-w-7xl px-4 sm:px-6">
        <div class="grid gap-8 rounded-2xl border border-white/10 bg-gradient-to-br from-[#1a1710] via-panel to-[#0f1428] p-7 sm:p-12 lg:grid-cols-2" data-reveal>
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
    <section class="mx-auto mt-24 max-w-3xl px-4 text-center sm:px-6" data-reveal>
        <p class="eyebrow">Secure your founding spot</p>
        <h2 class="mt-4 font-display text-5xl text-white">Claim 1 of the 1,000 founding spots before they're gone</h2>
        <p class="mx-auto mt-5 max-w-xl text-zinc-300">
            Join Executive Director MJ Schultz and space enthusiasts across the country. Put your name on the society plaque, get the
            newsletter, and tune in to the semi-monthly {{ config('lunar.podcast') }} podcast for roadmap updates.
        </p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <flux:button href="{{ route('claim') }}" variant="primary" icon:trailing="arrow-right" class="animate-glow h-12 px-6 text-base">Join the registry</flux:button>
            <flux:button href="#tiers" variant="outline" class="h-12 px-6 text-base">See what founders receive</flux:button>
        </div>
    </section>
</div>
