# Server info (notes)

## Hetzner server

- Public IP: (keep private; put it in `docs/server-info.private.md`)
- Domain: `savethechew.biz` (and `www.savethechew.biz`)
- HTTPS: enabled (Lets Encrypt)
- Hosting: Hetzner VPS (Caddy) + konsoleH web hosting also exists (separate product)

## Safety note

Don’t store secrets in this repo.
Keep credentials in `config.php` on the server only (and do not commit that file).

## Private notes

If you want to store IPs, usernames, or other operational notes, create `docs/server-info.private.md`.
That file is ignored by git.
