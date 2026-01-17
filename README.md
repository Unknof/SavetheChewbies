# Save The Chewbies

This is a minimal static website scaffold (no Node/npm required) so it can be deployed easily to Hetzner.

## Local preview

- Open `index.html` directly in your browser, or
- In VS Code, use any static server extension, or
- Run a quick local server (optional):

PowerShell:

```powershell
cd c:\Users\Phil\vscodeProjects\savethechewbies
python -m http.server 8080
```

Then open `http://localhost:8080`.

## Live total (milestones)

The homepage and Donate page can show a live raised total by calling `milestones.php`.

- Set `tiltify_campaign_id` in `config.php` (see `config.example.php`), or
- For testing, set `milestone_total_override` in `config.php`.

Tiltify campaign (reference)

- Campaign page: https://tiltify.com/@savethecewbies/savethechewbies-a-fundraiser-for-save-the-children
- Donation page: https://donate.tiltify.com/3fd651b1-6e86-4a84-be46-f9ae61ca718a
- Campaign id (for `tiltify_campaign_id`): `3fd651b1-6e86-4a84-be46-f9ae61ca718a`

## Deployment (high level)

1. Point your domain DNS `A/AAAA` record at your Hetzner server.
2. Configure your web server to serve this folder (or a copy of it) as the site root.
3. Enable HTTPS (Let’s Encrypt).

## Update `config.php` on the server (Linux / SSH)

`config.php` is intentionally not committed (it’s in `.gitignore`). Create/update it directly on the server.

### Option A: paste the contents while logged in (recommended)

SSH into the server, then run:

```bash
sudo tee /var/www/savethechew/config.php > /dev/null <<'PHP'
<?php

return [
	// Fill from config.example.php
	'tiltify_client_id' => '...',
	'tiltify_client_secret' => '...',
	'tiltify_webhook_signing_key' => '...',
	'tiltify_webhook_relay_id' => '...',
	'tiltify_donation_url' => 'https://donate.tiltify.com/3fd651b1-6e86-4a84-be46-f9ae61ca718a',
	'tiltify_campaign_id' => '3fd651b1-6e86-4a84-be46-f9ae61ca718a',
	'milestone_total_override' => null,
	'data_dir' => __DIR__ . DIRECTORY_SEPARATOR . 'data',
	'admin_key' => 'CHANGE_ME',
];
PHP

sudo chown root:www-data /var/www/savethechew/config.php
sudo chmod 640 /var/www/savethechew/config.php
```

### Option B: stream your local file up (Linux/WSL/macOS)

Run this from your local machine (not on the server):

```bash
ssh root@savethechew.biz 'cat > /var/www/savethechew/config.php' < ./config.php
ssh root@savethechew.biz 'chown root:www-data /var/www/savethechew/config.php; chmod 640 /var/www/savethechew/config.php'
```

If you’re deploying via FTP and you’re new to DNS/servers, see:

- [docs/deploy-hetzner.md](docs/deploy-hetzner.md)

## Connect to the server via SSH

You can manage/deploy the site over SSH (no WinSCP required).

### Prereqs

- You have SSH access (hostname/IP, username, and either a password or an SSH key).
- On Windows 10/11, you can use PowerShell’s built-in `ssh`.

### Quick connect (PowerShell)

```powershell
ssh USERNAME@savethechew.biz
```

If your server uses a non-default port:

```powershell
ssh -p 2222 USERNAME@savethechew.biz
```

If you use a specific private key:

```powershell
ssh -i "$env:USERPROFILE\.ssh\id_ed25519" USERNAME@savethechew.biz
```

### First-time key setup (recommended)

Generate a key (if you don’t already have one):

```powershell
ssh-keygen -t ed25519
```

Copy your public key to the server:

```powershell
ssh-copy-id USERNAME@savethechew.biz
```

Note: if `ssh-copy-id` isn’t available on your Windows install, you can paste the contents of
`$env:USERPROFILE\.ssh\id_ed25519.pub` into `~/.ssh/authorized_keys` on the server.

### Deployment over SSH

- SSH deploy options: [docs/deploy-with-ssh.md](docs/deploy-with-ssh.md)
- Full workflow (includes safe rsync notes): [docs/deploy-workflow.md](docs/deploy-workflow.md)

## Donation verification (direction)

For GDQ-style verification, the easiest small-scale approach is to use a donation platform that provides an API/webhooks.
Donations happen on that platform, and your site can display verified donation events and totals.

More detail:

- [docs/donations-and-verification.md](docs/donations-and-verification.md)

## Project notes

- [docs/next-steps.md](docs/next-steps.md)
- [docs/server-info.md](docs/server-info.md)

## Server setup

- [docs/server-php-setup.md](docs/server-php-setup.md)
- [docs/deploy-workflow.md](docs/deploy-workflow.md)

## Publishing

- [docs/PublicRepoChecklist.md](docs/PublicRepoChecklist.md)
