# Next steps (donations + verification)

Goal: users click Donate on your site, get redirected to a trusted donation provider, and your site can *verify* donations via webhooks — without you touching payment data.

## Recommended path (this repo already supports it): Tiltify Webhook Relays

1. Confirm production HTTPS + correct site root
   - Ensure `https://savethechew.biz/` serves *this* site (not a hosting panel default).

2. Create Tiltify developer resources
   - App (client credentials)
   - Webhook endpoint (your URL)
   - Webhook signing key (secret)
   - Webhook relay ID

3. Add server-side config
   - Copy `config.example.php` → `config.php` on the server
   - Fill the Tiltify values
   - Ensure PHP can write to `data/`

4. Wire the Donate page
   - Link your Donate button to `tiltify-donate.php?charity=default` (or one per charity)
   - Optionally show the returned verification code to the user for checking status

5. Test end-to-end
   - Use Tiltify’s webhook “Testing” to send a relay test message
   - Make a small real donation and confirm `verify.php?code=...` flips to `verified`

6. Decide what to display publicly
   - Totals only, or totals + recent donations
   - If showing donor names, confirm it’s allowed and document it

## Docs

- See `docs/tiltify-relay-setup.md` for the detailed setup.
- See `docs/donations-and-verification.md` for the options tradeoffs.
