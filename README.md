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

## Where to put GIFs

- Put your GIF files in `assets/gifs/`
- Update `index.html` to point to your filenames

## Deployment (high level)

1. Point your domain DNS `A/AAAA` record at your Hetzner server.
2. Configure your web server to serve this folder (or a copy of it) as the site root.
3. Enable HTTPS (Let’s Encrypt).

If you’re deploying via FTP and you’re new to DNS/servers, see:

- [docs/deploy-hetzner.md](docs/deploy-hetzner.md)

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
