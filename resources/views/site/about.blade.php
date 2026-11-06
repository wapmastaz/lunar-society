<x-layouts::site title="About M. J. Schultz">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16">
        <div class="grid gap-10 md:grid-cols-[22rem_1fr] md:gap-14" data-reveal>
            @if (config('lunar.host_portrait'))
                <img src="{{ asset(config('lunar.host_portrait')) }}" alt="M. J. Schultz, host of {{ config('lunar.podcast') }}" class="aspect-[4/5] w-full rounded-2xl border border-white/10 object-cover">
            @else
                {{-- Placeholder until the client sends a portrait (LUNAR_HOST_PORTRAIT). --}}
                <div aria-hidden="true" class="hatch grid aspect-[4/5] w-full place-items-center rounded-2xl border border-white/10 bg-panel">
                    <x-moon-mark class="size-16 opacity-60" />
                </div>
            @endif

            <div>
                <p class="eyebrow">About</p>
                <h1 class="mt-4 font-display text-5xl text-white sm:text-6xl">About M. J. Schultz</h1>
                <div class="mt-6 space-y-5 text-lg leading-relaxed text-zinc-300">
                    <p>
                        M. J. Schultz is the host of {{ config('lunar.podcast') }}, available on Spotify, YouTube and Beehiiv. The show follows a
                        flamboyant, optimistic character through surreal adventures with a rotating cast of guests, covering topics from the 2026
                        midterms to criminal justice and homelessness, with regular detours into outer space and political fantasy.
                    </p>
                    <p>
                        Schultz brings decades of media and publishing experience. In the 1990s he published Tower Air In-Flight Magazine and
                        The New York Ski Guide, and placed Broadway advertising in the in-flight magazines of Continental, TWA, Delta, Northwest
                        and United. He was executive producer of the award-winning docudrama Genius on Hold, narrated by Frank Langella, and has
                        helped raise over $1 million for leukemia research through The Jane Elissa Endowment Fund.
                    </p>
                </div>
            </div>
        </div>

        {{-- MJ's story, from his own account. Real people are named as part of his history only. --}}
        <section id="story" aria-labelledby="story-heading" class="mt-24 scroll-mt-24">
            <div class="max-w-3xl" data-reveal>
                <p class="eyebrow">Our Director's story</p>
                <h2 id="story-heading" class="mt-4 font-display text-5xl leading-[1.05] text-white sm:text-6xl">Why MJ Schultz created the Lunar Society</h2>
                <p class="mt-4 font-display text-2xl italic text-zinc-300">A lifelong dream to be part of space history.</p>
            </div>

            <div class="mt-12 grid items-start gap-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,30rem)] lg:gap-16">
                <div class="space-y-6 text-lg leading-relaxed text-zinc-300" data-reveal>
                    <p>
                        MJ Schultz has been an avid space enthusiast since his teenage years. He was a Junior Rocket Ranger, then joined the
                        Civil Air Patrol and AFROTC.
                    </p>
                    <p>
                        MJ was in his mid-twenties when President John F. Kennedy challenged America to reach the Moon before the decade was
                        out. On July 20, 1969, "the Eagle has landed," and man walked on the Moon.
                    </p>
                    <p>
                        In 2008 he was active with XCOR Aerospace, the developers of the Lynx spaceplane, and attended the Space Symposium in
                        Colorado Springs. There he briefly met Elon Musk, who had a SpaceX display, and befriended Buzz Aldrin, who, after a
                        little negotiation, endorsed the Lynx spaceplane. In the end, Lynx never flew.
                    </p>
                    <p>
                        Now MJ wants his place in space history, celebrating, in his words, that "after 54 years, President Trump brings us
                        back to the Moon." The Lunar Society is how he, and 1,000 founders, can be part of it.
                    </p>
                    <p class="text-sm text-zinc-500">
                        Elon Musk, Buzz Aldrin, SpaceX and XCOR are mentioned as part of MJ's personal history only. None of them is involved
                        with, or has endorsed, the Lunar Society.
                    </p>
                </div>

                <figure class="hover-lift overflow-hidden rounded-2xl border border-white/10 bg-panel" data-reveal style="--reveal-delay: 150ms">
                    <img src="{{ asset('images/story/mj-lunar-module.webp') }}" alt="MJ Schultz standing beside an Apollo Lunar Module display" loading="lazy" width="1080" height="810" class="w-full">
                    <figcaption class="px-5 py-4 text-sm text-zinc-400">MJ beside an Apollo Lunar Module display.</figcaption>
                </figure>
            </div>

            <blockquote class="mx-auto mt-20 max-w-4xl text-center" data-reveal>
                <flux:icon.star variant="solid" class="mx-auto size-6 text-gold" />
                <p class="mt-6 font-display text-3xl leading-snug text-white sm:text-5xl">
                    "We choose to go to the Moon in this decade and do the other things, not because they are easy, but because they are hard."
                </p>
                <footer class="mt-6 font-mono text-xs uppercase tracking-[0.25em] text-gold">President John F. Kennedy · Rice University, 1962</footer>
            </blockquote>

            <div class="mt-20 grid gap-6 sm:grid-cols-2">
                <figure class="hover-lift overflow-hidden rounded-2xl border border-white/10 bg-panel" data-reveal>
                    <img src="{{ asset('images/story/apollo-exhibit.webp') }}" alt="An exhibit projection of Apollo astronauts working on the lunar surface" loading="lazy" width="1080" height="810" class="aspect-[4/3] w-full object-cover">
                    <figcaption class="px-5 py-4 text-sm text-zinc-400">1969 and beyond: the Apollo era that inspired a generation.</figcaption>
                </figure>
                <figure class="hover-lift overflow-hidden rounded-2xl border border-white/10 bg-panel" data-reveal style="--reveal-delay: 150ms">
                    <img src="{{ asset('images/story/artemis-ii.webp') }}" alt="An Artemis II 2026 poster showing the launch and the rocket's design" loading="lazy" width="960" height="1280" class="aspect-[4/3] w-full object-cover object-top">
                    <figcaption class="px-5 py-4 text-sm text-zinc-400">Artemis II, 2026: America heads back to the Moon.</figcaption>
                </figure>
            </div>

            <div class="mt-20 rounded-3xl border border-gold/40 bg-gradient-to-br from-[#2a2110] via-[#17140d] to-panel p-8 text-center sm:p-14" data-reveal>
                <p class="eyebrow">Galactic Peace for Mankind</p>
                <h3 class="mx-auto mt-4 max-w-3xl font-display text-4xl leading-tight text-white sm:text-5xl">
                    Join us as a founder and support what the Moon base stands for
                </h3>
                <p class="mx-auto mt-5 max-w-2xl text-lg text-zinc-200">
                    Your gift of immortality will be a lasting legacy for you and those around you. Get on the list and start telling people
                    that you too will have your name on a plaque intended for the future Trumpian Lunar Moon Base, a patriotic symbol of your
                    support for America.
                </p>
                <div class="mt-8 flex flex-col items-center gap-3">
                    <flux:button href="{{ route('claim') }}" variant="primary" icon:trailing="chevron-right" class="animate-glow h-14 rounded-full px-8 text-lg font-semibold">Register now</flux:button>
                    <p class="text-sm text-zinc-400">Act soon. Once all 1,000 names are taken, the door closes for good.</p>
                </div>
            </div>
        </section>

        <section data-reveal aria-labelledby="society-heading" class="mt-24 rounded-2xl border border-white/10 bg-gradient-to-br from-[#1a1710] via-panel to-[#0f1428] p-7 sm:p-12">
            <h2 id="society-heading" class="font-display text-4xl text-white sm:text-5xl">About the Lunar Society</h2>
            <p class="mt-5 max-w-3xl text-lg leading-relaxed text-zinc-200">
                {{ config('lunar.brand') }} grew out of the show's episodes on America's return to the Moon. It's a crowd-powered,
                independent listener project: 1,000 Founders, one aerospace-grade plaque, and a long-haul goal of getting it to the lunar
                surface. Follow every step on the podcast.
            </p>
            <div class="mt-7 flex flex-wrap gap-3">
                <flux:button href="https://{{ config('lunar.domain') }}" variant="primary" class="h-11 px-5">Listen at {{ config('lunar.domain') }}</flux:button>
                <flux:button href="{{ route('claim') }}" variant="outline" class="h-11 px-5">Claim a slot</flux:button>
            </div>
        </section>
    </div>
</x-layouts::site>
