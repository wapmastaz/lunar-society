<?php

use App\Models\Tier;
use App\Services\Registry;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $variant = 'banner';

    #[Computed]
    public function claimed(): int
    {
        return app(Registry::class)->claimedTotal();
    }

    #[Computed]
    public function tiers()
    {
        return Tier::orderBy('slot_start')->get();
    }
}; ?>

<div wire:poll.30s.visible>
    @php($capacity = (int) config('lunar.capacity'))
    @if ($variant === 'banner')
        <div class="bg-gold px-4 py-1.5 text-center text-xs text-ink sm:py-2 sm:text-sm">
            {{-- One short line on phones, the full notice from tablet up. --}}
            <span class="sm:hidden"><strong>Only 1,000 slots</strong> · {{ number_format($capacity - $this->claimed) }} left</span>
            <span class="max-sm:hidden">
                <strong>ATTENTION PATRIOTS:</strong>
                Only {{ number_format($capacity) }} Founder slots.
                <span class="whitespace-nowrap">{{ number_format($this->claimed) }} claimed, {{ number_format($capacity - $this->claimed) }} left.</span>
            </span>
        </div>
    @else
        @php($pct = $capacity ? $this->claimed / $capacity * 100 : 0)
        <div class="panel p-6 sm:p-7">
            <div class="flex items-end justify-between gap-4">
                <p class="text-sm font-medium text-zinc-200">Founder slots claimed</p>
                <p class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold tabular-nums text-white">{{ number_format($this->claimed) }}</span>
                    <span class="text-zinc-400">/ {{ number_format($capacity) }}</span>
                    <span class="ms-2 font-mono text-xs text-gold">{{ number_format($pct, 1) }}%</span>
                </p>
            </div>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/10" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $capacity }}" aria-valuenow="{{ $this->claimed }}" aria-label="Founder slots claimed">
                <div class="animate-grow-x h-full rounded-full bg-gold" style="width: {{ max($pct, 0.5) }}%"></div>
            </div>
            <dl class="mt-5 grid grid-cols-2 gap-5 sm:grid-cols-3">
                @foreach ($this->tiers as $tier)
                    <div>
                        <dt @class(['font-mono text-[11px] uppercase tracking-[0.2em]', 'text-gold' => $tier->code === 'alpha', 'text-zinc-400' => $tier->code !== 'alpha'])>{{ $tier->name }}</dt>
                        <dd class="mt-1 text-zinc-300"><span class="text-lg font-bold tabular-nums text-white">{{ number_format($tier->remaining()) }}</span> of {{ number_format($tier->capacity()) }} left</dd>
                    </div>
                @endforeach
                <div>
                    <dt class="font-mono text-[11px] uppercase tracking-[0.2em] text-zinc-400">Remaining</dt>
                    <dd class="mt-1 text-zinc-300"><span class="text-lg font-bold tabular-nums text-white">{{ number_format($capacity - $this->claimed) }}</span> slots</dd>
                </div>
            </dl>
        </div>
    @endif
</div>
