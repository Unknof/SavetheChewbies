# Server Upload Checklist (savethechew.biz)

This is the repeatable checklist to keep your server up to date and avoid missing required files.

## Assumptions

- Domain points to your VPS running Caddy.
- Caddy site root is `/var/www/savethechew` (from your Caddyfile).
- Upload method: WinSCP (SFTP) or any SFTP client.

## 0) Never-do rules

- Never commit secrets into Git.
- Never upload a local dev `config.php` into a public repo.
- Do not leave PHP source exposed (a GET to `tiltify-webhook.php` must NOT show `<?php`).

## 1) Preflight (local)

- Make sure the site renders locally:
  - `python -m http.server 8080`
  - Open `http://localhost:8080`
- Identify what changed (so you don’t forget a file).

## 2) Upload (server)

Upload to: `/var/www/savethechew`

### Always upload when changed

- HTML pages: `index.html`, `donate.html`, `charities.html`
- PHP endpoints: `tiltify-donate.php`, `tiltify-webhook.php`, `verify.php`, `index.php`
- Static assets folder: `assets/` (including `assets/styles.css` and any images)

### Optional uploads

- Documentation: `docs/` (not required for the site to function)
- Images: `SplashArt.jpg`

### Server-only files (do not overwrite accidentally)

- `config.php` (lives on the server; keep permissions tight)
- `data/` (created on the server; must be writable by PHP)

## 3) Permissions (server)

On the server (SSH):

- Ensure the data folder exists and is writable by the PHP user:
  - `sudo mkdir -p /var/www/savethechew/data`
  - `sudo chown -R www-data:www-data /var/www/savethechew/data`

- Protect secrets:
  - `sudo chown root:www-data /var/www/savethechew/config.php`
  - `sudo chmod 640 /var/www/savethechew/config.php`

## 4) Post-deploy validation (from your PC)

Run these and verify the status codes:

- Webhook endpoint (GET):
  - `curl.exe -i https://savethechew.biz/tiltify-webhook.php`
  - Expected: `405 Method Not Allowed`

- Verification page:
  - `curl.exe -i https://savethechew.biz/verify.php`
  - Expected: `200 OK` and HTML response

- Start donation flow:
  - `curl.exe -I https://savethechew.biz/tiltify-donate.php`
  - Expected: `302` redirect to Tiltify (once config is correct)

## 5) Tiltify webhook test (source of truth)

In Tiltify dashboard → Webhook Endpoint → Testing/Deliveries:

- Send a Relay test message
- Confirm the delivery shows HTTP `200–299`

If the delivery is not 2xx, check server logs and confirm `config.php` values are correct.
