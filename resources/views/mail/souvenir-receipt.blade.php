<x-mail::message>
@if ($order->product->ships)
# Thank you for your order!

Hi {{ $order->payer_first_name ?: 'there' }},

Your order for **{{ $order->product->name }}** ({{ $order->amountLabel() }}) is confirmed. It will be mailed to the address you gave PayPal.

@if ($order->product->highlights)
## What you'll receive

@foreach ($order->product->highlights as $item)
- {{ $item }}
@endforeach
@endif

<x-mail::panel>
**Holiday Gift Delivery Notice:** {{ config('lunar.delivery_notice') }}
</x-mail::panel>

This is separate from Registered Founder memberships and does not include a slot on the Founders Plaque.
@else
# Thank you for your support!

Hi {{ $order->payer_first_name ?: 'there' }},

Your **{{ $order->product->name }}** contribution of {{ $order->amountLabel() }} is confirmed. We need a little help from our friends, and you came through. Thank you.

This is separate from Registered Founder memberships and does not include a slot on the Founders Plaque. The Lunar Society is a for-profit organization, so contributions are not tax-deductible.
@endif

Questions? Just reply to this email.

To the Moon and beyond,<br>
The Lunar Society team, {{ config('lunar.podcast') }}

<x-mail::subcopy>
{{ config('lunar.disclaimer') }}
</x-mail::subcopy>
</x-mail::message>
