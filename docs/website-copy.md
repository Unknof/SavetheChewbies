# Website copy (source of truth)

Purpose: keep *all* website text in one place so we can paste/iterate without losing the story.

Status: draft
Last updated: 2026-01-16

---

## Brand

### Name
Save The Chewbies

### Short tagline (header)
Donate to Save the Children (with fun incentives).

### Meta description (SEO)
Donate to Save the Children via Tiltify, with donation incentives that update live.

### One-liner (hero)
Make your donation count.

### Hero paragraph (home page)
Save The Chewbies is a simple hub to help you donate to **Save the Children**.
Donations run through **Tiltify**, so we can verify them via webhook events instead of relying on screenshots.

### Early-stage notice
Note: This is an early setup version. Content and artwork will be refined.

---

## About / Origin story

> This section is meant to answer: “Why does this exist?” and “Who’s behind it?”

### Long-form (draft)
I’m a developer for **Kerrigan Survival**, a StarCraft II Arcade mod (card-game style).
Within that project, I’m responsible for **Baby Chew** (Project B).

I’d been thinking about doing a charity drive for a long time, but I wanted it to be more than “please donate” — I wanted it to be fun, trackable, and transparent.
So I started building something small inside the game: an in-game pet — a cute follower that doesn’t change gameplay in a meaningful way, it’s just meant to be enjoyable.

This “Chew” pet is packed with unique behaviors and abilities (for a pet), and the idea is to tie specific upgrades/unlocks to donation incentives.
At the same time, I wanted the donation side to be honest and verifiable — not based on screenshots and easily faked receipts.
That’s why this project uses **Tiltify** and a webhook: donations can be verified automatically.

### Short-form (about card)
Built by a game developer who wanted a simple, verifiable donation drive with fun incentives.

### Disclaimer (recommended)
“Kerrigan Survival” and “StarCraft II” are referenced as personal background; this site is not affiliated with or endorsed by Blizzard Entertainment.

---

## Why Save the Children

Save the Children is the charity chosen for this project because:

- It’s widely known, so donors don’t have to wonder whether it’s credible.
- It’s straightforward to explain and easy for new donors to recognize.

### Fees / where the money goes (wording to use)

My goal is for donations to go to Save the Children (not to me).

Tiltify typically advertises **no platform fee**, but payment processors may still apply standard processing fees depending on payment method and region.
I’m verifying the exact breakdown for this campaign and will link it here once confirmed.

### Receipts / taxes (wording to use)

Save the Children may provide donation receipts that could be useful for tax purposes, depending on your country’s rules.
Please treat this as “best effort info” and rely on the receipt and local guidance for anything tax-related.

---

## Home page (index.html)

### Section: Incentives (card or callout)
**Heading:** Incentives

**Body (draft):**
Donate to Save the Children and help unlock five community incentives tied to the Baby Chew pet.

**Link text (pick one):**
- View incentives →
- See milestones →

### Section: Vetted charities (card)
**Heading:** Vetted charities

**Body:**
Keep a curated list of organizations you trust. Each charity gets a short “why” and a donate link.

**Link text:** Go to charities →

### Section: Donation verification (card)
**Heading:** Donation verification

**Body (draft):**
Best option at small scale: use a donation platform that provides an API/webhooks (GDQ-style), so we can confirm donations automatically.

**Link text:** See tracking options →

### Section: Transparency (card)
**Heading:** Transparency

**Body (draft):**
We can publish totals by charity and a donor feed (opt-in display names), plus exportable reports.

### Featured GIF captions (replace placeholders)
- Drop your first GIF into `assets/gifs/`
- Then replace this second one

TODO:
- Provide final GIF captions that match your theme/jokes/mood.

---

## Charities page (charities.html)

### Page intro (lead)
Replace these with the organizations you trust. Each entry can link to a verified donation platform page.

### Charity listing template (copy block)
Use this as the text template for each charity card.

**Charity name:** {NAME}

**Short description:**
{WHAT THEY DO. WHY YOU TRUST THEM. IMPACT OF A DONATION.}

**Focus:** {Children / health / education / protection / etc}

**Region:** {Global / US / EU / etc}

**Buttons:**
- Donate via platform
- Official site

### “What verified means here” callout
**Heading:** What “verified” means here

**Body (draft):**
For GDQ-style verification, we need donations to happen on a platform that can notify us (API/webhook) so we can record that donation server-side.

**Note (optional):**
If you want, I can wire this to a specific platform once you pick one.

TODO:
- List your real charities (name + why + links).

---

## Donate page (donate.html)

### Page intro (lead)
The goal is to make donating easy, fun (with incentives), and reliably verifiable.

### Section: Recommended approach (GDQ-style)
**Heading:** Recommended approach (GDQ-style)

**Body:**
Use a donation platform that supports campaigns + an API (and ideally webhooks).
Donors donate on the platform, and we fetch/receive donation events to display totals and verify donors.

#### Subsection: How this appears on your website
- **Safest MVP:** “Donate” buttons that link out to each campaign page.
- **Optional:** embed a platform widget (if the platform allows embedding).
- **Verified tracking:** later, add a small server endpoint to receive webhooks and store events.

#### Subsection: Why this is best at small scale
- No handling of card data on your server (lower risk and complexity).
- Verification is automatic: we record a donation event from the platform.

**Callout:**
Next decision: pick the platform you want to use for donations. Once you pick it, I’ll wire the “Donate” buttons and implement verified tracking.

### Section: Start a verified donation (Tiltify Relays)
**Heading:** Start a verified donation (Tiltify Relays)

**Body:**
Once configured, this button will generate a one-time verification code and send you to the donation form.
After donating, come back and check your code.

**Buttons:**
- Start verified donation
- Check verification status
- Admin panel

**Setup note:**
Setup guide: `docs/tiltify-relay-setup.md`

---

## Incentives (core feature)

Goal: create **five** incentives that:
- exist on the Tiltify campaign (so donors see them while donating)
- are also shown on this website
- stay in sync (same names, same dollar amounts, same status)

### Incentive list (draft; fill in amounts + names)

Use this table as the single source-of-truth for what we’re building.

| # | Incentive name | Amount (USD) | What it unlocks (Baby Chew / Project B) | Status |
|---|---|---:|---|---|
| 1 | (TBD) | (TBD) | (TBD) | Planned |
| 2 | (TBD) | (TBD) | (TBD) | Planned |
| 3 | (TBD) | (TBD) | (TBD) | Planned |
| 4 | (TBD) | (TBD) | (TBD) | Planned |
| 5 | (TBD) | (TBD) | (TBD) | Planned |

### How incentives stay updated (implementation note)
There are two viable ways to keep Tiltify + the website consistent:

1) **Tiltify is the source of truth (recommended):**
	- You create/edit incentives on Tiltify.
	- This site fetches incentive data (API) and renders it.
	- Result: one place to update; site always matches.

2) **This repo is the source of truth:**
	- You edit the table above.
	- We push the same incentive definitions to Tiltify via API.
	- Result: best for automation, but requires careful API permissions.

TODO:
- Confirm whether your five incentives are **milestones** (campaign total unlocks) or **donor rewards** (individual donation amount unlocks).
- Decide the source of truth (Tiltify-first vs repo-first).

### Section: Embed (optional)
**Heading:** Embed (optional)

**Body:**
Some platforms provide an embed snippet (script or iframe) for a campaign.
If embedding is blocked by the platform, the reliable fallback is always linking out.

**Placeholder note:**
When you have an embed snippet: paste it here. (We’ll replace this placeholder block with the platform-provided code.)

### Section: Options (A/B/C cards)
**Option A: Direct to charity sites**
Lowest effort, but hardest to verify automatically because every charity has different systems.
Verification usually becomes manual (receipts) unless each charity offers an API.

**Option B: Platform per charity**
Create a campaign page per charity (on one platform). Your site links to each campaign.
We can track totals per charity by querying the platform’s API.

**Option C: You collect money**
Most control, best tracking, but it means you become the payment receiver and must handle accounting, payouts, refunds, and legal/compliance responsibilities.

---

## Privacy (donate.html#privacy)

### Privacy section (draft)
If we display a donor feed, we should make it opt-in (display name) and keep personal data minimal.
A basic privacy policy and data retention notes should be added before launch.

TODO (fill these in before launch):
- Contact email for privacy requests
- Data collected (verification code, donation event fields, IP logs if any)
- Retention period
- How to request deletion

---

## Contact (donate.html#contact)

### Contact section (draft)
Add an email address or contact form later. For now you can put a simple email link here.

TODO:
- Preferred contact email address
- Whether you want a Discord/Twitter link here

---

## FAQ (recommended additions)

### What is “verified” on this site?
Verified means we received a donation event from a platform API/webhook (not a screenshot).

### Do you handle my payment information?
No. Donations happen on the donation platform or charity site.

### Can I donate anonymously?
Yes — if we display names at all, it should be opt-in.

---

## Content checklist (so nothing gets forgotten)

- [ ] Final origin story (1–3 paragraphs)
- [ ] Five incentives: name + amount + unlock description
- [ ] Charity list (names, links, short why)
- [ ] Donate page decisions: platform + campaign URLs
- [ ] Privacy/contact details
- [ ] Replace GIF placeholders + captions
