@props(['tier', 'couple' => false, 'total', 'number' => null])

<p class="font-mono text-xs uppercase tracking-[0.25em] text-zinc-400">Order summary</p>

@if ($tier)
    <div class="mt-5 flex justify-between gap-4 text-white">
        <span>{{ $tier->name }}</span>
        <span>{{ $tier->priceLabel() }}</span>
    </div>
    <p class="mt-2 font-mono text-sm text-zinc-400">
        @if ($number)
            Founder {{ $number }}
        @else
            {{ sprintf('#%03d–#%03d', $tier->slot_start, $tier->slot_end) }} · {{ number_format($tier->remaining()) }} left
        @endif
    </p>

    @if ($couple)
        <div class="mt-3 flex justify-between gap-4 text-sm text-zinc-300">
            <span>Couple/family name</span>
            <span>+${{ number_format(config('lunar.couple_addon_cents') / 100) }}</span>
        </div>
    @endif
@endif

<div class="mt-6 flex items-center justify-between gap-4 border-t border-white/10 pt-6">
    <span class="text-white">Total</span>
    <span class="text-3xl font-bold text-white">{{ $total }}</span>
</div>

<p class="mt-5 flex gap-3 text-xs leading-relaxed text-zinc-400">
    <flux:icon.lock-closed variant="micro" class="mt-0.5 size-4 shrink-0 text-zinc-300" />
    <span>Secure checkout with PayPal. We never see or store your card details.</span>
</p>
