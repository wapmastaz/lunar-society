@props(['tier', 'featured' => false])

@php
    $soldOut = $tier->isSoldOut();
    $perks = $tier->code === 'alpha'
        ? [
            'Row 1, Section A: your name goes in the top rows of the plaque.',
            'Elite Founder number from #001 to #050.',
            'Gold-foil Alpha Founder digital certificate.',
        ]
        : [
            'Your name laser engraved on the Founders Plaque.',
            'Numbered Founder digital certificate.',
            'Your name on the public Founders list.',
        ];

    if (config('lunar.pebble_included')) {
        $perks[] = 'Commemorative space pebble in a gift box.';
    }
@endphp

<article @class([
    'flex flex-col rounded-2xl border p-6 sm:p-8',
    'border-gold/60 bg-gradient-to-b from-gold/15 to-white/5' => $featured,
    'border-white/10 bg-white/5' => ! $featured,
])>
    <p @class(['text-xs font-semibold uppercase tracking-widest', 'text-gold' => $featured, 'text-zinc-400' => ! $featured])>
        Slots {{ $tier->rangeLabel() }}
    </p>
    <h3 class="mt-2 text-2xl font-bold text-white">{{ $tier->name }}</h3>
    <p class="mt-1 text-4xl font-extrabold text-white">{{ $tier->priceLabel() }}</p>
    <p class="mt-1 text-sm text-zinc-400">
        {{ $soldOut ? 'Sold out' : number_format($tier->remaining()).' of '.number_format($tier->capacity()).' available' }}
    </p>

    <ul class="mt-6 space-y-3 text-sm text-zinc-200">
        @foreach ($perks as $perk)
            <li class="flex gap-3">
                <flux:icon.check-circle variant="mini" class="mt-0.5 size-5 shrink-0 text-gold" />
                <span>{{ $perk }}</span>
            </li>
        @endforeach
    </ul>

    <div class="mt-auto pt-8">
        @if ($soldOut)
            <flux:button disabled class="w-full">{{ Str::upper($tier->name) }} SLOTS SOLD OUT</flux:button>
        @else
            <flux:button href="{{ route('claim', $tier->code) }}" :variant="$featured ? 'primary' : 'filled'" class="w-full">
                {{ $tier->code === 'alpha' ? 'CLAIM AN ALPHA SLOT' : 'CLAIM A FOUNDER SLOT' }} ({{ $tier->priceLabel() }})
            </flux:button>
        @endif
    </div>
</article>
