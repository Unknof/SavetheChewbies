# Donations & verification (no payments handled by you)

You said you *do not* want to collect payments yourself. That’s the right call for a small project.

## The key idea

To reliably “verify” donations, the donation must happen on a system that can prove it happened via:
- an API you can query, and/or
- webhooks (the platform notifies your server when a donation occurs).

This is the “GDQ-style” approach.

## Option 1 (MVP): Link out to a campaign page

This is the simplest and usually best first step:
- You create a campaign on a platform (often one campaign per charity).
- Your website links to those campaign donation pages.
- Your website shows totals/donor lists by embedding the platform’s own widgets (or by linking to them).

Pros:
- No backend required.
- No database required.
- Lowest risk.

Cons:
- If you want a custom “verified donor” badge system on *your* site, you’ll need a backend later.

## Option 2: Embedded donations on your site

Many platforms provide an embed widget or donation form you can place on your page.

Reality check:
- Some platforms intentionally prevent iframe embedding (security headers like `X-Frame-Options`).
- A safe fallback is always “click to donate” (link out) even if you also attempt an embed.

## Option 3: Verified donor feed on your site (still no payments)

If your donation platform supports webhooks, you can build:
- a tiny webhook endpoint (PHP works) that receives donation events
- a small database table to store them (MySQL/MariaDB/Postgres all fine)
- a page that displays totals + recent donors

If you want this, a DB becomes useful (not strictly required, but recommended).

## About receipts

Receipt uploads/screenshots are easy to fake.
If you *must* support a fallback, the least-bad approach is manual review plus strict rules, but treat it as secondary.

## Platform suggestion (example)

Tiltify is commonly used for community fundraising and has both an API and webhooks.
Developer docs: https://developers.tiltify.com/

If you pick a platform, I can tailor the integration plan (campaign structure, what gets tracked, and what you can show publicly).
