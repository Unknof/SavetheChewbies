# Server setup for Tiltify (PHP + webhooks)

Important: right now your server is **not executing PHP** (it is serving `.php` files as text). That must be fixed before Tiltify webhooks can work.

## Why this matters (urgent)

If `.php` files are served as plain text, visitors can see your source code.
Even if `config.php` isn’t publicly accessible, this is still a serious misconfiguration.

### How to tell if PHP is executing

If you run:

```powershell
curl.exe -i https://YOUR_DOMAIN/tiltify-webhook.php
```

and you see:
- `HTTP/... 200 OK`
- `Content-Type: application/x-php` (or similar)
- the response body contains `<?php`

…then PHP is **NOT** executing. The web server is just serving the PHP file as content.

## Two viable hosting paths

### Option A (simplest): Shared hosting that already runs PHP

Use a typical web host where PHP is enabled by default.
- Upload this repo to the web root (via SFTP/FTP)
- Confirm that visiting `https://YOUR_DOMAIN/verify.php` shows an HTML page (not PHP source)

If your host supports SFTP, prefer it over FTP.

#### Hetzner (konsoleH) notes

If you have access to a “PHP version / PHP configuration / PHP extensions” page in konsoleH:

- Set **PHP version** to a stable release (recommended: **PHP 8.3** or **PHP 8.2**).
  - Avoid preview/beta versions (for example 8.5) unless you specifically need them.
- Ensure the **cURL** extension is enabled (often shown as `curl`). This repo uses cURL to call Tiltify’s API.
- JSON is required (usually always enabled).
- Click **Save**, then wait a minute and re-test.

If you don’t see `curl` as an extension toggle, it may be enabled by default, or it may require Hetzner support to enable it.

Important: if your site responses show `Server: Caddy`, then your domain is likely pointing at a VPS where you installed Caddy.
In that case, the konsoleH PHP settings will NOT affect what your domain serves.

### Option B: Your own VPS with Caddy + PHP-FPM

If your `Server:` header shows `Caddy`, you likely need PHP-FPM and a Caddy config.

High-level steps (Linux):
1. Install PHP-FPM and required extensions (`curl` is needed)
2. Configure Caddy to route `.php` requests to PHP-FPM
3. Make sure the site root points at the folder that contains your `index.html`
4. Ensure `data/` is writable by the PHP-FPM user

#### Concrete steps (Debian/Ubuntu)

1) Install PHP-FPM + curl extension:

```bash
sudo apt update
sudo apt install -y php-fpm php-curl
```

2) Find the PHP-FPM socket:

```bash
ls -la /run/php/
```

You’re looking for something like `php8.3-fpm.sock`.

3) Update your Caddyfile (example):

```caddyfile
savethechew.biz, www.savethechew.biz {
  root * /var/www/savethechewbies

  # Update socket path as needed:
  php_fastcgi unix//run/php/php-fpm.sock

  file_server

  # Recommended: block private files/folders
  @private {
    path /config.php /config.example.php
    path /docs/* /data/*
  }
  respond @private 404
}
```

4) Reload Caddy:

```bash
sudo caddy fmt --overwrite /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

5) Ensure `data/` is writable by PHP-FPM (often `www-data`):

```bash
sudo mkdir -p /var/www/savethechewbies/data
sudo chown -R www-data:www-data /var/www/savethechewbies/data
```

A minimal Caddyfile example:

```caddyfile
savethechew.biz, www.savethechew.biz {
  root * /var/www/savethechewbies

  # Execute PHP (requires PHP-FPM installed)
  php_fastcgi unix//run/php/php-fpm.sock

  file_server

  # Optional hardening (recommended)
  @private {
    path /config.php /config.example.php
    path /docs/*
  }
  respond @private 404
}
```

Notes:
- The PHP-FPM socket path varies by distro/version.
- If `php_fastcgi` isn’t set, Caddy will serve `.php` as a download/text.

## Verification checks

Run these from your PC:

```powershell
# Should return HTML (not PHP code)
curl.exe -i https://savethechew.biz/verify.php

# Should return 405 (Method Not Allowed)
# and should NOT print PHP source code
curl.exe -i https://savethechew.biz/tiltify-webhook.php

Expected results:
- `verify.php` should render HTML (not raw PHP source)
- `tiltify-webhook.php` should return `405 Method Not Allowed` for GET

If you still see PHP source code, you’re not hitting a PHP-enabled webspace/docroot yet.

## Troubleshooting

### Webhook returns `500 Internal Server Error` with a short plain-text body

If `curl -i https://YOUR_DOMAIN/tiltify-webhook.php` returns `500` and the body says something like:
`Server not configured. Create config.php from config.example.php`

That means PHP is executing, but your server is missing a valid `config.php`.

Fix (on the server):

1) Create `config.php` next to the PHP files (same folder as `tiltify-webhook.php`)
2) Ensure it returns a PHP array (like `config.example.php`)
3) Set safe permissions so it’s readable by PHP-FPM but not world-readable

Example permissions (common):

```bash
cd /var/www/savethechew
sudo chown root:www-data config.php
sudo chmod 640 config.php
```

Do not paste secrets into Git or commit `config.php`.

## Hardening (recommended)

If your hosting uses Apache/LiteSpeed, you can block sensitive files/folders using `.htaccess`.
This repo includes an optional `.htaccess` that blocks:
- `config.php` and `config.example.php`
- `/data/` and `/docs/`
```

## What Tiltify needs on the server

- Public HTTPS URL for the webhook endpoint: `https://savethechew.biz/tiltify-webhook.php`
- PHP with:
  - `curl` extension enabled (required by `tiltify-donate.php` to call Tiltify API)
  - JSON support (almost always enabled)
- Writable `data/` directory
- `config.php` present on the server with:
  - `tiltify_client_id`
  - `tiltify_client_secret`
  - `tiltify_webhook_signing_key`
  - `tiltify_webhook_relay_id`
  - `tiltify_donation_url`
