# Deploy to your VPS without WinSCP (SSH-based)

Use this if you don’t want/ can’t use WinSCP.

Assumptions:
- You can SSH into the server.
- Your site root on the server is: `/var/www/savethechew`

## Option 1 (recommended): Git-based deploy (clean + repeatable)

### One-time setup

1) Create a Git repo (GitHub/GitLab). Private is fine.
2) On the server:

```bash
sudo apt update
sudo apt install -y git
sudo mkdir -p /var/www/savethechew
sudo chown -R $USER:$USER /var/www/savethechew
cd /var/www/savethechew

git clone YOUR_REPO_URL .
```

3) Keep secrets out of Git:
- Create `/var/www/savethechew/config.php` directly on the server
- Ensure `/var/www/savethechew/data/` exists and is writable by PHP

### Every deploy

From the server:

```bash
cd /var/www/savethechew

git pull
sudo systemctl reload caddy
```

Pros: hard to forget files.

## Option 2: `scp` copy (simple)

From your Windows PC (PowerShell) you can copy files over SSH.

Example (copy a single file):

```powershell
scp .\donate.html root@91.98.238.213:/var/www/savethechew/donate.html
```

Example (copy folders):

```powershell
scp -r .\assets root@91.98.238.213:/var/www/savethechew/
```

Pros: no extra tooling.
Cons: easy to forget a file.

### Upload/replace `config.php` over SSH (secrets)

`config.php` is ignored by Git in this repo and should live on the server only.

Assuming your server web root is `/var/www/savethechew`:

Option A: `scp` (copy the file)

```powershell
# Copy your local config.php up to the server
scp .\config.php root@91.98.238.213:/var/www/savethechew/config.php

# Lock down permissions (recommended)
ssh root@91.98.238.213 "chown root:www-data /var/www/savethechew/config.php; chmod 640 /var/www/savethechew/config.php"
```

Option B: “force the contents” via SSH (no scp)

This is handy when you want to stream the local file contents into place remotely.

```powershell
# Stream the local file contents into the remote path
Get-Content -Raw .\config.php | ssh root@91.98.238.213 "cat > /var/www/savethechew/config.php"

# Lock down permissions (recommended)
ssh root@91.98.238.213 "chown root:www-data /var/www/savethechew/config.php; chmod 640 /var/www/savethechew/config.php"
```

If you are NOT logging in as root, use `sudo tee` instead:

```powershell
Get-Content -Raw .\config.php | ssh USER@91.98.238.213 "sudo tee /var/www/savethechew/config.php > /dev/null"
ssh USER@91.98.238.213 "sudo chown root:www-data /var/www/savethechew/config.php; sudo chmod 640 /var/www/savethechew/config.php"
```

## Option 3 (best for syncing): `rsync` (fast + accurate)

`rsync` is ideal because it syncs changes and can delete removed files.

From Windows:
- If you have WSL: use `rsync` inside WSL.
- Or install a Windows rsync (e.g. via Git for Windows / cwRsync).

Example:

```bash
rsync -av --delete \
  --exclude '.git/' \
  --exclude 'docs/' \
  --exclude 'config.php' \
  ./ root@91.98.238.213:/var/www/savethechew/
```

## Post-deploy checks (always)

From your PC:

```powershell
curl.exe -I https://savethechew.biz/
curl.exe -i https://savethechew.biz/verify.php
curl.exe -i https://savethechew.biz/tiltify-webhook.php
```

Expected:
- `verify.php` returns HTML
- `tiltify-webhook.php` on GET returns `405`

## Safety notes

- Never transfer your local `config.php` into Git.
- Keep `/var/www/savethechew/config.php` readable by PHP only.
- Keep `/var/www/savethechew/data/` writable by PHP.
