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

## Troubleshooting: `verify.php` returns 404

A **404** means the web server is up, but `verify.php` is not in the site’s configured web root (or you deployed to a different folder). You usually **do not** need to restart the server for a new file.

SSH to the server and run:

```bash
# Find where your repo is actually deployed
ls -la /var/www

# Check that the file exists where you think it is
ls -la /var/www/savethechew/verify.php
ls -la /var/www/savethechewbies/verify.php

# If you’re using Caddy, confirm the configured site root
sudo caddy validate --config /etc/caddy/Caddyfile
sudo caddy adapt --config /etc/caddy/Caddyfile --pretty | sed -n '1,160p'
sudo grep -n "root \*" /etc/caddy/Caddyfile

# Reload Caddy only if you changed the Caddyfile (not needed for file uploads)
sudo systemctl reload caddy
```

Common cause: the repo was cloned into `/var/www/savethechewbies` but Caddy is serving `/var/www/savethechew` (or vice versa).

### Fix: Caddy root is correct, but the folder is empty

If `root * /var/www/savethechew` is set (as in the Caddyfile), but `ls -la /var/www/savethechew` shows no `index.html` / PHP files, you need to deploy the repo contents into that folder.

Option A (recommended): copy from wherever you cloned the repo using `rsync` (preserves `data/` and does NOT overwrite `config.php`):

```bash
# Find likely repo locations under /var/www
sudo find /var/www -maxdepth 2 -type f \( -name index.html -o -name index.php \) -print

# Example: if your repo is in /var/www/savethechewbies
test -d /var/www/savethechewbies || { echo "Missing: /var/www/savethechewbies"; exit 1; }
sudo rsync -av --delete \
   --exclude 'data/' \
   --exclude 'config.php' \
   --exclude '.git/' \
   /var/www/savethechewbies/ \
   /var/www/savethechew/
```

Important: do **not** run `rsync --delete` where the source folder is *inside* the destination (example: syncing `SavetheChewbies/` into `.` while you are in `/var/www/savethechew`). With `--delete`, rsync will delete the `SavetheChewbies/` folder as “extra destination files”, which also deletes your source mid-transfer.

If you accidentally cloned into a subfolder but want the files at the web root, use a temp clone outside the web root:

```bash
sudo rm -rf /root/deploy_tmp/SavetheChewbies
sudo git clone https://github.com/Unknof/SavetheChewbies /root/deploy_tmp/SavetheChewbies

sudo rsync -av --delete \
   --exclude 'data/' \
   --exclude 'config.php' \
   --exclude '.git/' \
   /root/deploy_tmp/SavetheChewbies/ \
   /var/www/savethechew/
```

Option B: clone directly into the Caddy root (only works if the folder is empty or you move `data/` out first):

```bash
# If /var/www/savethechew already contains data/, move it aside first
sudo mv /var/www/savethechew/data /tmp/savethechew_data_$(date +%F_%H%M%S)

# Clone into the web root
cd /var/www/savethechew
sudo git clone <YOUR_REPO_URL> .

# Put data/ back and re-apply permissions
sudo mkdir -p /var/www/savethechew/data
sudo mv /tmp/savethechew_data_*/* /var/www/savethechew/data/ 2>/dev/null || true
sudo chown -R www-data:www-data /var/www/savethechew/data
sudo chmod 2775 /var/www/savethechew/data
```

Then verify from your PC:

```powershell
curl.exe -i https://savethechew.biz/verify.php
```
