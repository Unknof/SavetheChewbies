# Public repo checklist (no secrets)

Use this before pushing to a **public** GitHub repo.

## 1) Keep secrets out of git

- Ensure these files are NOT committed:
  - `config.php`
  - any keys: `*.pem`, `*.ppk`, `*.key`
  - runtime data: `data/`
  - private notes: `docs/server-info.private.md`

Quick checks:

```powershell
git check-ignore -v config.php
git check-ignore -v data/
```

## 2) Sanitize docs

- Don’t include screenshots that may contain emails, tokens, IPs, or passwords.
- Keep operational notes (IPs, usernames) in `docs/server-info.private.md` (ignored).

Branding guardrail: the story page must be titled **“The Chew Story”** everywhere (HTML, nav links, translations). Never revert to “The True Story”.

## 3) Scan the repo for secrets before commit

```powershell
# Look for common secret strings
Select-String -Path . -Recurse -SimpleMatch -List -ErrorAction SilentlyContinue `
  -Pattern "client_secret","webhook_signing","BEGIN PRIVATE KEY","ssh-rsa"

# Ensure git also doesn’t see secrets
git grep -n "client_secret" || echo "OK"
```

## 4) First commit

```powershell
git add -A
git status

git commit -m "Initial public release"
```

## 5) Publish to GitHub

1. Create a new GitHub repository (Public)
2. Add the remote and push:

```powershell
git remote add origin https://github.com/YOUR_USER/YOUR_REPO.git
git branch -M main
git push -u origin main
```

## 6) After publishing

- Verify GitHub does **not** show `config.php` in the file list.
- Consider rotating any secrets that have ever been copied into files that might have been shared.
