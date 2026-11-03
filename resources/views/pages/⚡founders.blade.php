<?php

use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts::site')] #[Title('The Founders')] class extends Component {
    #[Url(except: '')]
    public string $search = '';

    #[Url(except: false)]
    public bool $list = false;

    /**
     * Public names keyed by slot number.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function founders(): array
    {
        return Order::query()
            ->paid()
            ->whereNotNull('slot_number')
            ->orderBy('slot_number')
            ->get(['slot_number', 'plaque_name', 'show_full_name'])
            ->mapWithKeys(fn (Order $o) => [$o->slot_number => $o->publicName()])
            ->all();
    }

    /**
     * Slot numbers matching the search, or null when not searching.
     *
     * @return array<int, true>|null
     */
    #[Computed]
    public function matches(): ?array
    {
        $term = trim($this->search);

        if ($term === '') {
            return null;
        }

        $number = ltrim($term, '#');

        if (ctype_digit($number)) {
            return [(int) $number => true];
        }

        return collect($this->founders)
            ->filter(fn (string $name) => str_contains(mb_strtolower($name), mb_strtolower($term)))
            ->map(fn () => true)
            ->all();
    }
}; ?>

<div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-16">
    <header class="text-center">
        <p class="eyebrow">The plaque · Live</p>
        <h1 class="mt-4 font-display text-6xl text-white sm:text-7xl">The Founders</h1>
        <p class="mx-auto mt-4 max-w-xl text-lg text-zinc-300">Every name below will be laser engraved on the Founders Plaque, in Founder number order.</p>
    </header>

    <div class="mx-auto mt-8 max-w-lg">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search a name or Founder number" aria-label="Search founders" clearable class="[&_input]:h-12" />
        @if ($this->matches !== null)
            <p class="mt-2 text-center text-sm text-zinc-400" role="status">
                {{ count(array_intersect_key($this->matches, $this->founders)) }} engraved {{ Str::plural('match', count(array_intersect_key($this->matches, $this->founders))) }}
            </p>
        @endif
    </div>

    <div class="mt-4 flex justify-end">
        <flux:button wire:click="$toggle('list')" variant="ghost" size="sm" :icon="$list ? 'squares-2x2' : 'list-bullet'">
            {{ $list ? 'Plaque view' : 'List view' }}
        </flux:button>
    </div>

    @if ($list)
        {{-- Plain list: easier on phones and screen readers. --}}
        <div class="panel mt-3 p-6">
            @php $rows = $this->matches === null ? $this->founders : array_intersect_key($this->founders, $this->matches); @endphp
            @if ($rows)
                <ol class="grid gap-x-8 gap-y-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($rows as $slot => $name)
                        <li class="flex items-baseline gap-3 border-b border-white/5 py-1.5">
                            <span @class(['w-12 shrink-0 text-right font-mono text-sm tabular-nums', 'text-gold' => $slot <= 50, 'text-zinc-500' => $slot > 50])>{{ sprintf('#%03d', $slot) }}</span>
                            <span class="truncate text-zinc-100">{{ $name }}</span>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="text-center text-zinc-400">{{ $search ? 'No founders match that search.' : 'No names engraved yet.' }}</p>
            @endif
        </div>
    @else
        <div x-data="{ picked: null }" class="metal mt-3 rounded-2xl p-4 shadow-2xl shadow-black/60 ring-1 ring-white/20 sm:p-7">
            <div class="flex flex-wrap items-center justify-between gap-2 font-mono text-[11px] uppercase tracking-[0.3em] text-zinc-600 sm:text-xs">
                <span>Mr SuperTrump's Lunar Society</span>
                <span x-show="!picked">{{ number_format(count($this->founders)) }} / 1,000 engraved</span>
                <span x-show="picked" x-text="picked" x-cloak class="normal-case tracking-normal text-zinc-800"></span>
            </div>

            <ol class="mt-5 grid grid-cols-10 gap-[3px] sm:grid-cols-20 lg:grid-cols-25" aria-label="Founders Plaque slots">
                @for ($n = 1; $n <= 1000; $n++)
                    @php
                        $name = $this->founders[$n] ?? null;
                        $dim = $this->matches !== null && ! isset($this->matches[$n]);
                        $hit = $this->matches !== null && isset($this->matches[$n]);
                        $label = sprintf('%03d', $n);
                    @endphp
                    <li @class([
                        'rounded-[3px] text-center font-mono text-[9px] leading-[18px] tabular-nums transition sm:text-[10px]',
                        'bg-zinc-800 text-zinc-100' => $name,
                        'bg-[#dcc68f] text-[#6b5320] ring-1 ring-inset ring-[#b99a4e]/40' => ! $name && $n <= 50,
                        'bg-white/40 text-zinc-500 ring-1 ring-inset ring-zinc-400/40' => ! $name && $n > 50,
                        'opacity-25' => $dim,
                        'ring-2 ring-gold ring-offset-1 ring-offset-zinc-300' => $hit,
                    ])>
                        @if ($name)
                            <button type="button" class="block w-full rounded-[3px] focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
                                title="#{{ $label }} · {{ $name }}"
                                @click="picked = @js('#'.$label.' · '.$name)">
                                {{ $label }}<span class="sr-only">: {{ $name }}</span>
                            </button>
                        @else
                            <span aria-label="Slot {{ $label }}, open">{{ $label }}</span>
                        @endif
                    </li>
                @endfor
            </ol>
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-4 text-sm sm:flex-row sm:items-center sm:justify-between">
        <ul class="flex flex-wrap gap-5 text-zinc-300">
            <li class="flex items-center gap-2"><span class="h-2.5 w-4 rounded-sm bg-[#dcc68f]"></span> Alpha (open)</li>
            <li class="flex items-center gap-2"><span class="h-2.5 w-4 rounded-sm bg-zinc-300"></span> Standard (open)</li>
            <li class="flex items-center gap-2"><span class="h-2.5 w-4 rounded-sm bg-zinc-700"></span> Engraved</li>
        </ul>
        <p class="text-white">
            @if (count($this->founders) === 0)
                The first Founder slot is waiting for you.
            @elseif (count($this->founders) < 1000)
                {{ number_format(1000 - count($this->founders)) }} slots still open.
            @else
                The plaque is full. Thank you, Founders!
            @endif
            @if (count($this->founders) < 1000)
                <a href="{{ route('claim') }}" class="text-gold underline underline-offset-2">Claim it now</a>.
            @endif
        </p>
    </div>
</div>
