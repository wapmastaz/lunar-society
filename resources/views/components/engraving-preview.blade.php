@props(['name', 'number' => null])

<div {{ $attributes->merge(['class' => 'metal flex items-center gap-4 rounded-xl px-5 py-5 shadow-inner']) }}>
    @if ($number)
        <span class="font-mono text-xs text-zinc-600">{{ $number }}</span>
    @endif
    <span class="min-w-0 truncate font-mono text-lg uppercase tracking-[0.2em] text-zinc-800 sm:text-xl">{{ filled($name) ? $name : 'Your name here' }}</span>
</div>
