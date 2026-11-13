@php
    $charter = config('lunar.charter');
    $pebbleActive = \App\Models\Product::where('slug', config('lunar.affiliate.slug'))->where('active', true)->exists();

    // Client's Charter text. Article II.2 lists what founders actually receive; II.5 adds the tax note.
    $articles = [
        ['I', 'Name, Structure, and Purpose', [
            ['Name and Legal Nature', 'The organization shall be known as The Lunar Society (hereafter referred to as "the Society"). The Society operates as a for-profit organization, combining commercial enterprise and visionary membership programs with dedicated philanthropic stewardship.'],
            ['Mission', $charter['mission']],
            ['Intergalactic Peace and Cosmic Non-Aggression', 'The Society is dedicated to the active fostering of Intergalactic Peace in Space. We stand firmly for the prevention and avoidance of space wars, whether instigated by rival terrestrial nations or arising from encounters with invading aliens from outer space, should such civilizations exist. All technological, commercial, and philosophical pursuits of the Society shall prioritize de-escalation, planetary defense through diplomacy, and peaceful first-contact protocols.'],
            ['Spirit of Inquiry', 'Discussions shall be conducted in an atmosphere of intellectual generosity, free from dogma, partisanship, or hierarchy. Every member enters as an equal seeker of truth.'],
        ]],
        ['II', 'Membership and Contributions', [
            ['Composition', 'Membership is open to thinkers, builders, artists, scientists, and diplomats from diverse fields who demonstrate a passion for discovery, constructive debate, and cosmic harmony.'],
            ['Size and Founding 1,000 Fellowship', null],
            ['Induction', 'New members may be proposed by any existing fellow or apply directly under the guidance of the Executive Director, subject to the upholding of the Society\'s values.'],
            ['Guests', 'Members may invite visiting scholars, creators, or curious minds to attend online (Zoom) meetings as guests, provided advance notice is given to the host.'],
            ['Donations and Support', 'All donations are welcome and appreciated. While the Society is a for-profit organization, voluntary financial contributions from members, patrons, and supporters directly fuel our mission, programs, and charitable commitments. Because the Society is a for-profit organization, contributions are not tax-deductible.'],
        ]],
        ['III', 'The Intergalactic Peace & Astronaut Children\'s Fund', [
            ['Establishment and 5% Commitment', 'The Lunar Society shall contribute five percent (5%) of all contributions, membership fees, dues, and donations received by the organization to establish and maintain a dedicated, growing fund for the benefit of the children of astronauts who visit, construct, or inhabit the Lunar Moon Base.'],
            ['Purpose and Stewardship', 'This fund shall be continuously grown and faithfully maintained for the specific purpose of supporting the families and children of the pioneering astronauts responsible for establishing and safeguarding Intergalactic Peace on the lunar frontier.'],
        ]],
        ['IV', 'Meetings and Gatherings', [
            ['Schedule', 'In keeping with tradition, the Society shall convene once a month on the Monday nearest the full moon, ensuring safe passage, bright reflection, and clear skies for observation.'],
            ['Format', 'Periodic Small Gatherings shall take place over a shared meal at the home of a hosting member or an agreed-upon venue.'],
            ['Proceedings', 'Each meeting shall feature an informal presentation, demonstration, or provocative question introduced by the host or a designated fellow, followed by open, unfiltered discussion.'],
            ['Confidentiality', 'To encourage bold and unfinished ideas, all discussions are held under the Chatham House Rule: participants are free to use the information received, but neither the identity nor the affiliation of the speaker(s) may be revealed without permission.'],
        ]],
        ['V', 'Governance and Operations', [
            ['Leadership', 'The administrative vision, commercial operations, stewardship of the Founding 1,000 program, and oversight of the Astronaut Children\'s Fund shall be guided by the Executive Director, supported by the fellowship.'],
            ['The Host', 'Responsibility for hosting and moderating monthly chapter conversations rotates among members.'],
            ['The Keeper of the Minutes', 'A rotating fellow shall record brief notes of key experiments, debates, and celestial observations to preserve the Society\'s intellectual lineage.'],
        ]],
        ['VI', 'Amendments and Dissolution', [
            ['Amendments', 'This Charter is a living document and may be amended at any full-moon gathering by a three-fourths consensus of the attending members, with the concurrence of the Executive Director.'],
            ['Dissolution', 'Should the Society cease to operate, it may be dissolved in accordance with applicable corporate laws and the determination of the Executive Director, with any remaining balances held in the Astronaut Children\'s Fund transferred directly to its designated beneficiaries or a qualifying space-peace institution.'],
        ]],
    ];
@endphp

<x-layouts::site title="Charter">
    <article class="mx-auto max-w-4xl px-4 py-14 sm:px-6 sm:py-16">
        <header class="text-center" data-reveal>
            <x-logo-mark large class="mx-auto size-52 drop-shadow-[0_10px_40px_rgba(232,184,74,0.25)] sm:size-64" />
            <p class="eyebrow mt-8">The founding document</p>
            <h1 class="mt-4 font-charter-title text-3xl font-bold uppercase leading-tight tracking-[0.08em] text-gold sm:text-5xl">Charter of the Lunar Society</h1>
        </header>

        <section aria-labelledby="preamble-heading" class="mt-14" data-reveal>
            <h2 id="preamble-heading" class="text-center font-charter-title text-sm font-medium uppercase tracking-[0.3em] text-zinc-300">Preamble</h2>
            <p class="mt-5 text-center font-charter text-2xl italic leading-relaxed text-zinc-100 sm:text-3xl">{{ $charter['preamble'] }}</p>
        </section>

        <div class="mt-16 space-y-14">
            @foreach ($articles as [$numeral, $heading, $clauses])
                <section aria-labelledby="article-{{ $numeral }}" class="border-t border-white/10 pt-10" data-reveal>
                    <p class="font-charter-title text-sm font-medium uppercase tracking-[0.3em] text-gold">Article {{ $numeral }}</p>
                    <h2 id="article-{{ $numeral }}" class="mt-3 font-charter-title text-2xl font-bold uppercase tracking-[0.05em] text-white sm:text-3xl">{{ $heading }}</h2>

                    <ol class="mt-6 space-y-5 font-charter text-xl leading-relaxed text-zinc-300">
                        @foreach ($clauses as [$title, $text])
                            <li class="flex gap-4">
                                <span class="font-charter-title text-base leading-8 text-gold">{{ $loop->iteration }}.</span>
                                <div>
                                    <span class="font-semibold text-white">{{ $title }}:</span>
                                    @if ($text)
                                        {{ $text }}
                                    @else
                                        While active table gatherings may remain intimate to encourage open dialogue, the Society opens its
                                        foundational roster to the first 1,000 Founding Members. Each of these initial 1,000 members receives
                                        a commemorative membership package, suitable as a unique gift or personal keepsake, which includes:
                                        <ul class="mt-3 space-y-2">
                                            @foreach ([
                                                'Their name engraved on the official Founders Plaque, intended for permanent placement within the future Lunar Moon Base. If that cannot happen, the plaque will be permanently displayed in the podcast studio.',
                                                'A personalized certificate, emailed as a PDF, confirming their name and Founder number on the plaque.',
                                                'A welcome package by email with introductory descriptions, history, and guiding principles of the Society.',
                                            ] as $item)
                                                <li class="flex gap-3">
                                                    <span aria-hidden="true" class="mt-3 size-1.5 shrink-0 rotate-45 bg-gold"></span>
                                                    <span>{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <p class="mt-3 text-lg text-zinc-400">
                                            @if ($pebbleActive)
                                                The Space Pebble Collectible is available separately with the <a href="{{ route('souvenirs') }}" class="text-gold underline underline-offset-2 hover:text-gold-light">Affiliate Membership Package</a>.
                                            @else
                                                The Space Pebble Collectible is available separately with the Affiliate Membership Package.
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endforeach
        </div>

        <footer class="mt-16 rounded-3xl border border-gold/40 bg-gradient-to-br from-[#2a2110] via-[#17140d] to-panel p-8 text-center sm:p-12" data-reveal>
            <p class="font-charter text-2xl italic text-zinc-100">{{ $charter['adopted'] }}</p>
            <p class="mt-2 text-zinc-400">{{ $charter['place'] }}</p>
            <p class="mt-8 font-charter-title text-sm font-medium uppercase tracking-[0.3em] text-gold">Executed by</p>
            <p class="mt-3 font-charter text-4xl italic text-white">{{ $charter['signatory'] }}</p>
            <p class="mt-1 text-zinc-300">Executive Director, The Lunar Society</p>
            <flux:button href="{{ route('claim') }}" variant="primary" icon:trailing="arrow-right" class="mt-10 h-12 rounded-full px-7 text-base">Become a Founding Member</flux:button>
        </footer>
    </article>
</x-layouts::site>
