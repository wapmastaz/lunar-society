<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@if ($forceDark ?? false)
    {{-- The public site is dark-only: ignore the visitor's system setting
         without touching the admin's saved Flux appearance preference. --}}
    <style>:root { color-scheme: dark; }</style>
    <script>
        window.Flux = { applyAppearance () { document.documentElement.classList.add('dark') } }
    </script>
@else
    @fluxAppearance
@endif
