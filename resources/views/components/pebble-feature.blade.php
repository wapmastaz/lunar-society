@props(['product'])

{{-- Homepage feature for the Affiliate Membership Package (affiliate membership + Space Pebble). "Coming soon" while it has no price. --}}
<section aria-labelledby="pebble-heading" {{ $attributes->merge(['class' => 'mx-auto max-w-7xl px-4 sm:px-6']) }}>
    <div class="grid items-center gap-10 overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-panel via-[#121729] to-[#1a1710] p-7 sm:p-12 lg:grid-cols-2 lg:gap-16" data-reveal>
        <div class="relative order-last lg:order-first">
            @if ($product->image_path)
                <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" loading="lazy" class="animate-float mx-auto aspect-square w-full max-w-md rounded-2xl object-cover shadow-2xl shadow-black/60 ring-1 ring-white/15">
            @else
                <x-pebble-art class="mx-auto w-full max-w-sm" />
            @endif
        </div>

        <div>
            <p class="eyebrow">{{ $product->tagline ?? 'Own a piece of space history' }}</p>
            <h2 id="pebble-heading" class="mt-4 font-display text-4xl leading-[1.05] text-white sm:text-5xl">Introducing the {{ $product->name }}</h2>
            <p class="mt-5 leading-relaxed text-zinc-300">{{ $product->description }}</p>

            @if ($product->highlights)
                <p class="mt-7 font-mono text-xs uppercase tracking-[0.2em] text-zinc-400">What you get</p>
                <ul class="mt-3 space-y-3 text-zinc-100">
                    @foreach ($product->highlights as $item)
                        <li class="flex gap-3">
                            <span aria-hidden="true" class="mt-2 size-1.5 shrink-0 rotate-45 bg-gold"></span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="mt-8 flex flex-wrap items-center gap-4">
                @if ($product->active)
                    <span class="font-display text-4xl text-white">{{ $product->priceLabel() }} <span class="font-sans text-sm text-zinc-400">+ {{ \App\Services\Registry::processingFeeLabel() }} processing</span></span>
                    <flux:button href="{{ route('souvenirs.show', $product) }}" variant="primary" icon:trailing="arrow-right" class="h-12 px-6 text-base">View package &amp; join</flux:button>
                @else
                    <flux:button disabled class="h-12 px-6 text-base">Coming soon</flux:button>
                @endif
            </div>
            <p class="mt-4 text-xs text-zinc-500">Not a Founder slot: affiliate names are not engraved on the plaque. The Space Pebble is a commemorative keepsake, not lunar material.</p>
        </div>
    </div>
</section>
