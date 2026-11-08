@props(['large' => false])

{{-- The Lunar Society emblem (rocket over the Moon, "Together Toward Intergalactic Peace"). --}}
<img
    src="{{ asset($large ? 'images/logo.webp' : 'images/logo-sm.webp') }}"
    alt=""
    width="{{ $large ? 512 : 128 }}"
    height="{{ $large ? 512 : 128 }}"
    {{ $attributes->merge(['class' => 'inline-block shrink-0 rounded-full object-cover ring-1 ring-gold/40']) }}
>
