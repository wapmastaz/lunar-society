<?php

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::site')] #[Title('Appreciative Contributions')] class extends Component {
    #[Computed]
    public function products()
    {
        return Product::where('active', true)->orderBy('sort')->orderBy('price_cents')->get();
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
    <header class="text-center">
        <p class="eyebrow">Appreciative Contributions</p>
        <h1 class="mt-4 font-display text-5xl text-white sm:text-6xl">We Need a Little Help from Our Friends</h1>
        <p class="mx-auto mt-3 max-w-2xl text-zinc-300">Ways to support the Lunar Society. These are separate from Registered Founder memberships and don't include a slot on the Founders Plaque.</p>
    </header>

    @if ($this->products->isEmpty())
        <p class="mt-16 text-center text-zinc-400">Contributions open soon.</p>
    @else
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($this->products as $product)
                <article class="flex flex-col overflow-hidden panel">
                    @if ($product->image_path)
                        <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover" loading="lazy">
                    @endif
                    <div class="flex flex-1 flex-col p-6">
                        <h2 class="text-lg font-semibold text-white">{{ $product->name }}</h2>
                        <p class="mt-1 text-2xl font-bold text-white">{{ $product->priceLabel() }}@if ($product->ships) <span class="text-sm font-normal text-zinc-400">+ {{ \App\Services\Registry::processingFeeLabel() }} processing</span>@endif</p>
                        @if ($product->description)
                            <p class="mt-3 text-sm text-zinc-300">{{ $product->description }}</p>
                        @endif
                        <div class="mt-auto pt-6">
                            <flux:button href="{{ route('souvenirs.show', $product) }}" variant="primary" class="w-full">{{ $product->ships ? 'Order now' : 'Contribute' }}</flux:button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
