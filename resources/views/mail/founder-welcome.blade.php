<x-mail::message>
# Welcome to the Society, Founder!

Dear {{ $order->payer_first_name ?: 'Founder' }},

Welcome to {{ config('lunar.brand') }}! Your name is now reserved on the Founders Plaque, a custom aerospace-grade aluminum plaque engraved with the names of 1,000 founding listeners. Our goal is to send it to the Moon.

## Your registry details

- **Name for the plaque:** {{ $order->plaque_name }}
- **Founder number:** {{ $order->slotLabel() }} of 1,000 ({{ $order->tier->name }})
- **Plaque:** 11.5" x 8.5" aerospace-grade 6061-T6 aluminum

Spotted a typo? Reply to this email before **{{ $order->locks_at?->timezone('America/New_York')->format('F j, Y \a\t g:i A T') }}**. After that the name is locked for engraving.

Your digital certificate is attached.

## What happens next

1. **Production:** once all 1,000 slots are filled, we lay out the four-column name grid and engrave the plaque.
2. **Unveiling:** the finished plaque is shown live on the podcast.
3. **The Moon:** we pursue a commercial lunar payload slot. Until then, and if it never flies, the plaque is permanently displayed in the podcast studio.

<x-mail::button :url="route('founders')">
See the Founders list
</x-mail::button>

Thank you for supporting the broadcast.

To the Moon and beyond,<br>
The Lunar Society team, {{ config('lunar.podcast') }}<br>
{{ config('lunar.city') }}

<x-mail::subcopy>
{{ config('lunar.disclaimer') }}
</x-mail::subcopy>
</x-mail::message>
