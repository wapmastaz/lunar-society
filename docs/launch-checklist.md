# Launch checklist

## Waiting on the client

These are set in `.env`, read through `config/lunar.php`.

- [ ] Legal entity name on the PayPal account (`LUNAR_ENTITY`)
- [ ] Domain (`LUNAR_DOMAIN`) and DNS
- [ ] City: Boca Raton or Delray Beach (`LUNAR_CITY`)
- [ ] Pebble included? (`LUNAR_PEBBLE_INCLUDED`). This turns on PayPal shipping address collection, the shipping cutoffs and the pebble copy.
- [ ] Recipient of the 5% set-aside (`LUNAR_SET_ASIDE_RECIPIENT`). The FAQ entry stays hidden until this is set.
- [ ] Refund policy. Section 6 of `resources/views/site/terms.blade.php` is a placeholder.
- [ ] Lawyer review of the Terms, the Privacy policy and the "Trumpian" naming
- [ ] Souvenir items. Add rows to the `products` table (name, slug, price_cents, image_path).
- [ ] Toll-free number (`LUNAR_PHONE`)

## PayPal

The client does these steps in their own PayPal developer dashboard.

1. Create a REST app.
2. Copy the sandbox Client ID and Secret into `PAYPAL_CLIENT_ID` and `PAYPAL_SECRET`.
3. Add a webhook to `https://<domain>/paypal/webhook` with these events:
   - `PAYMENT.CAPTURE.COMPLETED`
   - `PAYMENT.CAPTURE.REFUNDED`
   - `PAYMENT.CAPTURE.REVERSED`
4. Put the webhook ID in `PAYPAL_WEBHOOK_ID`.
5. Run a sandbox test purchase end to end, then a sandbox refund.
6. Switch to `PAYPAL_MODE=live` with the live credentials and the live webhook.

## Server

- [ ] `php artisan migrate --seed` with `ADMIN_EMAIL` and `ADMIN_PASSWORD` set (this creates the admin login)
- [ ] Queue worker running (`php artisan queue:work`). It sends the emails, PDFs and Beehiiv subscriptions.
- [ ] Scheduler cron running (`php artisan schedule:run` every minute). It releases expired 15-minute holds.
- [ ] Real mail driver (Postmark, SES or Resend), with `MAIL_FROM_ADDRESS` on the client's domain
- [ ] Beehiiv API key and publication ID, then `BEEHIIV_ENABLED=true`
- [ ] HTTPS on
