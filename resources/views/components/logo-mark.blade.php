@props(['large' => false])

{{-- The Lunar Society badge (rocket over the Moon, "Together Toward a Brighter Tomorrow"). Transparent PNG source. --}}
<img
    src="{{ asset($large ? 'images/logo.webp' : 'images/logo-sm.webp') }}"
    alt=""
    width="{{ $large ? 512 : 160 }}"
    height="{{ $large ? 485 : 152 }}"
    {{ $attributes->merge(['class' => 'inline-block shrink-0 object-contain']) }}
>
