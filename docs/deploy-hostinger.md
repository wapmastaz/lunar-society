# Deploying to Hostinger (shared/cloud hosting)

| | |
|---|---|
| Server | `72.60.93.25`, SSH port `65002` |
| User | `u840504408` |
| Host key (ED25519) | `SHA256:0jSSHZf7X3GpDpont5uo1lSTPdqRkb5U33kw6h218BE` |
| App folder | `~/domains/lunarsocietyregistry.com/app` |
| Web folder | `~/domains/lunarsocietyregistry.com/public_html`, a symlink to `app/public` |
| Repo | `git@github.com:wapmastaz/lunar-society.git` (`main`) |

## 0. DNS: keep Microsoft 365 email working

The nameservers now point to Hostinger. Recreate the Microsoft 365 records under hPanel, then **Domains**, then **DNS records**. Copy the exact values from GoDaddy's DNS page or the Microsoft 365 admin center:

- `MX @`: `lunarsocietyregistry-com.mail.protection.outlook.com` (priority 0)
- `TXT @`: `v=spf1 include:spf.protection.outlook.com -all`
- `CNAME autodiscover`: `autodiscover.outlook.com`
- Any DKIM `selector1._domainkey` and `selector2._domainkey` CNAMEs

If a sending service (Postmark or Resend) is added later, merge it into this **same** SPF record. Never add a second SPF record.

## 1. One-time setup

1. **SSH key.** In hPanel, go to Advanced, then SSH Access, then SSH keys, and add your Mac's public key (`~/.ssh/id_ed25519.pub`). Then connect:

   ```bash
   ssh -p 65002 u840504408@72.60.93.25
   ```

2. **Deploy key for GitHub.** Run this on the server:

   ```bash
   ssh-keygen -t ed25519 -C "hostinger-lunar" -N "" -f ~/.ssh/id_ed25519 && cat ~/.ssh/id_ed25519.pub
   ```

   On GitHub, open the repo's Settings, then Deploy keys, and add the printed key with read-only access.

3. **PHP 8.4.** In hPanel, go to Advanced, then PHP Configuration.

4. **Database.** In hPanel, go to Databases and create a MySQL database and user.

5. **Clone and install:**

   ```bash
   cd ~/domains/lunarsocietyregistry.com
   git clone git@github.com:wapmastaz/lunar-society.git app
   cd app
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   ```

6. **Edit `.env`** (`nano .env`):

   ```
   APP_NAME="Mr SuperTrump's Lunar Society"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://lunarsocietyregistry.com

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=...
   DB_USERNAME=...
   DB_PASSWORD=...

   QUEUE_CONNECTION=database
   QUEUE_VIA_SCHEDULER=true

   ADMIN_EMAIL=...
   ADMIN_PASSWORD=...

   PAYPAL_MODE=sandbox
   PAYPAL_CLIENT_ID=...
   PAYPAL_SECRET=...
   PAYPAL_WEBHOOK_ID=...

   MAIL_MAILER=...   # see Mail below
   ```

7. **Migrate and seed.** This creates the tiers and the admin login:

   ```bash
   php artisan migrate --seed --force && php artisan optimize
   ```

8. **Point the web folder at Laravel's `public/`:**

   ```bash
   cd ~/domains/lunarsocietyregistry.com && mv public_html public_html_old && ln -s ~/domains/lunarsocietyregistry.com/app/public public_html
   ```

9. **Upload the built CSS/JS from your Mac.** `public/build` is not in git:

   ```bash
   cd ~/Herd/lunar-society && npm run build && scp -P 65002 -r public/build u840504408@72.60.93.25:domains/lunarsocietyregistry.com/app/public/
   ```

10. **Cron.** In hPanel, go to Advanced, then Cron Jobs. Add a job that runs every minute:

    ```
    cd /home/u840504408/domains/lunarsocietyregistry.com/app && php artisan schedule:run >> /dev/null 2>&1
    ```

    This job releases expired slot holds. Because `QUEUE_VIA_SCHEDULER=true`, it also sends the queued confirmation emails, certificates and Beehiiv sign-ups.

11. **SSL.** In hPanel, go to Security, then SSL, and install the free certificate.

## 2. Deploying updates

Push to `main`. Then run this on your Mac (rebuild the assets only if CSS or JS changed):

```bash
ssh -p 65002 u840504408@72.60.93.25 'cd ~/domains/lunarsocietyregistry.com/app && git pull && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan optimize'
```

```bash
cd ~/Herd/lunar-society && npm run build && scp -P 65002 -r public/build u840504408@72.60.93.25:domains/lunarsocietyregistry.com/app/public/
```

## 3. Checks after go-live

- `https://lunarsocietyregistry.com` loads, and the disclaimer appears in the footer.
- `/login` works with the admin account.
- Make a PayPal sandbox purchase. Within about a minute the confirmation email arrives and the order shows as paid in `/admin`.
- In the PayPal webhook settings, add `https://lunarsocietyregistry.com/paypal/webhook` with these events:
  - `PAYMENT.CAPTURE.COMPLETED`
  - `PAYMENT.CAPTURE.REFUNDED`
  - `PAYMENT.CAPTURE.REVERSED`

## Mail

Shared hosting mail and Microsoft 365 both throttle app email. Use a transactional sender (Postmark or Resend). Set `MAIL_MAILER` and its API key, and set `MAIL_FROM_ADDRESS` to an address on `lunarsocietyregistry.com`. Then add the sender's DKIM and SPF records in Hostinger DNS.
