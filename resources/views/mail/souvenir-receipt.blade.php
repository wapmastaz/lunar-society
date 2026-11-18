<x-mail::message>
@if ($order->product->ships)
# Thank you for your order!

Hi {{ $order->payer_first_name ?: 'there' }},

Your order for **{{ $order->product->name }}** ({{ $order->amountLabel() }}) is confirmed. It will be mailed to the address you gave PayPal.

@if ($order->product->barter)
The {{ $order->product->priceLabel() }} package price is paid as a Barter payment through Barter Sales. The {{ $order->amountLabel() }} paid by PayPal covers {{ config('lunar.fee_note') }}.
@else
The {{ \App\Services\Registry::processingFeeLabel() }} fee covers {{ config('lunar.fee_note') }}.
@endif

@if ($order->product->highlights)
## What you'll receive

@foreach ($order->product->highlights as $item)
- {{ $item }}
@endforeach
@endif

<x-mail::panel>
**Holiday Gift Delivery Notice:** {{ config('lunar.delivery_notice') }}
</x-mail::panel>

@if ($order->product->listTitle() && $order->list_name)
**{{ $order->publicName() }}** will appear on our {{ $order->product->listTitle() }}: {{ route('supporters') }}
@endif

This is separate from Registered Founder memberships and does not include a slot on the Founders Plaque.
@else
# Thank you for your support!

Hi {{ $order->payer_first_name ?: 'there' }},

Your **{{ $order->product->name }}** contribution of {{ $order->amountLabel() }} is confirmed. We need a little help from our friends, and you came through. Thank you.

@if ($order->product->listTitle() && $order->list_name)
**{{ $order->publicName() }}** will appear on our {{ $order->product->listTitle() }}@if ($order->product->listing === 'boosters'), published on our site after {{ \Illuminate\Support\Carbon::parse(config('lunar.booster_list_from'))->format('F j') }}@endif: {{ route('supporters') }}
@endif

This is separate from Registered Founder memberships and does not include a slot on the Founders Plaque. The Lunar Society Network is a for-profit organization, so contributions are not tax-deductible.
@endif

Questions? Just reply to this email.

To the Moon and beyond,<br>
The Lunar Society Network team, {{ config('lunar.podcast') }}

<x-mail::subcopy>
{{ config('lunar.disclaimer') }}
</x-mail::subcopy>
</x-mail::message>
