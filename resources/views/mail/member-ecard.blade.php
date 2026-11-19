<x-mail::message>
<div style="text-align: center; padding: 8px 0 4px;">
<img src="{{ asset('images/logo.webp') }}" alt="The Lunar Society Network" width="180" style="width: 180px; max-width: 60%; height: auto;">
</div>

<div style="text-align: center;">

# Welcome to the Society!

**You are now a New Lunar Society Member.**

</div>

<x-mail::panel>
Dear {{ $order->greetingName() ?? ($order->payer_first_name ?: 'friend') }},

Thank you for your support of {{ config('lunar.brand') }}. You have joined a fellowship of thinkers, builders and dreamers who believe in Intergalactic Peace and the journey to the Moon. We are glad to have you with us.
</x-mail::panel>

@if (config('lunar.member_offers'))
## Your special member offers

@foreach (config('lunar.member_offers') as $offer)
- {{ $offer }}
@endforeach
@else
Watch your inbox: your special member offers will follow by email.
@endif

@if ($order->list_name)
**{{ $order->publicName() }}** will appear on our New Members list: {{ route('supporters') }}
@endif

<x-mail::button :url="route('charter')">
Read our Founders Charter
</x-mail::button>

Your membership ({{ $order->amountLabel() }}) is separate from Registered Founder memberships: nothing is shipped, and it does not include a slot on the Founders Plaque. The Lunar Society Network is a for-profit organization, so contributions are not tax-deductible.

Questions? Just reply to this email.

To the Moon and beyond,<br>
The Lunar Society Network team, {{ config('lunar.podcast') }}

<x-mail::subcopy>
{{ config('lunar.disclaimer') }}
</x-mail::subcopy>
</x-mail::message>
