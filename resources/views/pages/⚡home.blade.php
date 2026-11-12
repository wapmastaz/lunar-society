<?php

use App\Models\Product;
use App\Models\Tier;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::site', ['overlayHeader' => true])] #[Title('Be Part of History')] class extends Component {
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
    {{-- The Charter opens the site, as the client asked. Full text lives on /charter. --}}
    @php $charter = config('lunar.charter'); @endphp
    <section id="charter" aria-labelledby="charter-heading" class="relative isolate overflow-hidden px-4 pb-16 pt-10 sm:px-6 lg:pb-20 lg:pt-32">
        <div aria-hidden="true" class="absolute left-1/2 top-24 -z-10 size-[34rem] -translate-x-1/2 rounded-full bg-gold/10 blur-3xl"></div>
        <div class="mx-auto max-w-3xl text-center">
            <x-logo-mark large class="animate-rise mx-auto size-36 drop-shadow-[0_10px_40px_rgba(232,184,74,0.2)] sm:size-44" />
            <p class="animate-rise eyebrow mt-6" style="--reveal-delay: 100ms">The founding document</p>
            <h1 id="charter-heading" class="animate-rise mt-3 font-display text-4xl leading-[1.05] text-white sm:text-6xl" style="--reveal-delay: 160ms">Charter of the Lunar Society</h1>

            <p class="animate-rise mt-8 font-mono text-xs uppercase tracking-[0.25em] text-gold" style="--reveal-delay: 240ms">Preamble</p>
            <p class="animate-rise mt-3 font-display text-xl italic leading-relaxed text-zinc-100 sm:text-2xl" style="--reveal-delay: 280ms">{{ $charter['preamble'] }}</p>

            <div class="animate-rise mt-10 rounded-2xl border border-gold/30 bg-panel/70 p-6 text-left sm:p-8" style="--reveal-delay: 360ms">
                <p class="font-mono text-xs uppercase tracking-[0.25em] text-gold">Our mission</p>
                <p class="mt-3 text-lg leading-relaxed text-zinc-200">{{ $charter['mission'] }}</p>
                <p class="mt-3 leading-relaxed text-zinc-300">{{ $charter['peace'] }}</p>
            </div>

            <div class="animate-rise mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row" style="--reveal-delay: 440ms">
                <flux:button href="{{ route('charter') }}" variant="ghost" icon:trailing="arrow-right" class="h-12 rounded-full border border-gold/50 px-6 text-base text-gold hover:text-gold-light">Read the full Charter</flux:button>
                <flux:button href="{{ route('claim') }}" variant="primary" icon:trailing="chevron-right" class="h-12 rounded-full px-6 text-base">Register Today</flux:button>
            </div>
        </div>
    </section>

    {{-- Hero, after Daniel's mock: copy on the left, the sharp plaque artwork on the right.
         Phones show the whole artwork above the copy instead. --}}
    <section class="relative isolate overflow-hidden">
        {{-- Desktop artwork. A background on a hidden element isn't downloaded on phones. --}}
        <div aria-hidden="true" class="absolute inset-y-0 right-0 -z-10 hidden w-[70%] lg:block">
            <div class="hero-drift size-full bg-cover bg-center [mask-image:linear-gradient(to_right,transparent,black_28%)]" style="background-image: url('{{ asset('images/hero.webp') }}?v={{ substr(md5_file(public_path('images/hero.webp')), 0, 8) }}')"></div>
            <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent to-ink"></div>
            <div class="absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-ink/60 to-transparent"></div>
        </div>
        <div aria-hidden="true" class="absolute -left-40 top-10 -z-10 size-[36rem] rounded-full bg-gold/10 blur-3xl"></div>

        {{-- Phones and tablets: the whole artwork, uncropped, at its own aspect ratio (the box is
             sized to the image, so object-fill never stretches it). Drop a tall version in as
             public/images/hero-portrait.webp and it is used automatically. --}}
        @php
            $heroMobile = file_exists(public_path('images/hero-portrait.webp')) ? 'images/hero-portrait.webp' : 'images/hero.webp';
            [$heroW, $heroH] = getimagesize(public_path($heroMobile));
        @endphp
        <div class="relative isolate w-full overflow-hidden lg:hidden" style="aspect-ratio: {{ $heroW }} / {{ $heroH }}">
            <picture class="absolute inset-0 -z-10">
                <source media="(min-width: 1024px)" srcset="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">
                <img src="{{ asset($heroMobile) }}?v={{ substr(md5_file(public_path($heroMobile)), 0, 8) }}"
                    width="{{ $heroW }}" height="{{ $heroH }}"
                    alt="Artist's concept of the Lunar Society Founders Plaque on the Moon, with Earth rising behind it"
                    fetchpriority="high"
                    class="size-full object-fill">
            </picture>
            <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-1/4 bg-gradient-to-b from-transparent to-ink"></div>
            <p class="absolute bottom-2 right-3 font-mono text-[9px] uppercase tracking-[0.2em] text-zinc-300/80">Artist's concept</p>
        </div>

        <div id="hero-copy" class="mx-auto flex max-w-7xl scroll-mt-4 flex-col px-4 pb-20 pt-6 sm:px-6 lg:min-h-[65svh] lg:justify-center lg:pb-16 lg:pt-24">
            <div class="flex max-w-xl flex-col items-center text-center max-lg:mx-auto lg:items-start lg:text-left">
                <div aria-hidden="true" class="animate-rise flex w-full max-w-md items-center gap-3 text-gold">
                    <span class="h-px flex-1 bg-gradient-to-r from-transparent to-gold lg:from-gold/80"></span>
                    <flux:icon.star variant="solid" class="size-5" />
                    <span class="h-px flex-1 bg-gradient-to-l from-transparent to-gold"></span>
                </div>

                <h2 class="animate-rise mt-6 font-display leading-[0.98] drop-shadow-[0_2px_24px_rgba(0,0,0,0.7)]" style="--reveal-delay: 120ms">
                    <span class="block text-5xl text-white sm:text-6xl lg:text-7xl">Be Part of History</span>
                    <span class="text-gold-sheen mt-1 block pb-1 text-5xl text-gold sm:text-6xl lg:text-7xl">Join the Lunar Society</span>
                </h2>

                <p class="animate-rise mt-5 font-display text-2xl italic leading-snug text-zinc-100 sm:text-3xl" style="--reveal-delay: 260ms">
                    Support the journey to place a Founders Plaque on the Moon.
                </p>

                <div class="animate-rise mt-9" style="--reveal-delay: 380ms">
                    <flux:button href="{{ route('claim') }}" variant="primary" icon:trailing="chevron-right" class="animate-glow h-14 rounded-full px-8 text-lg font-semibold">
                        Register Today
                    </flux:button>
                </div>

                <ul class="animate-rise mt-10 grid w-full max-w-md grid-cols-3 divide-x divide-gold/30 text-sm text-zinc-100" style="--reveal-delay: 500ms">
                    @foreach ([
                        ['rocket-launch', 'A Lunar Vision'],
                        ['sparkles', 'Exclusive Collectibles'],
                        ['user-group', 'Prestigious Membership'],
                    ] as [$icon, $label])
                        <li class="flex flex-col items-center gap-2 px-2 text-center">
                            <flux:icon :icon="$icon" variant="solid" class="size-7 text-gold" />
                            <span class="leading-tight">{{ $label }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <p class="absolute bottom-14 right-6 hidden font-mono text-[10px] uppercase tracking-[0.2em] text-zinc-300/80 lg:block">Artist's concept</p>

        {{-- Gold sweep along the bottom edge. --}}
        <svg aria-hidden="true" viewBox="0 0 1440 80" preserveAspectRatio="none" class="absolute inset-x-0 bottom-0 h-10 w-full sm:h-16">
            <defs>
                <linearGradient id="hero-sweep" x1="0" x2="1">
                    <stop offset="0" stop-color="#e8b84a" stop-opacity="0.15" />
                    <stop offset="0.45" stop-color="#f3d68a" />
                    <stop offset="1" stop-color="#e8b84a" stop-opacity="0.4" />
                </linearGradient>
            </defs>
            <path d="M0 64 C 360 20, 900 10, 1440 46 L1440 80 L0 80 Z" fill="var(--color-ink)" />
            <path d="M0 64 C 360 20, 900 10, 1440 46" fill="none" stroke="url(#hero-sweep)" stroke-width="3" />
        </svg>
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
                    Named in the spirit of the Lunar Society.
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

                <a href="{{ route('about') }}#story" class="hover-lift group !mt-8 flex items-center gap-4 rounded-2xl border border-white/10 bg-panel p-4 text-base">
                    @if (config('lunar.host_portrait'))
                        <img src="{{ asset(config('lunar.host_portrait')) }}" alt="" class="size-16 shrink-0 rounded-full object-cover">
                    @else
                        <span class="hatch grid size-16 shrink-0 place-items-center rounded-full border border-white/10"><x-moon-mark class="size-8" /></span>
                    @endif
                    <span class="min-w-0 flex-1">
                        <span class="block font-mono text-[11px] uppercase tracking-[0.2em] text-gold">Executive Director</span>
                        <span class="mt-1 block font-display text-2xl text-white">MJ Schultz</span>
                        <span class="block text-sm text-zinc-400">A space enthusiast since his teens. Read why he created the Lunar Society.</span>
                    </span>
                    <flux:icon.arrow-right variant="micro" class="size-5 shrink-0 text-zinc-500 transition group-hover:translate-x-1 group-hover:text-gold" />
                    <span class="sr-only">Read his story</span>
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
