<x-mail::message>
# Thank you for your order!

Hi {{ $order->payer_first_name ?: 'there' }},

Your order for **{{ $order->product->name }}** ({{ $order->amountLabel() }}) is confirmed. We'll email you when it ships to the address you gave PayPal.

Questions? Just reply to this email.

To the Moon and beyond,<br>
The Lunar Society team, {{ config('lunar.podcast') }}

<x-mail::subcopy>
{{ config('lunar.disclaimer') }}
</x-mail::subcopy>
</x-mail::message>
