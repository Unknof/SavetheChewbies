# Ko-fi webhook bridge (Python) — safe VPS hookup

This repo includes a Ko-fi → Discord webhook bridge in [KS_Ko-Fi/](../KS_Ko-Fi/).

Goal: expose **only** a webhook endpoint publicly, while the Python service itself listens on **localhost**.

## Recommended architecture

- Caddy serves your main site (HTML/PHP)
- A small Python service listens on `127.0.0.1:8787`
- Caddy reverse-proxies a path (example: `/kofi/*`) to the Python service

Public endpoint Ko-fi calls:

- `https://savethechew.biz/kofi/webhooks/kofi`

## 1) Secrets/env on the server

Create an env file (example path): `/etc/ks-kofi.env`

Required:
- `KOFI_VERIFICATION_TOKEN=...`
- `DISCORD_WEBHOOK_URL=...`

Recommended hardening:
- `ENABLE_UI=0`
- `REQUIRE_KOFI_TOKEN=1`
- `MAX_REQUEST_BYTES=131072`
- `PORT=8787`

Lock it down:

```bash
sudo chown root:root /etc/ks-kofi.env
sudo chmod 600 /etc/ks-kofi.env
```

## 2) Install Python + run the service (systemd + waitress)

`KS_Ko-Fi` is a Flask WSGI app with entrypoint `wsgi:app`.

Install Python (Debian/Ubuntu):

```bash
sudo apt update
sudo apt install -y python3 python3-venv python3-pip
python3 --version
```

Install deps (example path; adjust to where you deploy the folder):

```bash
cd /opt/savethechewbies/KS_Ko-Fi
python3 -m venv .venv
./.venv/bin/python -m pip install -r requirements.txt
```

Create a systemd unit like `/etc/systemd/system/ks-kofi.service`:

```ini
[Unit]
Description=KS Ko-fi webhook bridge
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
WorkingDirectory=/opt/savethechewbies/KS_Ko-Fi
EnvironmentFile=/etc/ks-kofi.env
ExecStart=/opt/savethechewbies/KS_Ko-Fi/.venv/bin/python -m waitress --listen=127.0.0.1:8787 wsgi:app
Restart=on-failure
RestartSec=2

# Basic hardening
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/opt/savethechewbies/KS_Ko-Fi

[Install]
WantedBy=multi-user.target
```

Enable + start:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now ks-kofi
sudo systemctl status ks-kofi
```

## 3) Caddy reverse proxy

In your Caddyfile site block:

```caddyfile
savethechew.biz, www.savethechew.biz {
  # ... your existing site config

  handle_path /kofi/* {
    reverse_proxy 127.0.0.1:8787
  }
}
```

Reload Caddy:

```bash
sudo caddy fmt --overwrite /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

## 4) Verify

From your PC:

```powershell
curl.exe -i https://savethechew.biz/kofi/health
```

Expected: `200` with JSON `{ "ok": true }`.

## Notes

- The Flask app rejects webhook requests unless `KOFI_VERIFICATION_TOKEN` matches (when `REQUIRE_KOFI_TOKEN=1`).
- Keep the Python service bound to `127.0.0.1` so it’s not directly reachable from the internet.
