@props(['tier'])

@php
    $alpha = $tier->code === 'alpha';
    $soldOut = $tier->isSoldOut();
    $remaining = $tier->remaining();
    $share = round($tier->capacity() / config('lunar.capacity') * 100);
    $addon = '$'.number_format(config('lunar.couple_addon_cents') / 100);

    $perks = $alpha
        ? ['One of the first 50 positions on the Founders Plaque', 'Your name laser engraved on 6061-T6 aluminum', "Couple or family name available as a +{$addon} add-on"]
        : ['A numbered position from #051 to #1000', 'Your name laser engraved on 6061-T6 aluminum', "Couple or family name available as a +{$addon} add-on"];

    if (config('lunar.pebble_included')) {
        $perks[] = 'Commemorative space pebble in a gift box';
    }
@endphp

<article @class([
    'flex flex-col rounded-2xl border p-6 sm:p-7',
    'border-gold/70 bg-gradient-to-br from-[#2a2110] via-[#17140d] to-panel shadow-[0_0_40px_-12px] shadow-gold/30' => $alpha,
    'border-white/10 bg-panel' => ! $alpha,
])>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p @class(['font-mono text-xs uppercase tracking-[0.2em]', 'text-gold' => $alpha, 'text-zinc-400' => ! $alpha])>
                Slots {{ sprintf('#%03d', $tier->slot_start) }} – {{ sprintf('#%03d', $tier->slot_end) }}
            </p>
            <h3 class="mt-2 font-display text-3xl text-white">{{ $tier->name }}</h3>
        </div>
        <span @class([
            'shrink-0 rounded-full px-3 py-1 font-mono text-[11px] font-medium uppercase tracking-wider',
            'bg-gold text-ink' => $alpha,
            'border border-white/20 text-zinc-200' => ! $alpha,
        ])>{{ $alpha ? 'Limited · '.$tier->capacity() : number_format($tier->capacity()).' slots' }}</span>
    </div>

    <p class="mt-5 flex items-baseline gap-3">
        <span class="font-display text-6xl text-white">{{ $tier->priceLabel() }}</span>
        <span class="text-sm text-zinc-400">one-time</span>
    </p>

    <div class="mt-5">
        <div class="flex justify-between text-xs">
            <span class="font-medium text-white">{{ $soldOut ? 'Sold out' : number_format($remaining).' of '.number_format($tier->capacity()).' left' }}</span>
            <span class="text-zinc-400">{{ $alpha ? "First {$share}% of the plaque" : "{$share}% of the plaque" }}</span>
        </div>
        <div class="mt-2 h-1 overflow-hidden rounded-full bg-white/10">
            <div @class(['h-full rounded-full', 'bg-gold' => $alpha, 'bg-zinc-200' => ! $alpha]) style="width: {{ $remaining / $tier->capacity() * 100 }}%"></div>
        </div>
    </div>

    <ul class="mt-6 space-y-3 text-sm text-zinc-200">
        @foreach ($perks as $perk)
            <li class="flex gap-3">
                <span aria-hidden="true" @class(['mt-1.5 size-1.5 shrink-0 rotate-45', 'bg-gold' => $alpha, 'border border-zinc-400' => ! $alpha])></span>
                <span>{{ $perk }}</span>
            </li>
        @endforeach
    </ul>

    <div class="mt-auto pt-7">
        @if ($soldOut)
            <flux:button disabled class="w-full">{{ Str::upper($tier->name) }} SLOTS SOLD OUT</flux:button>
        @else
            <flux:button href="{{ route('claim', $tier->code) }}" :variant="$alpha ? 'primary' : 'outline'" class="w-full">
                {{ $alpha ? 'Claim an Alpha slot' : 'Claim a Standard slot' }}
            </flux:button>
        @endif
    </div>
</article>
