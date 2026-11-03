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
    @if ($variant === 'banner')
        <div class="bg-gold px-4 py-2 text-center text-sm font-semibold text-[#070b1a]">
            ATTENTION PATRIOTS: Only {{ number_format(config('lunar.capacity')) }} Founder slots.
            <span class="whitespace-nowrap">{{ number_format($this->claimed) }} claimed, {{ number_format(config('lunar.capacity') - $this->claimed) }} left.</span>
        </div>
    @else
        @php($pct = min(100, round($this->claimed / config('lunar.capacity') * 100, 1)))
        <div class="rounded-xl border border-white/10 bg-white/5 p-5">
            <div class="flex items-baseline justify-between gap-4">
                <p class="text-sm font-medium text-zinc-300">Founder slots claimed</p>
                <p class="text-2xl font-bold tabular-nums text-white">{{ number_format($this->claimed) }} <span class="text-base font-normal text-zinc-400">/ {{ number_format(config('lunar.capacity')) }}</span></p>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10" role="progressbar" aria-valuemin="0" aria-valuemax="{{ config('lunar.capacity') }}" aria-valuenow="{{ $this->claimed }}" aria-label="Founder slots claimed">
                <div class="h-full rounded-full bg-gold transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <ul class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-zinc-400">
                @foreach ($this->tiers as $tier)
                    <li>{{ $tier->name }}: {{ $tier->isSoldOut() ? 'sold out' : number_format($tier->remaining()).' left' }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
