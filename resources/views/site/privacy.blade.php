{{-- DRAFT: have the client's lawyer review before launch. --}}
<x-site-page eyebrow="Legal" title="Privacy Policy" intro="Last updated {{ now()->format('F j, Y') }}">
    <h2>What we collect</h2>
    <ul>
        <li>Your email address and the name you submit for the plaque.</li>
        <li>Your name and shipping address as provided by PayPal, when an order ships.</li>
        <li>An optional listener handle or comment.</li>
        <li>Your IP address at checkout, to prevent abuse.</li>
    </ul>
    <p>We never receive your card or bank details. PayPal handles payment.</p>

    <h2>How we use it</h2>
    <ul>
        <li>To fulfill your order, engrave the plaque and send your certificate.</li>
        <li>To show your plaque name on the public Founders list, using the display choice you made.</li>
        <li>If you opt in at checkout, to send project updates and the {{ config('lunar.podcast') }} newsletter (via Beehiiv). You can unsubscribe at any time.</li>
    </ul>

    <h2>Sharing</h2>
    <p>We don't sell your data. We share it only with the services that run this project: PayPal (payments), Beehiiv (newsletter), our email provider and our shipping carrier.</p>

    <h2>Contact</h2>
    <p>To access or delete your data, email <a href="mailto:{{ config('lunar.email') }}">{{ config('lunar.email') }}</a>.</p>
</x-site-page>
