@php
    $addon = '$'.number_format(config('lunar.couple_addon_cents') / 100);
    $faqs = [
        ['What is the Founders Plaque?', 'A custom 11.5" x 8.5" plaque made of aerospace-grade 6061-T6 aluminum, laser engraved with the names of exactly 1,000 supporters and listeners of '.config('lunar.podcast').'.'],
        ['Is my name really going to the Moon?', "That's our goal, not a guarantee. We intend to pursue a commercial lunar payload slot as commercial Moon missions mature, likely in the mid-2030s. Space timelines and payload costs are outside our control. If the plaque can't fly, it will be permanently displayed in the ".config('lunar.podcast').' studio. Either way, your name is on it.'],
        ['When will the plaque be made?', "Once all 1,000 slots are filled. We're targeting 2027 to 2028 and will unveil it live on the podcast."],
        ['How do I make sure my name is spelled right?', 'Check your confirmation email. Reply before the lock date ('.config('lunar.name_lock_hours').' hours after confirmation) to fix a typo. After that, names are locked for engraving.'],
        ['Can I put two names or a family name in one slot?', "Yes, for a {$addon} add-on. Accepted formats include Mr & Mrs John Doe, Mr & Mrs J Doe, Mr & Mrs Doe, John & Jane Doe, Doe Family and John Doe Family. The add-on is applied automatically when you type a name like these."],
        ["What's the difference between Alpha and Standard?", 'Alpha Founders (#001 to #050) are placed in the top rows of the plaque and receive a gold-foil Alpha certificate. Standard Founders are #051 to #1000.'],
        ['Will my name be public?', 'Yes, every Founder is listed on our Founders page. By default we show first name and last initial. You can choose to show your full name at checkout.'],
        ['Is this affiliated with President Trump or NASA?', 'No. '.config('lunar.disclaimer')],
        ['What if timelines change?', "Space projects depend on things outside our control, so dates may shift. We'll share every update on the podcast and by email."],
        ['How do I pay?', 'All payments are processed securely by PayPal. You can pay with a PayPal account or a card through PayPal.'],
    ];

    if (config('lunar.pebble_included')) {
        array_splice($faqs, 6, 0, [
            ['What is the space pebble?', 'A commemorative lunar-simulant stone, beautifully packaged for gifting. It is not lunar material.'],
            ['Can I send it as a Christmas gift?', "Yes. Enter the recipient's address in PayPal at checkout. There's no price receipt in the box. Order by ".config('lunar.shipping_cutoffs.standard').' for standard shipping or '.config('lunar.shipping_cutoffs.expedited')." for expedited shipping. If you miss the cutoff, you'll still receive a printable digital certificate to give on Christmas morning."],
        ]);
    }

    if (config('lunar.set_aside_recipient')) {
        $faqs[] = ['Does part of the money go to space exploration?', '5% of proceeds will be set aside for '.config('lunar.set_aside_recipient').'.'];
    }
@endphp

<x-site-page title="Frequently Asked Questions" eyebrow="FAQ">
    <div class="mt-10 border-t border-white/10">
        @foreach ($faqs as [$question, $answer])
            <details class="group border-b border-white/10 [&_p]:mt-0" name="faq" @if ($loop->first) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-6 text-lg font-medium text-white [&::-webkit-details-marker]:hidden">
                    {{ $question }}
                    <span aria-hidden="true" class="grid size-8 shrink-0 place-items-center rounded-full border border-white/20 text-gold transition group-open:rotate-45">
                        <flux:icon.plus variant="micro" class="size-4" />
                    </span>
                </summary>
                <p class="pb-7 pe-12">{{ $answer }}</p>
            </details>
        @endforeach
    </div>

    <p class="!mt-10">Still have a question? Email <a href="mailto:{{ config('lunar.email') }}">{{ config('lunar.email') }}</a>.</p>
</x-site-page>
