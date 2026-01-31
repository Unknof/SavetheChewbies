# KS Ko-fi → Discord bridge

This repo provides a **Ko-fi webhook receiver** that forwards events into a Discord channel.

By default it runs in **no-UI mode** (only the webhook + health endpoint) to keep the attack surface minimal.

## Why this approach
Ko-fi webhooks are server-to-server; Discord bots can’t “receive” Ko-fi webhooks directly. You still need **some public HTTP endpoint**. Once you have that, you can forward to Discord either via:
- a **Discord webhook URL** (simplest; no bot token), or
- a full **Discord bot** (more control).

This starter uses a **Discord webhook URL** for minimal setup.

## Setup (Python - recommended)

Your current environment has Python installed. Node/npm may not be available, so Python is the fastest way to validate end-to-end.

1) Install deps:

```bash
py -m pip install -r requirements.txt
```

2) Fill in `.env`:

- `KOFI_VERIFICATION_TOKEN`: from Ko-fi webhooks settings
- `KOFI_PROFILE_URL`: e.g. `https://ko-fi.com/YourName`
- `DISCORD_WEBHOOK_URL`: create an “Incoming Webhook” in your Discord channel

Hardening defaults (recommended):
- `ENABLE_UI=0`
- `REQUIRE_KOFI_TOKEN=1`
- `MAX_REQUEST_BYTES=131072`

3) Run locally:

```bash
py app.py
```

Local URLs:
- Webhook: `POST http://127.0.0.1:8787/webhooks/kofi`
- Health: `GET  http://127.0.0.1:8787/health`

If you *do* want the tip pages for internal use, set `ENABLE_UI=1`.

## Expose locally for Ko-fi testing (minimal “it works”)
Ko-fi must reach your server over the public internet.

- Option A: ngrok
  - `ngrok http 8787`
- Option B: Cloudflare tunnel
  - `cloudflared tunnel --url http://localhost:8787`

Set your Ko-fi webhook URL to:

- `https://<your-tunnel-host>/webhooks/kofi`

## Hook into your existing webserver (Caddy reverse proxy)

If your main site is served by Caddy (common on a VPS), run this Python app **only on localhost** and let Caddy proxy a path to it.

1) Run the app on localhost (example uses port 8787):

- Dev (simple): `py app.py`

2) Add a Caddy route so Ko-fi hits your main domain:

Example Caddyfile snippet:

```caddyfile
savethechew.biz, www.savethechew.biz {
  # ... your existing site config (php_fastcgi, file_server, etc)

  # Ko-fi webhook bridge (no UI)
  handle_path /kofi/* {
    reverse_proxy 127.0.0.1:8787
  }
}
```

3) In Ko-fi, set the webhook URL to:

- `https://savethechew.biz/kofi/webhooks/kofi`

This keeps the Python app off the public internet except through Caddy.

## Minimal automated testing

```bash
py -m pytest -q
```

## Manual webhook test (no Ko-fi needed)

Post a minimal form payload:

```bash
curl -X POST http://localhost:8787/webhooks/kofi -H "Content-Type: application/x-www-form-urlencoded" --data "data={\"verification_token\":\"test\",\"type\":\"Donation\",\"from_name\":\"Test Supporter\",\"message\":\"[TEAM] great work\",\"amount\":\"5\",\"currency\":\"USD\",\"message_id\":\"abc\",\"kofi_transaction_id\":\"tx1\",\"timestamp\":\"2026-01-01T00:00:00Z\"}"
```

## Node version (optional)

There’s also a Node implementation in `src/` + `test/`, but you’ll need Node.js installed (includes `npm`).
