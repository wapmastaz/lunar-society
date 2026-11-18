<x-mail::message>
# New payment: {{ $order->classLabel() }}

A payment just came in and has been sorted into its class automatically. The buyer has been sent their confirmation email.

- **Class:** {{ $order->classLabel() }}@if ($order->slotLabel()) (Founder {{ $order->slotLabel() }})@endif
- **Paid by PayPal:** {{ $order->amountLabel() }}@if ($order->processing_fee_cents > 0) (includes the ${{ number_format($order->processing_fee_cents / 100) }} fee: {{ config('lunar.fee_note') }})@endif
@if ($order->product?->barter)
- **Barter Sales:** {{ $order->product->priceLabel() }} still to be collected through Barter Sales
@endif
@if ($order->plaque_name)
- **Name for the plaque:** {{ $order->plaque_name }}
@endif
@if ($order->list_name)
- **Name for the {{ $order->product?->listTitle() }}:** {{ $order->list_name }}
@endif
- **Buyer:** {{ $order->buyer_name ?: trim($order->payer_first_name.' '.$order->payer_last_name) ?: '·' }}
- **Email:** {{ $order->email }}
@if ($order->phone)
- **Phone:** {{ $order->phone }}
@endif
- **PayPal capture:** {{ $order->paypal_capture_id ?? '·' }}

<x-mail::button :url="route('admin.orders')">
Open the orders
</x-mail::button>
</x-mail::message>
