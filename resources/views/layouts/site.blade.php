@props(['title' => null])

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
    <body class="site starfield min-h-screen bg-[#070b1a] text-zinc-100 antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2 focus:text-black">Skip to content</a>

        <livewire:slot-counter variant="banner" />

        <header class="border-b border-white/10 bg-[#070b1a]/80 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold tracking-wide">
                    <span aria-hidden="true" class="grid size-8 place-items-center rounded-full bg-gold text-[#070b1a]">
                        <flux:icon.moon variant="solid" class="size-5" />
                    </span>
                    <span class="leading-tight">Mr SuperTrump's<br class="sm:hidden"> Lunar Society</span>
                </a>

                <nav aria-label="Main" class="ms-auto hidden items-center gap-6 text-sm text-zinc-300 md:flex">
                    <a href="{{ route('founders') }}" class="hover:text-white">Founders</a>
                    <a href="{{ route('faq') }}" class="hover:text-white">FAQ</a>
                    <a href="{{ route('about') }}" class="hover:text-white">About</a>
                    @if (\App\Models\Product::where('active', true)->exists())
                        <a href="{{ route('souvenirs') }}" class="hover:text-white">Souvenirs</a>
                    @endif
                </nav>

                <flux:button href="{{ route('claim') }}" variant="primary" size="sm" class="ms-auto md:ms-0">Claim a slot</flux:button>
            </div>

            <nav aria-label="Main mobile" class="flex justify-center gap-6 border-t border-white/5 py-2 text-sm text-zinc-300 md:hidden">
                <a href="{{ route('founders') }}">Founders</a>
                <a href="{{ route('faq') }}">FAQ</a>
                <a href="{{ route('about') }}">About</a>
            </nav>
        </header>

        <main id="main">
            {{ $slot }}
        </main>

        <footer class="mt-24 border-t border-white/10 bg-[#050814]">
            <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
                {{-- Required disclaimer: plain text, high contrast, on every page. --}}
                <section aria-labelledby="disclaimer-heading" class="rounded-lg border border-white/15 bg-white/5 p-5">
                    <h2 id="disclaimer-heading" class="text-xs font-semibold uppercase tracking-widest text-white">Disclaimer</h2>
                    <p class="mt-2 text-sm leading-relaxed text-white">{{ config('lunar.disclaimer') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-200">{{ config('lunar.satire_notice') }}</p>
                </section>

                <div class="mt-8 flex flex-col gap-6 text-sm text-zinc-300 sm:flex-row sm:justify-between">
                    <div>
                        <p class="font-semibold text-white">{{ config('lunar.entity') }}</p>
                        <p>{{ config('lunar.city') }}</p>
                        <p><a href="mailto:{{ config('lunar.email') }}" class="underline hover:text-white">{{ config('lunar.email') }}</a></p>
                        @if (config('lunar.phone'))
                            <p>{{ config('lunar.phone') }}</p>
                        @endif
                    </div>
                    <nav aria-label="Footer" class="flex flex-wrap gap-x-6 gap-y-2">
                        <a href="{{ route('faq') }}" class="hover:text-white">FAQ</a>
                        <a href="{{ route('terms') }}" class="hover:text-white">Terms of Service</a>
                        <a href="{{ route('privacy') }}" class="hover:text-white">Privacy</a>
                        <a href="{{ route('disclaimer') }}" class="hover:text-white">Disclaimer</a>
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

        @stack('scripts')
        @fluxScripts
    </body>
</html>
