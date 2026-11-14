{{-- The Founders Plaque facsimile, full size, with its specs. --}}
<x-layouts::site title="The Founders Plaque">
    <div class="mx-auto max-w-5xl px-4 py-14 sm:px-6 sm:py-16">
        <header class="text-center" data-reveal>
            <p class="eyebrow">Facsimile replica</p>
            <h1 class="mt-4 font-charter-title text-3xl font-bold uppercase tracking-[0.08em] text-gold sm:text-5xl">The Founders Plaque</h1>
            <p class="mx-auto mt-4 max-w-2xl font-charter text-xl text-zinc-200 sm:text-2xl">
                {{ config('lunar.plaque_size') }}, made of {{ config('lunar.plaque_material') }}, laser engraved with the names of
                {{ number_format(config('lunar.capacity')) }} founders, for the future Moon base.
            </p>
        </header>

        <figure class="mx-auto mt-10 max-w-3xl" data-reveal>
            <a href="{{ asset('images/plaque/founders-plaque.webp') }}" target="_blank" class="group block overflow-hidden rounded-2xl ring-1 ring-white/15 shadow-2xl shadow-black/60">
                <img src="{{ asset('images/plaque/founders-plaque.webp') }}" width="848" height="1264"
                    alt="Facsimile of the Lunar Society Founders Plaque: a blue and gold {{ config('lunar.plaque_size') }} aluminum plaque with the Lunar Society emblem and five columns of engraved founder names"
                    class="w-full transition duration-500 group-hover:scale-[1.01]">
            </a>
            <figcaption class="mt-4 text-center text-sm text-zinc-400">
                Facsimile mockup. The names shown are samples: real Founder names are engraved in Founder number order, with the name at the top of each column in script.
                <a href="{{ asset('images/plaque/founders-plaque.webp') }}" target="_blank" class="text-gold underline underline-offset-2">Open full size</a>
            </figcaption>
        </figure>

        <dl class="mx-auto mt-12 grid max-w-3xl gap-4 sm:grid-cols-3" data-reveal>
            @foreach ([
                ['Size', config('lunar.plaque_size')],
                ['Material', 'Aerospace-grade 6061-T6 aluminum, laser engraved'],
                ['Names', number_format(config('lunar.capacity')).' founders, in Founder number order'],
            ] as [$term, $detail])
                <div class="panel p-5 text-center">
                    <dt class="font-charter-title text-xs font-bold uppercase tracking-[0.25em] text-gold">{{ $term }}</dt>
                    <dd class="mt-2 text-zinc-200">{{ $detail }}</dd>
                </div>
            @endforeach
        </dl>

        <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row" data-reveal>
            <flux:button href="{{ route('claim') }}" variant="primary" icon:trailing="chevron-right" class="h-12 rounded-full px-7 text-base">Put your name on it</flux:button>
            <flux:button href="{{ route('founders') }}" variant="ghost" icon:trailing="arrow-right" class="h-12 rounded-full border border-gold/50 px-6 text-base text-gold hover:text-gold-light">See who's on it</flux:button>
        </div>
    </div>
</x-layouts::site>
