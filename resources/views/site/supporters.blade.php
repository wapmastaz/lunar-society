@php
    use App\Models\Order;
    use App\Models\Product;
    use Illuminate\Support\Carbon;

    $boostersFrom = Carbon::parse(config('lunar.booster_list_from'), 'America/New_York')->startOfDay();

    // Public names per list, oldest first.
    $names = Order::query()
        ->paid()
        ->whereNotNull('list_name')
        ->whereHas('product', fn ($q) => $q->whereNotNull('listing'))
        ->with('product:id,listing')
        ->orderBy('paid_at')
        ->get(['id', 'product_id', 'list_name', 'show_full_name'])
        ->groupBy(fn (Order $o) => $o->product->listing)
        ->map(fn ($orders) => $orders->map->publicName()->all());
@endphp

<x-layouts::site title="Boosters, Supporters and Members">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16">
        <header class="max-w-3xl">
            <p class="eyebrow animate-rise">With our thanks</p>
            <h1 class="animate-rise mt-4 font-display text-5xl text-white sm:text-6xl" style="--reveal-delay: 100ms">Boosters, Supporters and Members</h1>
            <p class="mt-4 text-lg text-zinc-300">
                The friends who help the Lunar Society Network grow toward the Moon. Founders are listed on the
                <a href="{{ route('founders') }}" class="text-gold underline underline-offset-2">Founders page</a>.
                <a href="{{ route('souvenirs') }}" class="text-gold underline underline-offset-2">Join a list</a>.
            </p>
        </header>

        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            @foreach (['members' => 'New Members', 'supporters' => 'Supporters', 'boosters' => 'Boosters'] as $list => $heading)
                <section aria-labelledby="list-{{ $list }}" class="panel p-7" data-reveal style="--reveal-delay: {{ $loop->index * 120 }}ms">
                    <h2 id="list-{{ $list }}" class="font-display text-3xl text-white">{{ $heading }}</h2>

                    @if ($list === 'boosters' && now()->lt($boostersFrom))
                        <p class="mt-4 text-zinc-400">The Booster list will be published here on {{ $boostersFrom->format('F j, Y') }}.</p>
                    @elseif (empty($names[$list]))
                        <p class="mt-4 text-zinc-400">Be the first on this list.</p>
                    @else
                        <ul class="mt-4 space-y-2 text-zinc-200">
                            @foreach ($names[$list] as $name)
                                <li class="flex gap-3">
                                    <span aria-hidden="true" class="mt-2 size-1.5 shrink-0 rotate-45 bg-gold"></span>
                                    <span>{{ $name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
</x-layouts::site>
