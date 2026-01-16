# Tiltify (GDQ-style) verification setup

This guide sets up **verified donations** without you collecting payments.
It uses Tiltify Webhooks + **Webhook Relays**.

## What you’ll build

- A “Start verified donation” link on your site
- Your server creates a **Relay Key** (server-to-server API)
- User is redirected to Tiltify donate form with `?relay=<client_key>`
- Tiltify sends signed webhooks to your site
- Your site marks that verification code as `verified`

## Step 0: Fix HTTPS first

Tiltify webhooks require a public URL; you should use HTTPS.
Right now `https://savethechew.biz/` is being served by Hetzner/konsoleH with the message
“This domain does not yet support HTTPS. Please add an SSL Certificate.”

### Fix (Hetzner konsoleH)

1. Log into your Hetzner konsoleH panel.
2. Go to the **SSL Manager** / **SSL Accounts** section.
3. Create a new certificate (recommended: **Let’s Encrypt**).
4. Select the hostnames:
   - `savethechew.biz`
   - `www.savethechew.biz`
5. Issue/activate the certificate and assign it to your web space/domain.

Notes:
- Your DNS is already pointing to the server (A/AAAA records), which is required.
- Make sure HTTP (`http://savethechew.biz/`) works during issuance (Let’s Encrypt validation).

### Verify from Windows

```powershell
curl.exe -I https://savethechew.biz/
curl.exe -I https://www.savethechew.biz/
```

You want a `200` response **and** your actual site content (not the konsoleH login page).

## Step 0.5: Ensure PHP actually runs (required)

If your server is not configured to execute PHP, it may **show the PHP source code** instead of running it.
That must be fixed before webhooks/relays will work.

- See `docs/server-php-setup.md`
- See `docs/deploy-workflow.md`

## Step 1: Create Tiltify Developer resources

1. Create a Tiltify account
2. Create an Application (Developer Hub)
   - If you dont need user login, set redirect URI to `http://localhost`
3. Create a Webhook Endpoint
   - Endpoint URL example: `https://savethechew.biz/tiltify-webhook.php`
   - Copy the **Secret Webhook Signing Key** (used to verify signature)
4. Create a Relay under that Webhook Endpoint
   - Copy the **webhook_relay_id**

## Step 2: Create a donation campaign/page

Create a Tiltify campaign or donation page you want donors to use.
Copy its donation URL under `donate.tiltify.com`.
Example:

- `https://donate.tiltify.com/@username/campaign-slug`

## Step 3: Add config.php on your server

On your server (via FTP), copy `config.example.php` to `config.php` and fill in:

- `tiltify_client_id`
- `tiltify_client_secret`
- `tiltify_webhook_signing_key`
- `tiltify_webhook_relay_id`
- `tiltify_donation_url`

Also ensure the `data/` folder is writable by PHP.

## Step 4: Test webhooks

In the Tiltify dashboard for your webhook endpoint:
- Go to the **Testing** tab
- Send a **Relay test message**

Your endpoint must return HTTP 200-299 or it will eventually deactivate.

## Step 5: Use it on your site

- Start: `https://savethechew.biz/tiltify-donate.php?charity=default`
- Check status: `https://savethechew.biz/verify.php?code=<code>`

## Security notes

- Webhook signature verification is required (this repo does it using `X-Tiltify-Signature` and timestamp).
- Do not put personal data in relay metadata.
- Keep `config.php` private (never commit it).
