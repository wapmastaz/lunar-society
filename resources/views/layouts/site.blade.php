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
        @include('partials.head', ['title' => $title, 'forceDark' => true])
        <meta name="description" content="Put your name on the Founders Plaque of {{ config('lunar.brand') }}. Only 1,000 names. An independent project of the {{ config('lunar.podcast') }} podcast.">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- Opt in to scroll reveals; unhide everything if app.js never runs. --}}
        <script>
            document.documentElement.classList.add('reveal-ready');
            setTimeout(() => { if (!window.__revealInit) document.documentElement.classList.remove('reveal-ready') }, 2500);
        </script>
        @if (config('services.paypal.client_id'))
            <meta name="paypal-client-id" content="{{ config('services.paypal.client_id') }}">
        @endif
    </head>
    <body class="site starfield min-h-screen bg-ink text-zinc-100 antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2 focus:text-black">Skip to content</a>

        <livewire:slot-counter variant="banner" />

        <div x-data="{ menu: false }" @keydown.escape.window="menu = false">
            <header class="border-b border-white/[0.06] bg-ink/80 backdrop-blur">
                <div class="mx-auto flex max-w-7xl items-center gap-2 px-4 py-2 sm:gap-4 sm:px-6 sm:py-4">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5 font-semibold sm:gap-3">
                        <x-moon-mark class="size-7 sm:size-9" />
                        <span class="truncate text-[15px] leading-tight sm:text-lg"><span class="max-sm:hidden">Mr SuperTrump's </span>Lunar Society</span>
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

                    <flux:button href="{{ route('claim') }}" variant="primary" size="sm" class="ms-auto md:ms-0 md:h-10 md:px-4 md:text-sm">Claim a slot</flux:button>

                    <button
                        type="button"
                        class="-me-2 grid size-9 place-items-center rounded-lg text-zinc-200 hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-gold md:hidden"
                        @click="menu = true"
                        :aria-expanded="menu"
                        aria-controls="site-menu"
                    >
                        <flux:icon.bars-3 class="size-6" />
                        <span class="sr-only">Open menu</span>
                    </button>
                </div>
            </header>

            {{-- Mobile drawer. Kept outside the header: its backdrop-blur would trap fixed children. --}}
            <div class="md:hidden" :class="menu ? '' : 'pointer-events-none'">
                <div
                    x-show="menu"
                    x-cloak
                    x-transition.opacity.duration.300ms
                    class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm"
                    @click="menu = false"
                    aria-hidden="true"
                ></div>

                <div
                    id="site-menu"
                    x-show="menu"
                    x-cloak
                    x-trap.noscroll.inert="menu"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="starfield fixed inset-y-0 right-0 z-50 flex w-[86%] max-w-sm flex-col border-l border-white/10 bg-panel shadow-2xl shadow-black"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Menu"
                >
                    <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                        <span class="flex items-center gap-3 font-semibold">
                            <x-moon-mark class="size-8" />
                            Lunar Society
                        </span>
                        <button type="button" class="-me-2 grid size-10 place-items-center rounded-lg text-zinc-300 hover:bg-white/10 hover:text-white" @click="menu = false">
                            <flux:icon.x-mark class="size-6" />
                            <span class="sr-only">Close menu</span>
                        </button>
                    </div>

                    <nav aria-label="Mobile" class="flex-1 overflow-y-auto px-5 py-6">
                        <p class="eyebrow">Menu</p>
                        <ul class="mt-4 divide-y divide-white/[0.07]">
                            @foreach (array_merge([['home', 'Home']], $nav) as [$route, $label])
                                @php $current = request()->routeIs($route); @endphp
                                <li
                                    class="motion-safe:transition motion-safe:duration-500 motion-safe:ease-out"
                                    :class="menu ? 'translate-x-0 opacity-100' : 'motion-safe:translate-x-6 motion-safe:opacity-0'"
                                    style="transition-delay: {{ 120 + $loop->index * 60 }}ms"
                                >
                                    <a
                                        href="{{ route($route) }}"
                                        @class(['group flex items-center justify-between py-4 font-display text-3xl transition', 'text-gold' => $current, 'text-white hover:text-gold' => ! $current])
                                        @if ($current) aria-current="page" @endif
                                    >
                                        {{ $label }}
                                        <flux:icon.arrow-up-right variant="micro" class="size-5 text-zinc-500 transition group-hover:text-gold" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>

                    <div class="space-y-4 border-t border-white/10 px-5 py-5">
                        <p class="font-mono text-xs uppercase tracking-[0.2em] text-zinc-400">
                            <span class="text-gold">{{ number_format(config('lunar.capacity') - app(\App\Services\Registry::class)->claimedTotal()) }}</span> of {{ number_format(config('lunar.capacity')) }} slots left
                        </p>
                        <flux:button href="{{ route('claim') }}" variant="primary" icon:trailing="arrow-right" class="h-12 w-full text-base">Claim your Founder slot</flux:button>
                        <a href="mailto:{{ config('lunar.email') }}" class="block text-center text-sm text-zinc-400 hover:text-white">{{ config('lunar.email') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <main id="main">
            {{ $slot }}
        </main>

        <footer class="mt-24 border-t border-white/[0.06] bg-ink-deep">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
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
