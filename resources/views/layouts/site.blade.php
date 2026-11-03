@props(['title' => null])

@php
    $nav = [
        ['founders', 'Founders'],
        ['faq', 'FAQ'],
        ['about', 'About'],
    ];

    if (\App\Models\Product::where('active', true)->exists()) {
        $nav[] = ['souvenirs', 'Souvenirs'];
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => $title])
        <meta name="description" content="Put your name on the Founders Plaque of {{ config('lunar.brand') }}. Only 1,000 names. An independent project of the {{ config('lunar.podcast') }} podcast.">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @if (config('services.paypal.client_id'))
            <meta name="paypal-client-id" content="{{ config('services.paypal.client_id') }}">
        @endif
    </head>
    <body class="site starfield min-h-screen bg-ink text-zinc-100 antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2 focus:text-black">Skip to content</a>

        <livewire:slot-counter variant="banner" />

        <header class="border-b border-white/[0.06] bg-ink/80 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3 font-semibold">
                    <x-moon-mark class="size-9" />
                    <span class="leading-tight sm:text-lg">Mr SuperTrump's<br class="sm:hidden"> Lunar Society</span>
                </a>

                <nav aria-label="Main" class="ms-auto hidden items-center gap-8 md:flex">
                    @foreach ($nav as [$route, $label])
                        <a
                            href="{{ route($route) }}"
                            @class(['text-[15px] transition', 'text-gold' => request()->routeIs($route), 'text-zinc-300 hover:text-white' => ! request()->routeIs($route)])
                            @if (request()->routeIs($route)) aria-current="page" @endif
                        >{{ $label }}</a>
                    @endforeach
                </nav>

                <flux:button href="{{ route('claim') }}" variant="primary" class="ms-auto md:ms-0">Claim a slot</flux:button>
            </div>

            <nav aria-label="Main mobile" class="flex justify-center gap-6 border-t border-white/5 py-2 text-sm md:hidden">
                @foreach ($nav as [$route, $label])
                    <a href="{{ route($route) }}" @class(['text-gold' => request()->routeIs($route), 'text-zinc-300' => ! request()->routeIs($route)])>{{ $label }}</a>
                @endforeach
            </nav>
        </header>

        <main id="main">
            {{ $slot }}
        </main>

        <footer class="mt-24 border-t border-white/[0.06] bg-ink-deep">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                {{-- Required disclaimer: plain text, high contrast, on every page. --}}
                <section aria-labelledby="disclaimer-heading" class="rounded-2xl border border-white/10 p-6">
                    <h2 id="disclaimer-heading" class="font-mono text-xs uppercase tracking-[0.25em] text-white">Disclaimer</h2>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-200">{{ config('lunar.disclaimer') }}</p>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-300">{{ config('lunar.satire_notice') }}</p>
                </section>

                <div class="mt-8 flex flex-col gap-6 text-sm sm:flex-row sm:justify-between">
                    <div class="text-zinc-300">
                        <p class="font-semibold text-white">{{ config('lunar.entity') }}</p>
                        <p>{{ config('lunar.city') }}</p>
                        <p><a href="mailto:{{ config('lunar.email') }}" class="text-gold underline underline-offset-2 hover:text-gold-light">{{ config('lunar.email') }}</a></p>
                        @if (config('lunar.phone'))
                            <p>{{ config('lunar.phone') }}</p>
                        @endif
                    </div>
                    <nav aria-label="Footer" class="flex flex-wrap gap-x-6 gap-y-2 text-zinc-300 sm:justify-end">
                        <a href="{{ route('terms') }}" class="hover:text-white">Terms of Service</a>
                        <a href="{{ route('privacy') }}" class="hover:text-white">Privacy</a>
                        <a href="{{ route('disclaimer') }}" class="hover:text-white">Disclaimer</a>
                        <a href="{{ route('faq') }}" class="hover:text-white">FAQ</a>
                    </nav>
                </div>

                <p class="mt-8 text-xs text-zinc-400">&copy; {{ now()->year }} {{ config('lunar.entity') }}. {{ config('lunar.podcast') }} is available on Spotify, YouTube and Beehiiv.</p>
            </div>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
