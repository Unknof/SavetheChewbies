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

Tiltify does not take a platform fee. The only fees involved are standard payment processing fees (varies by payment method and region).

### Receipts / taxes (wording to use)

Save the Children may provide donation receipts that could be useful for tax purposes, depending on your country’s rules.
Please treat this as “best effort info” and rely on the receipt and local guidance for anything tax-related.

---

## Home page (index.html)

### Section: Incentives (card or callout)
**Heading:** Incentives

**Body (draft):**
Donate to Save the Children and help unlock five community incentives tied to the Baby Chew pet.

**Link text:** See milestones → (links to `donate.html#incentives`)

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

### Section: Dev streams (card)
**Heading:** Dev streams

**Body (draft):**
I’ll be streaming the implementation of these incentives (Discord, and bilibili if possible).
If at least one community milestone gets met, I’ll post the stream links + schedule on the site.

**Schedule note:**
Exact stream times will be posted on the website.

---

## Charities page (charities.html)

### Page intro (lead)
This project is currently focused on a single charity: **Save the Children**.

### Charity listing (current)

**Charity name:** Save the Children

**Short description:**
A well-known charity with a long track record supporting children worldwide.

**Buttons:**
- Donate via Tiltify (this site)
- Official site

### “What verified means here” callout
**Heading:** What “verified” means here

**Body (draft):**
For GDQ-style verification, we need donations to happen on a platform that can notify us (API/webhook) so we can record that donation server-side.

**Note (optional):**
If you want, I can wire this to a specific platform once you pick one.


Links:
- Official site: https://www.savethechildren.org/

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
- stay in sync (same names, same amounts, same status)

These are **campaign milestones** (community unlocks). No personal donor rewards.

Milestones unlock **in order** (1 unlocks 2, 2 unlocks 3, etc). On the website we can keep things a little “mystery/teaser”:
- show all already-unlocked milestones
- always show the *next* milestone
- always show the *final* milestone (Mecha Baby Chew) as a teaser

### Incentive list (draft; fill in amounts + names)

Use this table as the single source-of-truth for what we’re building.

| # | Incentive name | Amount (EUR) | What it unlocks (Baby Chew / Project B) | Status |
|---|---|---:|---|---|
| 1 | Tantrum (knockback reaction) | €351 | When Baby Chew gets knocked back, the baby chewbies throw a little tantrum (harmless / cosmetic). Est: 1–2 hours. | Planned |
| 2 | Playful sparring | €702 | Baby chewbies can have small harmless fights with baby chewbies from other players. Est: 3–4 hours. | Planned |
| 3 | Ice caves (building) | €702 | Baby Chew occasionally builds ice caves. Ice caves produce more baby chewbies on their own. Est: 4–5 hours. | Planned |
| 4 | Nydus teleport (fashionable) | €1053 | Baby Chew + the baby chewbies can teleport to the current hero in a more “fashionable” way: they use the Nydus like Chew does. Est: 5–6 hours. | Planned |
| 5 | Mecha Baby Chew (skin) | €1404 | Add a Mecha Baby Chew skin (similar to an existing Chew skin, but not available for Baby Chew yet). Est: 10–12 hours. | Planned |

**Currency note:** I’m based in Germany, so the target amounts are written in **euros**. If the donation platform requires a different currency (like USD), I’ll set the closest equivalent amounts there.

### Why €351?

€351 comes directly from a Kerrigan Survival tournament we ran on **January 10–11**.
During that event (where players from all over the world competed to be the best Kerrigan Survival player), I revealed Baby Chew to the player base — and across the event, players spawned a total of **351** baby chewbies.

So €351 became the “signature number” for this charity drive.

**Proof screenshots (tournament):**

![Game 1 proof screenshot](../assets/chewbieCountProof/Game1.jpg)

![Game 10 proof screenshot](../assets/chewbieCountProof/Game10.jpg)

### Creator pledge (proof of concept)

In addition to the five community incentives above, I’m also doing a personal pledge:

- I will donate **€10 for every Baby Chew that was used in the tournament**.
- In the tournament, Baby Chew was used **6** times.
- That means I will donate **€60**.

This is a creator-only goal (not something the community has to unlock), and I’ll use it as a proof of concept to make sure the donation + verification flow works end-to-end.

### Short descriptions (for Tiltify + website)

1) **Tantrum (knockback reaction):** If Baby Chew gets knocked back, the baby chewbies throw a little tantrum. Purely for fun/cuteness; no gameplay impact intended.

2) **Playful sparring:** Baby chewbies can start tiny, harmless “fights” with other players’ baby chewbies.

3) **Ice caves (build process):** Baby Chew occasionally builds ice caves. Aim for a nice little “construction” animation and process.

4) **Nydus teleport (fashionable):** Baby Chew and the baby chewbies teleport to the current hero using the Nydus (like Chew does).

5) **Mecha Baby Chew (skin):** Add a Mecha Baby Chew skin (existing for Chew, but not for Baby Chew yet).

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
- Decide the source of truth (Tiltify-first vs repo-first).

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

## Privacy (privacy.html)

### Privacy section
We keep personal data minimal and only store what’s needed to run donation verification.

Contact email for privacy requests: savethechewbies@protonmail.com

Data collected (current behavior):
- A short-lived cookie to remember your most recent verification code (so the Verify page can pre-fill it).
- Server-side verification records keyed by a random relay code (pending/verified/cancelled + timestamps).
- A minimal server log of webhook events for debugging/auditing. This may include donor display name and donation message if Tiltify sends it (and if the donor provided it).

Retention: keep verification records/logs only as long as needed to operate and debug the verification flow.

Deletion requests: email the address above with your verification code and I can remove the related server-side record.


---

## Contact (privacy.html#contact)

### Contact section
Email: savethechewbies@protonmail.com

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
- [ ] Incentives: confirm amounts are correct (EUR totals)
- [ ] Charity list (names, links, short why)
- [ ] Donate page decisions: platform + campaign URLs (Tiltify campaign link)
- [ ] Streams: add Discord invite link + bilibili channel link (only after first milestone is met)
- [ ] Streams: post schedule format (timezone + dates) (only after first milestone is met)
- [ ] Privacy/contact details
- [ ] Home page: pick final incentives link text ("View incentives" vs "See milestones")
