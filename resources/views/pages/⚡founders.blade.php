<?php

use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::site')] #[Title('The Founders')] class extends Component {
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function founders()
    {
        return Order::query()
            ->paid()
            ->with('tier')
            ->whereNotNull('slot_number')
            ->when($this->search !== '', fn ($q) => $q->where('plaque_name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $this->search).'%'))
            ->orderBy('slot_number')
            ->paginate(100);
    }
}; ?>

<div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
    <header class="text-center">
        <h1 class="text-3xl font-extrabold text-white sm:text-4xl">The Founders</h1>
        <p class="mx-auto mt-3 max-w-2xl text-zinc-300">Every name below will be laser engraved on the Founders Plaque, in Founder number order.</p>
    </header>

    <div class="mx-auto mt-8 max-w-md">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search for a name" aria-label="Search founders" clearable />
    </div>

    @if ($this->founders->isEmpty())
        <p class="mt-16 text-center text-zinc-400">
            {{ $search ? 'No founders match that search.' : 'The first Founder slot is waiting for you.' }}
            @unless ($search)
                <a href="{{ route('claim') }}" class="underline hover:text-white">Claim it now</a>.
            @endunless
        </p>
    @else
        <ol class="mt-10 grid gap-x-8 gap-y-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($this->founders as $founder)
                <li class="flex items-baseline gap-3 border-b border-white/5 py-1.5">
                    <span @class(['w-12 shrink-0 text-right font-mono text-sm tabular-nums', 'text-gold' => $founder->tier->code === 'alpha', 'text-zinc-500' => $founder->tier->code !== 'alpha'])>
                        {{ $founder->slotLabel() }}
                    </span>
                    <span class="truncate text-zinc-100">{{ $founder->publicName() }}</span>
                </li>
            @endforeach
        </ol>

        <div class="mt-8">
            {{ $this->founders->links() }}
        </div>
    @endif
</div>
