{{-- Illustrated pebble in a gift box, used until a real product photo is set (LUNAR_PEBBLE_IMAGE). --}}
<div aria-hidden="true" {{ $attributes->merge(['class' => 'relative aspect-square']) }}>
    <div class="absolute inset-[8%] rounded-full bg-gold/10 blur-3xl"></div>

    {{-- Display box --}}
    <div class="absolute inset-x-[12%] bottom-[10%] top-[38%] rounded-2xl border border-gold/40 bg-gradient-to-b from-[#1d2236] to-[#0b0f1c] shadow-2xl shadow-black/70">
        <div class="absolute inset-x-[10%] top-[18%] h-[46%] rounded-[50%] bg-black/50 blur-sm"></div>
        <p class="absolute inset-x-0 bottom-[10%] text-center font-mono text-[10px] uppercase tracking-[0.35em] text-gold/80">Lunar Society</p>
    </div>

    {{-- Pebble --}}
    <div class="animate-float absolute left-1/2 top-[14%] w-[48%] -translate-x-1/2">
        <div class="metal-shine aspect-[5/4] rounded-[48%_52%_45%_55%/55%_48%_52%_45%] bg-[radial-gradient(circle_at_30%_25%,#c9c3b8,#7d776d_45%,#3e3a35_80%)] shadow-[inset_-12px_-16px_30px_rgba(0,0,0,0.55),0_30px_40px_-18px_rgba(0,0,0,0.9)]">
            @foreach ([['22%', '30%', '9%'], ['58%', '46%', '6%'], ['40%', '66%', '7%'], ['70%', '24%', '4%']] as [$left, $top, $size])
                <span class="absolute rounded-full bg-black/25 shadow-[inset_1px_1px_2px_rgba(0,0,0,0.5)]" style="left: {{ $left }}; top: {{ $top }}; width: {{ $size }}; aspect-ratio: 1"></span>
            @endforeach
        </div>
    </div>

    {{-- Sparkles --}}
    @foreach ([['14%', '18%', '0s'], ['84%', '22%', '1.1s'], ['76%', '58%', '2s'], ['10%', '62%', '0.6s']] as [$left, $top, $delay])
        <span class="animate-twinkle absolute size-1.5 rotate-45 bg-gold-light" style="left: {{ $left }}; top: {{ $top }}; --twinkle-delay: {{ $delay }}"></span>
    @endforeach
</div>
