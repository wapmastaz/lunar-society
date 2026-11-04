# Launch audit: checklist vs. live site

Audited November 4, 2026 against https://lunarsocietyregistry.com (Hostinger) and the code on `main`.

**Status key:**
- ✅ done and checked
- 🟡 done, with a caveat
- ❌ not done yet
- ❓ waiting on Daniel

## Summary

The site and the registration flow are built and live. Every page and link works. Four things block taking real orders by Friday:

1. **PayPal is not connected.** There are no API keys on the server, so the payment step can't load.
2. **Emails aren't sent.** The server uses `MAIL_MAILER=log`, so confirmations and certificates are only written to a file.
3. **Space Pebble details** are still missing (included or not, and what it is).
4. **Domain spelling:** the checklist says LunaSocietyRegistry.com, but the purchased domain is lunarsocietyregistry.com.

## Website setup

| Item | Status | Notes |
|---|---|---|
| Build the site on **LunaSocietyRegistry.com** | 🟡 | Built on **lunarsocietyregistry.com**, the domain that was actually bought. `lunasocietyregistry.com` (no "r") doesn't resolve and isn't owned. Daniel should confirm the spelling for all promos. |
| Connect the GoDaddy domain to Hostinger | ✅ | Nameservers point to Hostinger. `www` and the bare domain both serve the site over HTTPS, with a Let's Encrypt certificate valid to Feb 2027. |
| Professional homepage using his introduction | ✅ | Live, with the revisions listed in `client-intro-revisions.md`. |
| Email on the domain | ❌ | The MX records point to **Hostinger mail**, not the Microsoft 365 plan bought at GoDaddy. Mail to `@lunarsocietyregistry.com` goes to Hostinger, not Microsoft 365. Either recreate the Microsoft 365 records in Hostinger DNS or use Hostinger mail and cancel the 365 plan. |

## Homepage content

| Item | Status | Notes |
|---|---|---|
| Lunar Society introduction | ✅ | The "Lunar Society" section. |
| His profile as Executive Director | ✅ | A profile card on the homepage linking to the full About page. A portrait photo is still needed (`LUNAR_HOST_PORTRAIT`). |
| Goal of 1,000 foundational MAGA community Trump supporters | ✅ | Word for word, as Daniel requested. |
| Astronaut Plaque project and proposed destination | ✅ | The "Astronaut Plaque Pledge" cards. The South Pole and January 18, 2029 are presented as goals. |
| Space Pebble Collectible information | ❓ | Built, but switched off until Daniel confirms it's included and describes it accurately. One `.env` change turns it on. |
| Beehiiv newsletter benefits | 🟡 | The section is live. Automatic sign-up of buyers is built but switched off until Beehiiv API keys are added. |
| Clear Join the Registry button | ✅ | In the header, the hero, the tier cards, the closing section and the mobile menu. |

## Registration and payment page (`/claim`)

| Item | Status | Notes |
|---|---|---|
| Page with the registration form | ✅ | A six-step wizard: tier, details, engraving, review, payment, done. |
| What each founder receives | ✅ | The "Every founder receives" list on step 1, plus the homepage tier cards. |
| Packages and prices | ✅ | Alpha $199 (#001 to #050), Standard $149 (#051 to #1000), and a +$30 couple/family add-on. The sold-out state is real. |
| Space Pebble offered separately | ❓ | The souvenirs store is built. It needs the product names, prices and photos. The page stays hidden until there's at least one product. |
| PayPal payments to his account | ❌ | The code is done and tested with simulated PayPal responses. The server has **no** `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET` or `PAYPAL_WEBHOOK_ID`. Daniel creates a REST app in his PayPal developer dashboard and shares the keys, or pastes them into `.env` himself. |
| Collect customer information | ✅ | Full name, email, name for the plaque, optional listener handle and public-name choice. PayPal adds the payer name, plus the shipping address once the pebble is on. |

## Confirmation and legal

| Item | Status | Notes |
|---|---|---|
| Registration and payment confirmation | 🟡 | The confirmation page works (step 6, with the Founder number). The **confirmation email and PDF certificate are not sent yet** (`MAIL_MAILER=log`, sender `hello@example.com`). It needs a mail provider (Postmark or Resend) and a from-address on the domain. |
| Disclaimer and agreement checkboxes | ✅ | Two unticked, required checkboxes. The independence wording is Daniel's, verbatim, and the consent text is stored with each order. |
| Lunar delivery not guaranteed | ✅ | Stated in the pledge cards, the FAQ, the Terms, the Disclaimer page, and now beside checkout. |
| Disclaimer visible, especially during checkout | ✅ | Plain-text footer on every page, plus a "Please note" box next to the order summary on every checkout step. |
| Terms and Privacy | 🟡 | Drafts. The refund policy is still a placeholder, and both need a lawyer's review. |

## Testing before Friday

| Item | Status | Notes |
|---|---|---|
| Registration form | ✅ | Automated tests cover every wizard step: validation, the 30-character and letters-only rules, the couple add-on, consents, going back and releasing the hold, and sold-out tiers. |
| PayPal payment process | 🟡 | Automated tests use simulated PayPal responses: create, capture, duplicate capture, wrong amount, expired hold, webhooks, refunds. **A real sandbox purchase can't be run until the keys exist.** |
| Customer details stored correctly | ✅ | Covered by tests. The server database is migrated and empty (0 orders). |
| All buttons and links | ✅ | Crawled every link on all public pages of the live site, and every one returns 200. That includes assets, fonts, `/claim/alpha`, `/claim/standard` and the MrSuperTrump.com link. |
| Homepage to registration to payment | 🟡 | Every page through to the payment step works. The payment step itself shows "PayPal is not configured yet" until the keys are added. |
| Cron (slot holds and email queue) | ✅ | Runs every minute on PHP 8.4. Checked with a test hold that expired on schedule, then deleted. |

## Other items found

- **Admin account:** two-factor authentication is **off**. Turn it on under Settings → Security after the first login.
- **Database password** is still the one first chosen in hPanel. Change it to a strong random one, then update `DB_PASSWORD` in the server's `.env`.
- **Contact email** shown on the site is still `MrSuperTrump77@gmail.com` (`LUNAR_EMAIL`). Switch it once a domain mailbox works.
- **Business name** is still "Mr SuperTrump" (`LUNAR_ENTITY`). It needs the exact legal name on the PayPal account.
- **5% set-aside:** hidden until a named recipient is provided.
- **Icons:** the tab icon and home-screen icon are now the gold moon, replacing Laravel's default.

## To be ready by Friday

**Daniel:**
1. Send the PayPal REST app keys and webhook ID (sandbox first, then live).
2. Confirm "Lunar" vs "Luna", and the Space Pebble details.
3. Provide the legal business name and refund policy.

**Us:**
4. Set up Postmark or Resend and add their DNS records.
5. Fix the email DNS (Microsoft 365 or Hostinger mail).
6. Run a full sandbox purchase on the live site: confirmation email, certificate, admin export.
7. Switch PayPal to live.
