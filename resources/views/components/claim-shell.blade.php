@props(['step', 'jump' => false])

{{-- Shared frame for the claim wizard and its confirmation page. --}}
@php
    $steps = ['Tier', 'Details', 'Engraving', 'Review', 'Payment', 'Done'];
@endphp

<div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-16">
    <p class="eyebrow">Claim a Founder slot</p>
    <h1 class="mt-4 font-display text-4xl text-white sm:text-6xl">Secure your place on the Founders Plaque</h1>

    <ol class="mt-8 grid grid-cols-6 gap-2" aria-label="Progress">
        @foreach ($steps as $i => $label)
            @php $n = $i + 1; @endphp
            <li>
                @if ($jump && $n < $step && $n <= 4)
                    <button type="button" wire:click="goTo({{ $n }})" class="group block w-full text-left">
                @endif
                <span @class(['block h-0.5 rounded-full', 'bg-gold' => $n <= $step, 'bg-white/15' => $n > $step])></span>
                <span @class([
                    'mt-3 block font-mono text-[11px] sm:text-xs',
                    'text-gold group-hover:text-gold-light' => $n < $step,
                    'text-white' => $n === $step,
                    'text-zinc-500' => $n > $step,
                ]) @if ($n === $step) aria-current="step" @endif>
                    {{ sprintf('%02d', $n) }}<span @class(['hidden sm:inline' => $n !== $step, 'max-sm:sr-only' => $n === $step])> {{ $label }}</span>
                </span>
                @if ($jump && $n < $step && $n <= 4)
                    </button>
                @endif
            </li>
        @endforeach
    </ol>

    <div class="mt-8 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="panel p-6 sm:p-9">
            {{ $slot }}
        </section>

        <aside aria-label="Order summary" class="panel p-6 sm:p-7 lg:sticky lg:top-6">
            {{ $summary }}
        </aside>
    </div>
</div>
