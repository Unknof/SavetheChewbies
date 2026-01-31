import express from 'express';
import { postDiscordMessage } from './services/discord.js';

function getEnv(name, fallback = undefined) {
  const value = process.env[name];
  if (value === undefined || value === '') return fallback;
  return value;
}

function escapeHtml(text) {
  return String(text)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function detectFund(message, { teamTag, prizeTag }) {
  const normalized = String(message ?? '').toLowerCase();
  if (teamTag && normalized.includes(String(teamTag).toLowerCase())) return 'team';
  if (prizeTag && normalized.includes(String(prizeTag).toLowerCase())) return 'prize';
  return 'unknown';
}

function formatDiscordLine(kofiData, fund) {
  const amount = kofiData?.amount ?? '?';
  const currency = kofiData?.currency ?? '';
  const fromName = kofiData?.from_name ?? 'Someone';
  const message = kofiData?.message ? `\nMessage: ${kofiData.message}` : '';

  const fundLabel = fund === 'team' ? 'TEAM FUND' : fund === 'prize' ? 'PRIZE POOL' : 'UNCLASSIFIED';

  return `Ko-fi ${kofiData?.type ?? 'Event'} → ${fundLabel}\nFrom: ${fromName}\nAmount: ${amount} ${currency}${message}`;
}

export function createApp(options = {}) {
  const app = express();

  const kofiVerificationToken =
    options.kofiVerificationToken ?? getEnv('KOFI_VERIFICATION_TOKEN');
  const kofiProfileUrl = options.kofiProfileUrl ?? getEnv('KOFI_PROFILE_URL');
  const discordWebhookUrl =
    options.discordWebhookUrl ?? getEnv('DISCORD_WEBHOOK_URL');

  const teamTag = options.teamTag ?? getEnv('TEAM_TAG', '[TEAM]');
  const prizeTag = options.prizeTag ?? getEnv('PRIZE_TAG', '[PRIZE]');

  const sendDiscord =
    options.sendDiscord ??
    (async (content) => {
      if (!discordWebhookUrl) {
        // eslint-disable-next-line no-console
        console.log('[discord] DISCORD_WEBHOOK_URL not set; would send:', content);
        return;
      }
      await postDiscordMessage({ webhookUrl: discordWebhookUrl, content });
    });

  // Ko-fi webhooks send `application/x-www-form-urlencoded` with `data=<json string>`.
  app.use(
    express.urlencoded({
      extended: true,
    })
  );

  app.get('/', (req, res) => {
    res.type('html').send(`<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>KS Ko-fi Links</title>
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;max-width:900px;margin:40px auto;padding:0 16px;line-height:1.4}
    .card{border:1px solid #ddd;border-radius:12px;padding:16px;margin:12px 0}
    a{color:#0b57d0}
    code{background:#f6f8fa;padding:2px 6px;border-radius:6px}
  </style>
</head>
<body>
  <h1>KS Ko-fi Links</h1>
  <div class="card">
    <h2>Team fund link</h2>
    <a href="/tip/team">/tip/team</a>
  </div>
  <div class="card">
    <h2>Prize pool link</h2>
    <a href="/tip/prize">/tip/prize</a>
  </div>
  <div class="card">
    <h2>Webhook endpoint</h2>
    <code>POST /webhooks/kofi</code>
  </div>
</body>
</html>`);
  });

  app.get('/tip/:fund', (req, res) => {
    const fund = req.params.fund;
    if (fund !== 'team' && fund !== 'prize') return res.status(404).send('Not found');

    const tag = fund === 'team' ? teamTag : prizeTag;
    const title = fund === 'team' ? 'Team Fund' : 'Tournament Prize Pool';
    const profile = kofiProfileUrl ?? 'https://ko-fi.com/';

    res.type('html').send(`<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${escapeHtml(title)} - Ko-fi</title>
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;max-width:900px;margin:40px auto;padding:0 16px;line-height:1.5}
    .card{border:1px solid #ddd;border-radius:12px;padding:16px;margin:12px 0}
    button{padding:10px 14px;border-radius:10px;border:1px solid #ccc;background:#fff;cursor:pointer}
    button:hover{background:#f6f6f6}
    .tag{font-weight:700}
    input{width:100%;padding:10px;border:1px solid #ddd;border-radius:10px;font-family:ui-monospace,Consolas,monospace}
    a{color:#0b57d0}
  </style>
</head>
<body>
  <h1>${escapeHtml(title)}</h1>

  <div class="card">
    <p>To help us track what this goes toward, please include this in your Ko-fi message:</p>
    <p class="tag">${escapeHtml(tag)}</p>
    <input id="msg" readonly value="${escapeHtml(tag)}" />
    <p>
      <button id="copy">Copy message</button>
      <a href="${escapeHtml(profile)}" target="_blank" rel="noreferrer">Open Ko-fi</a>
    </p>
    <p style="color:#555">If Ko-fi ever adds true “prefill message” URL params, we can swap this to one-click prefill. This copy+open method is reliable today.</p>
  </div>

  <div class="card">
    <p><a href="/">Back</a></p>
  </div>

  <script>
    const btn = document.getElementById('copy');
    btn.addEventListener('click', async () => {
      const input = document.getElementById('msg');
      input.select();
      input.setSelectionRange(0, 99999);
      try {
        await navigator.clipboard.writeText(input.value);
        btn.textContent = 'Copied!';
        setTimeout(() => (btn.textContent = 'Copy message'), 1500);
      } catch {
        document.execCommand('copy');
        btn.textContent = 'Copied!';
        setTimeout(() => (btn.textContent = 'Copy message'), 1500);
      }
    });
  </script>
</body>
</html>`);
  });

  // Tiny in-memory de-dupe (Ko-fi may retry on failures)
  const seenMessageIds = new Set();
  const maxSeen = 5000;

  app.post('/webhooks/kofi', async (req, res) => {
    try {
      const raw = req.body?.data;
      if (!raw || typeof raw !== 'string') {
        return res.status(400).json({ ok: false, error: 'Missing form field: data' });
      }

      let data;
      try {
        data = JSON.parse(raw);
      } catch {
        return res.status(400).json({ ok: false, error: 'Invalid JSON in data' });
      }

      if (kofiVerificationToken && data?.verification_token !== kofiVerificationToken) {
        return res.status(401).json({ ok: false, error: 'Invalid verification token' });
      }

      const messageId = data?.message_id;
      if (messageId && seenMessageIds.has(messageId)) {
        return res.status(200).json({ ok: true, deduped: true });
      }

      const fund = detectFund(data?.message, { teamTag, prizeTag });
      const content = formatDiscordLine(data, fund);

      // Respond quickly; do Discord sending async (but awaited here so tests can validate).
      await sendDiscord(content);

      if (messageId) {
        seenMessageIds.add(messageId);
        if (seenMessageIds.size > maxSeen) {
          // crude cleanup: clear set when too large
          seenMessageIds.clear();
        }
      }

      return res.status(200).json({ ok: true });
    } catch (err) {
      // eslint-disable-next-line no-console
      console.error('Ko-fi webhook error:', err);
      return res.status(500).json({ ok: false, error: 'Internal error' });
    }
  });

  app.get('/health', (req, res) => res.json({ ok: true }));

  return app;
}
