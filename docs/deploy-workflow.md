# Deployment workflow (beginner-friendly)

Goal: update the files on your server safely and repeatedly.

## Workflow A (beginner): Upload via SFTP with WinSCP

1. Install WinSCP
2. Connect to your server via **SFTP**
3. Find the web root (the folder that contains `index.html`)
   - On Hetzner konsoleH web hosting, this is commonly `httpdocs/` (or similar)
4. Upload changed files:
   - `index.html`, `donate.html`, `charities.html`
   - `assets/` (if you changed CSS/images)
   - PHP files (`tiltify-donate.php`, `tiltify-webhook.php`, `verify.php`)
5. Never upload `config.example.php` as `config.php` from your repo history — create `config.php` directly on the server and keep it private.

Pros: simple, no tooling.
Cons: manual, easy to forget a file.

## Workflow B (recommended): Git-based deploy

1. Put this repo on GitHub (private is fine)
2. On the server, clone it into something like `/var/www/savethechewbies`
3. When you want to deploy:
   - `git pull`

Pros: repeatable and auditable.
Cons: requires SSH access and minimal Git comfort.

## Workflow C: SSH-based deploy without WinSCP

If you don’t want to use WinSCP, use:

- Git-based deploy (`git pull` on the server), or
- `scp`/`rsync` from your PC.

See: `docs/deploy-with-ssh.md`

## “Copilot-friendly” habit

Whenever you change the site, do this checklist:
- Confirm HTTPS works
- Confirm `.php` executes (no source exposure)
- Confirm donate start endpoint redirects to Tiltify
- Confirm webhook endpoint returns 2xx for Tiltify test

Quick verify commands:

```powershell
curl.exe -i https://savethechew.biz/verify.php
curl.exe -i https://savethechew.biz/tiltify-webhook.php
```
