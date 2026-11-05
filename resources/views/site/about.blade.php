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

        <section data-reveal aria-labelledby="society-heading" class="mt-16 rounded-2xl border border-white/10 bg-gradient-to-br from-[#1a1710] via-panel to-[#0f1428] p-7 sm:p-12">
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
