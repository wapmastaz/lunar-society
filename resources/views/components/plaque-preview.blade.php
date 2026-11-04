{{-- Decorative plaque: 1,000 engraving lines in a 25-column grid, Alpha rows in gold. --}}
<figure {{ $attributes->merge(['class' => 'w-full']) }}>
    <div aria-hidden="true" class="metal metal-shine animate-float relative rounded-xl p-5 shadow-2xl shadow-black/60 ring-1 ring-white/20 sm:p-6">
        @foreach (['left-2 top-2', 'right-2 top-2', 'left-2 bottom-2', 'right-2 bottom-2'] as $pos)
            <span class="absolute {{ $pos }} size-2 rounded-full bg-gradient-to-br from-white to-zinc-400 ring-1 ring-zinc-500/40"></span>
        @endforeach

        <p class="text-center font-mono text-[10px] uppercase tracking-[0.35em] text-zinc-600 sm:text-xs">Mr SuperTrump's Lunar Society</p>
        <p class="mt-1 text-center text-xs text-zinc-700 sm:text-sm">Founders Plaque</p>

        <div class="mt-4 grid grid-cols-25 gap-x-[3px] gap-y-[3px]">
            @for ($i = 1; $i <= 1000; $i++)
                <span
                    @class(['h-[3px] rounded-[1px]', 'bg-[#c9a24a]' => $i <= 50, 'bg-zinc-500/45' => $i > 50, 'animate-twinkle' => $i <= 50])
                    @if ($i <= 50) style="--twinkle-delay: {{ ($i * 0.37) % 3 }}s" @endif
                ></span>
            @endfor
        </div>

        <p class="mt-4 text-center font-mono text-[9px] uppercase tracking-[0.25em] text-zinc-600 sm:text-[11px]">1,000 names · 11.5" × 8.5" · 6061-T6 aluminum</p>
    </div>
    <figcaption class="mt-4 flex justify-center gap-6 font-mono text-xs text-zinc-400">
        <span class="flex items-center gap-2"><span class="h-2 w-3 rounded-sm bg-gold"></span> Alpha · #001–#050</span>
        <span class="flex items-center gap-2"><span class="h-2 w-3 rounded-sm bg-zinc-300"></span> Standard · #051–#1000</span>
    </figcaption>
</figure>
