@props(['large' => false])

{{-- The Lunar Society badge (rocket over the Moon, "Together Toward a Brighter Tomorrow"). Transparent PNG source. --}}
@php $logoPath = $large ? 'images/logo.webp' : 'images/logo-sm.webp'; @endphp
<img
    src="{{ asset($logoPath) }}?v={{ substr(md5_file(public_path($logoPath)), 0, 8) }}"
    alt=""
    width="{{ $large ? 512 : 160 }}"
    height="{{ $large ? 485 : 152 }}"
    {{ $attributes->merge(['class' => 'inline-block shrink-0 object-contain']) }}
>
