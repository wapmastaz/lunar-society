<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: DejaVu Serif, serif; color: #1c1917; }
        .page { position: absolute; inset: 0; padding: 36px; background: {{ $order->tier->code === 'alpha' ? '#fbf6e9' : '#f5f5f4' }}; }
        .frame { border: 6px double {{ $order->tier->code === 'alpha' ? '#b8860b' : '#44403c' }}; height: 600px; padding: 48px 48px; text-align: center; }
        .eyebrow { font-family: DejaVu Sans, sans-serif; letter-spacing: 4px; font-size: 12px; text-transform: uppercase; color: #57534e; }
        h1 { font-size: 34px; margin: 14px 0 6px; letter-spacing: 1px; }
        .tier { font-family: DejaVu Sans, sans-serif; font-size: 14px; color: {{ $order->tier->code === 'alpha' ? '#b8860b' : '#44403c' }}; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        .certifies { margin-top: 34px; font-size: 15px; color: #44403c; }
        .name { font-size: 40px; margin: 14px 0; font-weight: bold; }
        .body { font-size: 14px; line-height: 1.6; color: #44403c; margin: 0 40px; }
        .meta { margin-top: 30px; font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #57534e; }
        .meta strong { color: #1c1917; }
        .sign { margin-top: 26px; font-style: italic; font-size: 16px; }
        .fine { position: absolute; bottom: 44px; left: 84px; right: 84px; font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #78716c; text-align: center; }
    </style>
</head>
<body>
<div class="page">
    <div class="frame">
        <div class="eyebrow">{{ config('lunar.brand') }}</div>
        <h1>Certificate of Founder Registry</h1>
        <div class="tier">{{ $order->tier->name }}</div>

        <div class="certifies">This certifies that</div>
        <div class="name">{{ $order->plaque_name }}</div>
        <div class="body">
            is registered as a Founder of the Lunar Society and will be laser engraved on the Founders Plaque.
            Our goal is to carry the plaque to the Moon on a commercial payload. Until then, it will be displayed
            in the {{ config('lunar.podcast') }} studio.
        </div>

        <div class="meta">
            Founder number <strong>{{ $order->slotLabel() }}</strong> of 1,000
            &nbsp;&nbsp;|&nbsp;&nbsp; Registered <strong>{{ $order->paid_at?->format('F j, Y') }}</strong>
        </div>

        <div class="sign">"To the Moon, Mars, and Beyond!" Mr SuperTrump</div>
    </div>
    <div class="fine">{{ config('lunar.disclaimer') }}</div>
</div>
</body>
</html>
