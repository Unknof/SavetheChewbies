# Deploying this site (Hetzner + FTP)

This repo is a static site (HTML/CSS) so you can deploy it by uploading files over FTP.

## 1) Verify your domain points to your server IP

### Find your server IP
- In Hetzner (Cloud Console), open your server details and copy:
  - IPv4 address
  - IPv6 address (optional)

### Check DNS from Windows (PowerShell)
Replace `YOUR_DOMAIN.com`.

```powershell
Resolve-DnsName YOUR_DOMAIN.com -Type A
Resolve-DnsName YOUR_DOMAIN.com -Type AAAA
```

- If `-Type A` returns an IPv4 address that matches your server IPv4, the domain is pointing correctly.
- DNS changes can take time (minutes to a few hours depending on TTL).

### If HTTPS fails with `SEC_E_WRONG_PRINCIPAL` (common on Windows)

If you see an error like:

```
curl: (60) schannel: ... SEC_E_WRONG_PRINCIPAL ... The target principal name is incorrect.
```

That usually means **your DNS is pointing at a server that is not presenting a certificate for your domain**.
Two common causes:

- **Split DNS:** the apex (`YOUR_DOMAIN.com`) points to one IP, but `www.YOUR_DOMAIN.com` points to a different IP.
- **IPv6 mismatch:** the `AAAA` record points to a different server than the `A` record.

Fix:

- Make sure `A` (and `AAAA`, if you use it) for `YOUR_DOMAIN.com` points to the same server you’re actually hosting on.
- Make sure `www` also points to the same server (or is a CNAME to the apex).
- If you don’t have IPv6 set up on your server yet, **remove the `AAAA` record** until you do.

Helpful verification commands:

```powershell
Resolve-DnsName YOUR_DOMAIN.com
Resolve-DnsName www.YOUR_DOMAIN.com

# Force-test a specific IP (replace IP) without waiting for DNS propagation:
curl.exe -vkI --resolve YOUR_DOMAIN.com:443:SERVER_IPV4 https://YOUR_DOMAIN.com/
```

### Quick "does the website answer" check
Once you’ve uploaded the site, test:

```powershell
curl.exe -I https://YOUR_DOMAIN.com
curl.exe -I http://YOUR_DOMAIN.com
```

Look for:
- `HTTP/1.1 200` (OK)
- A `Server:` header (sometimes gives a hint: Apache/nginx/litespeed)

## 2) Figure out whether it’s Apache or Nginx

With FTP-only hosting, you often don’t control the main web server directly.

Good heuristics:
- If `.htaccess` works, you’re almost certainly on **Apache** (or LiteSpeed).
- If `.htaccess` changes do nothing, it may be **Nginx**, or Apache with overrides disabled.

You can also check the `Server:` header from `curl -I` (not always shown).

## 3) Upload files via FTP

### What you need from Hetzner

From your hosting/FTP details, collect:
- **Host** (often your domain, or something like `ftp.yourdomain`)
- **Username**
- **Password**
- **Port** (usually 21 for FTP, 22 for SFTP)

If you see an option for **SFTP** (port 22), prefer it over plain FTP.

### Connect with an FTP client (FileZilla / WinSCP)

In your FTP client:
- Protocol: **SFTP** if available, otherwise **FTP**
- Host: the FTP host from Hetzner
- Port: 22 (SFTP) or 21 (FTP)
- Username/password: from Hetzner

If connection fails on FTP:
- Enable **Passive mode** in your client settings.

### Find the web root (the folder that is served as /)

After you connect, you’ll see folders on the server. The web root commonly is one of:
- `public_html`
- `httpdocs`
- `htdocs`
- `www`

Some hosts drop you directly *into* the web root.

How to confirm you’re in the web root:
- You may already see files like `index.html`, `index.php`, or folders like `cgi-bin`.
- If you upload a file and it doesn’t show up in the browser, you’re probably in the wrong folder.

### Upload the site

Upload **everything** from this repo into the web root:
- `index.html`
- `index.php` (this repo includes it as a safe fallback)
- `charities.html`
- `donate.html`
- `assets/` (folder)
- `docs/` and `README.md` are optional (not required for the site to run)

Important notes:
- Upload the `assets/` folder exactly as-is, keeping its structure.
- GIFs should be transferred in **Binary/Auto** mode (most clients default to Auto).

### Verify it worked

After upload, check these URLs:
- `http://YOUR_DOMAIN.com/`
- `http://YOUR_DOMAIN.com/charities.html`
- `http://YOUR_DOMAIN.com/donate.html`

If you get the old/default page:
- You likely uploaded into the wrong folder, OR
- The host has a different domain’s docroot, OR
- There’s another `index.html`/`index.php` being served from a different path.

If you want a quick header check from Windows:

```powershell
curl.exe -I http://YOUR_DOMAIN.com/
```

### Common pitfalls (fast fixes)

- **Only `www` works but the naked domain doesn’t (or vice-versa):** you need both DNS records and the server vhost configured for both.
- **HTTPS shows a certificate error:** Let’s Encrypt isn’t configured for this domain yet (see next section).
- **403 Forbidden:** permissions or wrong folder; confirm you uploaded into the web root and that files are readable.
- **Images/CSS not loading:** confirm `assets/styles.css` and `assets/gifs/...` exist on the server and the folder names match exactly.

If you paste the **top-level folder list** you see after logging in via FTP, I can tell you which one is almost certainly the web root.

## 4) HTTPS (recommended)

### What your current error means

If `https://YOUR_DOMAIN.com/` shows a Hetzner/konsoleH login page (or says the domain does not yet support HTTPS),
it usually means **no SSL certificate is assigned to that domain** in your hosting panel.

### Fix it in Hetzner (konsoleH)

1. Log into your Hetzner konsoleH panel.
2. Open **SSL Manager** / **SSL Accounts**.
3. Create an SSL certificate (recommended: **Let’s Encrypt**).
4. Include both hostnames:
  - `YOUR_DOMAIN.com`
  - `www.YOUR_DOMAIN.com`
5. Activate/assign it to your web space/domain.

Then re-check:

```powershell
curl.exe -I https://YOUR_DOMAIN.com/
curl.exe -I https://www.YOUR_DOMAIN.com/
```

### If you’re using Caddy (common on small VPS setups)

If `curl.exe -I https://YOUR_DOMAIN.com/` shows `Server: Caddy`, Caddy is probably managing HTTPS automatically.
In that case, make sure your Caddy config includes **both** hostnames, otherwise `www` can fail the TLS handshake.

Minimal example (redirect `www` to the apex):

```caddyfile
YOUR_DOMAIN.com {
  root * /var/www/site
  file_server
}

www.YOUR_DOMAIN.com {
  redir https://YOUR_DOMAIN.com{uri} permanent
}
```

If you later find you’re on Apache and `.htaccess` is allowed, you can add an HTTP → HTTPS redirect.

If you later find you’re on Apache and `.htaccess` is allowed, you can add a redirect.
(If you want, tell me what your web root folder is and what URLs you see working, and I’ll generate the smallest safe redirect config.)
