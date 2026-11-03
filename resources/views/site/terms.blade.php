{{-- DRAFT: have the client's lawyer review before launch. --}}
<x-site-page title="Terms of Service" intro="Last updated {{ now()->format('F j, Y') }}">
    <h2>1. Who we are</h2>
    <p>This website is operated by {{ config('lunar.entity') }}, {{ config('lunar.city') }} ("we", "us"). Contact: <a href="mailto:{{ config('lunar.email') }}">{{ config('lunar.email') }}</a>.</p>

    <h2>2. What you are buying</h2>
    <p>A Founder slot gives you:</p>
    <ul>
        <li>a numbered place for one name (or one couple/family name with the add-on) on the Lunar Society Founders Plaque;</li>
        <li>a digital Founder certificate;</li>
        <li>a listing on our public Founders page.</li>
        @if (config('lunar.pebble_included'))
            <li>a commemorative lunar-simulant space pebble in a gift box (not lunar material).</li>
        @endif
    </ul>
    <p>Souvenirs are physical merchandise and do not include a Founder slot.</p>

    <h2>3. No guarantee of lunar delivery</h2>
    <p>Sending the plaque to the Moon is our goal, not a promise. If it cannot fly, the plaque will be permanently displayed in the {{ config('lunar.podcast') }} studio. This outcome is not grounds for a refund.</p>

    <h2>4. Timelines</h2>
    <p>All dates are estimates. Manufacturing begins once all 1,000 slots are filled. Space-related milestones depend on third parties and may change.</p>

    <h2>5. Names</h2>
    <p>Names may contain letters, spaces and . ' &amp; - only, up to {{ config('lunar.name_max_length') }} characters. You can request a correction by replying to your confirmation email within {{ config('lunar.name_lock_hours') }} hours. After that the name is locked. We may refuse or edit names that are offensive, infringe someone's rights or impersonate others.</p>

    <h2>6. Refunds</h2>
    <p>[CONFIRM WITH CLIENT] You may request a full refund within 14 days of purchase. A refunded slot is released and its number may be reassigned.</p>

    <h2>7. Public Founders list</h2>
    <p>Your plaque name appears on our Founders page. By default we show first name and last initial unless you chose to show your full name.</p>

    <h2>8. Independence</h2>
    <p>{{ config('lunar.disclaimer') }}</p>

    <h2>9. Payments</h2>
    <p>Payments are processed by PayPal. We never see or store your card details.</p>

    <h2>10. Governing law</h2>
    <p>These terms are governed by the laws of the State of Florida.</p>
</x-site-page>
