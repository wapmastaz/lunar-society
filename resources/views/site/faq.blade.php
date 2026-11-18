@php
    $addon = '$'.number_format(config('lunar.couple_addon_cents') / 100);
    $tiers = \App\Models\Tier::all()->keyBy('code');
    $standard = $tiers->get('standard')?->priceLabel() ?? '$149';
    $alpha = $tiers->get('alpha')?->priceLabel() ?? '$199';
    $faqs = [
        ['Who leads the society?', 'Executive Director MJ Schultz. He has created as "Mr SuperTrump" since 2016, using animated promotional art to comment on (and, he would say, predict) political shifts. This lunar initiative grew out of ideas from his first book, The Predictor.'],
        ['What is the Founders Plaque?', 'A custom '.config('lunar.plaque_size').' plaque made of '.config('lunar.plaque_material').', laser engraved with the names of exactly 1,000 supporters and listeners of '.config('lunar.podcast').'.'],
        ['What do I receive?', "For {$standard} (Beta) or {$alpha} (Alpha), you become one of the society's 1,000 founders and receive a gift package: ".mb_strtolower(implode(', ', config('lunar.founder_package'))).". Your name is also engraved on the Founders Plaque and listed on our public Founders list, and if you opt in, you get the Lunar Society newsletter."],
        ['When will my gift package arrive?', 'Physical gift packages are expected to be delivered by '.config('lunar.delivery_by').'. Please consider this delivery timeframe when purchasing a membership as a Christmas gift. Packages are mailed in a '.config('lunar.package_box_size').' box to the shipping address on your registration form.'],
        ['Is the Moon dust real?', 'Yes. It is lunar meteorite dust: material from meteorites that originated on the Moon and fell to Earth, supplied with a certificate of authenticity. It is not an Apollo mission sample.'],
        ['How will the plaque get to the Moon?', 'Our goal is to have it carried by an astronaut and installed at the resource-rich lunar South Pole, within the boundaries of a future international Moon base. That is a goal, not a guarantee: no lunar base exists yet, and flights and payload costs are outside our control. Until the plaque can fly, and if it never does, MJ Schultz keeps it on display in the '.config('lunar.podcast').' studio. Either way, your name is on it.'],
        ['Why '.config('lunar.target_date').'?', 'It is the target MJ Schultz has set: days before the end of the current presidential term, with space agencies discussing lunar surface surveys from 2027 onward. Permanent lunar bases are currently planned for the 2030s, so treat it as a target date, not a promise.'],
        ['When will the plaque be made?', "Once all 1,000 slots are filled. We're targeting 2027 to 2028 and will unveil it live on the podcast."],
        ['How do I make sure my name is spelled right?', 'Check your confirmation email. Reply before the lock date ('.config('lunar.name_lock_hours').' hours after confirmation) to fix a typo. After that, names are locked for engraving.'],
        ['Can I put two names or a family name in one slot?', "Yes, for a {$addon} add-on. Accepted formats include Mr & Mrs John Doe, Mr & Mrs J Doe, Mr & Mrs Doe, John & Jane Doe, Doe Family and John Doe Family. The add-on is applied automatically when you type a name like these."],
        ["What's the difference between Alpha and Beta?", 'Alpha Founders (#001 to #050) are placed in the top rows of the plaque, receive a gold-foil Alpha certificate, and are the positions that determine eligibility for board membership. Positions #001 to #005 are held by MJ Schultz. Beta Founders are #051 to #1000.'],
        ['Will my name be public?', 'Yes, every Founder is listed on our Founders page, shown as first name and last initial.'],
        ['Is this affiliated with President Trump or NASA?', 'No. '.config('lunar.disclaimer')],
        ['What if timelines change?', "Space projects depend on things outside our control, so dates may shift. We'll share every update on the podcast and by email."],
        ['Are there any extra fees?', 'Founder memberships and items that ship include a flat '.\App\Services\Registry::processingFeeLabel().' processing fee, shown before you pay. The Supporter and Generous Supporter contributions have no fee. There are no other charges.'],
        ['How do I pay?', 'All payments are processed securely by PayPal. You can pay with a PayPal account or a card through PayPal.'],
    ];


    $pebble = \App\Models\Product::where('slug', config('lunar.affiliate.slug'))->where('active', true)->first();
    if ($pebble) {
        array_splice($faqs, 3, 0, [
            ['What is the Affiliate Membership Package?', 'For '.$pebble->priceLabel().' (plus the '.\App\Services\Registry::processingFeeLabel().' processing fee) you become an affiliate member of the Lunar Society and receive the Space Pebble Collectible, a commemorative keepsake. The pebble is not lunar material, and the package does not include a Founder slot or a name on the Founders Plaque. It ships to the address you give PayPal at checkout.'],
        ]);
    }

    if (config('lunar.set_aside_recipient')) {
        $faqs[] = ['Does part of the money go to space exploration?', '5% of proceeds will be set aside for '.config('lunar.set_aside_recipient').'.'];
    }
@endphp

<x-site-page title="Frequently Asked Questions" eyebrow="FAQ">
    <div class="mt-10 border-t border-white/10">
        @foreach ($faqs as [$question, $answer])
            <details class="group border-b border-white/10 [&_p]:mt-0" name="faq" @if ($loop->first) open @endif data-reveal style="--reveal-delay: {{ min($loop->index, 6) * 70 }}ms">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-6 text-lg font-medium text-white [&::-webkit-details-marker]:hidden">
                    {{ $question }}
                    <span aria-hidden="true" class="grid size-8 shrink-0 place-items-center rounded-full border border-white/20 text-gold transition duration-300 group-open:rotate-45 group-open:border-gold/60 group-hover:border-gold/60">
                        <flux:icon.plus variant="micro" class="size-4" />
                    </span>
                </summary>
                <p class="pb-7 pe-12">{{ $answer }}</p>
            </details>
        @endforeach
    </div>

    <p class="!mt-10">Still have a question? Email <a href="mailto:{{ config('lunar.email') }}">{{ config('lunar.email') }}</a>.</p>
</x-site-page>
