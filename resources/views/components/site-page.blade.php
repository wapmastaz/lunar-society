@props(['title', 'eyebrow' => null, 'intro' => null])

<x-layouts::site :title="$title">
    <article class="mx-auto max-w-3xl px-4 py-14 sm:px-6 sm:py-16
        [&_h2]:mt-10 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-white
        [&_h3]:mt-6 [&_h3]:font-semibold [&_h3]:text-white
        [&_p]:mt-3 [&_p]:leading-relaxed [&_p]:text-zinc-300
        [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:ps-6 [&_ul]:text-zinc-300
        [&_ol]:mt-3 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:ps-6 [&_ol]:text-zinc-300
        [&_a]:text-gold [&_a]:underline [&_a]:underline-offset-2 [&_a:hover]:text-gold-light">
        @if ($eyebrow)
            <p class="eyebrow animate-rise">{{ $eyebrow }}</p>
        @endif
        <h1 class="animate-rise mt-4 font-display text-5xl text-white sm:text-6xl" style="--reveal-delay: 100ms">{{ $title }}</h1>
        @if ($intro)
            <p class="!text-zinc-400">{{ $intro }}</p>
        @endif
        {{ $slot }}
    </article>
</x-layouts::site>
